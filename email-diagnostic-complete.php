<?php
/**
 * Diagnostic complet pour identifier les problèmes d'envoi d'email
 * Ce script teste tous les composants nécessaires pour l'envoi d'emails
 */

declare(strict_types=1);

date_default_timezone_set('Europe/Paris');

echo "<h1>Diagnostic Email - Beverly Limousine</h1>";
echo "<pre>";

// Test 1: Vérification des dépendances PHPMailer
echo "=== TEST 1: DÉPENDANCES PHPMailer ===\n";

$phpmailerPaths = [
    __DIR__ . '/vendor/phpmailer/src/Exception.php',
    __DIR__ . '/vendor/phpmailer/src/PHPMailer.php',
    __DIR__ . '/vendor/phpmailer/src/SMTP.php'
];

foreach ($phpmailerPaths as $path) {
    if (file_exists($path)) {
        echo "✓ Fichier trouvé: " . basename($path) . "\n";
    } else {
        echo "✗ FICHIRE MANQUANT: " . $path . "\n";
    }
}

echo "\n";

// Test 2: Chargement de la configuration
echo "=== TEST 2: CONFIGURATION ===\n";

try {
    $config = require __DIR__ . '/config.php';
    echo "✓ Configuration chargée avec succès\n";
    
    // Vérification des paramètres email
    if (isset($config['smtp'])) {
        echo "✓ Configuration SMTP présente\n";
        echo "  - Host: " . ($config['smtp']['host'] ?? 'NON DÉFINI') . "\n";
        echo "  - Port: " . ($config['smtp']['port'] ?? 'NON DÉFINI') . "\n";
        echo "  - Username: " . ($config['smtp']['username'] ?? 'NON DÉFINI') . "\n";
        echo "  - Encryption: " . ($config['smtp']['encryption'] ?? 'NON DÉFINI') . "\n";
        echo "  - From Email: " . ($config['smtp']['from_email'] ?? 'NON DÉFINI') . "\n";
    } else {
        echo "✗ Configuration SMTP manquante\n";
    }
    
    if (isset($config['email'])) {
        echo "✓ Configuration email présente\n";
        echo "  - Notification: " . ($config['email']['notification'] ?? 'NON DÉFINI') . "\n";
        echo "  - From: " . ($config['email']['from'] ?? 'NON DÉFINI') . "\n";
    } else {
        echo "✗ Configuration email manquante\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERREUR de configuration: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Chargement des classes PHPMailer
echo "=== TEST 3: CHARGEMENT DES CLASSES PHPMailer ===\n";

try {
    require __DIR__ . '/vendor/phpmailer/src/Exception.php';
    require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
    require __DIR__ . '/vendor/phpmailer/src/SMTP.php';
    echo "✓ Classes PHPMailer chargées avec succès\n";
    
    // Test de création d'instance
    $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
    echo "✓ Instance PHPMailer créée avec succès\n";
    
} catch (Exception $e) {
    echo "✗ ERREUR de chargement PHPMailer: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Configuration SMTP
echo "=== TEST 4: CONFIGURATION SMTP ===\n";

if (isset($config) && isset($config['smtp'])) {
    try {
        $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuration SMTP
        $mailer->isSMTP();
        $mailer->Host = $config['smtp']['host'];
        $mailer->SMTPAuth = $config['smtp']['auth'];
        $mailer->Username = $config['smtp']['username'];
        $mailer->Password = $config['smtp']['password'];
        $mailer->SMTPSecure = $config['smtp']['encryption'];
        $mailer->Port = $config['smtp']['port'];
        $mailer->CharSet = 'UTF-8';
        $mailer->Timeout = $config['smtp']['timeout'] ?? 20;
        
        echo "✓ Configuration SMTP appliquée\n";
        
        // Test de connexion
        echo "Test de connexion SMTP...\n";
        if ($mailer->smtpConnect()) {
            echo "✓ Connexion SMTP réussie\n";
            $mailer->smtpClose();
        } else {
            echo "✗ Échec de connexion SMTP\n";
        }
        
    } catch (Exception $e) {
        echo "✗ ERREUR de configuration SMTP: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ Configuration SMTP non disponible\n";
}

echo "\n";

// Test 5: Test d'envoi réel
echo "=== TEST 5: TEST D'ENVOI RÉEL ===\n";

if (isset($config) && isset($config['smtp'])) {
    try {
        $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuration SMTP
        $mailer->isSMTP();
        $mailer->Host = $config['smtp']['host'];
        $mailer->SMTPAuth = $config['smtp']['auth'];
        $mailer->Username = $config['smtp']['username'];
        $mailer->Password = $config['smtp']['password'];
        $mailer->SMTPSecure = $config['smtp']['encryption'];
        $mailer->Port = $config['smtp']['port'];
        $mailer->CharSet = 'UTF-8';
        $mailer->Timeout = $config['smtp']['timeout'] ?? 20;
        
        // Configuration du message test
        $mailer->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
        $mailer->addAddress($config['email']['notification']);
        $mailer->Subject = 'Test diagnostic - ' . date('Y-m-d H:i:s');
        $mailer->Body = "Ceci est un email de test envoyé par le diagnostic automatique.\n\n";
        $mailer->Body .= "Si vous recevez cet email, la configuration SMTP fonctionne correctement.";
        
        echo "Tentative d'envoi...\n";
        $mailer->send();
        echo "✓ EMAIL ENVOYÉ AVEC SUCCÈS!\n";
        
    } catch (Exception $e) {
        echo "✗ ERREUR D'ENVOI: " . $mailer->ErrorInfo . "\n";
        echo "Détails: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Test 6: Permissions des dossiers
echo "=== TEST 6: PERMISSIONS DES DOSSIERS ===\n";

$directories = [
    __DIR__ . '/logs',
    __DIR__ . '/pdfs'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✓ Dossier inscriptible: " . basename($dir) . "\n";
        } else {
            echo "✗ Dossier non inscriptible: " . basename($dir) . "\n";
        }
    } else {
        echo "! Dossier inexistant: " . basename($dir) . "\n";
    }
}

echo "\n";

// Test 7: Vérification PHP
echo "=== TEST 7: CONFIGURATION PHP ===\n";

echo "Version PHP: " . PHP_VERSION . "\n";
echo "Extensions requises:\n";

$requiredExtensions = ['openssl', 'curl', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ $ext chargée\n";
    } else {
        echo "✗ $ext NON chargée\n";
    }
}

echo "\n";

// Résumé des diagnostics
echo "=== RÉSUMÉ DES DIAGNOSTICS ===\n";
echo "Vérifiez les résultats ci-dessus pour identifier les problèmes.\n";
echo "Les problèmes les plus courants sont:\n";
echo "1. Fichiers PHPMailer manquants\n";
echo "2. Configuration SMTP incorrecte\n";
echo "3. Problèmes de connexion réseau\n";
echo "4. Permissions insuffisantes sur les dossiers\n";
echo "5. Extensions PHP manquantes\n";

echo "</pre>";
?>