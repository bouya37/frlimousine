<?php
// Fichier de test pour vérifier l'envoi d'emails avec PHPMailer et SMTP
header('Content-Type: text/html; charset=UTF-8');

require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';
require __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$config = require __DIR__ . '/config.php';

?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Email SMTP - Beverly Limousine</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .test-success { background: #d4edda; border: 1px solid #c3e6cb; }
        .test-error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .test-info { background: #d1ecf1; border: 1px solid #bee5eb; }
        code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Test Email SMTP - Beverly Limousine</h1>
    
    <div class="section">
        <h2>📋 Configuration SMTP</h2>
        <div class="test-result test-info">
            <strong>Serveur SMTP:</strong> <?= htmlspecialchars($config['smtp']['host']) ?><br>
            <strong>Port:</strong> <?= htmlspecialchars($config['smtp']['port']) ?><br>
            <strong>Chiffrement:</strong> <?= htmlspecialchars($config['smtp']['encryption']) ?><br>
            <strong>Authentification:</strong> <?= $config['smtp']['auth'] ? 'Activée' : 'Désactivée' ?><br>
            <strong>Utilisateur:</strong> <?= htmlspecialchars($config['smtp']['username']) ?><br>
            <strong>Mot de passe:</strong> <?= str_repeat('*', strlen($config['smtp']['password'])) ?><br>
        </div>
    </div>

    <?php
    // Test d'envoi d'email
    if (isset($_POST['test_email'])) {
        echo '<div class="section">';
        echo '<h2>🧪 Résultat du test</h2>';
        
        $testEmail = $_POST['test_email'];
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            echo '<div class="test-result test-error">';
            echo '<strong class="error">❌ Erreur:</strong> Adresse email invalide';
            echo '</div>';
        } else {
            $mail = new PHPMailer(true);
            
            try {
                // Configuration du serveur
                $mail->isSMTP();
                $mail->Host = $config['smtp']['host'];
                $mail->SMTPAuth = $config['smtp']['auth'];
                $mail->Username = $config['smtp']['username'];
                $mail->Password = $config['smtp']['password'];
                $mail->SMTPSecure = $config['smtp']['encryption'];
                $mail->Port = $config['smtp']['port'];
                
                // Configuration d'encodage
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = '8bit';
                
                // Destinataires
                $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
                $mail->addAddress($testEmail);
                
                // Contenu
                $mail->isHTML(false);
                $mail->Subject = 'Test Email SMTP - Beverly Limousine - ' . date('d/m/Y H:i:s');
                $mail->Body = 
                    "Test d'envoi d'email depuis Beverly Limousine\n\n" .
                    "Date: " . date('d/m/Y H:i:s') . "\n" .
                    "PHP Version: " . phpversion() . "\n" .
                    "Serveur: " . $_SERVER['SERVER_NAME'] . "\n\n" .
                    "Si vous recevez cet email, la configuration SMTP fonctionne correctement!\n\n" .
                    "Cordialement,\nBeverly Limousine";
                
                // Mode debug activé
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                
                // Envoi
                $mail->send();
                
                echo '<div class="test-result test-success">';
                echo '<strong class="success">✅ SUCCÈS!</strong><br>';
                echo 'Email de test envoyé avec succès vers: <strong>' . htmlspecialchars($testEmail) . '</strong><br>';
                echo '<strong>Message ID:</strong> ' . $mail->getLastMessageID();
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="test-result test-error">';
                echo '<strong class="error">❌ ÉCHEC!</strong><br>';
                echo '<strong>Erreur:</strong> ' . htmlspecialchars($mail->ErrorInfo) . '<br>';
                echo '<strong>Exception:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        }
        echo '</div>';
    }
    ?>

    <div class="section">
        <h2>🎯 Lancer un test</h2>
        <form method="post">
            <label for="test_email">Adresse email de destination:</label><br>
            <input type="email" id="test_email" name="test_email" required 
                   value="<?= htmlspecialchars($config['email']['notification']) ?>" 
                   style="width: 300px; padding: 5px; margin: 10px 0;">
            <br>
            <button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                📧 Envoyer un email de test
            </button>
        </form>
    </div>

    <div class="section">
        <h2>🔍 Vérifications système</h2>
        <div class="test-result test-info">
            <strong>Version PHP:</strong> <?= phpversion() ?><br>
            <strong>Extensions installées:</strong><br>
            <?php
            $extensions = ['openssl', 'curl', 'filter'];
            foreach ($extensions as $ext) {
                $status = extension_loaded($ext) ? '✅ Installée' : '❌ Non installée';
                echo '&nbsp;&nbsp;- ' . ucfirst($ext) . ': ' . $status . '<br>';
            }
            ?>
        </div>
    </div>

    <div class="section">
        <h2>📚 Instructions</h2>
        <ol>
            <li><strong>Vérifiez la configuration:</strong> Assurez-vous que les paramètres SMTP dans <code>config.php</code> sont corrects</li>
            <li><strong>Testez l'envoi:</strong> Cliquez sur le bouton "Envoyer un email de test"</li>
            <li><strong>Vérifiez les logs:</strong> Consultez le fichier <code>pdfs/mail.log</code> pour plus de détails</li>
            <li><strong>Configuration OVH:</strong>
                <ul>
                    <li>Connectez-vous à votre panel OVH</li>
                    <li>Allez dans "Emails" → "Comptes email"</li>
                    <li>Créez ou utilisez le compte <code>contact@transvoyage.fr</code></li>
                    <li>Notez le mot de passe du compte email</li>
                    <li>Mettez à jour <code>config.php</code> avec le bon mot de passe</li>
                </ul>
            </li>
        </ol>
    </div>

    <div class="section">
        <h2>🚀 Prochaines étapes</h2>
        <ol>
            <li>Après un test réussi, modifiez <code>config.php</code> pour désactiver le mode debug:
                <pre>'debug' => false,</pre>
            </li>
            <li>Testez le formulaire de réservation sur le site principal</li>
            <li>Surveillez les logs d'envoi dans <code>pdfs/mail.log</code></li>
        </ol>
    </div>

    <div class="section">
        <h2>🆘 Dépannage</h2>
        <p><strong>Si le test échoue:</strong></p>
        <ul>
            <li>Vérifiez que le mot de passe SMTP dans <code>config.php</code> est correct</li>
            <li>Assurez-vous que l'adresse email <code>contact@transvoyage.fr</code> existe sur OVH</li>
            <li>Vérifiez que le serveur SMTP est accessible (port 587)</li>
            <li>Consultez les logs d'erreur de votre hébergeur</li>
        </ul>
    </div>

    <p style="margin-top: 30px; color: #666; font-size: 12px;">
        <em>Test réalisé le <?= date('d/m/Y H:i:s') ?></em>
    </p>
</body>
</html>