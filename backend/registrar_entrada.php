<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "funcionario") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["erro" => "Método inválido"]);
    exit();
}

$ingresso_id = (int)$_POST["ingresso_id"];
$evento_id = (int)$_POST["evento_id"];

if ($ingresso_id <= 0 || $evento_id <= 0) {
    echo json_encode(["erro" => "Dados inválidos"]);
    exit();
}

// atualizar status do ingresso para utilizado
$sql = "UPDATE ingressos SET status = 'utilizado' WHERE id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $ingresso_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// registrar entrada
$sql_entrada = "INSERT INTO entradas (evento_id, ingresso_id, funcionario_id, metodo) VALUES (?, ?, ?, 'qr_code')";
$stmt_entrada = mysqli_prepare($conexao, $sql_entrada);
mysqli_stmt_bind_param($stmt_entrada, "iii", $evento_id, $ingresso_id, $_SESSION["usuario_id"]);

if (mysqli_stmt_execute($stmt_entrada)) {
    mysqli_stmt_close($stmt_entrada);
    mysqli_close($conexao);
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Entrada liberada com sucesso!"
    ]);
} else {
    mysqli_stmt_close($stmt_entrada);
    mysqli_close($conexao);
    echo json_encode([
        "erro" => "Erro ao registrar entrada"
    ]);
}
?>
