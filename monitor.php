<?php
require_once 'security.php';
$security = initSecurity();

echo "<h1>🚗 Monitoring Sécurité FRLimousine</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .stat { background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .warning { background: #ffebee; border-left: 4px solid #f44336; }
    .success { background: #e8f5e8; border-left: 4px solid #4caf50; }
    .info { background: #e3f2fd; border-left: 4px solid #2196f3; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Fonction pour lire les logs de sécurité
function readSecurityLogs($lines = 50) {
    $logFile = 'pdfs/security.log';
    if (!file_exists($logFile)) {
        return [];
    }

    $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return array_slice(array_reverse($logs), 0, $lines);
}

// Fonction pour analyser les statistiques
function getSecurityStats() {
    $stats = [
        'total_requests' => 0,
        'blocked_ips' => 0,
        'suspicious_activities' => 0,
        'recent_attacks' => 0
    ];

    $blockedFile = 'pdfs/blocked_ips.json';
    if (file_exists($blockedFile)) {
        $blocked = json_decode(file_get_contents($blockedFile), true);
        $stats['blocked_ips'] = count($blocked);
    }

    $logs = readSecurityLogs(1000);
    $stats['total_requests'] = count($logs);

    foreach ($logs as $log) {
        if (strpos($log, 'ATTAQUE_DETECTEE') !== false || strpos($log, 'IP_BLOQUEE') !== false) {
            $stats['suspicious_activities']++;
        }
        if (strpos($log, 'ATTAQUE_DETECTEE') !== false) {
            $stats['recent_attacks']++;
        }
    }

    return $stats;
}

// Afficher les statistiques
$stats = getSecurityStats();
echo "<div class='stat info'>";
echo "<h2>📊 Statistiques de Sécurité</h2>";
echo "<p><strong>Total des requêtes analysées :</strong> " . $stats['total_requests'] . "</p>";
echo "<p><strong>IPs bloquées :</strong> " . $stats['blocked_ips'] . "</p>";
echo "<p><strong>Activités suspectes :</strong> " . $stats['suspicious_activities'] . "</p>";
echo "<p><strong>Attaques récentes :</strong> " . $stats['recent_attacks'] . "</p>";
echo "</div>";

// Afficher les logs récents
echo "<div class='stat'>";
echo "<h2>📋 Logs de Sécurité Récents</h2>";
echo "<table>";
echo "<tr><th>Timestamp</th><th>Événement</th><th>IP</th><th>Détails</th></tr>";

$logs = readSecurityLogs(20);
foreach ($logs as $log) {
    preg_match('/\[([^\]]+)\]\s*\[([^\]]+)\]\s*IP:([^\s]+)\s*(.*)/', $log, $matches);
    if (count($matches) >= 4) {
        $timestamp = $matches[1];
        $event = $matches[2];
        $ip = $matches[3];
        $details = $matches[4] ?? '';

        $class = 'info';
        if (strpos($event, 'BLOCAGE') !== false || strpos($event, 'ATTAQUE') !== false) {
            $class = 'warning';
        }

        echo "<tr class='$class'>";
        echo "<td>" . htmlspecialchars($timestamp) . "</td>";
        echo "<td>" . htmlspecialchars($event) . "</td>";
        echo "<td>" . htmlspecialchars($ip) . "</td>";
        echo "<td>" . htmlspecialchars($details) . "</td>";
        echo "</tr>";
    }
}
echo "</table>";
echo "</div>";

// Afficher les IPs bloquées
echo "<div class='stat warning'>";
echo "<h2>🚫 IPs Bloquées</h2>";
$blockedFile = 'pdfs/blocked_ips.json';
if (file_exists($blockedFile)) {
    $blocked = json_decode(file_get_contents($blockedFile), true);
    if (!empty($blocked)) {
        echo "<table>";
        echo "<tr><th>IP</th><th>Raison</th><th>Bloquée depuis</th></tr>";
        foreach ($blocked as $ip => $data) {
            $blockedSince = date('Y-m-d H:i:s', $data['timestamp']);
            echo "<tr>";
            echo "<td>" . htmlspecialchars($ip) . "</td>";
            echo "<td>" . htmlspecialchars($data['reason']) . "</td>";
            echo "<td>" . htmlspecialchars($blockedSince) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='success'>✅ Aucune IP bloquée actuellement</p>";
    }
} else {
    echo "<p class='success'>✅ Aucune IP bloquée</p>";
}
echo "</div>";

// Conseils de sécurité
echo "<div class='stat success'>";
echo "<h2>🛡️ Conseils de Sécurité</h2>";
echo "<ul>";
echo "<li>✅ Rate limiting activé (20 req/min, 100 req/heure)</li>";
echo "<li>✅ Protection CSRF et XSS opérationnelle</li>";
echo "<li>✅ Validation stricte des emails et téléphones</li>";
echo "<li>✅ Détection automatique des bots malveillants</li>";
echo "<li>✅ Logging complet de toutes les activités</li>";
echo "<li>✅ Headers de sécurité HTTP configurés</li>";
echo "</ul>";
echo "</div>";

// Bouton d'actualisation
echo "<div style='text-align: center; margin: 20px;'>";
echo "<button onclick='window.location.reload()' style='padding: 10px 20px; background: #2196f3; color: white; border: none; border-radius: 5px; cursor: pointer;'>🔄 Actualiser le monitoring</button>";
echo "</div>";

echo "<p style='text-align: center; color: #666; font-size: 12px;'>";
echo "Monitoring généré le " . date('Y-m-d H:i:s') . " - FRLimousine Security System";
echo "</p>";
?>