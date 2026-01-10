<?php
// Génération du token CSRF pour sécuriser le formulaire
session_start();

// Générer un nouveau token CSRF s'il n'existe pas ou s'il a plus d'une heure
if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['csrf_token' => $_SESSION['csrf_token']]);
?>

