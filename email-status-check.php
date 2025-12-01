<?php
/**
 * Vérification de l'état des fichiers email
 * Ce script analyse tous les fichiers liés à l'envoi d'emails
 */

$files_to_check = [
    'vendor/phpmailer/src/Exception.php' => 'Classe Exception PHPMailer',
    'vendor/phpmailer/src/PHPMailer.php' => 'Classe principale PHPMailer', 
    'vendor/phpmailer/src/SMTP.php' => 'Classe SMTP PHPMailer',
    'config.php' => 'Configuration principale',
    'security.php' => 'Classe de sécurité',
    'send_mail.php' => 'Handler d\'envoi email principal',
    'send-reservation.php' => 'Handler de réservation',
    'logs/' => 'Dossier des logs',
    'pdfs/' => 'Dossier des PDFs'
];

$config = require __DIR__ . '/config.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État des fichiers Email - Beverly Limousine</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1000px; margin: 0 auto; }
        .status-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .status-table th, .status-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .status-table th { background: #f8f9fa; font-weight: bold; }
        .ok { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .config-section { background: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0; }
        .code { background: #f1f3f4; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 État des fichiers Email - Beverly Limousine</h1>
        <p><em>Analyse effectuée le <?= date('Y-m-d H:i:s') ?></em></p>

        <h2>🔍 État des fichiers</h2>
        <table class="status-table">
            <thead>
                <tr>
                    <th>Fichier/Dossier</th>
                    <th>Statut</th>
                    <th>Description</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files_to_check as $file => $description): ?>
                <tr>
                    <td><code><?= htmlspecialchars($file) ?></code></td>
                    <td>
                        <?php
                        if (file_exists(__DIR__ . '/' . $file)) {
                            if (is_dir(__DIR__ . '/' . $file)) {
                                if (is_writable(__DIR__ . '/' . $file)) {
                                    echo '<span class="ok">✅ Dossier inscriptible</span>';
                                } else {
                                    echo '<span class="error">❌ Dossier non inscriptible</span>';
                                }
                            } else {
                                echo '<span class="ok">✅ Fichier présent</span>';
                            }
                        } else {
                            echo '<span class="error">❌ Fichier manquant</span>';
                        }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($description) ?></td>
                    <td>
                        <?php
                        if (file_exists(__DIR__ . '/' . $file) && !is_dir(__DIR__ . '/' . $file)) {
                            $size = filesize(__DIR__ . '/' . $file);
                            $modified = date('Y-m-d H:i:s', filemtime(__DIR__ . '/' . $file));
                            echo "Taille: " . number_format($size) . " octets<br>";
                            echo "Modifié: $modified";
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>⚙️ Configuration SMTP</h2>
        <div class="config-section">
            <h3>Paramètres actuels</h3>
            <table class="status-table">
                <tr>
                    <th>Paramètre</th>
                    <th>Valeur</th>
                    <th>Statut</th>
                </tr>
                <tr>
                    <td>Serveur SMTP</td>
                    <td><code><?= htmlspecialchars($config['smtp']['host'] ?? 'NON DÉFINI') ?></code></td>
                    <td><?= isset($config['smtp']['host']) ? '<span class="ok">✅ Configuré</span>' : '<span class="error">❌ Manquant</span>' ?></td>
                </tr>
                <tr>
                    <td>Port</td>
                    <td><code><?= htmlspecialchars($config['smtp']['port'] ?? 'NON DÉFINI') ?></code></td>
                    <td><?= isset($config['smtp']['port']) ? '<span class="ok">✅ Configuré</span>' : '<span class="error">❌ Manquant</span>' ?></td>
                </tr>
                <tr>
                    <td>Sécurité</td>
                    <td><code><?= htmlspecialchars($config['smtp']['encryption'] ?? 'NON DÉFINI') ?></code></td>
                    <td><?= isset($config['smtp']['encryption']) ? '<span class="ok">✅ Configuré</span>' : '<span class="error">❌ Manquant</span>' ?></td>
                </tr>
                <tr>
                    <td>Utilisateur</td>
                    <td><code><?= htmlspecialchars($config['smtp']['username'] ?? 'NON DÉFINI') ?></code></td>
                    <td><?= isset($config['smtp']['username']) ? '<span class="ok">✅ Configuré</span>' : '<span class="error">❌ Manquant</span>' ?></td>
                </tr>
                <tr>
                    <td>Mot de passe</td>
                    <td><code><?= isset($config['smtp']['password']) ? '••••••••••' : 'NON DÉFINI' ?></code></td>
                    <td><?= isset($config['smtp']['password']) ? '<span class="ok">✅ Configuré</span>' : '<span class="error">❌ Manquant</span>' ?></td>
                </tr>
                <tr>
                    <td>Email expéditeur</td>
                    <td><code><?= htmlspecialchars($config['smtp']['from_email'] ?? 'NON DÉFINI') ?></code></td>
                    <td><?= isset($config['smtp']['from_email']) ? '<span class="ok">✅ Configuré</span>' : '<span class="error">❌ Manquant</span>' ?></td>
                </tr>
                <tr>
                    <td>Email notification</td>
                    <td><code><?= htmlspecialchars($config['email']['notification'] ?? 'NON DÉFINI') ?></code></td>
                    <td><?= isset($config['email']['notification']) ? '<span class="ok">✅ Configuré</span>' : '<span class="error">❌ Manquant</span>' ?></td>
                </tr>
            </table>
        </div>

        <h2>🚨 Problèmes détectés</h2>
        <div class="config-section">
            <?php
            $issues = [];
            
            // Vérification des fichiers PHPMailer
            $phpmailer_files = ['vendor/phpmailer/src/Exception.php', 'vendor/phpmailer/src/PHPMailer.php', 'vendor/phpmailer/src/SMTP.php'];
            foreach ($phpmailer_files as $file) {
                if (!file_exists(__DIR__ . '/' . $file)) {
                    $issues[] = "Fichier PHPMailer manquant: $file";
                }
            }
            
            // Vérification des dossiers
            if (!is_writable(__DIR__ . '/logs/')) {
                $issues[] = "Dossier 'logs/' n'est pas inscriptible";
            }
            if (!is_writable(__DIR__ . '/pdfs/')) {
                $issues[] = "Dossier 'pdfs/' n'est pas inscriptible";
            }
            
            // Vérification de la configuration
            if (!isset($config['smtp']['host']) || $config['smtp']['host'] === '') {
                $issues[] = "Serveur SMTP non configuré";
            }
            
            if (empty($issues)) {
                echo '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; border: 1px solid #c3e6cb;">';
                echo '✅ <strong>Aucun problème détecté dans la configuration de base !</strong><br>';
                echo 'Les problèmes d\'envoi peuvent être liés à :<br>';
                echo '• Problèmes réseau ou pare-feu<br>';
                echo '• Authentification SMTP échouée<br>';
                echo '• Restrictions du serveur de messagerie<br>';
                echo '• Problèmes temporaires du serveur SMTP';
                echo '</div>';
            } else {
                echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; border: 1px solid #f5c6cb;">';
                echo '❌ <strong>Problèmes détectés :</strong><ul>';
                foreach ($issues as $issue) {
                    echo '<li>' . htmlspecialchars($issue) . '</li>';
                }
                echo '</ul></div>';
            }
            ?>
        </div>

        <h2>🛠️ Actions recommandées</h2>
        <div class="config-section">
            <h3>Test 1: Vérifier les dépendances</h3>
            <p>Si des fichiers PHPMailer sont manquants :</p>
            <div class="code"># Via Composer
composer require phpmailer/phpmailer

# Ou téléchargement manuel
# Télécharger depuis https://github.com/PHPMailer/PHPMailer/releases/latest
# Extraire dans vendor/phpmailer/</div>

            <h3>Test 2: Corriger les permissions</h3>
            <div class="code"># Linux/Mac
chmod 755 logs/ pdfs/
chown www-data:www-data logs/ pdfs/</div>

            <h3>Test 3: Lancer les diagnostics</h3>
            <p>Utilisez les fichiers de diagnostic créés :</p>
            <ul>
                <li><a href="smtp-test-enhanced.php" target="_blank">🧪 Test SMTP complet</a></li>
                <li><a href="email-diagnostic-report.html" target="_blank">📊 Rapport de diagnostic</a></li>
            </ul>
        </div>

        <h2>📞 Support</h2>
        <div class="config-section">
            <p>Si les problèmes persistent après avoir suivi ces étapes :</p>
            <ol>
                <li>Vérifiez les logs d'erreur de votre hébergeur o2switch</li>
                <li>Testez la connectivité depuis un autre serveur</li>
                <li>Contactez le support o2switch pour vérifier les paramètres SMTP</li>
                <li>Vérifiez que le compte email contact@transvoyage.fr est bien configuré</li>
            </ol>
        </div>
    </div>
</body>
</html>