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

$sql_funcionario = "SELECT balada_id FROM usuarios WHERE id = ? AND tipo = 'funcionario' AND ativo = 1";
$stmt_funcionario = mysqli_prepare($conexao, $sql_funcionario);
mysqli_stmt_bind_param($stmt_funcionario, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_funcionario);
$resultado_funcionario = mysqli_stmt_get_result($stmt_funcionario);
$funcionario = mysqli_fetch_assoc($resultado_funcionario);
mysqli_stmt_close($stmt_funcionario);

if (!$funcionario || !$funcionario["balada_id"]) {
    echo json_encode(["erro" => "Funcionário sem balada vinculada"]);
    exit();
}

// verificar se ingresso ainda esta disponivel
$sql_check = "SELECT i.status
              FROM ingressos i
              INNER JOIN ingressos_lotes il ON i.lote_id = il.id
              INNER JOIN eventos e ON il.evento_id = e.id
              WHERE i.id = ? AND il.evento_id = ? AND e.balada_id = ? AND e.status = 'ativo'";
$stmt_check = mysqli_prepare($conexao, $sql_check);
mysqli_stmt_bind_param($stmt_check, "iii", $ingresso_id, $evento_id, $funcionario["balada_id"]);
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
$sql = "UPDATE ingressos SET status = 'utilizado' WHERE id = ? AND status = 'disponivel'";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $ingresso_id);
if (!mysqli_stmt_execute($stmt) || mysqli_affected_rows($conexao) == 0) {
    mysqli_rollback($conexao);
    mysqli_stmt_close($stmt);
    echo json_encode(["erro" => "Ingresso não disponível ou já utilizado"]);
    exit();
}
mysqli_stmt_close($stmt);

// registrar entrada
$sql_entrada = "INSERT INTO entradas (evento_id, ingresso_id, funcionario_id, metodo) VALUES (?, ?, ?, ?)";
$stmt_entrada = mysqli_prepare($conexao, $sql_entrada);
mysqli_stmt_bind_param($stmt_entrada, "iiis", $evento_id, $ingresso_id, $_SESSION["usuario_id"], $metodo);
if (!mysqli_stmt_execute($stmt_entrada)) {
    mysqli_rollback($conexao);
    mysqli_stmt_close($stmt_entrada);
    echo json_encode(["erro" => "Erro ao registrar entrada"]);
    exit();
}
mysqli_stmt_close($stmt_entrada);

// confirmar transacao
mysqli_commit($conexao);

mysqli_close($conexao);
echo json_encode([
    "sucesso" => true,
    "mensagem" => "Entrada liberada com sucesso!"
]);
?>
