<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../frontend/login.html");
    exit();
}

include("conexao.php");

$sql_usuario = "SELECT ativo, tipo FROM usuarios WHERE id = ?";
$stmt_usuario = mysqli_prepare($conexao, $sql_usuario);
mysqli_stmt_bind_param($stmt_usuario, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_usuario);
$resultado_usuario = mysqli_stmt_get_result($stmt_usuario);
$usuario_logado = mysqli_fetch_assoc($resultado_usuario);
mysqli_stmt_close($stmt_usuario);

if (!$usuario_logado || $usuario_logado["ativo"] == 0) {
    session_destroy();
    header("Location: ../frontend/login.html");
    exit();
}

$_SESSION["usuario_tipo"] = $usuario_logado["tipo"];

// verificar contrato do gestor
if ($_SESSION["usuario_tipo"] == "gestor") {
    $sql = "SELECT status, data_vencimento FROM contratos_gestores WHERE usuario_id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $contrato = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if (!$contrato || $contrato["status"] == "inativo" || $contrato["data_vencimento"] < date("Y-m-d")) {
        session_destroy();
        header("Location: ../frontend/login.html?contrato=1");
        exit();
    }
}
?>
