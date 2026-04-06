<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
include("../auth_check.php");
include("../conexao.php");

if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

$sql = "SELECT i.qr_code, i.status, i.comprado_em, il.nome_lote, il.preco, il.taxa_plataforma, e.nome AS evento_nome, e.data_evento, e.horario_abertura
        FROM ingressos i
        JOIN ingressos_lotes il ON i.lote_id = il.id
        JOIN eventos e ON il.evento_id = e.id
        WHERE i.usuario_id = ?
        ORDER BY i.comprado_em DESC";

$stmt = mysqli_prepare($conexao, $sql);
if (!$stmt) {
    echo json_encode(["erro" => "Erro ao preparar consulta"]);
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$ingressos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ingressos[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode(["ingressos" => $ingressos]);
?>