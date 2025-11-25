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

// Inclure PHPMailer
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';
require __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

function sendEmailWithSMTP(array $config, string $to, string $subject, string $message, array $headers = []): array {
    $mail = new PHPMailer(true);
    
    try {
        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = $config['smtp']['auth'];
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['encryption'];
        $mail->Port = $config['smtp']['port'];
        
        // Configuration d'encodage
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = '8bit';
        
        // Destinataires
        $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
        $mail->addAddress($to);
        
        // Contenu
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        // Headers supplémentaires
        foreach ($headers as $header) {
            if (strpos($header, 'Reply-To:') === 0) {
                $replyTo = trim(str_replace('Reply-To:', '', $header));
                $mail->addReplyTo(trim($replyTo));
            }
        }
        
        // Mode debug si activé
        if ($config['debug'] ?? false) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
        
        // Envoi
        $result = $mail->send();
        
        return [
            'success' => $result,
            'message' => 'Email envoyé avec succès via SMTP',
            'message_id' => $mail->getLastMessageID()
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erreur SMTP: ' . $mail->ErrorInfo,
            'exception' => $e->getMessage()
        ];
    }
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
    'Reply-To: ' . clean($client['email'])
];

// Logs détaillés avant envoi
write_log('=== DEBUT ENVOI EMAIL SMTP ===');
write_log('TO: ' . $config['email']['notification']);
write_log('FROM: ' . $config['smtp']['from_email']);
write_log('SMTP_HOST: ' . $config['smtp']['host'] . ':' . $config['smtp']['port']);
write_log('SUBJECT: ' . $subject);
write_log('PHP_VERSION: ' . phpversion());
write_log('CLIENT_EMAIL: ' . $client['email']);

// Tentative d'envoi avec SMTP
$result = sendEmailWithSMTP($config, $config['email']['notification'], $subject, $message, $headers);

// Logs détaillés après envoi
write_log('SMTP_RESULT: ' . ($result['success'] ? 'SUCCESS' : 'FAILED'));
write_log('SMTP_MESSAGE: ' . $result['message']);
if (isset($result['message_id'])) {
    write_log('MESSAGE_ID: ' . $result['message_id']);
}
if (!$result['success'] && isset($result['exception'])) {
    write_log('SMTP_EXCEPTION: ' . $result['exception']);
}
write_log('=== FIN ENVOI EMAIL ===');

if (!$result['success']) {
    write_log('ENVOI_ECHOUE_SMTP - Details: ' . print_r($result, true));
    
    // Réponse d'erreur améliorée pour le debug
    $errorResponse = [
        'error' => 'Impossible d\'envoyer le mail via SMTP',
        'debug_info' => [
            'smtp_host' => $config['smtp']['host'] . ':' . $config['smtp']['port'],
            'smtp_auth' => $config['smtp']['auth'] ? 'activé' : 'désactivé',
            'smtp_error' => $result['message'],
            'php_version' => phpversion(),
            'client_email' => $client['email']
        ]
    ];
    
    http_response_code(500);
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => true]);
?>
