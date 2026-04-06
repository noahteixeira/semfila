<?php
include("../auth_check.php");
include("../conexao.php");

// verificar se é admin
if ($_SESSION["usuario_tipo"] != "admin") {
    header("Location: ../../frontend/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST["id"];
    $versao = trim($_POST["versao"]);
    $data = $_POST["data"];
    $descricao = trim($_POST["descricao"]);
    $autor = $_SESSION["usuario_nome"];

    // validações
    if (empty($versao) || empty($data) || empty($descricao)) {
        header("Location: ../../frontend/admin/editar_changelog.html?id=$id&erro=1");
        exit();
    }

    // atualizar changelog
    $sql = "UPDATE changelogs SET versao = ?, data = ?, descricao = ?, autor = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $versao, $data, $descricao, $autor, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/admin/listar_changelog.html?sucesso=2");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/admin/editar_changelog.html?id=$id&erro=db");
        exit();
    }
} else {
    // GET: buscar dados do changelog para edição
    $id = (int)$_GET["id"];

    $sql = "SELECT id, versao, data, descricao, autor FROM changelogs WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $changelog = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);

    if ($changelog) {
        echo json_encode($changelog);
    } else {
        echo json_encode(["erro" => "Changelog não encontrado"]);
    }
}
?>