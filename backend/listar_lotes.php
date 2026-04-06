<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$evento_id = (int)$_GET["evento_id"];

$sql = "SELECT il.id, il.nome_lote, il.preco, il.taxa_plataforma, il.quantidade_total, il.quantidade_vendida
        FROM ingressos_lotes il
        INNER JOIN eventos e ON il.evento_id = e.id
        WHERE il.evento_id = ? AND il.ativo = 1 AND e.status = 'ativo'
        ORDER BY il.preco ASC";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$lotes = [];
while ($lote = mysqli_fetch_assoc($resultado)) {
    $lote["quantidade_disponivel"] = $lote["quantidade_total"] - $lote["quantidade_vendida"];
    $lotes[] = $lote;
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode($lotes);
?>