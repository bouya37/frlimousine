<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Journalisation des erreurs fatales pour faciliter le debug en prod
$bootstrapLogDir = __DIR__ . '/logs';
if (!is_dir($bootstrapLogDir)) {
    @mkdir($bootstrapLogDir, 0755, true);
}
$bootstrapLogFile = $bootstrapLogDir . '/send-reservation-error.log';
@ini_set('log_errors', '1');
@ini_set('display_errors', '0');
@ini_set('error_log', $bootstrapLogFile);

$config = require __DIR__ . '/config.php';
require __DIR__ . '/security.php';
require __DIR__ . '/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/src/SMTP.php';

$security = initSecurity();

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$logFile = $config['logging']['path'] ?? __DIR__ . '/logs/reservation-handler.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function logMessage(string $file, string $message): void
{
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($file, '[' . $timestamp . '] ' . $message . PHP_EOL, FILE_APPEND);
}

function isValidPhone(string $phone): bool
{
    $clean = preg_replace('/[^0-9+]/', '', $phone);
    $french = '/^(?:\\+|00)?33[1-9](?:[0-9]{2}){4}$|^0[1-9](?:[0-9]{2}){4}$/';
    $international = '/^\\+[1-9]\\d{1,14}$/';
    $europe = '/^\\+?[1-9]\\d{1,3}?[1-9]\\d{6,13}$/';

    return (bool)preg_match($french, $clean) ||
           (bool)preg_match($international, $clean) ||
           (bool)preg_match($europe, $clean);
}

if (!$security->checkRateLimit($_SERVER['REMOTE_ADDR'] ?? '')) {
    logMessage($logFile, 'RATE_LIMIT: request blocked');
    respond(429, ['error' => 'Trop de requêtes, veuillez réessayer plus tard.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['error' => 'Méthode non autorisée.']);
}

$rawInput = file_get_contents('php://input');
if (!$rawInput) {
    respond(400, ['error' => 'Payload vide.']);
}

$data = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    respond(400, ['error' => 'JSON invalide.']);
}

$client = $data['client'] ?? [];
$prix = $data['prix'] ?? [];

$requiredFields = [
    'nom', 'email', 'telephone', 'service', 'vehicule', 'passagers',
    'date', 'duree', 'heureDebut', 'lieuDepart', 'lieuArrivee'
];

foreach ($requiredFields as $field) {
    if (empty($client[$field])) {
        respond(422, ['error' => "Champ manquant : {$field}"]);
    }
}

if (!$security->validateEmail($client['email'])) {
    respond(422, ['error' => 'Email invalide']);
}

if (!isValidPhone((string)$client['telephone'])) {
    respond(422, ['error' => 'Téléphone invalide']);
}

foreach ($client as $key => $value) {
    if (is_string($value) && $security->detectAttack($value)) {
        logMessage($logFile, "ATTACK_DETECTED sur {$key}");
        respond(403, ['error' => 'Entrée non autorisée détectée.']);
    }
}

$client = $security->sanitizeInput($client);
$client['options'] = isset($client['options']) && is_array($client['options'])
    ? array_map('strip_tags', $client['options'])
    : [];
$client['message'] = $client['message'] ?? '';

$prixVehicule = $prix['vehicule'] ?? 0;
$prixOptions = $prix['options'] ?? 0;
$prixTotal = $prix['total'] ?? ($prixVehicule + $prixOptions);

$optionsList = count($client['options']) ? implode(', ', $client['options']) : 'Aucune';
$payloadTime = $data['submitted_at'] ?? date('c');

$emailBody = <<<EOT
Nouvelle demande via le formulaire Beverly Limousine

--- Informations client ---
Nom : {$client['nom']}
Email : {$client['email']}
Téléphone : {$client['telephone']}

--- Demande ---
Service : {$client['service']}
Véhicule : {$client['vehicule']}
Passagers : {$client['passagers']}
Date : {$client['date']}
Heure de début : {$client['heureDebut']}
Durée : {$client['duree']}h
Lieu de prise en charge : {$client['lieuDepart']}
Destination : {$client['lieuArrivee']}
Options : {$optionsList}
Message complémentaire :
{$client['message']}

--- Tarification estimée ---
Véhicule : {$prixVehicule} EUR
Options : {$prixOptions} EUR
Total : {$prixTotal} EUR

--- Métadonnées ---
Envoyé le : {$payloadTime}
IP : {$_SERVER['REMOTE_ADDR'] ?? 'non disponible'}
User-Agent : {$_SERVER['HTTP_USER_AGENT'] ?? 'non disponible'}
EOT;

$mailer = new PHPMailer(true);

try {
    $mailer->CharSet = 'UTF-8';
    $mailer->isSMTP();
    $mailer->Host = $config['smtp']['host'];
    $mailer->Port = $config['smtp']['port'];
    $mailer->SMTPAuth = $config['smtp']['auth'];
    $mailer->Username = $config['smtp']['username'];
    $mailer->Password = $config['smtp']['password'];
    $mailer->SMTPSecure = $config['smtp']['encryption'];
    $mailer->Timeout = $config['smtp']['timeout'] ?? 20;

    $fromEmail = $config['smtp']['from_email'] ?? $config['email']['from'];
    $fromName = $config['smtp']['from_name'] ?? 'Beverly Limousine';
    $mailer->setFrom($fromEmail, $fromName);
    $mailer->addAddress($config['email']['notification']);
    $mailer->addReplyTo($client['email'], $client['nom']);

    $mailer->Subject = 'Nouvelle demande Beverly Limousine';
    $mailer->Body = $emailBody;
    $mailer->AltBody = $emailBody;

    $mailer->send();

    logMessage($logFile, 'EMAIL_SENT pour ' . $client['nom']);
    respond(200, ['success' => true, 'message' => 'Demande envoyée']);
} catch (Exception $e) {
    logMessage($logFile, 'EMAIL_ERROR: ' . $e->getMessage());
    respond(500, ['error' => 'Erreur lors de l’envoi', 'details' => $e->getMessage()]);
}
