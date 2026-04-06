<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "funcionario") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$rfid = $_GET["rfid"];

if (empty($rfid)) {
    echo json_encode(["erro" => "RFID não informado"]);
    exit();
}

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

mysqli_close($conexao);
echo json_encode(["nome" => $pulseira["nome"], "saldo" => $pulseira["saldo"]]);
?>