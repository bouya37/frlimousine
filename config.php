<?php
return [
    'domain' => [
        'name' => 'beverlylimousine.fr',
    ],
    'upload' => [
        // Dossier où sont enregistrés les devis générés (doit être inscriptible par PHP)
        'directory' => __DIR__ . '/pdfs/',
        // Limite de taille pour la payload JSON envoyée par le formulaire (en octets)
        'max_size' => 500000,
    ],
    'email' => [
        // Adresse qui recevra les notifications de réservation
        'notification' => 'contact@transvoyage.fr',
        // Adresse utilisée en expéditeur
        'from' => 'contact@transvoyage.fr',
    ],
    'security' => [
        // Limite par minute pour receive-pdf.php
        'rate_limit' => 20,
        // Limites utilisées par security.php
        'rate_limit_minute' => 20,
        'rate_limit_hour' => 100,
    ],
];
