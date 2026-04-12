<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["erro" => "Método inválido"]);
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// verificar se ja tem pulseira
$sql_check = "SELECT id FROM pulseiras WHERE usuario_id = ?";
$stmt_check = mysqli_prepare($conexao, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $usuario_id);
mysqli_stmt_execute($stmt_check);
$resultado_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($resultado_check) > 0) {
    mysqli_stmt_close($stmt_check);
    echo json_encode(["erro" => "Você já possui uma pulseira"]);
    exit();
}
mysqli_stmt_close($stmt_check);

// gerar codigo rfid
$codigo_rfid = "RFID-" . strtoupper(uniqid());

// data de assinatura: hoje ate 30 dias
$assinatura_inicio = date("Y-m-d");
$assinatura_fim = date("Y-m-d", strtotime("+30 days"));

$sql = "INSERT INTO pulseiras (usuario_id, codigo_rfid, saldo, status, assinatura_inicio, assinatura_fim) VALUES (?, ?, 0.00, 'ativa', ?, ?)";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "isss", $usuario_id, $codigo_rfid, $assinatura_inicio, $assinatura_fim);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    echo json_encode(["sucesso" => true, "codigo_rfid" => $codigo_rfid, "assinatura_fim" => $assinatura_fim]);
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    echo json_encode(["erro" => "Erro ao assinar pulseira"]);
}
?>
