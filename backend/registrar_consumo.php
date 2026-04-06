<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "funcionario") {
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