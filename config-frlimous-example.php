<?php
/**
 * Configuration mise à jour pour frlimous.odns.fr
 * Remplacez cette configuration dans config.php
 */

// Configuration pour frlimous.odns.fr
// ⚠️ REMPLACEZ CES VALEURS PAR VOS VRAIES INFORMATIONS
$frlimous_config = [
    'domain' => [
        'name' => 'beverlylimousine.fr',
    ],
    'upload' => [
        'directory' => __DIR__ . '/pdfs/',
        'max_size' => 500000,
    ],
    'email' => [
        // Adresse qui recevra les notifications de réservation
        // ⚠️ REMPLACEZ par votre vraie adresse frlimous
        'notification' => 'VOTRE_EMAIL@frlimous.odns.fr',
        // Adresse utilisée en expéditeur
        // ⚠️ REMPLACEZ par votre vraie adresse frlimous
        'from' => 'VOTRE_EMAIL@frlimous.odns.fr',
    ],
    'smtp' => [
        // Configuration SMTP pour frlimous.odns.fr
        // ⚠️ CES VALEURS DOIVENT ÊTRE DÉTERMINÉES PAR LES TESTS
        'host' => 'mail.frlimous.odns.fr', // À déterminer via smtp-detector-frlimous.php
        'port' => 587, // Port le plus courant, peut être 465 ou 25
        'encryption' => 'tls', // 'tls', 'ssl', ou 'none'
        'auth' => true,
        'username' => 'VOTRE_EMAIL@frlimous.odns.fr', // ⚠️ VOTRE VRAI EMAIL
        'password' => 'VOTRE_MOT_DE_PASSE', // ⚠️ VOTRE VRAI MOT DE PASSE
        'from_email' => 'VOTRE_EMAIL@frlimous.odns.fr', // ⚠️ VOTRE VRAI EMAIL
        'from_name' => 'Beverly Limousine',
        'timeout' => 20,
    ],
    'debug' => true,
    'logging' => [
        'path' => __DIR__ . '/logs/reservation-handler.log',
    ],
    'security' => [
        'rate_limit' => 20,
        'rate_limit_minute' => 20,
        'rate_limit_hour' => 100,
    ],
];

echo "Configuration pour frlimous.odns.fr\n";
echo "=================================\n\n";

echo "⚠️  ATTENTION: Cette configuration est un EXEMPLE\n";
echo "Vous devez la personnaliser avec vos vraies informations:\n\n";

echo "1. Lancez smtp-detector-frlimous.php pour trouver les bons paramètres SMTP\n\n";

echo "2. Remplacez dans config.php:\n";
echo "   - VOTRE_EMAIL@frlimous.odns.fr par votre vraie adresse email\n";
echo "   - VOTRE_MOT_DE_PASSE par votre vrai mot de passe\n";
echo "   - Les paramètres SMTP (host, port, encryption) trouvés par le test\n\n";

echo "3. Exemple de config.php mise à jour:\n";
echo "```php\n";
echo "<?php\n";
echo "return [\n";
echo "    'smtp' => [\n";
echo "        'host' => 'mail.frlimous.odns.fr', // Valeur trouvée par le test\n";
echo "        'port' => 587, // Port trouvé par le test\n";
echo "        'encryption' => 'tls', // Chiffrement trouvé par le test\n";
echo "        'username' => 'VOTRE_EMAIL@frlimous.odns.fr',\n";
echo "        'password' => 'VOTRE_MOT_DE_PASSE',\n";
echo "        'from_email' => 'VOTRE_EMAIL@frlimous.odns.fr',\n";
echo "        // ... autres paramètres\n";
echo "    ],\n";
echo "    // ... reste de la config\n";
echo "];\n";
echo "?>```\n\n";

echo "4. Testez avec le fichier smtp-test-enhanced.php une fois la config mise à jour\n";
?>