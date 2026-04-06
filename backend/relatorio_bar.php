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

// verificar se o evento pertence a balada do gestor
$sql_verificar = "SELECT e.id FROM eventos e INNER JOIN baladas b ON e.balada_id = b.id WHERE e.id = ? AND b.gestor_id = ?";
$stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
mysqli_stmt_bind_param($stmt_verificar, "ii", $evento_id, $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_verificar);
$resultado_verificar = mysqli_stmt_get_result($stmt_verificar);

if (!mysqli_fetch_assoc($resultado_verificar)) {
    mysqli_stmt_close($stmt_verificar);
    echo json_encode(["erro" => "Acesso negado ao evento"]);
    exit();
}
mysqli_stmt_close($stmt_verificar);

// buscar consumos do evento
$sql = "SELECT cb.id, cb.valor_total, cb.registrado_em, u.nome AS usuario_nome, f.nome AS funcionario_nome,
               GROUP_CONCAT(CONCAT(ic.produto, ' (', ic.quantidade, 'x R$', FORMAT(ic.valor_unitario, 2), ')') SEPARATOR '; ') AS itens
        FROM consumos_bar cb
        INNER JOIN pulseiras p ON cb.pulseira_id = p.id
        INNER JOIN usuarios u ON p.usuario_id = u.id
        INNER JOIN usuarios f ON cb.funcionario_id = f.id
        LEFT JOIN itens_consumo ic ON cb.id = ic.consumo_id
        WHERE cb.evento_id = ?
        GROUP BY cb.id
        ORDER BY cb.registrado_em DESC";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
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