<?php
// Configuration de sécurité renforcée
header('Content-Type: application/json; charset=utf-8');

// CORS restreint uniquement au domaine du site (sécurité)
$allowedOrigins = [
    'https://beverlylimousine.fr',
    'https://www.beverlylimousine.fr',
    'http://localhost', // Pour développement local uniquement
    'http://127.0.0.1'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    // Si pas d'origine, vérifier le Referer
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $isAllowed = false;
    foreach ($allowedOrigins as $allowed) {
        if (strpos($referer, $allowed) === 0) {
            $isAllowed = true;
            break;
        }
    }
    if (!$isAllowed) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
        exit();
    }
}

header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Vérifier que la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Rate limiting côté serveur (protection contre spam)
session_start();
$rateLimitFile = sys_get_temp_dir() . '/beverly_limousine_rate_limit_' . session_id() . '.json';
$rateLimitData = file_exists($rateLimitFile) ? json_decode(file_get_contents($rateLimitFile), true) : ['count' => 0, 'reset_time' => time() + 3600];

if (time() > $rateLimitData['reset_time']) {
    $rateLimitData = ['count' => 0, 'reset_time' => time() + 3600];
}

if ($rateLimitData['count'] >= 5) { // Maximum 5 soumissions par heure
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Trop de soumissions. Veuillez attendre quelques minutes.']);
    exit();
}

$rateLimitData['count']++;
file_put_contents($rateLimitFile, json_encode($rateLimitData), LOCK_EX);

// Vérification CSRF (protection contre attaques Cross-Site Request Forgery)
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide. Veuillez recharger la page.']);
    exit();
}

// Fonction pour nettoyer les données d'entrée
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fonction pour valider strictement l'email (prévention injection email header)
function validateEmailHeader($email) {
    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    // Vérifier qu'il n'y a pas de caractères dangereux pour les headers email
    if (preg_match('/[\r\n]/', $email)) {
        return false;
    }
    // Limiter la longueur
    if (strlen($email) > 254) {
        return false;
    }
    return true;
}

// Limites de longueur pour chaque champ (protection DoS)
$fieldLimits = [
    'nom' => 100,
    'telephone' => 20,
    'email' => 254,
    'service' => 50,
    'vehicule' => 50,
    'passagers' => 2,
    'date' => 10,
    'duree' => 2,
    'heure-debut' => 5,
    'heure-fin' => 5,
    'lieu-depart' => 500,
    'lieu-arrivee' => 500,
    'message' => 2000
];

// Récupération, validation de longueur et nettoyage des données
$nom = cleanInput($_POST['nom'] ?? '');
if (strlen($nom) > $fieldLimits['nom']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le nom est trop long']);
    exit();
}

$telephone = cleanInput($_POST['telephone'] ?? '');
if (strlen($telephone) > $fieldLimits['telephone']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le numéro de téléphone est trop long']);
    exit();
}

$email = cleanInput($_POST['email'] ?? '');
if (strlen($email) > $fieldLimits['email'] || !validateEmailHeader($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Adresse email invalide ou trop longue']);
    exit();
}

$service = cleanInput($_POST['service'] ?? '');
if (strlen($service) > $fieldLimits['service']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le service est invalide']);
    exit();
}

$vehicule = cleanInput($_POST['vehicule'] ?? '');
if (strlen($vehicule) > $fieldLimits['vehicule']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le véhicule est invalide']);
    exit();
}

$passagers = cleanInput($_POST['passagers'] ?? '');
if (strlen($passagers) > $fieldLimits['passagers'] || !is_numeric($passagers) || (int)$passagers < 1 || (int)$passagers > 20) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre de passagers invalide']);
    exit();
}

$date = cleanInput($_POST['date'] ?? '');
if (strlen($date) > $fieldLimits['date']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La date est invalide']);
    exit();
}

$duree = cleanInput($_POST['duree'] ?? '');
if (strlen($duree) > $fieldLimits['duree'] || !is_numeric($duree) || (int)$duree < 1 || (int)$duree > 24) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La durée est invalide']);
    exit();
}

$heureDebut = cleanInput($_POST['heure-debut'] ?? '');
if (strlen($heureDebut) > $fieldLimits['heure-debut']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "L'heure de début est invalide"]);
    exit();
}

$heureFin = cleanInput($_POST['heure-fin'] ?? '');
if (strlen($heureFin) > $fieldLimits['heure-fin']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "L'heure de fin est invalide"]);
    exit();
}

$lieuDepart = cleanInput($_POST['lieu-depart'] ?? '');
if (strlen($lieuDepart) > $fieldLimits['lieu-depart']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le lieu de départ est trop long']);
    exit();
}

$lieuArrivee = cleanInput($_POST['lieu-arrivee'] ?? '');
if (strlen($lieuArrivee) > $fieldLimits['lieu-arrivee']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le lieu d\'arrivée est trop long']);
    exit();
}

$message = cleanInput($_POST['message'] ?? '');
if (strlen($message) > $fieldLimits['message']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le message est trop long']);
    exit();
}

$options = $_POST['options'] ?? [];
if (is_array($options)) {
    foreach ($options as $key => $option) {
        $options[$key] = cleanInput($option);
        if (strlen($options[$key]) > 100) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Une option est invalide']);
            exit();
        }
    }
}

// Validation des champs obligatoires
$requiredFields = [
    'nom' => $nom,
    'telephone' => $telephone,
    'email' => $email,
    'vehicule' => $vehicule,
    'passagers' => $passagers,
    'date' => $date,
    'duree' => $duree,
    'heure-debut' => $heureDebut,
    'lieu-depart' => $lieuDepart,
    'lieu-arrivee' => $lieuArrivee
];

foreach ($requiredFields as $field => $value) {
    if (empty($value)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Le champ $field est obligatoire"]);
        exit();
    }
}

// Validation supplémentaire de l'email (déjà fait plus haut mais double vérification)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Adresse email invalide']);
    exit();
}

// Mapping des véhicules pour affichage
$vehiculesNames = [
    'excalibur' => 'Excalibur',
    'hummer-limousine' => 'Hummer Limousine',
    'mercedes-viano' => 'Mercedes Classe V',
    'mustang-rouge' => 'Mustang Rouge',
    'mustang-bleu' => 'Mustang Bleu'
];

$servicesNames = [
    'mariage' => 'Mariage',
    'evenement-pro' => 'Événement d\'entreprise',
    'transfert-aeroport' => 'Transfert aéroport',
    'soiree-privee' => 'Soirée privée',
    'clip-shooting' => 'Clip/Shooting',
    'autre' => 'Autre'
];

// Configuration email avec validation stricte de l'email Reply-To
$to = 'contact@transvoyage.fr';
$subject = 'Nouvelle réservation - Beverly Limousine';

// Validation stricte de l'email pour Reply-To (prévention injection header)
$replyTo = filter_var($email, FILTER_SANITIZE_EMAIL);
if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
    $replyTo = 'contact@transvoyage.fr'; // Email de secours si invalide
}

$headers = [
    'From' => 'noreply@beverlylimousine.fr',
    'Reply-To' => $replyTo,
    'Content-Type' => 'text/html; charset=UTF-8',
    'MIME-Version' => '1.0',
    'X-Mailer' => 'PHP/' . phpversion()
];

// Formatage de la date
$dateFormatee = date('d/m/Y', strtotime($date));

// Options sélectionnées
$optionsTexte = is_array($options) ? implode(', ', array_map('cleanInput', $options)) : cleanInput($options);

// Corps de l'email
$emailBody = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Nouvelle réservation Beverly Limousine</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2c2c2c; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; }
        .field { margin: 10px 0; padding: 10px; background: white; border-radius: 5px; }
        .label { font-weight: bold; color: #2c2c2c; }
        .value { margin-left: 10px; }
        .urgent { background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🚗 Nouvelle Réservation</h1>
            <p>Beverly Limousine</p>
        </div>
        <div class='content'>
            <h2>Détails de la réservation</h2>
            
            <div class='field'>
                <span class='label'>Nom complet :</span>
                <span class='value'>" . htmlspecialchars($nom) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Téléphone :</span>
                <span class='value'>" . htmlspecialchars($telephone) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Email :</span>
                <span class='value'>" . htmlspecialchars($email) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Service :</span>
                <span class='value'>" . htmlspecialchars($servicesNames[$service] ?? $service) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Véhicule souhaité :</span>
                <span class='value'>" . htmlspecialchars($vehiculesNames[$vehicule] ?? $vehicule) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Nombre de passagers :</span>
                <span class='value'>" . htmlspecialchars($passagers) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Date :</span>
                <span class='value'>" . htmlspecialchars($dateFormatee) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Heure de début :</span>
                <span class='value'>" . htmlspecialchars($heureDebut) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Heure de fin :</span>
                <span class='value'>" . htmlspecialchars($heureFin) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Durée :</span>
                <span class='value'>" . htmlspecialchars($duree) . " heure(s)</span>
            </div>
            
            <div class='field'>
                <span class='label'>Lieu de prise en charge :</span>
                <span class='value'>" . htmlspecialchars($lieuDepart) . "</span>
            </div>
            
            <div class='field'>
                <span class='label'>Lieu de destination :</span>
                <span class='value'>" . htmlspecialchars($lieuArrivee) . "</span>
            </div>
            
            " . (!empty($optionsTexte) ? "
            <div class='field'>
                <span class='label'>Options supplémentaires :</span>
                <span class='value'>" . htmlspecialchars($optionsTexte) . "</span>
            </div>
            " : "") . "
            
            " . (!empty($message) ? "
            <div class='field'>
                <span class='label'>Message :</span>
                <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
            </div>
            " : "") . "
            
            <div class='urgent'>
                <strong>⚡ Action requise :</strong><br>
                Contacté le client sous 2 heures pour confirmer la disponibilité.
                <br>Téléphone : " . htmlspecialchars($telephone) . "
            </div>
            
            <p style='text-align: center; margin-top: 30px; color: #666;'>
                <small>Email généré automatiquement le " . date('d/m/Y à H:i') . "</small>
            </p>
        </div>
    </div>
</body>
</html>
";

// Envoi de l'email
$mailSent = mail($to, $subject, $emailBody, implode("\r\n", array_map(function($k, $v) { return "$k: $v"; }, array_keys($headers), $headers)));

// Log de la réservation pour debugging (hors racine web pour sécurité)
// Note: Les données personnelles sont anonymisées dans le log pour RGPD
$logDir = dirname(__FILE__) . '/../logs/'; // Répertoire hors racine web si possible
if (!is_dir($logDir)) {
    $logDir = sys_get_temp_dir() . '/'; // Fallback vers temp si logs/ n'existe pas
}

$logFile = $logDir . 'reservation_log_' . date('Y-m') . '.log'; // Log mensuel

$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'nom_hash' => substr(hash('sha256', $nom), 0, 16), // Hash partiel pour anonymisation RGPD
    'email_hash' => substr(hash('sha256', $email), 0, 16), // Hash partiel pour anonymisation RGPD
    'telephone_hash' => substr(hash('sha256', $telephone), 0, 16), // Hash partiel pour anonymisation RGPD
    'vehicule' => $vehicule,
    'date' => $date,
    'mail_sent' => $mailSent,
    'ip_hash' => substr(hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 16) // IP anonymisée
];

// Sauvegarder dans un fichier de log sécurisé
@file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);

// Réponse
if ($mailSent) {
    echo json_encode([
        'success' => true, 
        'message' => 'Votre réservation a été envoyée avec succès. Nous vous contacterons sous 2 heures.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur lors de l\'envoi. Veuillez réessayer ou nous contacter directement.'
    ]);
}
?>