<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$sql = "SELECT i.qr_code, i.status, i.comprado_em, il.nome_lote, il.preco, il.taxa_plataforma, e.nome AS evento_nome, e.data_evento, e.horario_abertura
        FROM ingressos i
        INNER JOIN ingressos_lotes il ON i.lote_id = il.id
        INNER JOIN eventos e ON il.evento_id = e.id
        WHERE i.usuario_id = ?
        ORDER BY i.comprado_em DESC";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$ingressos = [];
while ($ingresso = mysqli_fetch_assoc($resultado)) {
    $ingressos[] = $ingresso;
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode($ingressos);
?>