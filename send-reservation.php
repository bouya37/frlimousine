<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ---------- Bootstrap logging (avoid blank 500) ----------
function bl_get_log_file(): string
{
    $primaryDir = __DIR__ . '/logs';
    if (!is_dir($primaryDir)) {
        @mkdir($primaryDir, 0755, true);
    }
    if (is_dir($primaryDir) && is_writable($primaryDir)) {
        return $primaryDir . '/send-reservation-error.log';
    }
    if (is_writable(__DIR__)) {
        return __DIR__ . '/send-reservation-error.log';
    }
    return rtrim(sys_get_temp_dir(), '/\\') . '/send-reservation-error.log';
}

$bootstrapLogFile = bl_get_log_file();
@ini_set('log_errors', '1');
@ini_set('display_errors', '0');
@ini_set('error_log', $bootstrapLogFile);
@error_reporting(E_ALL);

register_shutdown_function(function () use ($bootstrapLogFile) {
    $error = error_get_last();
    if ($error !== null) {
        $line = '[' . date('Y-m-d H:i:s') . '] SHUTDOWN ' . $error['type'] . ' ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] . PHP_EOL;
        @file_put_contents($bootstrapLogFile, $line, FILE_APPEND);
    }
});

// ---------- Dependencies ----------
$config = require __DIR__ . '/config.php';
$DEBUG = $config['debug'] ?? false;
require __DIR__ . '/security.php';
require __DIR__ . '/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/src/SMTP.php';

$security = initSecurity();

// ---------- HTTP headers ----------
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ---------- Helpers ----------
$logFile = $config['logging']['path'] ?? __DIR__ . '/logs/reservation-handler.log';
if (!is_dir(dirname($logFile))) {
    @mkdir(dirname($logFile), 0755, true);
}

function respond(int $status, array $payload): void
{
    global $DEBUG;
    if (!$DEBUG) {
        unset($payload['details'], $payload['trace']);
    }
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function logMessage(string $file, string $message): void
{
    $timestamp = date('Y-m-d H:i:s');
    @file_put_contents($file, '[' . $timestamp . '] ' . $message . PHP_EOL, FILE_APPEND);
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

// ---------- Rate limit ----------
if (!$security->checkRateLimit($_SERVER['REMOTE_ADDR'] ?? '')) {
    logMessage($logFile, 'RATE_LIMIT: request blocked');
    respond(429, ['error' => 'Trop de requetes, veuillez reessayer plus tard.']);
}

// ---------- Method check ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['error' => 'Methode non autorisee.']);
}

// ---------- Payload ----------
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
    respond(422, ['error' => 'Telephone invalide']);
}

foreach ($client as $key => $value) {
    if (is_string($value) && $security->detectAttack($value)) {
        logMessage($logFile, "ATTACK_DETECTED sur {$key}");
        respond(403, ['error' => 'Entree non autorisee detectee.']);
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
Telephone : {$client['telephone']}

--- Demande ---
Service : {$client['service']}
Vehicule : {$client['vehicule']}
Passagers : {$client['passagers']}
Date : {$client['date']}
Heure de debut : {$client['heureDebut']}
Duree : {$client['duree']}h
Lieu de prise en charge : {$client['lieuDepart']}
Destination : {$client['lieuArrivee']}
Options : {$optionsList}
Message complementaire :
{$client['message']}

--- Tarification estimee ---
Vehicule : {$prixVehicule} EUR
Options : {$prixOptions} EUR
Total : {$prixTotal} EUR

--- Metadonnees ---
Envoye le : {$payloadTime}
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
    respond(200, ['success' => true, 'message' => 'Demande envoyee']);
} catch (Exception $e) {
    $details = $e->getMessage();
    logMessage($logFile, 'EMAIL_ERROR: ' . $details);
    respond(500, ['error' => 'Erreur lors de lenvoi', 'details' => $details]);
}
