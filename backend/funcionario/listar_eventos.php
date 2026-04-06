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

try {
    // Buscar balada do funcionário
    $sql = "SELECT balada_id FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$usuario || !$usuario['balada_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Funcionário não associado a uma balada']);
        exit;
    }

    // Buscar eventos ativos da balada
    $sql = "SELECT id, nome FROM eventos WHERE balada_id = ? AND status = 'ativo' ORDER BY data_evento DESC";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario['balada_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $eventos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $eventos[] = $row;
    }
    mysqli_stmt_close($stmt);

    echo json_encode($eventos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>