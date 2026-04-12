<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

$sql = "SELECT id, codigo_rfid, saldo, status, assinatura_inicio, assinatura_fim, criado_em FROM pulseiras WHERE usuario_id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$pulseira = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);
mysqli_close($conexao);

if (!$pulseira) {
    echo json_encode(["erro" => "Você não possui pulseira"]);
    exit();
}

echo json_encode($pulseira);
?>
