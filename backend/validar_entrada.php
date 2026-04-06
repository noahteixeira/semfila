<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "funcionario") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "GET" || !isset($_GET["codigo"])) {
    echo json_encode(["erro" => "Dados inválidos"]);
    exit();
}

$codigo = trim($_GET["codigo"]);
$metodo = "qr_code";
$ingresso = null;

// primeiro tenta buscar como QR code
$sql = "SELECT i.id, i.status, i.comprado_em, il.preco, e.id AS evento_id, e.idade_minima, e.nome AS evento_nome, 
                u.id AS usuario_id, u.nome, u.data_nascimento, u.foto_perfil
        FROM ingressos i
        INNER JOIN ingressos_lotes il ON i.lote_id = il.id
        INNER JOIN eventos e ON il.evento_id = e.id
        INNER JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.qr_code = ?";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "s", $codigo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$ingresso = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

// se nao encontrou como QR code, tentar como RFID de pulseira
if (!$ingresso) {
    $metodo = "rfid";

    $sql_rfid = "SELECT i.id, i.status, i.comprado_em, il.preco, e.id AS evento_id, e.idade_minima, e.nome AS evento_nome,
                        u.id AS usuario_id, u.nome, u.data_nascimento, u.foto_perfil
                 FROM pulseiras p
                 INNER JOIN usuarios u ON p.usuario_id = u.id
                 INNER JOIN ingressos i ON i.usuario_id = u.id
                 INNER JOIN ingressos_lotes il ON i.lote_id = il.id
                 INNER JOIN eventos e ON il.evento_id = e.id
                 WHERE p.codigo_rfid = ? AND i.status = 'disponivel'
                 ORDER BY i.comprado_em DESC
                 LIMIT 1";

    $stmt_rfid = mysqli_prepare($conexao, $sql_rfid);
    mysqli_stmt_bind_param($stmt_rfid, "s", $codigo);
    mysqli_stmt_execute($stmt_rfid);
    $resultado_rfid = mysqli_stmt_get_result($stmt_rfid);
    $ingresso = mysqli_fetch_assoc($resultado_rfid);
    mysqli_stmt_close($stmt_rfid);
}

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
$ano_nasc = date("Y", strtotime($ingresso["data_nascimento"]));
$mes_nasc = date("m", strtotime($ingresso["data_nascimento"]));
$dia_nasc = date("d", strtotime($ingresso["data_nascimento"]));

$idade = date("Y") - $ano_nasc;
if (date("m") < $mes_nasc || (date("m") == $mes_nasc && date("d") < $dia_nasc)) {
    $idade = $idade - 1;
}

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
    "foto_perfil" => $ingresso["foto_perfil"],
    "metodo" => $metodo
]);
?>
