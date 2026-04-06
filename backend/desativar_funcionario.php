<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "gestor") {
    header("Location: ../frontend/login.html");
    exit();
}

if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    // verificar se gestor tem balada
    $sql_balada = "SELECT id FROM baladas WHERE gestor_id = ? AND ativo = 1";
    $stmt_balada = mysqli_prepare($conexao, $sql_balada);
    mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt_balada);
    $resultado_balada = mysqli_stmt_get_result($stmt_balada);
    $balada = mysqli_fetch_assoc($resultado_balada);
    mysqli_stmt_close($stmt_balada);

    if (!$balada) {
        header("Location: ../frontend/listar_funcionarios.html?erro=balada");
        exit();
    }

    // verificar se funcionario pertence a balada do gestor
    $sql_verificar = "SELECT id FROM usuarios WHERE id = ? AND tipo = 'funcionario' AND balada_id = ?";
    $stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "ii", $id, $balada["id"]);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
    mysqli_stmt_close($stmt_verificar);

    if (mysqli_num_rows($resultado_verificar) == 0) {
        header("Location: ../frontend/listar_funcionarios.html?erro=acesso");
        exit();
    }

    // desativar funcionario
    $sql = "UPDATE usuarios SET ativo = 0 WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_close($conexao);
    header("Location: ../frontend/listar_funcionarios.html?desativado=1");
    exit();

} else {
    header("Location: ../frontend/listar_funcionarios.html");
    exit();
}
?>
