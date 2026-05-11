<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "admin") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$sql = "SELECT u.id, u.nome, u.email
        FROM usuarios u
        INNER JOIN contratos_gestores c ON c.usuario_id = u.id
        LEFT JOIN baladas b ON b.gestor_id = u.id AND b.ativo = 1
        WHERE u.tipo = 'gestor'
        AND u.ativo = 1
        AND c.status = 'ativo'
        AND c.data_vencimento >= CURDATE()
        AND b.id IS NULL
        ORDER BY u.nome ASC";

$resultado = mysqli_query($conexao, $sql);
$gestores = [];

while ($gestor = mysqli_fetch_assoc($resultado)) {
    $gestores[] = $gestor;
}

mysqli_close($conexao);
echo json_encode($gestores);
?>