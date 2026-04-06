<?php
$conexao = mysqli_connect('localhost', 'root', '', 'semfila');

// Criar tabela changelogs se não existir
$sql = "CREATE TABLE IF NOT EXISTS changelogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    versao VARCHAR(20) NOT NULL UNIQUE,
    data DATE NOT NULL,
    descricao TEXT NOT NULL,
    autor VARCHAR(100) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conexao, $sql)) {
    echo "✓ Tabela de changelog criada/já existe!";
} else {
    echo "Erro: " . mysqli_error($conexao);
}

mysqli_close($conexao);
?>