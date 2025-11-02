<?php


ini_set('memory_limit', '128M');
ini_set('max_execution_time', '30');
ini_set('display_errors', '0'); // Ne jamais afficher les erreurs en production
ini_set('log_errors', '1');
date_default_timezone_set('Europe/Paris');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Type: application/json; charset=UTF-8');

class SecurityHelper {
    private $logFile = 'pdfs/security.log';
    private $rateLimitFileDir = 'pdfs/ratelimit/';
    private $maxRequestsPerMinute;

    public function __construct($config = null) {
        global $config;
        if (!is_dir($this->rateLimitFileDir)) {
            mkdir($this->rateLimitFileDir, 0755, true);
        }
        if ($config && isset($config['security']['rate_limit'])) {
            $this->maxRequestsPerMinute = $config['security']['rate_limit'];
        }
    }

    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    public function checkRateLimit($ip) {
        $file = $this->rateLimitFileDir . md5($ip) . '.json';
        $now = time();
        $limitData = ['timestamp' => $now, 'count' => 1];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && ($now - $data['timestamp']) < 60) {
                if ($data['count'] >= $this->maxRequestsPerMinute) {
                    $this->log("RATE_LIMIT: IP $ip bloquée (trop de requêtes).");
                    http_response_code(429); // Too Many Requests
                    echo json_encode(['error' => 'Trop de requêtes.']);
                    return false;
                }
                $limitData['count'] = $data['count'] + 1;
            }
        }
        file_put_contents($file, json_encode($limitData));
        return true;
    }

    public function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function validatePhone($phone) {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);

        // Validation des numéros français
        $frenchPattern = '/^(?:\+|00)?33[1-9](?:[0-9]{2}){4}$|^0[1-9](?:[0-9]{2}){4}$/';

        // Validation des numéros internationaux (format E.164)
        $internationalPattern = '/^\+[1-9]\d{1,14}$/';

        // Validation des numéros européens courants
        $europeanPattern = '/^\+?[1-9]\d{1,3}?[1-9]\d{6,13}$/';

        return preg_match($frenchPattern, $cleanPhone) ||
               preg_match($internationalPattern, $cleanPhone) ||
               preg_match($europeanPattern, $cleanPhone);
    }

    public function formatPhone($phone) {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);

        // Format français : 06 12 34 56 78
        if (preg_match('/^0[1-9]([0-9]{2})([0-9]{2})([0-9]{2})$/', $cleanPhone, $matches)) {
            return $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . substr($matches[0], -2);
        }

        // Format international : +33 6 12 34 56 78
        if (preg_match('/^\+33([1-9])([0-9]{2})([0-9]{2})([0-9]{2})$/', $cleanPhone, $matches)) {
            return '+33 ' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . substr($matches[0], -2);
        }

        // Retourner le numéro nettoyé si format non reconnu
        return $cleanPhone;
    }

    public function detectAttack($input) {
        $patterns = [
            '/<script/i', '/javascript:/i', '/onclick=/i', '/onerror=/i', // XSS
            '/SELECT.*FROM/i', '/UNION.*SELECT/i', '/--/i', // SQL Injection
            '/\.\.\//', '/\.\.\\\//', // Path Traversal
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->log("ATTACK_DETECTED: Pattern '$pattern' trouvé dans l'input.");
                return true;
            }
        }
        return false;
    }
}

$security = new SecurityHelper($config);


$ip = $_SERVER['REMOTE_ADDR'];
if (!$security->checkRateLimit($ip)) {
    exit; // Le message d'erreur a déjà été envoyé par checkRateLimit
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit;
}
$config = require_once 'config.php';
$uploadDir = $config['upload']['directory'];
$emailNotification = $config['email']['notification'];
$logFile = 'pdfs/reception.log';
$domainName = $config['domain']['name'];

if (!file_exists($uploadDir)) {
    if (!@mkdir($uploadDir, 0755, true)) {
        $security->log("ERREUR: Impossible de créer le répertoire $uploadDir");
        http_response_code(500);
        echo json_encode(['error' => 'Erreur création répertoire']);
        exit;
    }
    $security->log("Répertoire $uploadDir créé avec succès");
}

$input = file_get_contents('php://input');
if (strlen($input) > $config['upload']['max_size']) {
    $security->log("BLOCAGE: Payload trop volumineux de $ip");
    http_response_code(413);
    echo json_encode(['error' => 'Données trop volumineuses']);
    exit;
}

$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($data['client']) || !is_array($data['client'])) {
    $security->log("ERREUR: Structure JSON invalide reçue de $ip");
    http_response_code(400);
    echo json_encode(['error' => 'Structure des données invalide']);
    exit;
}

$requiredFields = ['nom', 'email', 'telephone', 'service', 'vehicule', 'passagers', 'date', 'duree', 'prix'];
foreach ($requiredFields as $field) {
    if (empty($data['client'][$field])) {
        $security->log("ERREUR: Champ obligatoire manquant: $field pour IP $ip");
        http_response_code(400);
        echo json_encode(['error' => "Champ obligatoire manquant: $field"]);
        exit;
    }
}

$client = [];
foreach ($data['client'] as $key => $value) {
    if ($security->detectAttack($value)) {
        $security->log("BLOCAGE: Tentative d'attaque détectée dans le champ '$key' pour IP $ip");
        http_response_code(403);
        echo json_encode(['error' => 'Données suspectes détectées.']);
        exit;
    }
    $client[$key] = $security->sanitize($value);
}

if (!$security->validateEmail($client['email'])) {
    $security->log("ERREUR: Format email invalide: " . $client['email'] . " pour IP $ip");
    http_response_code(400);
    echo json_encode(['error' => 'Format email invalide']);
    exit;
}

if (!$security->validatePhone($client['telephone'])) {
    $security->log("ERREUR: Format téléphone invalide: " . $client['telephone'] . " pour IP $ip");
    http_response_code(400);
    echo json_encode(['error' => 'Format téléphone invalide (français ou international)']);
    exit;
}

$formattedPhone = $security->formatPhone($client['telephone']);

$filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $data['filename']);
$filepath = $uploadDir . $filename;

if (!file_put_contents($filepath, $data['content'])) {
    $security->log("ERREUR: Impossible de sauvegarder le PDF: $filename");
    http_response_code(500);
    echo json_encode(['error' => 'Erreur sauvegarde PDF']);
    exit;
}

$infoFile = $uploadDir . str_replace('.html', '_info.json', $filename);
file_put_contents($infoFile, json_encode($client, JSON_PRETTY_PRINT));

$subject = '🚗 Nouveau devis PDF - ' . $client['nom'];
$message = "Bonjour FRLimousine,

Un nouveau devis a été généré automatiquement sur votre site :

📋 INFORMATIONS CLIENT
Nom: {$client['nom']}
Email: {$client['email']}
Téléphone: {$formattedPhone}

🚗 DÉTAILS DE RÉSERVATION
Service: {$client['service']}
Véhicule: {$client['vehicule']}
Passagers: {$client['passagers']}
Date: {$client['date']}
Durée: {$client['duree']}
Prix: {$client['prix']}

📄 FICHIER PDF
Emplacement: $filepath
Nom du fichier: $filename

⏰ Reçu le: " . date('d/m/Y à H:i:s') . "

Cordialement,
Système automatique FRLimousine";

$headers = 'From: ' . $config['email']['from'] . "\r\n" .
           'Reply-To: ' . $client['email'] . "\r\n" .
           'X-Mailer: PHP/' . phpversion() . "\r\n" .
           'Content-Type: text/plain; charset=UTF-8' . "\r\n" .
           'Return-Path: ' . $config['email']['from'];

if (mail($emailNotification, $subject, $message, $headers)) {
    $security->log("EMAIL_SENT: Email de notification envoyé pour: " . $client['nom']);
} else {
    $security->log("EMAIL_ERROR: Impossible d'envoyer l'email de notification pour " . $client['nom']);
}

echo json_encode([
    'success' => true,
    'message' => 'Devis reçu avec succès',
    'filename' => $filename,
    'filepath' => $filepath,
    'server' => $_SERVER['SERVER_NAME'],
    'timestamp' => date('Y-m-d H:i:s')
]);

$security->log("SUCCESS: Devis traité avec succès pour: " . $client['nom']);
?>