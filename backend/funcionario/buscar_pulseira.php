<?php
session_start();
require_once '../conexao.php';

// Verificar se o usuário está logado e é funcionário
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'funcionario') {
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

$rfid = $_GET['rfid'] ?? '';

if (empty($rfid)) {
    http_response_code(400);
    echo json_encode(['error' => 'RFID não informado']);
    exit;
}

try {
    // Buscar pulseira pelo RFID
    $sql = "SELECT p.id, p.saldo, u.nome FROM pulseiras p JOIN usuarios u ON p.usuario_id = u.id WHERE p.codigo_rfid = ? AND p.status = 'ativa'";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "s", $rfid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $pulseira = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$pulseira) {
        http_response_code(404);
        echo json_encode(['error' => 'Pulseira não encontrada ou inativa']);
        exit;
    }

    echo json_encode(['nome' => $pulseira['nome'], 'saldo' => $pulseira['saldo']]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>