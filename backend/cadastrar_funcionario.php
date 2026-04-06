<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "gestor") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["erro" => "Método inválido"]);
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
    echo json_encode(["erro" => "Você precisa ter uma balada cadastrada"]);
    exit();
}

$nome = trim($_POST["nome"]);
$email = trim($_POST["email"]);

if (empty($nome) || empty($email)) {
    echo json_encode(["erro" => "Preencha todos os campos obrigatórios"]);
    exit();
}

// verificar se email ja existe
$sql_check = "SELECT id FROM usuarios WHERE email = ?";
$stmt_check = mysqli_prepare($conexao, $sql_check);
mysqli_stmt_bind_param($stmt_check, "s", $email);
mysqli_stmt_execute($stmt_check);
$resultado_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($resultado_check) > 0) {
    mysqli_stmt_close($stmt_check);
    echo json_encode(["erro" => "E-mail já cadastrado no sistema"]);
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
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    echo json_encode(["sucesso" => true, "senha" => $senha_gerada]);
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    echo json_encode(["erro" => "Erro ao cadastrar funcionário"]);
}
?>
