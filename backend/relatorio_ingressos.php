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

$sql = "SELECT l.nome_lote,
               COUNT(i.id) AS ingressos_vendidos,
               SUM(CASE WHEN i.status = 'disponivel' THEN 1 ELSE 0 END) AS ingressos_nao_utilizados,
               COALESCE(SUM(CASE WHEN i.id IS NOT NULL THEN l.preco ELSE 0 END), 0) AS receita_lote
        FROM ingressos_lotes l
        LEFT JOIN ingressos i ON i.lote_id = l.id
        WHERE l.evento_id = ?
        GROUP BY l.id
        ORDER BY l.nome_lote";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $evento_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$relatorio = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $relatorio[] = [
        "tipo" => "Lote",
        "descricao" => $linha["nome_lote"],
        "ingressos_vendidos" => (int)$linha["ingressos_vendidos"],
        "ingressos_nao_utilizados" => (int)$linha["ingressos_nao_utilizados"],
        "receita" => floatval($linha["receita_lote"])
    ];
}

mysqli_stmt_close($stmt);

$sql_total = "SELECT
                  COUNT(i.id) AS total_vendidos,
                  SUM(CASE WHEN i.status = 'disponivel' THEN 1 ELSE 0 END) AS total_nao_utilizados,
                  COALESCE(SUM(CASE WHEN i.id IS NOT NULL THEN l.preco ELSE 0 END), 0) AS receita_total
              FROM ingressos i
              INNER JOIN ingressos_lotes l ON i.lote_id = l.id
              WHERE l.evento_id = ?";

$stmt_total = mysqli_prepare($conexao, $sql_total);
mysqli_stmt_bind_param($stmt_total, "i", $evento_id);
mysqli_stmt_execute($stmt_total);
$resultado_total = mysqli_stmt_get_result($stmt_total);
$total = mysqli_fetch_assoc($resultado_total);

mysqli_stmt_close($stmt_total);
mysqli_close($conexao);

$relatorio[] = [
    "tipo" => "Resumo",
    "descricao" => "Ingressos não utilizados",
    "ingressos_vendidos" => "",
    "ingressos_nao_utilizados" => (int)$total["total_nao_utilizados"],
    "receita" => ""
];
$relatorio[] = [
    "tipo" => "Resumo",
    "descricao" => "Receita total",
    "ingressos_vendidos" => "",
    "ingressos_nao_utilizados" => "",
    "receita" => floatval($total["receita_total"])
];

echo json_encode($relatorio);
