<?php
session_start();
require_once '../conexao.php';

// Verificar se o usuário está logado e é gestor
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'gestor') {
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

$event_id = $_GET['event_id'] ?? 0;

if ($event_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Evento não informado']);
    exit;
}

// Verificar se o evento pertence à balada do gestor
$sql = "SELECT e.id FROM eventos e JOIN baladas b ON e.balada_id = b.id WHERE e.id = ? AND b.gestor_id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "ii", $event_id, $_SESSION['usuario_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!mysqli_fetch_assoc($result)) {
    mysqli_stmt_close($stmt);
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado ao evento']);
    exit;
}
mysqli_stmt_close($stmt);

try {
    // Buscar consumos do evento
    $sql = "SELECT cb.id, cb.valor_total, cb.registrado_em, u.nome as usuario_nome, f.nome as funcionario_nome,
                   GROUP_CONCAT(CONCAT(ic.produto, ' (', ic.quantidade, 'x R$', FORMAT(ic.valor_unitario, 2), ')') SEPARATOR '; ') as itens
            FROM consumos_bar cb
            JOIN pulseiras p ON cb.pulseira_id = p.id
            JOIN usuarios u ON p.usuario_id = u.id
            JOIN usuarios f ON cb.funcionario_id = f.id
            LEFT JOIN itens_consumo ic ON cb.id = ic.consumo_id
            WHERE cb.evento_id = ?
            GROUP BY cb.id
            ORDER BY cb.registrado_em DESC";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
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