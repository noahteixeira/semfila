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

$nome = trim($_POST["nome"]);
$preco = floatval($_POST["preco"]);

if ($nome == "" || $preco <= 0) {
    echo json_encode(["erro" => "Dados inválidos"]);
    exit();
}

$sql_balada = "SELECT id FROM baladas WHERE gestor_id = ? AND ativo = 1";
$stmt_balada = mysqli_prepare($conexao, $sql_balada);
mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_balada);
$resultado_balada = mysqli_stmt_get_result($stmt_balada);
$balada = mysqli_fetch_assoc($resultado_balada);
mysqli_stmt_close($stmt_balada);

if (!$balada) {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Balada não encontrada"]);
    exit();
}

$sql = "INSERT INTO produtos_bar (balada_id, nome, preco) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "isd", $balada["id"], $nome, $preco);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    echo json_encode(["sucesso" => true, "mensagem" => "Produto cadastrado com sucesso"]);
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    echo json_encode(["erro" => "Erro ao cadastrar produto"]);
}
?>
