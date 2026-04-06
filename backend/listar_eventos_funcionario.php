<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "funcionario") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

// buscar balada do funcionario
$sql_balada = "SELECT balada_id FROM usuarios WHERE id = ?";
$stmt_balada = mysqli_prepare($conexao, $sql_balada);
mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_balada);
$resultado_balada = mysqli_stmt_get_result($stmt_balada);
$usuario = mysqli_fetch_assoc($resultado_balada);
mysqli_stmt_close($stmt_balada);

if (!$usuario || !$usuario["balada_id"]) {
    echo json_encode(["erro" => "Funcionário não associado a uma balada"]);
    exit();
}

// buscar eventos ativos da balada
$sql = "SELECT id, nome FROM eventos WHERE balada_id = ? AND status = 'ativo' ORDER BY data_evento DESC";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $usuario["balada_id"]);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$eventos = [];
while ($evento = mysqli_fetch_assoc($resultado)) {
    $eventos[] = $evento;
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode($eventos);
?>