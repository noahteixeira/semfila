<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "admin") {
    header("Location: ../frontend/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $cnpj = $_POST["cnpj"];
    $razao_social = $_POST["razao_social"];
    $data_inicio = $_POST["data_inicio"];
    $data_vencimento = $_POST["data_vencimento"];
    $observacoes = $_POST["observacoes"];

    if (empty($nome) || empty($email) || empty($cnpj) || empty($razao_social) || empty($data_inicio) || empty($data_vencimento)) {
        header("Location: ../frontend/cadastrar_gestor.html?erro=1");
        exit();
    }

    // validar CNPJ (14 digitos numericos)
    $cnpj_limpo = preg_replace("/\D/", "", $cnpj);
    if (strlen($cnpj_limpo) != 14) {
        header("Location: ../frontend/cadastrar_gestor.html?erro=2");
        exit();
    }

    // verificar se email ja existe
    $sql_check = "SELECT id FROM usuarios WHERE email = ?";
    $stmt_check = mysqli_prepare($conexao, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    $resultado_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($resultado_check) > 0) {
        header("Location: ../frontend/cadastrar_gestor.html?erro=3");
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // verificar se CNPJ ja existe
    $sql_check_cnpj = "SELECT id FROM contratos_gestores WHERE cnpj = ?";
    $stmt_check_cnpj = mysqli_prepare($conexao, $sql_check_cnpj);
    mysqli_stmt_bind_param($stmt_check_cnpj, "s", $cnpj_limpo);
    mysqli_stmt_execute($stmt_check_cnpj);
    $resultado_check_cnpj = mysqli_stmt_get_result($stmt_check_cnpj);

    if (mysqli_num_rows($resultado_check_cnpj) > 0) {
        header("Location: ../frontend/cadastrar_gestor.html?erro=4");
        exit();
    }
    mysqli_stmt_close($stmt_check_cnpj);

    // gerar senha automatica
    $senha_gerada = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
    $senha_hash = password_hash($senha_gerada, PASSWORD_DEFAULT);

    // inserir usuario
    $sql_usuario = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'gestor')";
    $stmt_usuario = mysqli_prepare($conexao, $sql_usuario);
    mysqli_stmt_bind_param($stmt_usuario, "sss", $nome, $email, $senha_hash);

    if (!mysqli_stmt_execute($stmt_usuario)) {
        header("Location: ../frontend/cadastrar_gestor.html?erro=5");
        exit();
    }

    $usuario_id = mysqli_insert_id($conexao);
    mysqli_stmt_close($stmt_usuario);

    // inserir contrato
    $sql_contrato = "INSERT INTO contratos_gestores (usuario_id, cnpj, razao_social, data_inicio, data_vencimento, observacoes) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_contrato = mysqli_prepare($conexao, $sql_contrato);
    mysqli_stmt_bind_param($stmt_contrato, "isssss", $usuario_id, $cnpj_limpo, $razao_social, $data_inicio, $data_vencimento, $observacoes);

    if (mysqli_stmt_execute($stmt_contrato)) {
        header("Location: ../frontend/cadastrar_gestor.html?sucesso=1&senha=" . urlencode($senha_gerada));
    } else {
        header("Location: ../frontend/cadastrar_gestor.html?erro=5");
    }

    mysqli_stmt_close($stmt_contrato);
    mysqli_close($conexao);
    exit();

} else {
    header("Location: ../frontend/cadastrar_gestor.html");
    exit();
}
?>
