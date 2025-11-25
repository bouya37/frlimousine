<?php
declare(strict_types=1);

// Configuration debug (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

mb_internal_encoding('UTF-8');
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$config = require __DIR__ . '/config.php';

// Utilitaires simples
function clean(string $value): string {
    return trim(filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
}

function write_log(string $message): void {
    $logFile = __DIR__ . '/pdfs/mail.log';
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    
    // Créer le dossier s'il n'existe pas
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    
    // Log aussi dans error_log PHP si disponible
    error_log($message);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || empty($data['client'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload JSON invalide']);
    exit;
}

$client = $data['client'];
$prix = $data['prix'] ?? [];

$required = ['nom','email','telephone','service','vehicule','passagers','date','duree','lieuDepart','lieuArrivee'];
foreach ($required as $field) {
    if (empty($client[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Champ manquant: $field"]);
        exit;
    }
}

if (!filter_var($client['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email invalide']);
    exit;
}

$message =
    "Nouvelle demande de reservation\n\n" .
    "CLIENT\n" .
    "Nom: " . clean($client['nom']) . "\n" .
    "Email: " . clean($client['email']) . "\n" .
    "Telephone: " . clean($client['telephone']) . "\n\n" .
    "DETAILS\n" .
    "Service: " . clean($client['service']) . "\n" .
    "Vehicule: " . clean($client['vehicule']) . "\n" .
    "Passagers: " . clean((string)$client['passagers']) . "\n" .
    "Date: " . clean($client['date']) . (empty($client['heureDebut']) ? '' : ' a ' . clean((string)$client['heureDebut'])) . "\n" .
    "Duree: " . clean((string)$client['duree']) . " heures\n" .
    "Depart: " . clean($client['lieuDepart']) . "\n" .
    "Arrivee: " . clean($client['lieuArrivee']) . "\n" .
    "Options: " . (empty($client['options']) ? 'Aucune' : clean(implode(', ', $client['options']))) . "\n\n" .
    "PRIX\n" .
    "Vehicule: " . ($prix['vehicule'] ?? '0') . ' ' . ($prix['devise'] ?? 'EUR') . "\n" .
    "Options: " . ($prix['options'] ?? '0') . ' ' . ($prix['devise'] ?? 'EUR') . "\n" .
    "Total: " . ($prix['total'] ?? '0') . ' ' . ($prix['devise'] ?? 'EUR') . "\n\n" .
    "Message client: " . (empty($client['message']) ? 'Aucun' : clean($client['message'])) . "\n" .
    "Recu le: " . date('d/m/Y H:i:s') . "\n";

$subject = 'Nouvelle demande - Beverly Limousine - ' . clean($client['nom']);
$headers = [
    'From: ' . $config['email']['from'],
    'Reply-To: ' . clean($client['email']),
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: PHP/' . phpversion()
];

// Logs détaillés avant envoi
write_log('=== DEBUT ENVOI EMAIL ===');
write_log('TO: ' . $config['email']['notification']);
write_log('FROM: ' . $config['email']['from']);
write_log('SUBJECT: ' . $subject);
write_log('PHP_VERSION: ' . phpversion());
write_log('SMTP_SERVER: ' . ini_get('SMTP') . ':' . ini_get('smtp_port'));
write_log('SENDMAIL_PATH: ' . (ini_get('sendmail_path') ?: 'non défini'));
write_log('CLIENT_EMAIL: ' . $client['email']);

// Tentative d'envoi
$sent = mail($config['email']['notification'], $subject, $message, implode("\r\n", $headers));

// Logs détaillés après envoi
$lastError = error_get_last();
if ($lastError) {
    write_log('PHP_LAST_ERROR: ' . $lastError['message']);
}

write_log('MAIL_RESULT: ' . ($sent ? 'SUCCESS' : 'FAILED'));
write_log('=== FIN ENVOI EMAIL ===');

if (!$sent) {
    write_log('ENVOI_ECHOUE - Details: ' . print_r($lastError, true));
    
    // Réponse d'erreur améliorée pour le debug
    $errorResponse = [
        'error' => 'Impossible d\'envoyer le mail',
        'debug_info' => [
            'php_version' => phpversion(),
            'smtp_config' => ini_get('SMTP') . ':' . ini_get('smtp_port'),
            'sendmail_path' => ini_get('sendmail_path'),
            'last_error' => $lastError
        ]
    ];
    
    http_response_code(500);
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => true]);
?>
