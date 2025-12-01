<?php
// Test 1: Vérification que PHP fonctionne
echo "✅ PHP fonctionne !";
echo "<br>Version PHP: " . PHP_VERSION;
echo "<br>Date: " . date('Y-m-d H:i:s');
echo "<br>Si vous voyez ce message, PHP fonctionne sur votre serveur.";

// Test 2: Extensions PHP requises
echo "<br><br>Extensions requises :";
echo "<br>OpenSSL: " . (extension_loaded('openssl') ? '✅' : '❌');
echo "<br>cURL: " . (extension_loaded('curl') ? '✅' : '❌');
echo "<br>mbstring: " . (extension_loaded('mbstring') ? '✅' : '❌');

// Test 3: Dossiers
echo "<br><br>Dossiers :";
echo "<br>logs/ existe: " . (is_dir('logs') ? '✅' : '❌');
echo "<br>pdfs/ existe: " . (is_dir('pdfs') ? '✅' : '❌');
echo "<br>vendor/ existe: " . (is_dir('vendor') ? '✅' : '❌');
?>