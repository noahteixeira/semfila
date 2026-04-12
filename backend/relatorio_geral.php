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

// Entradas
$sql_total_entradas = "SELECT COUNT(*) AS total_pessoas FROM entradas WHERE evento_id = ?";
$stmt = mysqli_prepare($conexao, $sql_total_entradas);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$total_entradas = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

$sql_entradas_metodo = "SELECT metodo, COUNT(*) AS total FROM entradas WHERE evento_id = ? GROUP BY metodo";
$stmt = mysqli_prepare($conexao, $sql_entradas_metodo);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$entradas_metodo = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $descricao = $linha["metodo"] === "qr_code" ? "QR Code" : "RFID";
    $entradas_metodo[] = [
        "tipo" => "Método",
        "descricao" => $descricao,
        "total" => (int)$linha["total"]
    ];
}
mysqli_stmt_close($stmt);

$sql_entradas_horario = "SELECT DATE_FORMAT(registrado_em, '%H:%i') AS horario, COUNT(*) AS total FROM entradas WHERE evento_id = ? GROUP BY horario ORDER BY horario";
$stmt = mysqli_prepare($conexao, $sql_entradas_horario);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$entradas_horario = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $entradas_horario[] = [
        "tipo" => "Horário",
        "descricao" => $linha["horario"],
        "total" => (int)$linha["total"]
    ];
}
mysqli_stmt_close($stmt);

$entradas = [];
$entradas[] = [
    "tipo" => "Total",
    "descricao" => "Total de pessoas",
    "total" => (int)$total_entradas["total_pessoas"]
];
$entradas = array_merge($entradas, $entradas_metodo, $entradas_horario);

// Ingressos
$sql_ingressos_lotes = "SELECT l.nome_lote,
                               COUNT(i.id) AS ingressos_vendidos,
                               SUM(CASE WHEN i.status = 'disponivel' THEN 1 ELSE 0 END) AS ingressos_nao_utilizados,
                               COALESCE(SUM(CASE WHEN i.id IS NOT NULL THEN l.preco ELSE 0 END), 0) AS receita_lote
                        FROM ingressos_lotes l
                        LEFT JOIN ingressos i ON i.lote_id = l.id
                        WHERE l.evento_id = ?
                        GROUP BY l.id
                        ORDER BY l.nome_lote";
$stmt = mysqli_prepare($conexao, $sql_ingressos_lotes);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$ingressos = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $ingressos[] = [
        "tipo" => "Lote",
        "descricao" => $linha["nome_lote"],
        "ingressos_vendidos" => (int)$linha["ingressos_vendidos"],
        "ingressos_nao_utilizados" => (int)$linha["ingressos_nao_utilizados"],
        "receita" => floatval($linha["receita_lote"])
    ];
}
mysqli_stmt_close($stmt);

$sql_ingressos_resumo = "SELECT
                             SUM(CASE WHEN i.status = 'disponivel' THEN 1 ELSE 0 END) AS ingressos_nao_utilizados,
                             COALESCE(SUM(CASE WHEN i.id IS NOT NULL THEN l.preco ELSE 0 END), 0) AS receita_total
                         FROM ingressos i
                         INNER JOIN ingressos_lotes l ON i.lote_id = l.id
                         WHERE l.evento_id = ?";
$stmt = mysqli_prepare($conexao, $sql_ingressos_resumo);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$ingressos_resumo = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

$ingressos[] = [
    "tipo" => "Resumo",
    "descricao" => "Ingressos não utilizados",
    "ingressos_vendidos" => "",
    "ingressos_nao_utilizados" => (int)$ingressos_resumo["ingressos_nao_utilizados"],
    "receita" => ""
];
$ingressos[] = [
    "tipo" => "Resumo",
    "descricao" => "Receita total de ingressos",
    "ingressos_vendidos" => "",
    "ingressos_nao_utilizados" => "",
    "receita" => floatval($ingressos_resumo["receita_total"])
];

// Consumo do bar
$sql_produtos = "SELECT ic.produto,
                         SUM(ic.quantidade) AS quantidade_vendida,
                         SUM(ic.quantidade * ic.valor_unitario) AS receita_produto
                  FROM itens_consumo ic
                  INNER JOIN consumos_bar cb ON ic.consumo_id = cb.id
                  WHERE cb.evento_id = ?
                  GROUP BY ic.produto
                  ORDER BY quantidade_vendida DESC";
$stmt = mysqli_prepare($conexao, $sql_produtos);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$bar = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $bar[] = [
        "tipo" => "Produto",
        "descricao" => $linha["produto"],
        "quantidade" => (int)$linha["quantidade_vendida"],
        "receita" => floatval($linha["receita_produto"])
    ];
}
mysqli_stmt_close($stmt);

$sql_receita_bar = "SELECT COALESCE(SUM(valor_total), 0) AS receita_total FROM consumos_bar WHERE evento_id = ?";
$stmt = mysqli_prepare($conexao, $sql_receita_bar);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$receita_bar = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

$sql_bar_horario = "SELECT DATE_FORMAT(registrado_em, '%H:%i') AS horario, SUM(valor_total) AS receita FROM consumos_bar WHERE evento_id = ? GROUP BY horario ORDER BY horario";
$stmt = mysqli_prepare($conexao, $sql_bar_horario);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
while ($linha = mysqli_fetch_assoc($resultado)) {
    $bar[] = [
        "tipo" => "Horário",
        "descricao" => $linha["horario"],
        "quantidade" => "",
        "receita" => floatval($linha["receita"])
    ];
}
mysqli_stmt_close($stmt);

$bar[] = [
    "tipo" => "Resumo",
    "descricao" => "Receita total do bar",
    "quantidade" => "",
    "receita" => floatval($receita_bar["receita_total"])
];

$resumo = [
    ["metrica" => "Total de pessoas", "valor" => (int)$total_entradas["total_pessoas"]],
    ["metrica" => "Ingressos não utilizados", "valor" => (int)$ingressos_resumo["ingressos_nao_utilizados"]],
    ["metrica" => "Receita total do bar", "valor" => floatval($receita_bar["receita_total"])]
];

mysqli_close($conexao);

echo json_encode([
    "entradas" => $entradas,
    "ingressos" => $ingressos,
    "bar" => $bar,
    "resumo" => $resumo
]);
