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
$metodo = $_POST["metodo"];

if ($ingresso_id <= 0 || $evento_id <= 0) {
    echo json_encode(["erro" => "Dados inválidos"]);
    exit();
}

if ($metodo != "qr_code" && $metodo != "rfid") {
    $metodo = "qr_code";
}

// verificar se ingresso ainda esta disponivel
$sql_check = "SELECT status FROM ingressos WHERE id = ?";
$stmt_check = mysqli_prepare($conexao, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $ingresso_id);
mysqli_stmt_execute($stmt_check);
$resultado_check = mysqli_stmt_get_result($stmt_check);
$ingresso = mysqli_fetch_assoc($resultado_check);
mysqli_stmt_close($stmt_check);

if (!$ingresso || $ingresso["status"] != "disponivel") {
    echo json_encode(["erro" => "Ingresso não disponível ou já utilizado"]);
    exit();
}

// iniciar transacao
mysqli_begin_transaction($conexao);

// atualizar status do ingresso para utilizado
$sql = "UPDATE ingressos SET status = 'utilizado' WHERE id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $ingresso_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// registrar entrada
$sql_entrada = "INSERT INTO entradas (evento_id, ingresso_id, funcionario_id, metodo) VALUES (?, ?, ?, ?)";
$stmt_entrada = mysqli_prepare($conexao, $sql_entrada);
mysqli_stmt_bind_param($stmt_entrada, "iiis", $evento_id, $ingresso_id, $_SESSION["usuario_id"], $metodo);
mysqli_stmt_execute($stmt_entrada);
mysqli_stmt_close($stmt_entrada);

// confirmar transacao
mysqli_commit($conexao);

mysqli_close($conexao);
echo json_encode([
    "sucesso" => true,
    "mensagem" => "Entrada liberada com sucesso!"
]);
?>
