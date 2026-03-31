<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "admin") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

$sql = "SELECT u.id, u.nome, u.email, u.ativo, c.cnpj, c.razao_social, c.data_inicio, c.data_vencimento, c.status, c.observacoes
        FROM usuarios u
        INNER JOIN contratos_gestores c ON c.usuario_id = u.id
        WHERE u.tipo = 'gestor'
        ORDER BY c.status ASC, u.nome ASC";

$resultado = mysqli_query($conexao, $sql);
$gestores = [];

while ($gestor = mysqli_fetch_assoc($resultado)) {
    $gestores[] = $gestor;
}

mysqli_close($conexao);
echo json_encode($gestores);
?>
