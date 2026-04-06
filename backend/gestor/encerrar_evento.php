<?php
include("../auth_check.php");
include("../conexao.php");

// verificar se é gestor
if ($_SESSION["usuario_tipo"] != "gestor") {
    header("Location: ../../frontend/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $evento_id = (int)$_POST["evento_id"];
    $acao = $_POST["acao"]; // 'encerrar' ou 'cancelar'

    if ($acao != "encerrar" && $acao != "cancelar") {
        header("Location: ../../frontend/gestor/listar_eventos.html?erro=acao");
        exit();
    }

    $novo_status = ($acao == "encerrar") ? "encerrado" : "cancelado";

    // verificar se evento pertence ao gestor e está ativo
    $sql_verificar = "SELECT e.id FROM eventos e INNER JOIN baladas b ON e.balada_id = b.id WHERE e.id = ? AND b.gestor_id = ? AND b.ativo = 1 AND e.status = 'ativo'";
    $stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "ii", $evento_id, $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
    mysqli_stmt_close($stmt_verificar);

    if (mysqli_num_rows($resultado_verificar) == 0) {
        header("Location: ../../frontend/gestor/listar_eventos.html?erro=acesso");
        exit();
    }

    // atualizar status
    $sql = "UPDATE eventos SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "si", $novo_status, $evento_id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/gestor/listar_eventos.html?sucesso=3");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/gestor/listar_eventos.html?erro=db");
        exit();
    }
} else {
    header("Location: ../../frontend/gestor/listar_eventos.html");
    exit();
}
?>