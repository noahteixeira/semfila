<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $carrinho_json = $_POST["carrinho_json"];
    $carrinho = json_decode($carrinho_json, true);
    $pagamento_status = isset($_POST["pagamento_status"]) ? trim($_POST["pagamento_status"]) : "";

    if (empty($carrinho)) {
        echo json_encode(["erro" => "Carrinho vazio"]);
        exit();
    }

    if ($pagamento_status != "aprovado") {
        echo json_encode(["erro" => "Pagamento não aprovado"]);
        exit();
    }

    $pulseira_id = 0;
    $sql_pulseira = "SELECT id FROM pulseiras WHERE usuario_id = ? AND status = 'ativa'";
    $stmt_pulseira = mysqli_prepare($conexao, $sql_pulseira);
    mysqli_stmt_bind_param($stmt_pulseira, "i", $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt_pulseira);
    $resultado_pulseira = mysqli_stmt_get_result($stmt_pulseira);
    $pulseira = mysqli_fetch_assoc($resultado_pulseira);
    mysqli_stmt_close($stmt_pulseira);

    if ($pulseira) {
        $pulseira_id = (int)$pulseira["id"];
    }

    $todos_os_ingressos = [];

    mysqli_begin_transaction($conexao);

    foreach ($carrinho as $item) {
        $lote_id = (int)$item["loteId"];
        $evento_id = (int)$item["eventoId"];
        $quantidade = (int)$item["quantidade"];

        if ($quantidade <= 0) {
            mysqli_rollback($conexao);
            echo json_encode(["erro" => "Quantidade inválida"]);
            exit();
        }

        // verificar disponibilidade
        $sql_verificar = "SELECT il.quantidade_total, il.quantidade_vendida
                          FROM ingressos_lotes il
                          INNER JOIN eventos e ON il.evento_id = e.id
                          WHERE il.id = ? AND il.evento_id = ? AND il.ativo = 1 AND e.status = 'ativo'";
        $stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
        mysqli_stmt_bind_param($stmt_verificar, "ii", $lote_id, $evento_id);
        mysqli_stmt_execute($stmt_verificar);
        $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
        $lote = mysqli_fetch_assoc($resultado_verificar);
        mysqli_stmt_close($stmt_verificar);

        if (!$lote || ($lote["quantidade_total"] - $lote["quantidade_vendida"]) < $quantidade) {
            mysqli_rollback($conexao);
            echo json_encode(["erro" => "Ingressos insuficientes para o lote: " . $item["loteName"]]);
            exit();
        }

        // atualizar quantidade vendida
        $sql_atualizar = "UPDATE ingressos_lotes SET quantidade_vendida = quantidade_vendida + ? WHERE id = ? AND evento_id = ? AND quantidade_vendida + ? <= quantidade_total";
        $stmt_atualizar = mysqli_prepare($conexao, $sql_atualizar);
        mysqli_stmt_bind_param($stmt_atualizar, "iiii", $quantidade, $lote_id, $evento_id, $quantidade);
        if (!mysqli_stmt_execute($stmt_atualizar) || mysqli_affected_rows($conexao) == 0) {
            mysqli_rollback($conexao);
            mysqli_stmt_close($stmt_atualizar);
            echo json_encode(["erro" => "Ingressos insuficientes para o lote: " . $item["loteName"]]);
            exit();
        }
        mysqli_stmt_close($stmt_atualizar);

        // criar ingressos com QR codes
        for ($i = 0; $i < $quantidade; $i++) {
            $qr_code = uniqid("QR_", true);
            if ($pulseira_id > 0) {
                $sql_ingresso = "INSERT INTO ingressos (lote_id, usuario_id, qr_code, pulseira_id, status) VALUES (?, ?, ?, ?, 'disponivel')";
                $stmt_ingresso = mysqli_prepare($conexao, $sql_ingresso);
                mysqli_stmt_bind_param($stmt_ingresso, "iisi", $lote_id, $_SESSION["usuario_id"], $qr_code, $pulseira_id);
            } else {
                $sql_ingresso = "INSERT INTO ingressos (lote_id, usuario_id, qr_code, status) VALUES (?, ?, ?, 'disponivel')";
                $stmt_ingresso = mysqli_prepare($conexao, $sql_ingresso);
                mysqli_stmt_bind_param($stmt_ingresso, "iis", $lote_id, $_SESSION["usuario_id"], $qr_code);
            }

            if (!mysqli_stmt_execute($stmt_ingresso)) {
                mysqli_rollback($conexao);
                mysqli_stmt_close($stmt_ingresso);
                echo json_encode(["erro" => "Erro ao gerar ingresso"]);
                exit();
            }

            $todos_os_ingressos[] = [
                "evento" => $item["eventoNome"],
                "lote" => $item["loteName"],
                "qr_code" => $qr_code
            ];

            mysqli_stmt_close($stmt_ingresso);
        }
    }

    mysqli_commit($conexao);

    mysqli_close($conexao);

    if (count($todos_os_ingressos) > 0) {
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Compra finalizada! Total de ingressos: " . count($todos_os_ingressos),
            "ingressos" => $todos_os_ingressos
        ]);
    } else {
        echo json_encode(["erro" => "Erro ao gerar ingressos"]);
    }

} else {
    echo json_encode(["erro" => "Método inválido"]);
}
?>