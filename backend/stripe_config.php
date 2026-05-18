<?php

// Arquivo versionado.
// Coloque a chave real em backend/stripe_config.local.php
$STRIPE_SECRET_KEY = "";

$arquivo_local = __DIR__ . "/stripe_config.local.php";
if (file_exists($arquivo_local)) {
	include($arquivo_local);
}

?>
