<?php
echo "<h1>Test Configuration</h1>";

try {
    echo "🔄 Tentative de chargement de config.php...<br><br>";
    
    $config = require 'config.php';
    
    echo "✅ Configuration chargée avec succès !<br><br>";
    
    echo "<h3>Configuration SMTP :</h3>";
    echo "Host: " . $config['smtp']['host'] . "<br>";
    echo "Port: " . $config['smtp']['port'] . "<br>";
    echo "Encryption: " . $config['smtp']['encryption'] . "<br>";
    echo "Username: " . $config['smtp']['username'] . "<br>";
    echo "From Email: " . $config['smtp']['from_email'] . "<br>";
    
    echo "<br><h3>Configuration Email :</h3>";
    echo "Notification: " . $config['email']['notification'] . "<br>";
    echo "From: " . $config['email']['from'] . "<br>";
    
    echo "<br><h3>Configuration Upload :</h3>";
    echo "Directory: " . $config['upload']['directory'] . "<br>";
    echo "Max Size: " . $config['upload']['max_size'] . "<br>";
    
    echo "<br><div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>";
    echo "🎉 <strong>SUCCESS:</strong> Votre config.php fonctionne correctement !";
    echo "</div>";
    
} catch (ParseError $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "❌ <strong>ERREUR DE SYNTAXE dans config.php:</strong><br>";
    echo "Ligne: " . $e->getLine() . "<br>";
    echo "Message: " . $e->getMessage();
    echo "</div>";
    
    echo "<br><h3>Solutions :</h3>";
    echo "<ol>";
    echo "<li>Vérifiez les accolades {{}} dans config.php</li>";
    echo "<li>Vérifiez les guillemets et apostrophes</li>";
    echo "<li>Vérifiez les virgules entre les éléments</li>";
    echo "<li>Utilisez un éditeur avec coloration syntaxique</li>";
    echo "</ol>";
    
} catch (Error $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "❌ <strong>ERREUR FATALE:</strong><br>";
    echo "Message: " . $e->getMessage();
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404;'>";
    echo "⚠️ <strong>EXCEPTION:</strong><br>";
    echo "Message: " . $e->getMessage();
    echo "</div>";
}
?>