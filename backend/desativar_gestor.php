<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "admin") {
    header("Location: ../frontend/login.html");
    exit();
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    // desativar contrato
    $sql = "UPDATE contratos_gestores SET status = 'inativo' WHERE usuario_id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // desativar usuario
    $sql_usuario = "UPDATE usuarios SET ativo = 0 WHERE id = ? AND tipo = 'gestor'";
    $stmt_usuario = mysqli_prepare($conexao, $sql_usuario);
    mysqli_stmt_bind_param($stmt_usuario, "i", $id);
    mysqli_stmt_execute($stmt_usuario);
    mysqli_stmt_close($stmt_usuario);

    mysqli_close($conexao);

    header("Location: ../frontend/listar_gestores.html?desativado=1");
    exit();

} else {
    header("Location: ../frontend/listar_gestores.html");
    exit();
}
?>
