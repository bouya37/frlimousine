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


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- CONFIGURATION DE L'E-MAIL ---
    $recipient_email = $config['email']['notification'];
    $subject = "Nouvelle demande de réservation via le site beverlylimousine.fr";

    // --- NETTOYAGE DES DONNÉES ---
    // Récupération et nettoyage de chaque champ du formulaire
    $nom = $security->sanitizeInput($_POST['nom'] ?? '');
    $telephone = $security->sanitizeInput($_POST['telephone'] ?? '');
    $email = $security->sanitizeInput($_POST['email'] ?? '');
    $service = $security->sanitizeInput($_POST['service'] ?? '');
    $vehicule = $security->sanitizeInput($_POST['vehicule'] ?? '');
    $passagers = $security->sanitizeInput($_POST['passagers'] ?? '');
    $date = $security->sanitizeInput($_POST['date'] ?? '');
    $heure_debut = $security->sanitizeInput($_POST['heure-debut'] ?? '');
    $duree = $security->sanitizeInput($_POST['duree'] ?? '');
    $lieu_depart = $security->sanitizeInput($_POST['lieu-depart'] ?? '');
    $lieu_arrivee = $security->sanitizeInput($_POST['lieu-arrivee'] ?? '');
    $options = isset($_POST['options']) && is_array($_POST['options']) ? $security->sanitizeInput($_POST['options']) : [];
    $message = $security->sanitizeInput($_POST['message'] ?? 'Aucun message.');

    // Validation simple
    if (empty($nom) || empty($telephone) || !$security->validateEmail($email)) {
        $security->logSecurityEvent("VALIDATION_ECHOUEE", $_SERVER['REMOTE_ADDR'] ?? 'unknown', "Champs invalides dans le formulaire de contact.");
        header("Location: /index.html?status=error#contact");
        exit();
    }
    
    // --- CONSTRUCTION DE L'E-MAIL ---
    $email_body = "Une nouvelle demande de réservation a été soumise depuis beverlylimousine.fr :\n\n";
    $email_body .= "Nom: $nom\n";
    $email_body .= "Téléphone: $telephone\n";
    $email_body .= "Email: $email\n\n";
    $email_body .= "Service souhaité: $service\n";
    $email_body .= "Véhicule: $vehicule\n";
    $email_body .= "Nombre de passagers: $passagers\n\n";
    $email_body .= "Date: $date\n";
    $email_body .= "Heure de début: $heure_debut\n";
    $email_body .= "Durée: $duree heure(s)\n\n";
    $email_body .= "Options: " . (count($options) > 0 ? implode(', ', $options) : 'Aucune') . "\n";
    $email_body .= "Lieu de départ: $lieu_depart\n";
    $email_body .= "Lieu d'arrivée: $lieu_arrivee\n\n";
    $email_body .= "Message complémentaire:\n$message\n";

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
        $mailer->addReplyTo($email, $nom); // Pour que "Répondre" aille au client

        // Contenu
        $mailer->isHTML(false); // E-mail en format texte
        $mailer->Subject = $subject;
        $mailer->Body    = $email_body;

        $mailer->send();
        header("Location: /index.html?status=success#contact");
    } catch (Exception $e) {
        // En cas d'erreur, on logue l'erreur et on redirige
        $security->logSecurityEvent("ERREUR_EMAIL", $_SERVER['REMOTE_ADDR'] ?? 'unknown', "Erreur PHPMailer: {$mailer->ErrorInfo}");
        header("Location: /index.html?status=error#contact");
    }
} else {
    // Rediriger si le script est accédé directement
    header("Location: /index.html");
}
?>