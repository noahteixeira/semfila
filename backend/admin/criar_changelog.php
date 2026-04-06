<?php
include("../auth_check.php");
include("../conexao.php");

// verificar se é admin
if ($_SESSION["usuario_tipo"] != "admin") {
    header("Location: ../../frontend/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $versao = trim($_POST["versao"]);
    $data = $_POST["data"];
    $descricao = trim($_POST["descricao"]);
    $autor = $_SESSION["usuario_nome"];

    // validações
    if (empty($versao) || empty($data) || empty($descricao)) {
        header("Location: ../../frontend/admin/criar_changelog.html?erro=1");
        exit();
    }

    // inserir changelog
    $sql = "INSERT INTO changelogs (versao, data, descricao, autor) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $versao, $data, $descricao, $autor);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/admin/listar_changelog.html?sucesso=1");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/admin/criar_changelog.html?erro=db");
        exit();
    }
} else {
    header("Location: ../../frontend/admin/listar_changelog.html");
    exit();
}
?>