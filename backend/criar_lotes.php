<?php
$conexao = mysqli_connect('localhost', 'root', '', 'semfila');

// Pegar o último evento criado (Festa de Teste)
$sql_evento = "SELECT id FROM eventos WHERE nome = 'Festa de Teste' ORDER BY id DESC LIMIT 1";
$resultado = mysqli_query($conexao, $sql_evento);
$evento = mysqli_fetch_assoc($resultado);

if ($evento) {
    $evento_id = $evento['id'];
    
    // Criar lotes de exemplo
    $lotes = [
        ['nome' => 'Lote Pré-Venda', 'preco' => 50.00, 'taxa' => 5.00, 'quantidade' => 100],
        ['nome' => 'Lote Comum', 'preco' => 80.00, 'taxa' => 8.00, 'quantidade' => 200],
        ['nome' => 'Lote VIP', 'preco' => 150.00, 'taxa' => 15.00, 'quantidade' => 50]
    ];
    
    foreach ($lotes as $lote) {
        $sql_insert = "INSERT INTO ingressos_lotes (evento_id, nome_lote, preco, taxa_plataforma, quantidade_total, quantidade_vendida, ativo) 
                      VALUES ($evento_id, '{$lote['nome']}', {$lote['preco']}, {$lote['taxa']}, {$lote['quantidade']}, 0, 1)";
        mysqli_query($conexao, $sql_insert);
    }
    
    echo "✓ Lotes de ingressos criados com sucesso!<br>";
    echo "- Lote Pré-Venda: R$ 50.00<br>";
    echo "- Lote Comum: R$ 80.00<br>";
    echo "- Lote VIP: R$ 150.00<br>";
    echo "<br>Agora você pode comprar ingressos na página de compra!";
} else {
    echo "Nenhum evento encontrado!";
}

mysqli_close($conexao);
?>