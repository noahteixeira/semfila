<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "admin") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

// GET: retorna dados do gestor para preencher o formulario
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $sql = "SELECT u.id, u.nome, u.email, c.cnpj, c.razao_social, c.data_inicio, c.data_vencimento, c.status, c.observacoes
            FROM usuarios u
            INNER JOIN contratos_gestores c ON c.usuario_id = u.id
            WHERE u.id = ? AND u.tipo = 'gestor'";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $gestor = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);

    echo json_encode($gestor ? $gestor : ["erro" => "Gestor não encontrado"]);
    exit();
}

// POST: atualiza dados do gestor
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = intval($_POST["id"]);
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $cnpj = $_POST["cnpj"];
    $razao_social = $_POST["razao_social"];
    $data_inicio = $_POST["data_inicio"];
    $data_vencimento = $_POST["data_vencimento"];
    $status = $_POST["status"];
    $observacoes = $_POST["observacoes"];

    if (empty($nome) || empty($email) || empty($cnpj) || empty($razao_social) || empty($data_inicio) || empty($data_vencimento)) {
        header("Location: ../frontend/editar_gestor.html?id=" . $id . "&erro=1");
        exit();
    }

    // validar CNPJ
    $cnpj_limpo = preg_replace("/\D/", "", $cnpj);
    if (strlen($cnpj_limpo) != 14) {
        header("Location: ../frontend/editar_gestor.html?id=" . $id . "&erro=2");
        exit();
    }

    // verificar se email ja existe em outro usuario
    $sql_check = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt_check = mysqli_prepare($conexao, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "si", $email, $id);
    mysqli_stmt_execute($stmt_check);
    $resultado_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($resultado_check) > 0) {
        header("Location: ../frontend/editar_gestor.html?id=" . $id . "&erro=3");
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // verificar se CNPJ ja existe em outro contrato
    $sql_check_cnpj = "SELECT id FROM contratos_gestores WHERE cnpj = ? AND usuario_id != ?";
    $stmt_check_cnpj = mysqli_prepare($conexao, $sql_check_cnpj);
    mysqli_stmt_bind_param($stmt_check_cnpj, "si", $cnpj_limpo, $id);
    mysqli_stmt_execute($stmt_check_cnpj);
    $resultado_check_cnpj = mysqli_stmt_get_result($stmt_check_cnpj);

    if (mysqli_num_rows($resultado_check_cnpj) > 0) {
        header("Location: ../frontend/editar_gestor.html?id=" . $id . "&erro=4");
        exit();
    }
    mysqli_stmt_close($stmt_check_cnpj);

    // atualizar usuario
    $sql_usuario = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ? AND tipo = 'gestor'";
    $stmt_usuario = mysqli_prepare($conexao, $sql_usuario);
    mysqli_stmt_bind_param($stmt_usuario, "ssi", $nome, $email, $id);
    mysqli_stmt_execute($stmt_usuario);
    mysqli_stmt_close($stmt_usuario);

    // atualizar contrato
    $sql_contrato = "UPDATE contratos_gestores SET cnpj = ?, razao_social = ?, data_inicio = ?, data_vencimento = ?, status = ?, observacoes = ? WHERE usuario_id = ?";
    $stmt_contrato = mysqli_prepare($conexao, $sql_contrato);
    mysqli_stmt_bind_param($stmt_contrato, "ssssssi", $cnpj_limpo, $razao_social, $data_inicio, $data_vencimento, $status, $observacoes, $id);

    if (mysqli_stmt_execute($stmt_contrato)) {
        header("Location: ../frontend/editar_gestor.html?id=" . $id . "&sucesso=1");
    } else {
        header("Location: ../frontend/editar_gestor.html?id=" . $id . "&erro=5");
    }

    mysqli_stmt_close($stmt_contrato);
    mysqli_close($conexao);
    exit();
}
?>
