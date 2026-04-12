<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["erro" => "Método inválido"]);
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// buscar pulseira do usuario
$sql = "SELECT id, status FROM pulseiras WHERE usuario_id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$pulseira = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$pulseira) {
    echo json_encode(["erro" => "Você não possui pulseira"]);
    exit();
}

if ($pulseira["status"] == "inativa") {
    echo json_encode(["erro" => "Pulseira já está cancelada"]);
    exit();
}

// cancelar: status inativa e saldo zerado
$sql_update = "UPDATE pulseiras SET status = 'inativa', saldo = 0.00 WHERE id = ?";
$stmt_update = mysqli_prepare($conexao, $sql_update);
mysqli_stmt_bind_param($stmt_update, "i", $pulseira["id"]);

if (mysqli_stmt_execute($stmt_update)) {
    mysqli_stmt_close($stmt_update);
    mysqli_close($conexao);
    echo json_encode(["sucesso" => true]);
} else {
    mysqli_stmt_close($stmt_update);
    mysqli_close($conexao);
    echo json_encode(["erro" => "Erro ao cancelar pulseira"]);
}
?>
