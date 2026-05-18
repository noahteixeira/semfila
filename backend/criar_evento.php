<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "gestor") {
    header("Location: ../frontend/login.html");
    exit();
}

// verificar se gestor tem balada cadastrada
$sql_balada = "SELECT id, capacidade_maxima FROM baladas WHERE gestor_id = ? AND ativo = 1";
$stmt_balada = mysqli_prepare($conexao, $sql_balada);
mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_balada);
$resultado_balada = mysqli_stmt_get_result($stmt_balada);
$balada = mysqli_fetch_assoc($resultado_balada);
mysqli_stmt_close($stmt_balada);

if (!$balada) {
    header("Location: ../frontend/listar_eventos.html?erro=balada");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $descricao = trim($_POST["descricao"]);
    $data_evento = $_POST["data_evento"];
    $horario_abertura = $_POST["horario_abertura"];
    $idade_minima = (int)$_POST["idade_minima"];
    $capacidade_maxima = (int)$_POST["capacidade_maxima"];
    $nome_lote = trim($_POST["nome_lote"]);
    $preco_lote = (float)$_POST["preco_lote"];
    $taxa_plataforma = (float)$_POST["taxa_plataforma"];
    $quantidade_lote = (int)$_POST["quantidade_lote"];

    if (empty($nome) || empty($data_evento) || empty($horario_abertura) || $capacidade_maxima <= 0) {
        header("Location: ../frontend/criar_evento.html?erro=1");
        exit();
    }

    if (empty($nome_lote) || $preco_lote <= 0 || $taxa_plataforma < 0 || $quantidade_lote <= 0 || $quantidade_lote > $capacidade_maxima) {
        header("Location: ../frontend/criar_evento.html?erro=lote");
        exit();
    }

    if ($capacidade_maxima > (int)$balada["capacidade_maxima"]) {
        header("Location: ../frontend/criar_evento.html?erro=capacidade");
        exit();
    }

    // data nao pode ser no passado
    if ($data_evento < date("Y-m-d")) {
        header("Location: ../frontend/criar_evento.html?erro=data");
        exit();
    }

    mysqli_begin_transaction($conexao);

    $sql = "INSERT INTO eventos (balada_id, nome, descricao, data_evento, horario_abertura, idade_minima, capacidade_maxima) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "issssii", $balada["id"], $nome, $descricao, $data_evento, $horario_abertura, $idade_minima, $capacidade_maxima);

    if (mysqli_stmt_execute($stmt)) {
        $evento_id = mysqli_insert_id($conexao);
        mysqli_stmt_close($stmt);

        $sql_lote = "INSERT INTO ingressos_lotes (evento_id, nome_lote, preco, taxa_plataforma, quantidade_total) VALUES (?, ?, ?, ?, ?)";
        $stmt_lote = mysqli_prepare($conexao, $sql_lote);
        mysqli_stmt_bind_param($stmt_lote, "isddi", $evento_id, $nome_lote, $preco_lote, $taxa_plataforma, $quantidade_lote);

        if (mysqli_stmt_execute($stmt_lote)) {
            mysqli_commit($conexao);
            mysqli_stmt_close($stmt_lote);
            header("Location: ../frontend/listar_eventos.html?sucesso=1");
        } else {
            mysqli_rollback($conexao);
            mysqli_stmt_close($stmt_lote);
            header("Location: ../frontend/criar_evento.html?erro=db");
        }
    } else {
        mysqli_rollback($conexao);
        mysqli_stmt_close($stmt);
        header("Location: ../frontend/criar_evento.html?erro=db");
    }

    mysqli_close($conexao);
    exit();

} else {
    header("Location: ../frontend/criar_evento.html");
    exit();
}
?>