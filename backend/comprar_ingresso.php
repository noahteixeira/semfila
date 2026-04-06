<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lote_id = (int)$_POST["lote_id"];
    $quantidade = (int)$_POST["quantidade"];
    $evento_id = (int)$_POST["evento_id"];

    if ($quantidade <= 0) {
        echo json_encode(["erro" => "Quantidade inválida"]);
        exit();
    }

    // verificar disponibilidade
    $sql_verificar = "SELECT quantidade_total, quantidade_vendida FROM ingressos_lotes WHERE id = ? AND evento_id = ? AND ativo = 1";
    $stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "ii", $lote_id, $evento_id);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
    $lote = mysqli_fetch_assoc($resultado_verificar);
    mysqli_stmt_close($stmt_verificar);

    if (!$lote || ($lote["quantidade_total"] - $lote["quantidade_vendida"]) < $quantidade) {
        echo json_encode(["erro" => "Ingressos insuficientes"]);
        exit();
    }

    // atualizar quantidade vendida
    $sql_atualizar = "UPDATE ingressos_lotes SET quantidade_vendida = quantidade_vendida + ? WHERE id = ?";
    $stmt_atualizar = mysqli_prepare($conexao, $sql_atualizar);
    mysqli_stmt_bind_param($stmt_atualizar, "ii", $quantidade, $lote_id);
    mysqli_stmt_execute($stmt_atualizar);
    mysqli_stmt_close($stmt_atualizar);

    // criar ingressos com QR codes
    for ($i = 0; $i < $quantidade; $i++) {
        $qr_code = uniqid("QR_");
        $sql_ingresso = "INSERT INTO ingressos (lote_id, usuario_id, qr_code, status) VALUES (?, ?, ?, 'disponivel')";
        $stmt_ingresso = mysqli_prepare($conexao, $sql_ingresso);
        mysqli_stmt_bind_param($stmt_ingresso, "iis", $lote_id, $_SESSION["usuario_id"], $qr_code);
        mysqli_stmt_execute($stmt_ingresso);
        mysqli_stmt_close($stmt_ingresso);
    }

    mysqli_close($conexao);
    echo json_encode(["sucesso" => true, "mensagem" => "Ingressos comprados com sucesso! Total: " . $quantidade]);

} else {
    echo json_encode(["erro" => "Método inválido"]);
}
?>