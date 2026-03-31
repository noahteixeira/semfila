<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../frontend/login.html");
    exit();
}

// verificar contrato do gestor
if ($_SESSION["usuario_tipo"] == "gestor") {
    include("conexao.php");

    $sql = "SELECT status, data_vencimento FROM contratos_gestores WHERE usuario_id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $contrato = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if (!$contrato || $contrato["status"] == "inativo" || $contrato["data_vencimento"] < date("Y-m-d")) {
        session_destroy();
        header("Location: ../frontend/login.html?erro=1");
        exit();
    }
}
?>
