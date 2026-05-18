<?php

$servidor = "localhost";
$usuario = "root";
$senha_banco = "";
$banco = "semfila";

if (!isset($conexao)) {
    $conexao = mysqli_connect($servidor, $usuario, $senha_banco, $banco);

    if (!$conexao) {
        die("Erro ao conectar no banco de dados.");
    }
}

?>