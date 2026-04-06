<?php
session_start();
require_once '../conexao.php';

// Verificar se o usuário está logado e é baladeiro
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'baladeiro') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    // Buscar consumos do usuário
    $sql = "SELECT cb.id, cb.valor_total, cb.registrado_em, e.nome as evento_nome,
                   GROUP_CONCAT(CONCAT(ic.produto, ' (', ic.quantidade, 'x R$', FORMAT(ic.valor_unitario, 2), ')') SEPARATOR '; ') as itens
            FROM consumos_bar cb
            JOIN pulseiras p ON cb.pulseira_id = p.id
            JOIN eventos e ON cb.evento_id = e.id
            LEFT JOIN itens_consumo ic ON cb.id = ic.consumo_id
            WHERE p.usuario_id = ?
            GROUP BY cb.id
            ORDER BY cb.registrado_em DESC";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $consumos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $consumos[] = $row;
    }
    mysqli_stmt_close($stmt);

    echo json_encode($consumos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>