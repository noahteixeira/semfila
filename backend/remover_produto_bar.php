<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "gestor") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["erro" => "Método inválido"]);
    exit();
}

$id = (int)$_POST["id"];

if ($id <= 0) {
    echo json_encode(["erro" => "Produto inválido"]);
    exit();
}

$sql = "DELETE pb FROM produtos_bar pb INNER JOIN baladas b ON pb.balada_id = b.id WHERE pb.id = ? AND b.gestor_id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION["usuario_id"]);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_affected_rows($conexao) > 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        echo json_encode(["sucesso" => true, "mensagem" => "Produto removido com sucesso"]);
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        echo json_encode(["erro" => "Produto não encontrado"]);
    }
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    echo json_encode(["erro" => "Erro ao remover produto"]);
}
?>
