<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "gestor") {
    header("Location: ../frontend/login.html");
    exit();
}

// verificar se gestor tem balada cadastrada
$sql_balada = "SELECT id FROM baladas WHERE gestor_id = ? AND ativo = 1";
$stmt_balada = mysqli_prepare($conexao, $sql_balada);
mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_balada);
$resultado_balada = mysqli_stmt_get_result($stmt_balada);
$balada = mysqli_fetch_assoc($resultado_balada);
mysqli_stmt_close($stmt_balada);

if (!$balada) {
    header("Location: ../frontend/cadastrar_funcionario.html?erro=balada");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);

    if (empty($nome) || empty($email)) {
        header("Location: ../frontend/cadastrar_funcionario.html?erro=1");
        exit();
    }

    // verificar se email ja existe
    $sql_check = "SELECT id FROM usuarios WHERE email = ?";
    $stmt_check = mysqli_prepare($conexao, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    $resultado_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($resultado_check) > 0) {
        header("Location: ../frontend/cadastrar_funcionario.html?erro=email");
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // gerar senha automatica
    $senha_gerada = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
    $senha_hash = password_hash($senha_gerada, PASSWORD_DEFAULT);

    // inserir funcionario
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo, balada_id) VALUES (?, ?, ?, 'funcionario', ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $nome, $email, $senha_hash, $balada["id"]);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../frontend/cadastrar_funcionario.html?sucesso=1&senha=" . urlencode($senha_gerada));
    } else {
        header("Location: ../frontend/cadastrar_funcionario.html?erro=db");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    exit();

} else {
    header("Location: ../frontend/cadastrar_funcionario.html");
    exit();
}
?>
