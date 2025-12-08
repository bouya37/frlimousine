<?php
// Script de test pour vérifier si la fonction mail() fonctionne chez OVH
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Test d'envoi d'email - OVH</h1>";
echo "<p>Test de la fonction mail() PHP...</p>";

// Configuration du test
$to = 'contact@transvoyage.fr';
$subject = 'Test Email - Beverly Limousine (Script de test)';
$message = "
<h2>🎉 Test d'email réussi !</h2>
<p>Ce email de test a été envoyé le " . date('d/m/Y à H:i:s') . " depuis votre site Beverly Limousine.</p>
<p><strong>Si vous recevez cet email, cela signifie que :</strong></p>
<ul>
    <li>✅ La fonction mail() PHP fonctionne chez OVH</li>
    <li>✅ Le système de réservation enverra les emails correctement</li>
    <li>✅ Votre formulaire de contact est opérationnel</li>
</ul>
<p><em>Cet email de test a été envoyé automatiquement pour vérifier la configuration.</em></p>
";

$headers = [
    'From' => 'noreply@beverlylimousine.fr',
    'Reply-To' => 'noreply@beverlylimousine.fr',
    'Content-Type' => 'text/html; charset=UTF-8',
    'MIME-Version' => '1.0'
];

// Tentative d'envoi
$result = mail($to, $subject, $message, implode("\r\n", array_map(function($k, $v) { return "$k: $v"; }, array_keys($headers), $headers)));

if ($result) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ Succès !</h3>";
    echo "<p>Email de test envoyé avec succès vers <strong>$to</strong></p>";
    echo "<p><strong>Action :</strong> Vérifiez votre boîte email dans les prochaines minutes.</p>";
    echo "<p>Si vous ne recevez pas l'email, vérifiez :</p>";
    echo "<ul>";
    echo "<li>Vos spams/courriers indésirables</li>";
    echo "<li>L'adresse <strong>contact@transvoyage.fr</strong> existe bien</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Erreur</h3>";
    echo "<p>L'envoi d'email a échoué.</p>";
    echo "<p><strong>Actions à effectuer :</strong></p>";
    echo "<ul>";
    echo "<li>Vérifiez dans votre panel OVH que l'email est activé</li>";
    echo "<li>Contactez le support OVH si le problème persiste</li>";
    echo "</ul>";
    echo "</div>";
}

// Informations de débogage
echo "<h3>Informations de débogage :</h3>";
echo "<ul>";
echo "<li><strong>Serveur :</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li><strong>PHP Version :</strong> " . phpversion() . "</li>";
echo "<li><strong>Date/Heure :</strong> " . date('d/m/Y H:i:s') . "</li>";
echo "<li><strong>Test effectué à :</strong> " . date('c') . "</li>";
echo "</ul>";

// Log du test
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'result' => $result,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'php_version' => phpversion()
];

file_put_contents('email_test_log.txt', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);

echo "<p><small>Log sauvegardé dans : email_test_log.txt</small></p>";
?>