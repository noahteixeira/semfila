<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "funcionario") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "GET" || !isset($_GET["qr_code"])) {
    echo json_encode(["erro" => "Dados inválidos"]);
    exit();
}

$qr_code = trim($_GET["qr_code"]);

// buscar ingresso pelo QR code
$sql = "SELECT i.id, i.status, i.comprado_em, il.preco, e.id AS evento_id, e.idade_minima, e.nome AS evento_nome, 
                u.id AS usuario_id, u.nome, u.data_nascimento, u.foto_perfil
        FROM ingressos i
        INNER JOIN ingressos_lotes il ON i.lote_id = il.id
        INNER JOIN eventos e ON il.evento_id = e.id
        INNER JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.qr_code = ?";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "s", $qr_code);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$ingresso = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$ingresso) {
    echo json_encode(["erro" => "Ingresso não encontrado"]);
    exit();
}

if ($ingresso["status"] == "utilizado") {
    echo json_encode(["erro" => "Ingresso já utilizado"]);
    exit();
}

if ($ingresso["status"] == "cancelado") {
    echo json_encode(["erro" => "Ingresso cancelado"]);
    exit();
}

// verificar idade
$nascimento = new DateTime($ingresso["data_nascimento"]);
$hoje = new DateTime();
$idade = $hoje->diff($nascimento)->y;

if ($idade < $ingresso["idade_minima"]) {
    echo json_encode(["erro" => "Idade mínima: " . $ingresso["idade_minima"] . " anos. Participante tem " . $idade . " anos."]);
    exit();
}

mysqli_close($conexao);

// retornar dados para validacao
echo json_encode([
    "sucesso" => true,
    "ingresso_id" => $ingresso["id"],
    "evento_id" => $ingresso["evento_id"],
    "evento_nome" => $ingresso["evento_nome"],
    "usuario_id" => $ingresso["usuario_id"],
    "nome" => $ingresso["nome"],
    "idade" => $idade,
    "foto_perfil" => $ingresso["foto_perfil"]
]);
?>
