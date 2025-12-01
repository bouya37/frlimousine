<?php
/**
 * Test SMTP rapide pour diagnostiquer les problèmes de connexion
 * Utilisez ce fichier pour identifier la cause exacte du problème
 */

declare(strict_types=1);

date_default_timezone_set('Europe/Paris');

// Configuration
$config = [
    'host' => 'dolphin.o2switch.net',
    'port' => 465,
    'username' => 'contact@transvoyage.fr',
    'password' => '19Massenet!!',
    'from_email' => 'contact@transvoyage.fr',
    'from_name' => 'Test Beverly Limousine'
];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SMTP - Beverly Limousine</title>
    <style>
        body { 
            font-family: 'Courier New', monospace; 
            margin: 20px; 
            background: #1e1e1e; 
            color: #d4d4d4; 
        }
        .container { 
            background: #252526; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.3); 
            max-width: 1000px;
            margin: 0 auto;
        }
        .log { 
            background: #1e1e1e; 
            padding: 20px; 
            border-radius: 4px; 
            border-left: 4px solid #007acc;
            margin: 20px 0;
            font-size: 14px;
            line-height: 1.4;
            white-space: pre-wrap;
            overflow-x: auto;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #9cdcfe; }
        h1 { 
            color: #4ec9b0; 
            border-bottom: 2px solid #007acc; 
            padding-bottom: 15px;
            font-size: 28px;
        }
        h2 { color: #9cdcfe; margin-top: 30px; }
        .test-form { 
            background: #2d2d30; 
            padding: 20px; 
            border-radius: 6px; 
            margin: 20px 0; 
        }
        button {
            background: #007acc;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px 10px 0;
        }
        button:hover { background: #005a9e; }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background: #1e1e1e;
            border: 1px solid #3c3c3c;
            color: #d4d4d4;
            border-radius: 4px;
            font-family: inherit;
        }
        .progress { 
            background: #1e1e1e; 
            height: 20px; 
            border-radius: 10px; 
            margin: 10px 0; 
            overflow: hidden; 
        }
        .progress-bar { 
            background: linear-gradient(90deg, #007acc, #4ec9b0); 
            height: 100%; 
            width: 0%; 
            transition: width 0.3s ease; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test SMTP - Beverly Limousine</h1>
        
        <div class="log">
<?php
if (isset($_GET['test'])) {
    echo "<span class='info'>🚀 Démarrage du test SMTP...</span>\n\n";
    
    // Test 1: Vérification de PHPMailer
    echo "<span class='info'>[1/5] Vérification de PHPMailer...</span>\n";
    
    try {
        require __DIR__ . '/vendor/phpmailer/src/Exception.php';
        require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
        require __DIR__ . '/vendor/phpmailer/src/SMTP.php';
        echo "<span class='success'>✅ PHPMailer chargé avec succès</span>\n\n";
    } catch (Exception $e) {
        echo "<span class='error'>❌ Erreur PHPMailer: " . $e->getMessage() . "</span>\n\n";
        exit;
    }
    
    // Test 2: Test DNS
    echo "<span class='info'>[2/5] Test de résolution DNS...</span>\n";
    $host = $config['host'];
    $ip = gethostbyname($host);
    if ($ip !== $host) {
        echo "<span class='success'>✅ DNS résolu: $host → $ip</span>\n\n";
    } else {
        echo "<span class='error'>❌ Résolution DNS échouée pour $host</span>\n\n";
        exit;
    }
    
    // Test 3: Connexion TCP brute
    echo "<span class='info'>[3/5] Test de connexion TCP brute...</span>\n";
    $connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 10);
    if ($connection) {
        echo "<span class='success'>✅ Connexion TCP établie sur port " . $config['port'] . "</span>\n";
        
        // Lire la réponse du serveur
        $response = fgets($connection, 1024);
        echo "<span class='info'>📨 Réponse serveur: " . trim($response) . "</span>\n";
        fclose($connection);
        echo "\n";
    } else {
        echo "<span class='error'>❌ Échec connexion TCP: Erreur $errno - $errstr</span>\n\n";
        exit;
    }
    
    // Test 4: Test SMTP complet
    echo "<span class='info'>[4/5] Test SMTP complet avec PHPMailer...</span>\n";
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuration du debug
        $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            echo "<span class='info'>  📝 SMTP: " . htmlspecialchars($str) . "</span>\n";
        };
        
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $config['port'];
        $mail->Timeout = 30;
        
        // Configuration message
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($config['username']); // Envoi à soi-même
        $mail->Subject = 'Test SMTP - ' . date('Y-m-d H:i:s');
        $mail->Body = "Ceci est un test de connectivité SMTP.\n\n";
        $mail->Body .= "Si vous recevez cet email, la configuration fonctionne correctement.\n";
        $mail->Body .= "Test envoyé le: " . date('Y-m-d H:i:s') . "\n";
        
        echo "<span class='info'>📤 Tentative d'envoi...</span>\n";
        $mail->send();
        
        echo "<span class='success'>🎉 EMAIL ENVOYÉ AVEC SUCCÈS !</span>\n\n";
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Erreur SMTP: " . $mail->ErrorInfo . "</span>\n";
        echo "<span class='error'>💡 Détails: " . $e->getMessage() . "</span>\n\n";
    }
    
    // Test 5: Informations système
    echo "<span class='info'>[5/5] Informations système...</span>\n";
    echo "<span class='info'>📋 PHP Version: " . PHP_VERSION . "</span>\n";
    echo "<span class='info'>📋 OpenSSL: " . (extension_loaded('openssl') ? '✅ Activé' : '❌ Désactivé') . "</span>\n";
    echo "<span class='info'>📋 cURL: " . (extension_loaded('curl') ? '✅ Activé' : '❌ Désactivé') . "</span>\n";
    echo "<span class='info'>📋 mbstring: " . (extension_loaded('mbstring') ? '✅ Activé' : '❌ Désactivé') . "</span>\n\n";
    
    echo "<span class='success'>🏁 Test terminé !</span>\n";
}
?>
        </div>

        <div class="test-form">
            <h2>🧪 Lancer un test</h2>
            <p>Cliquez sur le bouton ci-dessous pour lancer le test SMTP complet :</p>
            <a href="?test=1">
                <button>🚀 Lancer le test SMTP</button>
            </a>
            <p><small>Le test peut prendre 10-30 secondes. Les résultats s'afficheront dans le journal ci-dessus.</small></p>
        </div>

        <div class="test-form">
            <h2>📊 Test manuel personnalisé</h2>
            <p>Pour tester avec vos propres paramètres :</p>
            
            <form method="post" action="">
                <label>Adresse email destinataire :</label>
                <input type="email" name="to_email" value="contact@transvoyage.fr" required>
                
                <label>Sujet :</label>
                <input type="text" name="subject" value="Test SMTP manuel - <?= date('Y-m-d H:i:s') ?>" required>
                
                <label>Message :</label>
                <textarea name="message" rows="5" required>Ceci est un test manuel de la configuration SMTP de Beverly Limousine.

Config actuelle :
- Serveur : dolphin.o2switch.net
- Port : 465 (SSL)
- Compte : contact@transvoyage.fr

Test effectué le <?= date('Y-m-d H:i:s') ?>.</textarea>
                
                <button type="submit" name="send_test">📧 Envoyer le test</button>
            </form>
        </div>

<?php
if (isset($_POST['send_test'])) {
    try {
        require __DIR__ . '/vendor/phpmailer/src/Exception.php';
        require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
        require __DIR__ . '/vendor/phpmailer/src/SMTP.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $config['port'];
        
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($_POST['to_email']);
        $mail->Subject = $_POST['subject'];
        $mail->Body = $_POST['message'];
        
        if ($mail->send()) {
            echo "<div class='log'><span class='success'>✅ Email envoyé avec succès à " . htmlspecialchars($_POST['to_email']) . " !</span></div>";
        } else {
            echo "<div class='log'><span class='error'>❌ Échec de l'envoi : " . $mail->ErrorInfo . "</span></div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='log'><span class='error'>❌ Erreur : " . $e->getMessage() . "</span></div>";
    }
}
?>

        <div class="log">
            <h2>🔧 Solutions aux problèmes courants</h2>
            
            <span class="error">❌ "Class PHPMailer\PHPMailer\PHPMailer not found"</span>
            <span class="info">→ Réinstallez PHPMailer via Composer ou vérifiez le chemin d'accès</span>
            
            <span class="error">❌ "Connection timed out"</span>
            <span class="info">→ Problème réseau/pare-feu, vérifiez la connectivité avec dolphin.o2switch.net:465</span>
            
            <span class="error">❌ "Authentication failed"</span>
            <span class="info">→ Vérifiez les identifiants (contact@transvoyage.fr / 19Massenet!!)</span>
            
            <span class="error">❌ "SMTP Error: Could not authenticate"</span>
            <span class="info">→ Le mot de passe est peut-être incorrect ou le compte email n'est pas configuré</span>
            
            <span class="error">❌ "SMTP Error: Connection refused"</span>
            <span class="info">→ Le serveur SMTP n'accepte pas les connexions depuis votre serveur</span>
        </div>
    </div>
</body>
</html>