<?php
// Test rapide d'envoi d'email via la configuration OVH/PHP.
// Accédez à /mail-test.php depuis le navigateur pour vérifier que l'envoi fonctionne.

$config = require __DIR__ . '/config.php';

$to = $config['email']['notification'];
$from = $config['email']['from'];
$subject = 'Test envoi OVH - Beverly Limousine';
$message = "Test d'envoi effectué le " . date('d/m/Y H:i:s') . " depuis mail-test.php.\n";

$headers = [
    'From: ' . $from,
    'Reply-To: ' . $from,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
];

$sent = mail($to, $subject, $message, implode("\r\n", $headers));

header('Content-Type: text/plain; charset=UTF-8');
echo $sent ? "OK - message envoyé à $to" : 'ERREUR - envoi impossible';
