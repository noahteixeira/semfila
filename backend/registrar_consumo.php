<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "funcionario" && $_SESSION["usuario_tipo"] != "gestor") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["erro" => "Método inválido"]);
    exit();
}

$rfid = $_POST["rfid"];
$evento_id = (int)$_POST["evento_id"];
$produtos_json = $_POST["produtos_json"];
$valor_total = (float)$_POST["valor_total"];

$produtos = json_decode($produtos_json, true);

if (empty($rfid) || $evento_id <= 0 || empty($produtos) || $valor_total <= 0) {
    echo json_encode(["erro" => "Dados incompletos"]);
    exit();
}

if ($_SESSION["usuario_tipo"] == "funcionario") {
    $sql_balada = "SELECT balada_id FROM usuarios WHERE id = ?";
    $stmt_balada = mysqli_prepare($conexao, $sql_balada);
    mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt_balada);
    $resultado_balada = mysqli_stmt_get_result($stmt_balada);
    $usuario = mysqli_fetch_assoc($resultado_balada);
    mysqli_stmt_close($stmt_balada);

    $balada_id = (int)$usuario["balada_id"];
} else {
    $sql_balada = "SELECT id FROM baladas WHERE gestor_id = ? AND ativo = 1";
    $stmt_balada = mysqli_prepare($conexao, $sql_balada);
    mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt_balada);
    $resultado_balada = mysqli_stmt_get_result($stmt_balada);
    $balada = mysqli_fetch_assoc($resultado_balada);
    mysqli_stmt_close($stmt_balada);

    $balada_id = (int)$balada["id"];
}

if ($balada_id <= 0) {
    echo json_encode(["erro" => "Balada não encontrada"]);
    exit();
}

$sql_evento = "SELECT id FROM eventos WHERE id = ? AND balada_id = ?";
$stmt_evento = mysqli_prepare($conexao, $sql_evento);
mysqli_stmt_bind_param($stmt_evento, "ii", $evento_id, $balada_id);
mysqli_stmt_execute($stmt_evento);
$resultado_evento = mysqli_stmt_get_result($stmt_evento);
$evento = mysqli_fetch_assoc($resultado_evento);
mysqli_stmt_close($stmt_evento);

if (!$evento) {
    echo json_encode(["erro" => "Evento inválido para esta balada"]);
    exit();
}

// buscar pulseira pelo RFID
$sql = "SELECT p.id, p.saldo, u.nome FROM pulseiras p INNER JOIN usuarios u ON p.usuario_id = u.id WHERE p.codigo_rfid = ? AND p.status = 'ativa'";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "s", $rfid);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$pulseira = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$pulseira) {
    echo json_encode(["erro" => "Pulseira não encontrada ou inativa"]);
    exit();
}

if ($pulseira["saldo"] < $valor_total) {
    echo json_encode(["erro" => "Saldo insuficiente"]);
    exit();
}

// iniciar transacao
mysqli_begin_transaction($conexao);

// debitar saldo da pulseira
$sql = "UPDATE pulseiras SET saldo = saldo - ? WHERE id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "di", $valor_total, $pulseira["id"]);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// registrar transacao de consumo
$sql_trans = "INSERT INTO transacoes_saldo (pulseira_id, tipo, valor, descricao) VALUES (?, 'consumo', ?, 'Consumo no bar')";
$stmt_trans = mysqli_prepare($conexao, $sql_trans);
mysqli_stmt_bind_param($stmt_trans, "id", $pulseira["id"], $valor_total);
mysqli_stmt_execute($stmt_trans);
mysqli_stmt_close($stmt_trans);

// inserir consumo_bar
$sql = "INSERT INTO consumos_bar (evento_id, pulseira_id, funcionario_id, valor_total) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "iiid", $evento_id, $pulseira["id"], $_SESSION["usuario_id"], $valor_total);
mysqli_stmt_execute($stmt);
$consumo_id = mysqli_insert_id($conexao);
mysqli_stmt_close($stmt);

// inserir itens do consumo
foreach ($produtos as $produto) {
    $sql = "INSERT INTO itens_consumo (consumo_id, produto, quantidade, valor_unitario) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "isid", $consumo_id, $produto["nome"], $produto["quantidade"], $produto["valor_unitario"]);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// confirmar transacao
mysqli_commit($conexao);

$novo_saldo = $pulseira["saldo"] - $valor_total;

mysqli_close($conexao);
echo json_encode(["sucesso" => true, "mensagem" => "Consumo registrado com sucesso", "novo_saldo" => $novo_saldo]);
?>