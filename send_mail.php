<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Paris');

// Importation des classes PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chargement des dépendances et de la configuration
require __DIR__ . '/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/src/SMTP.php';
require __DIR__ . '/config.php'; // Votre fichier de configuration
require __DIR__ . '/security.php'; // Pour réutiliser les fonctions de sécurité

// Initialiser la sécurité pour les logs et la validation
$security = new FRLimousineSecurity($config);

/**
 * Crée un rapport d'erreur détaillé en cas d'échec de l'envoi d'e-mail.
 * @param PHPMailer $mailer L'instance de PHPMailer qui a échoué.
 * @param array $formData Les données du formulaire soumises par l'utilisateur.
 * @param FRLimousineSecurity $security L'instance de la classe de sécurité.
 */
function createErrorLog(PHPMailer $mailer, array $formData, FRLimousineSecurity $security) {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/error_report_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.txt';

    $reportContent = "============================================\n";
    $reportContent .= "RAPPORT D'ERREUR D'ENVOI D'EMAIL\n";
    $reportContent .= "============================================\n";
    $reportContent .= "Date et Heure: " . date('Y-m-d H:i:s') . "\n";
    $reportContent .= "IP du client: " . ($_SERVER['REMOTE_ADDR'] ?? 'Non disponible') . "\n";
    $reportContent .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Non disponible') . "\n";
    $reportContent .= "--------------------------------------------\n";
    $reportContent .= "ERREUR PHPMailer:\n";
    $reportContent .= "--------------------------------------------\n";
    $reportContent .= $mailer->ErrorInfo . "\n\n";
    $reportContent .= "--------------------------------------------\n";
    $reportContent .= "DONNÉES DU FORMULAIRE SOUMISES:\n";
    $reportContent .= "--------------------------------------------\n";

    foreach ($formData as $key => $value) {
        $displayValue = is_array($value) ? implode(', ', $value) : $value;
        $reportContent .= str_pad(ucfirst($key), 15) . ": " . $displayValue . "\n";
    }

    @file_put_contents($logFile, $reportContent);
    $security->logSecurityEvent("ERREUR_RAPPORT", $_SERVER['REMOTE_ADDR'] ?? 'unknown', "Rapport d'erreur créé : " . basename($logFile));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- CONFIGURATION DE L'E-MAIL ---
    $recipient_email = $config['email']['notification'];
    $subject = "Nouvelle demande de réservation via le site beverlylimousine.fr";

    // --- NETTOYAGE DES DONNÉES ---
    $formData = [
        'nom' => $security->sanitizeInput($_POST['nom'] ?? ''),
        'telephone' => $security->sanitizeInput($_POST['telephone'] ?? ''),
        'email' => $security->sanitizeInput($_POST['email'] ?? ''),
        'service' => $security->sanitizeInput($_POST['service'] ?? ''),
        'vehicule' => $security->sanitizeInput($_POST['vehicule'] ?? ''),
        'passagers' => $security->sanitizeInput($_POST['passagers'] ?? ''),
        'date' => $security->sanitizeInput($_POST['date'] ?? ''),
        'heure-debut' => $security->sanitizeInput($_POST['heure-debut'] ?? ''),
        'duree' => $security->sanitizeInput($_POST['duree'] ?? ''),
        'lieu-depart' => $security->sanitizeInput($_POST['lieu-depart'] ?? ''),
        'lieu-arrivee' => $security->sanitizeInput($_POST['lieu-arrivee'] ?? ''),
        'options' => isset($_POST['options']) && is_array($_POST['options']) ? $security->sanitizeInput($_POST['options']) : [],
        'message' => $security->sanitizeInput($_POST['message'] ?? 'Aucun message.')
    ];

    // Récupération et nettoyage de chaque champ du formulaire
    // Validation simple
    if (empty($formData['nom']) || empty($formData['telephone']) || !$security->validateEmail($formData['email'])) {
        $security->logSecurityEvent("VALIDATION_ECHOUEE", $_SERVER['REMOTE_ADDR'] ?? 'unknown', "Champs invalides dans le formulaire de contact.");
        header("Location: /index.html?status=error#contact");
        exit();
    }
    
    // --- CONSTRUCTION DE L'E-MAIL ---
    $email_body = "Une nouvelle demande de réservation a été soumise depuis beverlylimousine.fr :\n\n";
    $email_body .= "Nom: {$formData['nom']}\n";
    $email_body .= "Téléphone: {$formData['telephone']}\n";
    $email_body .= "Email: {$formData['email']}\n\n";
    $email_body .= "Service souhaité: {$formData['service']}\n";
    $email_body .= "Véhicule: {$formData['vehicule']}\n";
    $email_body .= "Nombre de passagers: {$formData['passagers']}\n\n";
    $email_body .= "Date: {$formData['date']}\n";
    $email_body .= "Heure de début: {$formData['heure-debut']}\n";
    $email_body .= "Durée: {$formData['duree']} heure(s)\n\n";
    $email_body .= "Options: " . (count($formData['options']) > 0 ? implode(', ', $formData['options']) : 'Aucune') . "\n";
    $email_body .= "Lieu de départ: {$formData['lieu-depart']}\n";
    $email_body .= "Lieu d'arrivée: {$formData['lieu-arrivee']}\n\n";
    $email_body .= "Message complémentaire:\n{$formData['message']}\n";

    $mailer = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP à partir de votre fichier config.php
        $mailer->isSMTP();
        $mailer->Host = $config['smtp']['host'];
        $mailer->SMTPAuth = $config['smtp']['auth'];
        $mailer->Username = $config['smtp']['username'];
        $mailer->Password = $config['smtp']['password'];
        $mailer->SMTPSecure = $config['smtp']['encryption'];
        $mailer->Port = $config['smtp']['port'];
        $mailer->CharSet = 'UTF-8';

        // Destinataires
        $mailer->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
        $mailer->addAddress($recipient_email); 
        $mailer->addReplyTo($formData['email'], $formData['nom']); // Pour que "Répondre" aille au client

        // Contenu
        $mailer->isHTML(false); // E-mail en format texte
        $mailer->Subject = $subject;
        $mailer->Body    = $email_body;

        $mailer->send();
        header("Location: /index.html?status=success#contact");
    } catch (Exception $e) {
        // En cas d'erreur, on crée un rapport détaillé et on redirige
        createErrorLog($mailer, $formData, $security);
        header("Location: /index.html?status=error#contact");
    }
} else {
    // Rediriger si le script est accédé directement
    header("Location: /index.html");
}
?>