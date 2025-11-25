<?php
// Script de diagnostic pour identifier les problèmes d'envoi d'emails
header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Diagnostic Email - Beverly Limousine</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .check { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>🔍 Diagnostic Email - Beverly Limousine</h1>
    
    <div class="section">
        <h2>1. Configuration Email</h2>
        <?php
        try {
            $config = require __DIR__ . '/config.php';
            echo '<p class="check">✅ Configuration chargée avec succès</p>';
            echo '<p><strong>Notification:</strong> ' . htmlspecialchars($config['email']['notification']) . '</p>';
            echo '<p><strong>From:</strong> ' . htmlspecialchars($config['email']['from']) . '</p>';
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur de configuration: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>2. Configuration PHP Mail</h2>
        <?php
        echo '<p class="check">SMTP: ' . (ini_get('SMTP') ?: 'non défini') . '</p>';
        echo '<p class="check">smtp_port: ' . ini_get('smtp_port') . '</p>';
        echo '<p class="check">sendmail_from: ' . (ini_get('sendmail_from') ?: 'non défini') . '</p>';
        echo '<p class="check">sendmail_path: ' . (ini_get('sendmail_path') ?: 'non défini') . '</p>';
        ?>
    </div>

    <div class="section">
        <h2>3. Test fonction mail()</h2>
        <?php
        $test_email = 'test@beverlylimousine.fr';
        $subject = 'Test Email - ' . date('Y-m-d H:i:s');
        $message = "Test d'envoi d'email depuis le diagnostic\n\n";
        $message .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $message .= "PHP Version: " . phpversion() . "\n";
        $message .= "Server: " . $_SERVER['SERVER_NAME'] . "\n";
        
        $headers = [
            'From: contact@transvoyage.fr',
            'Reply-To: contact@transvoyage.fr',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $sent = mail($test_email, $subject, $message, implode("\r\n", $headers));
        
        if ($sent) {
            echo '<p class="success">✅ Fonction mail() fonctionne</p>';
            echo '<p>Email de test envoyé vers: ' . htmlspecialchars($test_email) . '</p>';
        } else {
            echo '<p class="error">❌ Échec de l\'envoi avec mail()</p>';
            echo '<p>Vérifiez la configuration SMTP du serveur</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>4. Test d'envoi personnalisé</h2>
        <?php
        $config = require __DIR__ . '/config.php';
        
        $test_message = "Test personnalisé depuis le diagnostic\n\n";
        $test_message .= "Date: " . date('d/m/Y H:i:s') . "\n";
        $test_message .= "URL: " . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
        
        $custom_sent = mail(
            $config['email']['notification'], 
            'Diagnostic Email - Beverly Limousine', 
            $test_message, 
            implode("\r\n", $headers)
        );
        
        if ($custom_sent) {
            echo '<p class="success">✅ Envoi vers contact@transvoyage.fr réussi</p>';
        } else {
            echo '<p class="error">❌ Échec d\'envoi vers contact@transvoyage.fr</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>5. Logs d'erreur PHP</h2>
        <?php
        $error_log = ini_get('error_log');
        if ($error_log) {
            echo '<p class="check">Log d\'erreur: ' . htmlspecialchars($error_log) . '</p>';
            if (file_exists($error_log)) {
                $last_lines = file($error_log);
                $recent_errors = array_slice($last_lines, -10);
                echo '<h3>Dernières erreurs:</h3>';
                foreach ($recent_errors as $line) {
                    if (strpos($line, 'mail') !== false || strpos($line, 'sendmail') !== false) {
                        echo '<p style="color: red;">' . htmlspecialchars($line) . '</p>';
                    }
                }
            } else {
                echo '<p class="warning">⚠️ Fichier de log non trouvé</p>';
            }
        } else {
            echo '<p class="warning">⚠️ Aucun log d\'erreur configuré</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>6. Recommandations</h2>
        <ul>
            <li><strong>Si mail() ne fonctionne pas:</strong> Vérifiez la configuration SMTP de votre hébergeur</li>
            <li><strong>Pour OVH:</strong> Utilisez SMTP authentifié au lieu de mail()</li>
            <li><strong>Alternative:</strong> Implémentez PHPMailer ou SwiftMailer</li>
            <li><strong>Logs:</strong> Vérifiez les logs d'erreur de votre hébergeur</li>
            <li><strong>Test:</strong> Utilisez mail-test.php pour tester l'envoi</li>
        </ul>
    </div>

    <div class="section">
        <h2>🔧 Actions à effectuer</h2>
        <ol>
            <li>Contactez votre hébergeur pour vérifier la configuration email</li>
            <li>Vérifiez que contact@transvoyage.fr est une adresse valide</li>
            <li>Consultez les logs d'erreur du serveur</li>
            <li>Envisagez d'utiliser un service SMTP tiers (SendGrid, Mailgun, etc.)</li>
        </ol>
    </div>
</body>
</html>