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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

$rfid = $input['rfid'] ?? '';
$event_id = $input['event_id'] ?? 0;
$produtos = $input['produtos'] ?? [];
$valor_total = $input['valor_total'] ?? 0;

if (empty($rfid) || $event_id <= 0 || empty($produtos) || $valor_total <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos']);
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

    if ($pulseira['saldo'] < $valor_total) {
        http_response_code(400);
        echo json_encode(['error' => 'Saldo insuficiente']);
        exit;
    }

    // Iniciar transação
    mysqli_begin_transaction($conexao);

    // Debitar saldo da pulseira
    $sql = "UPDATE pulseiras SET saldo = saldo - ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "di", $valor_total, $pulseira['id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Inserir consumo_bar
    $sql = "INSERT INTO consumos_bar (evento_id, pulseira_id, funcionario_id, valor_total) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "iiid", $event_id, $pulseira['id'], $_SESSION['usuario_id'], $valor_total);
    mysqli_stmt_execute($stmt);
    $consumo_id = mysqli_insert_id($conexao);
    mysqli_stmt_close($stmt);

    // Inserir itens_consumo
    $sql = "INSERT INTO itens_consumo (consumo_id, produto, quantidade, valor_unitario) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    foreach ($produtos as $produto) {
        mysqli_stmt_bind_param($stmt, "isid", $consumo_id, $produto['nome'], $produto['quantidade'], $produto['valor_unitario']);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    // Commit transação
    mysqli_commit($conexao);

    echo json_encode(['success' => true, 'message' => 'Consumo registrado com sucesso', 'novo_saldo' => $pulseira['saldo'] - $valor_total]);

} catch (Exception $e) {
    mysqli_rollback($conexao);
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>