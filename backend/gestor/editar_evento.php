<?php
include("../auth_check.php");
include("../conexao.php");

// verificar se é gestor
if ($_SESSION["usuario_tipo"] != "gestor") {
    header("Location: ../../frontend/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $evento_id = (int)$_POST["evento_id"];
    $nome = trim($_POST["nome"]);
    $descricao = trim($_POST["descricao"]);
    $data_evento = $_POST["data_evento"];
    $horario_abertura = $_POST["horario_abertura"];
    $idade_minima = (int)$_POST["idade_minima"];
    $capacidade_maxima = (int)$_POST["capacidade_maxima"];

    // validações
    if (empty($nome) || empty($data_evento) || empty($horario_abertura) || $capacidade_maxima <= 0) {
        header("Location: ../../frontend/gestor/editar_evento.html?id=$evento_id&erro=1");
        exit();
    }

    // data não pode ser no passado
    $data_atual = date("Y-m-d");
    if ($data_evento < $data_atual) {
        header("Location: ../../frontend/gestor/editar_evento.html?id=$evento_id&erro=data");
        exit();
    }

    // verificar se evento pertence ao gestor
    $sql_verificar = "SELECT e.id FROM eventos e INNER JOIN baladas b ON e.balada_id = b.id WHERE e.id = ? AND b.gestor_id = ? AND b.ativo = 1";
    $stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "ii", $evento_id, $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
    mysqli_stmt_close($stmt_verificar);

    if (mysqli_num_rows($resultado_verificar) == 0) {
        header("Location: ../../frontend/gestor/listar_eventos.html?erro=acesso");
        exit();
    }

    // atualizar evento
    $sql = "UPDATE eventos SET nome = ?, descricao = ?, data_evento = ?, horario_abertura = ?, idade_minima = ?, capacidade_maxima = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ssssiii", $nome, $descricao, $data_evento, $horario_abertura, $idade_minima, $capacidade_maxima, $evento_id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/gestor/listar_eventos.html?sucesso=2");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/gestor/editar_evento.html?id=$evento_id&erro=db");
        exit();
    }
} else {
    // GET: buscar dados do evento para edição
    $evento_id = (int)$_GET["id"];

    $sql = "SELECT e.id, e.nome, e.descricao, e.data_evento, e.horario_abertura, e.idade_minima, e.capacidade_maxima
            FROM eventos e
            INNER JOIN baladas b ON e.balada_id = b.id
            WHERE e.id = ? AND b.gestor_id = ? AND b.ativo = 1";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $evento_id, $_SESSION["usuario_id"]);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $evento = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);

    if ($evento) {
        echo json_encode($evento);
    } else {
        echo json_encode(["erro" => "Evento não encontrado ou acesso negado"]);
    }
}
?>