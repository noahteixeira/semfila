<?php
include("auth_check.php");
include("conexao.php");

// verificar se é baladeiro
if ($_SESSION["usuario_tipo"] != "baladeiro") {
    header("Location: ../frontend/login.html");
    exit();
}

// desativar conta (soft delete)
$sql = "UPDATE usuarios SET ativo = 0 WHERE id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);

if (mysqli_stmt_execute($stmt)) {
    // logout
    session_destroy();
    header("Location: ../frontend/login.html?desativada=1");
} else {
    header("Location: ../frontend/area_baladeiro.php?erro=1");
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);
exit();
?>