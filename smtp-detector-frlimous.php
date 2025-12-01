<?php
/**
 * Détecteur automatique de configuration SMTP pour frlimous.odns.fr
 * Ce script teste différents serveurs SMTP possibles
 */

$possible_servers = [
    [
        'host' => 'mail.frlimous.odns.fr',
        'port' => 587,
        'encryption' => 'tls',
        'name' => 'Mail frlimous (port 587 TLS)'
    ],
    [
        'host' => 'mail.frlimous.odns.fr', 
        'port' => 465,
        'encryption' => 'ssl',
        'name' => 'Mail frlimous (port 465 SSL)'
    ],
    [
        'host' => 'localhost',
        'port' => 25,
        'encryption' => 'none',
        'name' => 'Serveur local (port 25)'
    ],
    [
        'host' => 'localhost',
        'port' => 587,
        'encryption' => 'tls', 
        'name' => 'Serveur local (port 587 TLS)'
    ],
    [
        'host' => 'smtp.frlimous.odns.fr',
        'port' => 587,
        'encryption' => 'tls',
        'name' => 'SMTP frlimous (port 587 TLS)'
    ],
    [
        'host' => 'smtp.frlimous.odns.fr',
        'port' => 465,
        'encryption' => 'ssl',
        'name' => 'SMTP frlimous (port 465 SSL)'
    ]
];

// Configuration pour les tests
$test_email = 'contact@frlimous.odns.fr'; // À adapter selon votre vrai email
$test_password = 'VOTRE_MOT_DE_PASSE_ICI'; // À remplacer par le vrai mot de passe

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détecteur SMTP - frlimous.odns.fr</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 900px; margin: 0 auto; }
        .server-test { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 6px; border-left: 4px solid #007bff; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .testing { color: #ffc107; font-weight: bold; }
        .config-box { background: #e8f5e8; padding: 20px; border-radius: 6px; margin: 20px 0; border: 1px solid #28a745; }
        .warning-box { background: #fff3cd; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #ffc107; }
        code { background: #f1f3f4; padding: 2px 6px; border-radius: 3px; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        input, select { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; }
        .log { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Détecteur SMTP - frlimous.odns.fr</h1>
        
        <div class="warning-box">
            <strong>⚠️ Important :</strong> Ce script va tester différentes configurations SMTP possibles pour frlimous.odns.fr. 
            Vous devez d'abord configurer vos identifiants corrects ci-dessous.
        </div>

        <h2>⚙️ Configuration de test</h2>
        <div class="server-test">
            <form method="post">
                <label>Adresse email :</label><br>
                <input type="email" name="test_email" value="<?= htmlspecialchars($_POST['test_email'] ?? $test_email) ?>" style="width: 300px;"><br>
                
                <label>Mot de passe :</label><br>
                <input type="password" name="test_password" value="<?= htmlspecialchars($_POST['test_password'] ?? '') ?>" style="width: 300px;"><br>
                
                <label>Email de test (destinataire) :</label><br>
                <input type="email" name="test_to" value="<?= htmlspecialchars($_POST['test_to'] ?? ($_POST['test_email'] ?? $test_email)) ?>" style="width: 300px;"><br>
                
                <button type="submit" name="start_test">🚀 Lancer les tests</button>
            </form>
        </div>

        <?php
        if (isset($_POST['start_test'])) {
            $test_email = $_POST['test_email'];
            $test_password = $_POST['test_password'];
            $test_to = $_POST['test_to'];
            
            if (empty($test_email) || empty($test_password)) {
                echo '<div class="warning-box">Veuillez remplir l\'email et le mot de passe.</div>';
            } else {
                echo '<h2>🧪 Résultats des tests</h2>';
                
                foreach ($possible_servers as $i => $server) {
                    echo '<div class="server-test">';
                    echo '<h3>Test ' . ($i+1) . ' : ' . $server['name'] . '</h3>';
                    echo '<div class="log">';
                    
                    // Test de résolution DNS
                    echo "🔍 Test résolution DNS...\n";
                    $ip = gethostbyname($server['host']);
                    if ($ip !== $server['host']) {
                        echo "✅ DNS résolu : " . $server['host'] . " → " . $ip . "\n";
                    } else {
                        echo "❌ DNS non résolu : " . $server['host'] . "\n";
                        echo '</div></div>';
                        continue;
                    }
                    
                    // Test de connexion TCP
                    echo "🔌 Test connexion TCP...\n";
                    $connection = @fsockopen($server['host'], $server['port'], $errno, $errstr, 10);
                    if ($connection) {
                        echo "✅ Connexion TCP établie sur port " . $server['port'] . "\n";
                        fclose($connection);
                        
                        // Test SMTP complet si PHPMailer est disponible
                        if (file_exists(__DIR__ . '/vendor/phpmailer/src/PHPMailer.php')) {
                            echo "📧 Test SMTP complet...\n";
                            
                            try {
                                require __DIR__ . '/vendor/phpmailer/src/Exception.php';
                                require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
                                require __DIR__ . '/vendor/phpmailer/src/SMTP.php';
                                
                                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                                $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
                                $mail->Debugoutput = function($str, $level) {
                                    echo "  📝 " . trim($str) . "\n";
                                };
                                
                                $mail->isSMTP();
                                $mail->Host = $server['host'];
                                $mail->Port = $server['port'];
                                $mail->SMTPAuth = true;
                                $mail->Username = $test_email;
                                $mail->Password = $test_password;
                                
                                if ($server['encryption'] === 'tls') {
                                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                                } elseif ($server['encryption'] === 'ssl') {
                                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                                } else {
                                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                                }
                                
                                $mail->setFrom($test_email, 'Test frlimous');
                                $mail->addAddress($test_to);
                                $mail->Subject = 'Test SMTP ' . $server['name'] . ' - ' . date('Y-m-d H:i:s');
                                $mail->Body = "Configuration SMTP testée :\n\n";
                                $mail->Body .= "Serveur : " . $server['host'] . ":" . $server['port'] . "\n";
                                $mail->Body .= "Chiffrement : " . $server['encryption'] . "\n";
                                $mail->Body .= "Compte : " . $test_email . "\n";
                                $mail->Body .= "Test effectué le : " . date('Y-m-d H:i:s') . "\n";
                                
                                if ($mail->send()) {
                                    echo "🎉 EMAIL ENVOYÉ AVEC SUCCÈS !\n";
                                    echo "✅ Configuration fonctionnelle trouvée :\n";
                                    echo "   Host: " . $server['host'] . "\n";
                                    echo "   Port: " . $server['port'] . "\n";
                                    echo "   Encryption: " . $server['encryption'] . "\n";
                                    echo "   Username: " . $test_email . "\n";
                                    
                                    echo "</div></div>";
                                    break; // Arrêter les tests si un fonctionne
                                } else {
                                    echo "❌ Échec authentification : " . $mail->ErrorInfo . "\n";
                                }
                                
                            } catch (Exception $e) {
                                echo "❌ Erreur test SMTP : " . $e->getMessage() . "\n";
                            }
                        } else {
                            echo "⚠️ PHPMailer non disponible, test TCP uniquement\n";
                        }
                        
                    } else {
                        echo "❌ Échec connexion TCP : Erreur $errno - $errstr\n";
                    }
                    
                    echo "</div></div>";
                }
            }
        }
        ?>

        <div class="config-box">
            <h2>💡 Configuration recommandée</h2>
            <p>Après avoir trouvé la configuration qui fonctionne, remplacez dans <code>config.php</code> :</p>
            
            <pre>// Ancienne configuration (o2switch)
'smtp' => [
    'host' => 'dolphin.o2switch.net',
    'port' => 465,
    'encryption' => 'ssl',
    'username' => 'contact@transvoyage.fr',
    'password' => '19Massenet!!',
    // ...
]

// Nouvelle configuration (frlimous)
'smtp' => [
    'host' => 'mail.frlimous.odns.fr', // Serveur trouvé par le test
    'port' => 587, // Port fonctionnel
    'encryption' => 'tls', // Chiffrement fonctionnel
    'username' => 'VOTRE_EMAIL_FRIMOUS', // Votre vrai email
    'password' => 'VOTRE_VRAI_MOT_DE_PASSE', // Votre vrai mot de passe
    // ...
]</pre>
        </div>

        <div class="warning-box">
            <h3>🔒 Sécurité</h3>
            <ul>
                <li>Ne publiez jamais vos vrais mots de passe en ligne</li>
                <li>Utilisez des variables d'environnement pour les mots de passe en production</li>
                <li>Supprimez ce fichier de diagnostic après avoir trouvé la bonne configuration</li>
            </ul>
        </div>
    </div>
</body>
</html>