<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "gestor") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

// verificar se gestor tem balada
$sql_balada = "SELECT id FROM baladas WHERE gestor_id = ? AND ativo = 1";
$stmt_balada = mysqli_prepare($conexao, $sql_balada);
mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_balada);
$resultado_balada = mysqli_stmt_get_result($stmt_balada);
$balada = mysqli_fetch_assoc($resultado_balada);
mysqli_stmt_close($stmt_balada);

if (!$balada) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT id, nome, email, ativo, criado_em FROM usuarios WHERE tipo = 'funcionario' AND balada_id = ? ORDER BY nome ASC";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $balada["id"]);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$funcionarios = [];
while ($func = mysqli_fetch_assoc($resultado)) {
    $funcionarios[] = $func;
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode($funcionarios);
?>
