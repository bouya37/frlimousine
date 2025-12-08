<?php
// Configuration de sécurité
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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

// Fonction pour nettoyer les données d'entrée
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Récupération et nettoyage des données
$nom = cleanInput($_POST['nom'] ?? '');
$telephone = cleanInput($_POST['telephone'] ?? '');
$email = cleanInput($_POST['email'] ?? '');
$service = cleanInput($_POST['service'] ?? '');
$vehicule = cleanInput($_POST['vehicule'] ?? '');
$passagers = cleanInput($_POST['passagers'] ?? '');
$date = cleanInput($_POST['date'] ?? '');
$duree = cleanInput($_POST['duree'] ?? '');
$heureDebut = cleanInput($_POST['heure-debut'] ?? '');
$heureFin = cleanInput($_POST['heure-fin'] ?? '');
$lieuDepart = cleanInput($_POST['lieu-depart'] ?? '');
$lieuArrivee = cleanInput($_POST['lieu-arrivee'] ?? '');
$message = cleanInput($_POST['message'] ?? '');
$options = $_POST['options'] ?? [];

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

// Validation de l'email
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

// Configuration email
$to = 'contact@transvoyage.fr';
$subject = 'Nouvelle réservation - Beverly Limousine';
$headers = [
    'From' => 'noreply@beverlylimousine.fr',
    'Reply-To' => $email,
    'Content-Type' => 'text/html; charset=UTF-8',
    'MIME-Version' => '1.0'
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

// Log de la réservation pour debugging
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'nom' => $nom,
    'email' => $email,
    'telephone' => $telephone,
    'vehicule' => $vehicule,
    'date' => $date,
    'mail_sent' => $mailSent
];

// Sauvegarder dans un fichier de log
file_put_contents('reservation_log.txt', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);

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