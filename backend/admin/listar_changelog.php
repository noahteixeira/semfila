<?php
include("../auth_check.php");
include("../conexao.php");

// verificar se é admin
if ($_SESSION["usuario_tipo"] != "admin") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

// buscar todos os changelogs
$sql = "SELECT id, versao, data, descricao, autor, criado_em FROM changelogs ORDER BY data DESC";

$resultado = mysqli_query($conexao, $sql);

$changelogs = [];
while ($changelog = mysqli_fetch_assoc($resultado)) {
    $changelogs[] = $changelog;
}

mysqli_close($conexao);

echo json_encode($changelogs);
?>