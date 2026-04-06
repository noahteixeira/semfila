<?php
$conexao = mysqli_connect('localhost', 'root', '', 'semfila');
$sql = 'DELETE FROM eventos WHERE nome = "Festa de Teste" LIMIT 1';
mysqli_query($conexao, $sql);
echo 'Um evento removido!';
mysqli_close($conexao);
?>