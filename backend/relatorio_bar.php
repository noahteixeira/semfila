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
$sql = "SELECT cb.id, cb.valor_total, cb.registrado_em, u.nome AS usuario_nome, f.nome AS funcionario_nome
        FROM consumos_bar cb
        INNER JOIN pulseiras p ON cb.pulseira_id = p.id
        INNER JOIN usuarios u ON p.usuario_id = u.id
        INNER JOIN usuarios f ON cb.funcionario_id = f.id
        WHERE cb.evento_id = ?
        ORDER BY cb.registrado_em DESC";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$consumos = [];
while ($consumo = mysqli_fetch_assoc($resultado)) {
    // buscar itens desse consumo
    $sql_itens = "SELECT produto, quantidade, valor_unitario FROM itens_consumo WHERE consumo_id = ?";
    $stmt_itens = mysqli_prepare($conexao, $sql_itens);
    mysqli_stmt_bind_param($stmt_itens, "i", $consumo["id"]);
    mysqli_stmt_execute($stmt_itens);
    $resultado_itens = mysqli_stmt_get_result($stmt_itens);

    $itens = "";
    while ($item = mysqli_fetch_assoc($resultado_itens)) {
        if ($itens != "") {
            $itens = $itens . ", ";
        }
        $itens = $itens . $item["produto"] . " (" . $item["quantidade"] . "x)";
    }
    mysqli_stmt_close($stmt_itens);

    $consumo["itens"] = $itens;
    $consumos[] = $consumo;
}
mysqli_stmt_close($stmt);

// produtos mais vendidos
$sql_produtos = "SELECT ic.produto, SUM(ic.quantidade) AS total_vendido, SUM(ic.quantidade * ic.valor_unitario) AS receita
                 FROM itens_consumo ic
                 INNER JOIN consumos_bar cb ON ic.consumo_id = cb.id
                 WHERE cb.evento_id = ?
                 GROUP BY ic.produto
                 ORDER BY total_vendido DESC";

$stmt = mysqli_prepare($conexao, $sql_produtos);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$produtos = [];
while ($produto = mysqli_fetch_assoc($resultado)) {
    $produtos[] = [
        "produto" => $produto["produto"],
        "quantidade" => (int)$produto["total_vendido"],
        "receita" => floatval($produto["receita"])
    ];
}
mysqli_stmt_close($stmt);

// receita total do bar
$sql_receita = "SELECT COALESCE(SUM(valor_total), 0) AS receita_total FROM consumos_bar WHERE evento_id = ?";
$stmt = mysqli_prepare($conexao, $sql_receita);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$receita = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

mysqli_close($conexao);

echo json_encode([
    "consumos" => $consumos,
    "produtos" => $produtos,
    "receita_total" => floatval($receita["receita_total"])
]);
?>