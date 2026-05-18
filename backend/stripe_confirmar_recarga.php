<?php
include("auth_check.php");
include("conexao.php");
include("stripe_config.php");

header("Content-Type: application/json; charset=utf-8");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    echo json_encode(["erro" => "Método inválido"]);
    exit();
}

if (empty($STRIPE_SECRET_KEY) || $STRIPE_SECRET_KEY == "SUA_CHAVE_SECRETA_STRIPE_AQUI") {
    echo json_encode(["erro" => "Stripe não configurada no servidor"]);
    exit();
}

$session_id = "";
if (isset($_GET["session_id"])) {
    $session_id = trim($_GET["session_id"]);
}

if ($session_id == "") {
    echo json_encode(["erro" => "Session ID não informado"]);
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// buscar pulseira do usuario
$sql = "SELECT id, saldo, status FROM pulseiras WHERE usuario_id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$pulseira = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$pulseira) {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Você não possui pulseira"]);
    exit();
}

if ($pulseira["status"] != "ativa") {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Pulseira inativa"]);
    exit();
}

// evitar duplicidade: se já lançou essa session no histórico, não soma de novo
$descricao = "Recarga Stripe #" . $session_id;
$sql_check = "SELECT id FROM transacoes_saldo WHERE pulseira_id = ? AND tipo = 'recarga' AND descricao = ?";
$stmt_check = mysqli_prepare($conexao, $sql_check);
mysqli_stmt_bind_param($stmt_check, "is", $pulseira["id"], $descricao);
mysqli_stmt_execute($stmt_check);
$res_check = mysqli_stmt_get_result($stmt_check);
$ja_processada = mysqli_fetch_assoc($res_check);
mysqli_stmt_close($stmt_check);

if ($ja_processada) {
    mysqli_close($conexao);
    echo json_encode(["sucesso" => true, "novo_saldo" => $pulseira["saldo"], "ja_processada" => true]);
    exit();
}

// consultar sessão na Stripe
$url = "https://api.stripe.com/v1/checkout/sessions/" . urlencode($session_id);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $STRIPE_SECRET_KEY
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if (!empty($curl_error)) {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Erro de conexão com Stripe"]);
    exit();
}

$data = json_decode($response, true);

if ($http_code < 200 || $http_code >= 300) {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Não foi possível validar o pagamento"]);
    exit();
}

if (!isset($data["payment_status"]) || $data["payment_status"] != "paid") {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Pagamento ainda não confirmado"]);
    exit();
}

if (!isset($data["metadata"]["usuario_id"]) || intval($data["metadata"]["usuario_id"]) != $usuario_id) {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Sessão inválida para este usuário"]);
    exit();
}

$valor_centavos = 0;
if (isset($data["amount_total"])) {
    $valor_centavos = intval($data["amount_total"]);
}

if ($valor_centavos <= 0) {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Valor do pagamento inválido"]);
    exit();
}

$valor = $valor_centavos / 100;
$novo_saldo = $pulseira["saldo"] + $valor;

$sql_update = "UPDATE pulseiras SET saldo = ? WHERE id = ?";
$stmt_update = mysqli_prepare($conexao, $sql_update);
mysqli_stmt_bind_param($stmt_update, "di", $novo_saldo, $pulseira["id"]);
$ok_update = mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);

if (!$ok_update) {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Erro ao atualizar saldo"]);
    exit();
}

$sql_trans = "INSERT INTO transacoes_saldo (pulseira_id, tipo, valor, descricao) VALUES (?, 'recarga', ?, ?)";
$stmt_trans = mysqli_prepare($conexao, $sql_trans);
mysqli_stmt_bind_param($stmt_trans, "ids", $pulseira["id"], $valor, $descricao);
$ok_trans = mysqli_stmt_execute($stmt_trans);
mysqli_stmt_close($stmt_trans);

mysqli_close($conexao);

if (!$ok_trans) {
    echo json_encode(["erro" => "Saldo atualizado, mas falhou no histórico"]);
    exit();
}

echo json_encode(["sucesso" => true, "novo_saldo" => $novo_saldo]);
?>
