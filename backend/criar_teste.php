<?php
$servidor = "localhost";
$usuario = "root";
$senha_banco = "";
$banco = "semfila";

$conexao = mysqli_connect($servidor, $usuario, $senha_banco, $banco);

if (!$conexao) {
    die("Erro ao conectar no banco de dados.");
}

// Verificar se existe balada
$sql_balada = "SELECT id FROM baladas LIMIT 1";
$resultado = mysqli_query($conexao, $sql_balada);
$balada = mysqli_fetch_assoc($resultado);

if (!$balada) {
    echo "Nenhuma balada encontrada. Criando dados de teste...<br>";
    
    // Criar gestor de teste
    $sql_gestor = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES ('Gestor Teste', 'gestor@teste.com', '" . password_hash('123456', PASSWORD_DEFAULT) . "', 'gestor')";
    mysqli_query($conexao, $sql_gestor);
    $gestor_id = mysqli_insert_id($conexao);
    
    // Criar balada de teste
    $sql_balada_insert = "INSERT INTO baladas (gestor_id, nome, cnpj, endereco, cidade, capacidade_maxima) VALUES ($gestor_id, 'Balada Teste', '12.345.678/0001-00', 'Rua das Flores, 123', 'São Paulo', 500)";
    mysqli_query($conexao, $sql_balada_insert);
    $balada_id = mysqli_insert_id($conexao);
} else {
    $balada_id = $balada['id'];
}

// Criar evento de teste (data futura)
$data_evento = date('Y-m-d', strtotime('+15 days'));
$horario = '22:00:00';

$sql_evento = "INSERT INTO eventos (balada_id, nome, descricao, data_evento, horario_abertura, idade_minima, capacidade_maxima, status) 
              VALUES ($balada_id, 'Festa de Teste', 'Uma festa de teste para visualizar o sistema', '$data_evento', '$horario', 18, 500, 'ativo')";

if (mysqli_query($conexao, $sql_evento)) {
    echo "✓ Evento de teste criado com sucesso!<br>";
    echo "Nome: Festa de Teste<br>";
    echo "Data: " . date('d/m/Y', strtotime($data_evento)) . " às 22h<br>";
    echo "Capacidade: 500 pessoas<br>";
    echo "<br>Você já pode ver o evento em: <a href='../frontend/baladeiro/ver_eventos.html'>Ver Eventos</a>";
} else {
    echo "Erro ao criar evento: " . mysqli_error($conexao);
}

mysqli_close($conexao);
?>