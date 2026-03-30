<?php
include("auth_check.php");
include("conexao.php");

// verificar se é baladeiro
if ($_SESSION["usuario_tipo"] != "baladeiro") {
    header("Location: ../frontend/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $data_nascimento = $_POST["data_nascimento"];
    $senha = $_POST["senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if (empty($nome) || empty($data_nascimento)) {
        header("Location: ../frontend/editar_baladeiro.html?erro=1");
        exit();
    }

    // verificar idade minima de 16 anos
    $nascimento = new DateTime($data_nascimento);
    $hoje = new DateTime();
    $idade = $hoje->diff($nascimento)->y;

    if ($idade < 16) {
        header("Location: ../frontend/editar_baladeiro.html?erro=2");
        exit();
    }

    // senha minimo 6 caracteres
    if (!empty($senha) && strlen($senha) < 6) {
        header("Location: ../frontend/editar_baladeiro.html?erro=3");
        exit();
    }

    // confirmar senha
    if ($senha != $confirmar_senha) {
        header("Location: ../frontend/editar_baladeiro.html?erro=4");
        exit();
    }

    // upload da foto de perfil se enviada
    $foto_perfil = null;
    if (isset($_FILES["foto_perfil"]) && $_FILES["foto_perfil"]["error"] == 0) {
        $extensoes_foto = ["jpg", "jpeg", "png"];
        $extensao = strtolower(pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION));
        $tamanho = $_FILES["foto_perfil"]["size"];

        if (!in_array($extensao, $extensoes_foto) || $tamanho > 2 * 1024 * 1024) {
            header("Location: ../frontend/editar_baladeiro.html?erro=6");
            exit();
        }

        $pasta_fotos = "../uploads/fotos/";
        if (!is_dir($pasta_fotos)) {
            mkdir($pasta_fotos, 0755, true);
        }

        $nome_arquivo = uniqid() . "." . $extensao;
        move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $pasta_fotos . $nome_arquivo);
        $foto_perfil = "uploads/fotos/" . $nome_arquivo;
    }

    // upload do documento se enviado
    $documento_url = null;
    if (isset($_FILES["documento"]) && $_FILES["documento"]["error"] == 0) {
        $extensoes_doc = ["jpg", "jpeg", "png", "pdf"];
        $extensao = strtolower(pathinfo($_FILES["documento"]["name"], PATHINFO_EXTENSION));
        $tamanho = $_FILES["documento"]["size"];

        if (!in_array($extensao, $extensoes_doc) || $tamanho > 5 * 1024 * 1024) {
            header("Location: ../frontend/editar_baladeiro.html?erro=7");
            exit();
        }

        $pasta_docs = "../uploads/documentos/";
        if (!is_dir($pasta_docs)) {
            mkdir($pasta_docs, 0755, true);
        }

        $nome_arquivo = uniqid() . "." . $extensao;
        move_uploaded_file($_FILES["documento"]["tmp_name"], $pasta_docs . $nome_arquivo);
        $documento_url = "uploads/documentos/" . $nome_arquivo;
    }

    // atualizar dados
    $sql = "UPDATE usuarios SET nome = ?, data_nascimento = ?";
    $params = [$nome, $data_nascimento];
    $types = "ss";

    if (!empty($senha)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql .= ", senha = ?";
        $params[] = $senha_hash;
        $types .= "s";
    }

    if ($foto_perfil) {
        $sql .= ", foto_perfil = ?";
        $params[] = $foto_perfil;
        $types .= "s";
    }

    if ($documento_url) {
        $sql .= ", documento_url = ?";
        $params[] = $documento_url;
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    $params[] = $_SESSION["usuario_id"];
    $types .= "i";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../frontend/editar_baladeiro.html?sucesso=1");
    } else {
        header("Location: ../frontend/editar_baladeiro.html?erro=5");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    exit();

} else {
    header("Location: ../frontend/editar_baladeiro.html");
    exit();
}
?>