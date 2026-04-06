<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "gestor") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

// verificar se gestor tem balada
$sql_balada = "SELECT id FROM baladas WHERE gestor_id = ? AND ativo = 1";
$stmt_balada = mysqli_prepare($conexao, $sql_balada);
mysqli_stmt_bind_param($stmt_balada, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_balada);
$resultado_balada = mysqli_stmt_get_result($stmt_balada);
$balada = mysqli_fetch_assoc($resultado_balada);
mysqli_stmt_close($stmt_balada);

if (!$balada) {
    echo json_encode(["erro" => "Gestor sem balada"]);
    exit();
}

// GET: retorna dados do funcionario
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $sql = "SELECT id, nome, email, ativo FROM usuarios WHERE id = ? AND tipo = 'funcionario' AND balada_id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $balada["id"]);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $funcionario = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);

    if ($funcionario) {
        echo json_encode($funcionario);
    } else {
        echo json_encode(["erro" => "Funcionário não encontrado"]);
    }
    exit();
}

// POST: atualiza dados do funcionario
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = intval($_POST["id"]);
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);

    if (empty($nome) || empty($email)) {
        header("Location: ../frontend/editar_funcionario.html?id=" . $id . "&erro=1");
        exit();
    }

    // verificar se email ja existe em outro usuario
    $sql_check = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt_check = mysqli_prepare($conexao, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "si", $email, $id);
    mysqli_stmt_execute($stmt_check);
    $resultado_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($resultado_check) > 0) {
        header("Location: ../frontend/editar_funcionario.html?id=" . $id . "&erro=email");
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // verificar se funcionario pertence a balada do gestor
    $sql_verificar = "SELECT id FROM usuarios WHERE id = ? AND tipo = 'funcionario' AND balada_id = ?";
    $stmt_verificar = mysqli_prepare($conexao, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "ii", $id, $balada["id"]);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
    mysqli_stmt_close($stmt_verificar);

    if (mysqli_num_rows($resultado_verificar) == 0) {
        header("Location: ../frontend/listar_funcionarios.html?erro=acesso");
        exit();
    }

    // atualizar
    $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $nome, $email, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../frontend/listar_funcionarios.html?sucesso=1");
    } else {
        header("Location: ../frontend/editar_funcionario.html?id=" . $id . "&erro=db");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    exit();
}
?>
