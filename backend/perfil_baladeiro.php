<?php
include("auth_check.php");
include("conexao.php");

// verificar se é baladeiro
if ($_SESSION["usuario_tipo"] != "baladeiro") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

// buscar dados do usuario
$sql = "SELECT nome, email, cpf, data_nascimento, foto_perfil, documento_url, criado_em FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode($usuario);
?>