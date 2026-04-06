<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$sql = "SELECT cb.id, cb.valor_total, cb.registrado_em, e.nome AS evento_nome,
               GROUP_CONCAT(CONCAT(ic.produto, ' (', ic.quantidade, 'x R$', FORMAT(ic.valor_unitario, 2), ')') SEPARATOR '; ') AS itens
        FROM consumos_bar cb
        INNER JOIN pulseiras p ON cb.pulseira_id = p.id
        INNER JOIN eventos e ON cb.evento_id = e.id
        LEFT JOIN itens_consumo ic ON cb.id = ic.consumo_id
        WHERE p.usuario_id = ?
        GROUP BY cb.id
        ORDER BY cb.registrado_em DESC";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$consumos = [];
while ($consumo = mysqli_fetch_assoc($resultado)) {
    $consumos[] = $consumo;
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode($consumos);
?>