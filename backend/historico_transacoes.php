<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$sql = "SELECT ts.tipo, ts.valor, ts.descricao, ts.registrado_em
        FROM transacoes_saldo ts
        INNER JOIN pulseiras p ON ts.pulseira_id = p.id
        WHERE p.usuario_id = ?
        ORDER BY ts.registrado_em DESC";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$transacoes = [];
while ($transacao = mysqli_fetch_assoc($resultado)) {
    $transacoes[] = $transacao;
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode($transacoes);
?>