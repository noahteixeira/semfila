<?php
include("auth_check.php");
include("conexao.php");

$balada_nome = "";

if ($_SESSION["usuario_tipo"] == "gestor") {
    $sql = "SELECT nome FROM baladas WHERE gestor_id = ? ORDER BY ativo DESC, id DESC LIMIT 1";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $balada = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if ($balada) {
        $balada_nome = $balada["nome"];
    }
} elseif ($_SESSION["usuario_tipo"] == "funcionario") {
    $sql = "SELECT b.nome FROM usuarios u INNER JOIN baladas b ON b.id = u.balada_id WHERE u.id = ? LIMIT 1";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $balada = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if ($balada) {
        $balada_nome = $balada["nome"];
    }
}

mysqli_close($conexao);

echo json_encode([
    "balada" => $balada_nome
]);
?>