<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$sql = "SELECT e.id, e.nome, e.descricao, e.data_evento, e.horario_abertura, e.idade_minima, e.capacidade_maxima, b.nome AS balada_nome, b.endereco, b.cidade
        FROM eventos e
        INNER JOIN baladas b ON e.balada_id = b.id
        WHERE e.status = 'ativo' AND b.ativo = 1
        ORDER BY e.data_evento ASC, e.horario_abertura ASC";

$stmt = mysqli_prepare($conexao, $sql);
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