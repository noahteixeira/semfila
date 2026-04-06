<?php
include("../auth_check.php");
include("../conexao.php");

// verificar se é gestor
if ($_SESSION["usuario_tipo"] != "gestor") {
    header("Location: ../../frontend/login.html");
    exit();
}

// verificar se gestor tem balada cadastrada
$sql_balada = "SELECT id FROM baladas WHERE gestor_id = ? AND ativo = 1";
$stmt_balada = mysqli_prepare($conexao, $sql_balada);
mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_balada);
$resultado_balada = mysqli_stmt_get_result($stmt_balada);
$balada = mysqli_fetch_assoc($resultado_balada);
mysqli_stmt_close($stmt_balada);

if (!$balada) {
    header("Location: ../../frontend/gestor/listar_eventos.html?erro=balada");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $descricao = trim($_POST["descricao"]);
    $data_evento = $_POST["data_evento"];
    $horario_abertura = $_POST["horario_abertura"];
    $idade_minima = (int)$_POST["idade_minima"];
    $capacidade_maxima = (int)$_POST["capacidade_maxima"];

    // validações
    if (empty($nome) || empty($data_evento) || empty($horario_abertura) || $capacidade_maxima <= 0) {
        header("Location: ../../frontend/gestor/criar_evento.html?erro=1");
        exit();
    }

    // data não pode ser no passado
    $data_atual = date("Y-m-d");
    if ($data_evento < $data_atual) {
        header("Location: ../../frontend/gestor/criar_evento.html?erro=data");
        exit();
    }

    // inserir evento
    $sql = "INSERT INTO eventos (balada_id, nome, descricao, data_evento, horario_abertura, idade_minima, capacidade_maxima) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "issssis", $balada["id"], $nome, $descricao, $data_evento, $horario_abertura, $idade_minima, $capacidade_maxima);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/gestor/listar_eventos.html?sucesso=1");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        header("Location: ../../frontend/gestor/criar_evento.html?erro=db");
        exit();
    }
} else {
    header("Location: ../../frontend/gestor/criar_evento.html");
    exit();
}
?>