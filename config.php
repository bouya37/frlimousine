<?php
mb_internal_encoding('UTF-8');
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
    'smtp' => [
        // Configuration SMTP o2switch selon les paramètres officiels
        // Serveur sortant : dolphin.o2switch.net
        // SMTP Port: 465 avec SSL
        'host' => 'dolphin.o2switch.net',
        'port' => 465,
        'encryption' => 'ssl', // SSL/TLS selon les paramètres o2switch
        'auth' => true,
        'username' => 'contact@transvoyage.fr',
        'password' => '19Massenet!!',
        'from_email' => 'contact@transvoyage.fr',
        'from_name' => 'Beverly Limousine',
        'timeout' => 20,
    ],
    // Mode debug : true pour activer les logs détaillés, false pour production
    'debug' => true,
    'logging' => [
        'path' => __DIR__ . '/logs/reservation-handler.log',
    ],
    'security' => [
        // Limite par minute pour receive-pdf.php
        'rate_limit' => 20,
        // Limites utilisées par security.php
        'rate_limit_minute' => 20,
        'rate_limit_hour' => 100,
    ],
];
