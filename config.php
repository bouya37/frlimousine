<?php
/**
 * config.php - Configuration sécurisée pour FRLimousine
 * Ce fichier contient les informations sensibles et doit être protégé
 */

// Configuration des notifications par email
$config = [
    'email' => [
        'notification' => 'proayoubfarkh@gmail.com', // Email de notification
        'from' => 'noreply@frlimousine.ovh', // Email expéditeur
        'reply_to' => 'contact@frlimousine.ovh' // Email de réponse
    ],
    'domain' => [
        'name' => 'frlimousine.ovh', // Nom de domaine
        'url' => 'https://frlimousine.ovh' // URL complète du site
    ],
    'upload' => [
        'directory' => 'pdfs/', // Répertoire d'upload
        'max_size' => 1048576 // Taille maximale en octets (1MB)
    ],
    'security' => [
        'rate_limit' => 20, // Requêtes par minute maximum
        'log_retention' => 30 // Jours de rétention des logs
    ]
];

// Empêcher l'accès direct à ce fichier
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    die('Accès interdit');
}

return $config;
?>