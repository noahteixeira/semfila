<?php
include("auth_check.php");
include("conexao.php");
include("stripe_config.php");

header("Content-Type: application/json; charset=utf-8");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["erro" => "Método inválido"]);
    exit();
}

if (empty($STRIPE_SECRET_KEY) || $STRIPE_SECRET_KEY == "SUA_CHAVE_SECRETA_STRIPE_AQUI") {
    echo json_encode(["erro" => "Stripe não configurada no servidor"]);
    exit();
}

$valor = 0;
if (isset($_POST["valor"])) {
    $valor = floatval($_POST["valor"]);
}

if ($valor <= 0) {
    echo json_encode(["erro" => "Valor inválido"]);
    exit();
}

$usuario_id = $_SESSION["usuario_id"];
$origem = "pulseira";
if (isset($_POST["origem"])) {
    $origem = trim($_POST["origem"]);
}

if ($origem != "pulseira" && $origem != "recarregar") {
    $origem = "pulseira";
}

// validar pulseira ativa
$sql = "SELECT id, status FROM pulseiras WHERE usuario_id = ?";
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

// Stripe usa centavos (inteiro)
$valor_centavos = (int) round($valor * 100);
if ($valor_centavos < 100) {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Valor mínimo: R$ 1,00"]);
    exit();
}

$pagina_retorno = "pulseira.html";
if ($origem == "recarregar") {
    $pagina_retorno = "recarregar_saldo.html";
}

$dominio = "http://localhost/semfila/frontend/" . $pagina_retorno;
$success_url = $dominio . "?pagamento=sucesso&session_id={CHECKOUT_SESSION_ID}";
$cancel_url = $dominio . "?pagamento=cancelado";

$post_fields = [
    "mode" => "payment",
    "success_url" => $success_url,
    "cancel_url" => $cancel_url,
    "line_items[0][price_data][currency]" => "brl",
    "line_items[0][price_data][product_data][name]" => "Recarga de saldo SemFila",
    "line_items[0][price_data][unit_amount]" => $valor_centavos,
    "line_items[0][quantity]" => 1,
    "metadata[usuario_id]" => (string)$usuario_id,
    "metadata[origem]" => $origem
];

$ch = curl_init("https://api.stripe.com/v1/checkout/sessions");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $STRIPE_SECRET_KEY,
    "Content-Type: application/x-www-form-urlencoded"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

mysqli_close($conexao);

if (!empty($curl_error)) {
    echo json_encode(["erro" => "Erro de conexão com Stripe"]);
    exit();
}

$data = json_decode($response, true);

if ($http_code < 200 || $http_code >= 300 || !isset($data["url"])) {
    $mensagem = "Erro ao criar checkout";
    if (isset($data["error"]["message"])) {
        $mensagem = $data["error"]["message"];
    }
    echo json_encode(["erro" => $mensagem]);
    exit();
}

echo json_encode([
    "sucesso" => true,
    "checkout_url" => $data["url"]
]);
?>
