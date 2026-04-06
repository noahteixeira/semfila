<?php
include("../auth_check.php");
include("../conexao.php");

// verificar se é admin
if ($_SESSION["usuario_tipo"] != "admin") {
    header("Location: ../../frontend/login.html");
    exit();
}

$id = (int)$_GET["id"];

// deletar changelog
$sql = "DELETE FROM changelogs WHERE id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    header("Location: ../../frontend/admin/listar_changelog.html?sucesso=3");
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    header("Location: ../../frontend/admin/listar_changelog.html?erro=db");
}
exit();
?>