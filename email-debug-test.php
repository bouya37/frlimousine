<?php
// Script de test et debug pour l'envoi d'emails
header('Content-Type: text/html; charset=UTF-8');

// Inclure la configuration
$config = require __DIR__ . '/config.php';

// Fonction de log similaire à send-reservation.php
function debug_log($message) {
    $logFile = __DIR__ . '/pdfs/mail-debug.log';
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    error_log($message);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Debug Email - Beverly Limousine</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Debug Email - Test d'envoi</h1>
    
    <div class="section">
        <h2>1. Configuration détectée</h2>
        <pre><?php print_r($config['email']); ?></pre>
    </div>

    <div class="section">
        <h2>2. Configuration PHP Email</h2>
        <div class="info">
            <p><strong>SMTP:</strong> <?php echo ini_get('SMTP') ?: 'non défini'; ?></p>
            <p><strong>SMTP Port:</strong> <?php echo ini_get('smtp_port') ?: 'non défini'; ?></p>
            <p><strong>Sendmail Path:</strong> <?php echo ini_get('sendmail_path') ?: 'non défini'; ?></p>
            <p><strong>Sendmail From:</strong> <?php echo ini_get('sendmail_from') ?: 'non défini'; ?></p>
        </div>
    </div>

    <div class="section">
        <h2>3. Test d'envoi simple</h2>
        <?php
        debug_log('=== DEBUT TEST EMAIL ===');
        debug_log('Configuration: ' . print_r($config['email'], true));
        
        $test_subject = 'Test DEBUG - ' . date('Y-m-d H:i:s');
        $test_message = "Test d'envoi d'email depuis debug-test.php\n\n";
        $test_message .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $test_message .= "PHP Version: " . phpversion() . "\n";
        $test_message .= "Server: " . ($_SERVER['SERVER_NAME'] ?? 'inconnu') . "\n";
        $test_message .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'inconnu') . "\n\n";
        $test_message .= "Configuration Email:\n";
        $test_message .= "  - Notification: " . $config['email']['notification'] . "\n";
        $test_message .= "  - From: " . $config['email']['from'] . "\n";
        
        $headers = [
            'From: ' . $config['email']['from'],
            'Reply-To: ' . $config['email']['from'],
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion(),
            'X-Priority: 3'
        ];
        
        debug_log('Headers: ' . implode("\r\n", $headers));
        debug_log('Message: ' . $test_message);
        
        $start_time = microtime(true);
        $sent = mail($config['email']['notification'], $test_subject, $test_message, implode("\r\n", $headers));
        $end_time = microtime(true);
        $duration = round(($end_time - $start_time) * 1000, 2);
        
        debug_log('Mail() returned: ' . ($sent ? 'TRUE' : 'FALSE'));
        debug_log('Duration: ' . $duration . 'ms');
        
        $last_error = error_get_last();
        if ($last_error) {
            debug_log('Last error: ' . $last_error['message']);
        }
        
        debug_log('=== FIN TEST EMAIL ===');
        ?>
        
        <div class="<?php echo $sent ? 'success' : 'error'; ?>">
            <?php echo $sent ? '✅ Email envoyé avec succès' : '❌ Échec de l\'envoi'; ?>
        </div>
        <p><strong>Durée:</strong> <?php echo $duration; ?>ms</p>
        <?php if ($last_error): ?>
            <div class="error">
                <strong>Dernière erreur PHP:</strong><br>
                <pre><?php echo htmlspecialchars($last_error['message']); ?></pre>
            </div>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>4. Test avec adresse différente</h2>
        <?php
        // Test avec une adresse de test différente
        $test_address = 'debug@beverlylimousine.fr';
        $test_subject2 = 'Test DEBUG 2 - ' . date('Y-m-d H:i:s');
        $sent2 = mail($test_address, $test_subject2, $test_message, implode("\r\n", $headers));
        
        debug_log('Test 2 - Email envoyé vers: ' . $test_address . ' - Result: ' . ($sent2 ? 'SUCCESS' : 'FAILED'));
        ?>
        <div class="<?php echo $sent2 ? 'success' : 'error'; ?>">
            <?php echo $sent2 ? '✅ Email envoyé vers test address' : '❌ Échec vers test address'; ?>
        </div>
    </div>

    <div class="section">
        <h2>5. Analyse des logs</h2>
        <?php
        $logFile = __DIR__ . '/pdfs/mail-debug.log';
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            echo '<pre>' . htmlspecialchars($logs) . '</pre>';
        } else {
            echo '<p class="warning">⚠️ Fichier de log non trouvé</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>6. Recommandations</h2>
        <div class="warning">
            <h3>Si tous les tests échouent:</h3>
            <ol>
                <li><strong>Contactez votre hébergeur</strong> pour vérifier la configuration email</li>
                <li><strong>Utilisez SMTP authentifié</strong> au lieu de mail()</li>
                <li><strong>Vérifiez les logs serveur</strong> dans votre panel d'hébergement</li>
                <li><strong>Testez avec un autre domaine</strong> pour éliminer les problèmes DNS</li>
            </ol>
        </div>
    </div>

    <div class="section">
        <h2>🔗 Liens utiles</h2>
        <ul>
            <li><a href="mail-test.php">Test original</a></li>
            <li><a href="email-diagnostic.php">Diagnostic complet</a></li>
            <li><a href="pdfs/mail-debug.log" target="_blank">Voir les logs debug</a></li>
        </ul>
    </div>
</body>
</html>