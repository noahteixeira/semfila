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
$acao = trim($_POST["acao"]);

// buscar pulseira do usuario
$sql = "SELECT id, saldo, status, assinatura_fim FROM pulseiras WHERE usuario_id = ?";
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

// adicionar saldo
if ($acao == "adicionar_saldo") {
    $valor = floatval($_POST["valor"]);

    if ($valor <= 0) {
        echo json_encode(["erro" => "Valor inválido"]);
        exit();
    }

    if ($pulseira["status"] != "ativa") {
        echo json_encode(["erro" => "Pulseira inativa"]);
        exit();
    }

    $novo_saldo = $pulseira["saldo"] + $valor;

    $sql_update = "UPDATE pulseiras SET saldo = ? WHERE id = ?";
    $stmt_update = mysqli_prepare($conexao, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "di", $novo_saldo, $pulseira["id"]);

    if (mysqli_stmt_execute($stmt_update)) {
        mysqli_stmt_close($stmt_update);
        mysqli_close($conexao);
        echo json_encode(["sucesso" => true, "novo_saldo" => $novo_saldo]);
    } else {
        mysqli_stmt_close($stmt_update);
        mysqli_close($conexao);
        echo json_encode(["erro" => "Erro ao adicionar saldo"]);
    }
    exit();
}

// renovar assinatura
if ($acao == "renovar") {
    $hoje = date("Y-m-d");

    // se a assinatura ainda nao venceu, renovar a partir do fim atual
    if ($pulseira["assinatura_fim"] >= $hoje) {
        $nova_data = date("Y-m-d", strtotime($pulseira["assinatura_fim"] . " +30 days"));
    } else {
        $nova_data = date("Y-m-d", strtotime("+30 days"));
    }

    $sql_update = "UPDATE pulseiras SET assinatura_fim = ?, status = 'ativa' WHERE id = ?";
    $stmt_update = mysqli_prepare($conexao, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "si", $nova_data, $pulseira["id"]);

    if (mysqli_stmt_execute($stmt_update)) {
        mysqli_stmt_close($stmt_update);
        mysqli_close($conexao);
        echo json_encode(["sucesso" => true, "assinatura_fim" => $nova_data]);
    } else {
        mysqli_stmt_close($stmt_update);
        mysqli_close($conexao);
        echo json_encode(["erro" => "Erro ao renovar assinatura"]);
    }
    exit();
}

echo json_encode(["erro" => "Ação inválida"]);
?>
