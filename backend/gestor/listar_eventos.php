<?php
include("../auth_check.php");
include("../conexao.php");

// verificar se é gestor
if ($_SESSION["usuario_tipo"] != "gestor") {
    header("Location: ../../frontend/login.html");
    exit();
}

// buscar eventos do gestor (através da balada)
$sql = "SELECT e.id, e.nome, e.descricao, e.data_evento, e.horario_abertura, e.idade_minima, e.capacidade_maxima, e.status, e.criado_em
        FROM eventos e
        INNER JOIN baladas b ON e.balada_id = b.id
        WHERE b.gestor_id = ? AND b.ativo = 1
        ORDER BY e.data_evento DESC, e.horario_abertura DESC";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
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