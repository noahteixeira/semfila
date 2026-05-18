<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "gestor") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

// GET: retorna dados do evento para o formulario
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {

    $evento_id = (int)$_GET["id"];

    $sql = "SELECT e.id, e.nome, e.descricao, e.data_evento, e.horario_abertura, e.idade_minima, e.capacidade_maxima
            FROM eventos e
            INNER JOIN baladas b ON e.balada_id = b.id
            WHERE e.id = ? AND b.gestor_id = ? AND b.ativo = 1 AND e.status = 'ativo'";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $evento_id, $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $evento = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if ($evento) {
        $sql_lote = "SELECT id, nome_lote, preco, taxa_plataforma, quantidade_total, quantidade_vendida FROM ingressos_lotes WHERE evento_id = ? AND ativo = 1 ORDER BY id ASC LIMIT 1";
        $stmt_lote = mysqli_prepare($conexao, $sql_lote);
        mysqli_stmt_bind_param($stmt_lote, "i", $evento_id);
        mysqli_stmt_execute($stmt_lote);
        $resultado_lote = mysqli_stmt_get_result($stmt_lote);
        $lote = mysqli_fetch_assoc($resultado_lote);
        mysqli_stmt_close($stmt_lote);

        if ($lote) {
            $evento["lote_id"] = $lote["id"];
            $evento["nome_lote"] = $lote["nome_lote"];
            $evento["preco_lote"] = $lote["preco"];
            $evento["taxa_plataforma"] = $lote["taxa_plataforma"];
            $evento["quantidade_lote"] = $lote["quantidade_total"];
            $evento["quantidade_vendida"] = $lote["quantidade_vendida"];
        }

        mysqli_close($conexao);
        echo json_encode($evento);
    } else {
        mysqli_close($conexao);
        echo json_encode(["erro" => "Evento não encontrado"]);
    }
    exit();
}

// POST: atualiza dados do evento
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $evento_id = (int)$_POST["evento_id"];
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
        header("Location: ../frontend/editar_evento.html?id=" . $evento_id . "&erro=1");
        exit();
    }

    if (empty($nome_lote) || $preco_lote <= 0 || $taxa_plataforma < 0 || $quantidade_lote <= 0 || $quantidade_lote > $capacidade_maxima) {
        header("Location: ../frontend/editar_evento.html?id=" . $evento_id . "&erro=lote");
        exit();
    }

    if ($data_evento < date("Y-m-d")) {
        header("Location: ../frontend/editar_evento.html?id=" . $evento_id . "&erro=data");
        exit();
    }

    // verificar se evento pertence ao gestor
    $sql_verificar = "SELECT e.id, b.capacidade_maxima FROM eventos e INNER JOIN baladas b ON e.balada_id = b.id WHERE e.id = ? AND b.gestor_id = ? AND b.ativo = 1 AND e.status = 'ativo'";
    $stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "ii", $evento_id, $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
    $evento = mysqli_fetch_assoc($resultado_verificar);
    mysqli_stmt_close($stmt_verificar);

    if (!$evento) {
        header("Location: ../frontend/listar_eventos.html?erro=acesso");
        exit();
    }

    if ($capacidade_maxima > (int)$evento["capacidade_maxima"]) {
        header("Location: ../frontend/editar_evento.html?id=" . $evento_id . "&erro=capacidade");
        exit();
    }

    $sql_lote_atual = "SELECT id, quantidade_vendida FROM ingressos_lotes WHERE evento_id = ? AND ativo = 1 ORDER BY id ASC LIMIT 1";
    $stmt_lote_atual = mysqli_prepare($conexao, $sql_lote_atual);
    mysqli_stmt_bind_param($stmt_lote_atual, "i", $evento_id);
    mysqli_stmt_execute($stmt_lote_atual);
    $resultado_lote_atual = mysqli_stmt_get_result($stmt_lote_atual);
    $lote_atual = mysqli_fetch_assoc($resultado_lote_atual);
    mysqli_stmt_close($stmt_lote_atual);

    if ($lote_atual && $quantidade_lote < (int)$lote_atual["quantidade_vendida"]) {
        header("Location: ../frontend/editar_evento.html?id=" . $evento_id . "&erro=lote");
        exit();
    }

    mysqli_begin_transaction($conexao);

    $sql = "UPDATE eventos SET nome = ?, descricao = ?, data_evento = ?, horario_abertura = ?, idade_minima = ?, capacidade_maxima = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ssssiii", $nome, $descricao, $data_evento, $horario_abertura, $idade_minima, $capacidade_maxima, $evento_id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        if ($lote_atual) {
            $sql_lote = "UPDATE ingressos_lotes SET nome_lote = ?, preco = ?, taxa_plataforma = ?, quantidade_total = ? WHERE id = ?";
            $stmt_lote = mysqli_prepare($conexao, $sql_lote);
            mysqli_stmt_bind_param($stmt_lote, "sddii", $nome_lote, $preco_lote, $taxa_plataforma, $quantidade_lote, $lote_atual["id"]);
        } else {
            $sql_lote = "INSERT INTO ingressos_lotes (evento_id, nome_lote, preco, taxa_plataforma, quantidade_total) VALUES (?, ?, ?, ?, ?)";
            $stmt_lote = mysqli_prepare($conexao, $sql_lote);
            mysqli_stmt_bind_param($stmt_lote, "isddi", $evento_id, $nome_lote, $preco_lote, $taxa_plataforma, $quantidade_lote);
        }

        if (mysqli_stmt_execute($stmt_lote)) {
            mysqli_commit($conexao);
            mysqli_stmt_close($stmt_lote);
            header("Location: ../frontend/listar_eventos.html?sucesso=2");
        } else {
            mysqli_rollback($conexao);
            mysqli_stmt_close($stmt_lote);
            header("Location: ../frontend/editar_evento.html?id=" . $evento_id . "&erro=db");
        }
    } else {
        mysqli_rollback($conexao);
        mysqli_stmt_close($stmt);
        header("Location: ../frontend/editar_evento.html?id=" . $evento_id . "&erro=db");
    }

    mysqli_close($conexao);
    exit();
}
?>