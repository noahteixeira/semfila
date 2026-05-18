<?php
include("auth_check.php");
include("conexao.php");

$balada_id = 0;

if ($_SESSION["usuario_tipo"] == "gestor") {
    $sql_balada = "SELECT id FROM baladas WHERE gestor_id = ? AND ativo = 1";
    $stmt_balada = mysqli_prepare($conexao, $sql_balada);
    mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt_balada);
    $resultado_balada = mysqli_stmt_get_result($stmt_balada);
    $balada = mysqli_fetch_assoc($resultado_balada);
    mysqli_stmt_close($stmt_balada);

    if ($balada) {
        $balada_id = (int)$balada["id"];
    }
} else if ($_SESSION["usuario_tipo"] == "funcionario") {
    $sql_balada = "SELECT balada_id FROM usuarios WHERE id = ?";
    $stmt_balada = mysqli_prepare($conexao, $sql_balada);
    mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt_balada);
    $resultado_balada = mysqli_stmt_get_result($stmt_balada);
    $usuario = mysqli_fetch_assoc($resultado_balada);
    mysqli_stmt_close($stmt_balada);

    if ($usuario) {
        $balada_id = (int)$usuario["balada_id"];
    }
} else {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($balada_id <= 0) {
    mysqli_close($conexao);
    echo json_encode(["erro" => "Balada não encontrada"]);
    exit();
}

$sql = "SELECT id, nome, preco FROM produtos_bar WHERE balada_id = ? ORDER BY nome ASC";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $balada_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$produtos = [];
while ($produto = mysqli_fetch_assoc($resultado)) {
    $produtos[] = [
        "id" => (int)$produto["id"],
        "nome" => $produto["nome"],
        "preco" => (float)$produto["preco"]
    ];
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode($produtos);
?>
