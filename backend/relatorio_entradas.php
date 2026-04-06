<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "gestor") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$evento_id = (int)$_GET["evento_id"];

if ($evento_id <= 0) {
    echo json_encode(["erro" => "Evento não informado"]);
    exit();
}

// verificar se evento pertence a balada do gestor
$sql_verificar = "SELECT e.id FROM eventos e INNER JOIN baladas b ON e.balada_id = b.id WHERE e.id = ? AND b.gestor_id = ?";
$stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
mysqli_stmt_bind_param($stmt_verificar, "ii", $evento_id, $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_verificar);
$resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
mysqli_stmt_close($stmt_verificar);

if (mysqli_num_rows($resultado_verificar) == 0) {
    echo json_encode(["erro" => "Acesso negado ao evento"]);
    exit();
}

// buscar entradas do evento
$sql = "SELECT e.id, e.registrado_em, u.nome, f.nome AS funcionario_nome, e.metodo
        FROM entradas e
        INNER JOIN ingressos i ON e.ingresso_id = i.id
        INNER JOIN usuarios u ON i.usuario_id = u.id
        INNER JOIN usuarios f ON e.funcionario_id = f.id
        WHERE e.evento_id = ?
        ORDER BY e.registrado_em DESC";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$entradas = [];
while ($entrada = mysqli_fetch_assoc($resultado)) {
    $entradas[] = $entrada;
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode($entradas);
?>
