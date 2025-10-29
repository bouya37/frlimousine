<?php
/**
 * security.php - Fonctions de sécurité avancées pour FRLimousine
 * Protection contre DDoS, spam, injections et attaques diverses
 */

// Classe principale de sécurité
class FRLimousineSecurity {

    private $logFile;
    private $maxRequestsPerMinute;
    private $maxRequestsPerHour;
    private $blockedIPs;

    public function __construct($config = []) {
        $this->logFile = 'pdfs/security.log';
        // Utilise la configuration si elle est fournie, sinon valeurs par défaut
        $this->maxRequestsPerMinute = $config['security']['rate_limit_minute'] ?? 20;
        $this->maxRequestsPerHour = $config['security']['rate_limit_hour'] ?? 100;
        $this->blockedIPs = [];
        $this->loadBlockedIPs();
    }

    // Vérification de l'IP et rate limiting
    public function checkRateLimit($ip = null) {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Vérifier si l'IP est bloquée
        if ($this->isIPBlocked($ip)) {
            $this->logSecurityEvent("IP_BLOQUEE", $ip, "Tentative d'accès depuis IP bloquée");
            return false;
        }

        $rateData = $this->getRateLimitData($ip);

        // Nettoyer les anciennes données (plus d'1 heure)
        if (isset($rateData['hourly']) && (time() - $rateData['hourly']['start_time']) > 3600) {
            unset($rateData['hourly']);
        }

        // Vérification par minute
        if (isset($rateData['minute'])) {
            if ((time() - $rateData['minute']['start_time']) < 60) {
                if ($rateData['minute']['count'] >= $this->maxRequestsPerMinute) {
                    $this->blockIP($ip, "Dépassement limite/minute");
                    return false;
                }
                $rateData['minute']['count']++;
            } else {
                $rateData['minute'] = ['start_time' => time(), 'count' => 1];
            }
        } else {
            $rateData['minute'] = ['start_time' => time(), 'count' => 1];
        }

        // Vérification par heure
        if (isset($rateData['hourly'])) {
            if ($rateData['hourly']['count'] >= $this->maxRequestsPerHour) {
                $this->blockIP($ip, "Dépassement limite/heure");
                return false;
            }
            $rateData['hourly']['count']++;
        } else {
            $rateData['hourly'] = ['start_time' => time(), 'count' => 1];
        }

        $this->saveRateLimitData($ip, $rateData);
        return true;
    }

    // Vérification des données d'entrée
    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }

        $sanitized = trim($data);
        $sanitized = stripslashes($sanitized);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');

        return $sanitized;
    }

    // Détection de bots malveillants
    public function detectBot() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $suspiciousPatterns = [
            '/bot/i', '/crawler/i', '/spider/i', '/scraper/i',
            '/libwww-perl/i', '/wget/i', '/curl/i',
            '/sqlmap/i', '/nmap/i', '/nikto/i', '/w3af/i',
            '/nessus/i', '/openvas/i', '/qualys/i'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    // Validation d'email avancée
    public function validateEmail($email) {
        // Vérification format de base
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Vérification DNS du domaine
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            return false;
        }

        // Liste noire d'emails temporaires (à étendre)
        $tempDomains = ['10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
        $emailDomain = strtolower(substr(strrchr($email, "@"), 1));

        if (in_array($emailDomain, $tempDomains)) {
            return false;
        }

        return true;
    }

    // Protection contre les injections XSS
    public function preventXSS($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Détection d'attaques par pattern
    public function detectAttack($input) {
        $attackPatterns = [
            // Injections SQL
            '/(\%3D)|(=)[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/i',
            '/\w*((\%27)|(\'))((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i',
            '/((\%27)|(\'))union/i',

            // XSS patterns
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>.*?<\/iframe>/is',

            // Path traversal
            '/\.\.\//',
            '/\.\.\\\/',
            '/\.\.\/\.\.\//',

            // Command injection
            '/;\s*(cat|ls|dir|type|echo|wget|curl)/i',
            '/\|\s*(cat|ls|dir|type|echo|wget|curl)/i'
        ];

        foreach ($attackPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    // Logging des événements de sécurité
    private function logSecurityEvent($event, $ip, $details = '') {
        $timestamp = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $logEntry = "[$timestamp] [$event] IP:$ip UA:$userAgent $details\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    // Gestion des IPs bloquées
    private function loadBlockedIPs() {
        $blockedFile = 'pdfs/blocked_ips.json';
        if (file_exists($blockedFile)) {
            $data = json_decode(file_get_contents($blockedFile), true);
            $this->blockedIPs = $data ?? [];
        }
    }

    private function isIPBlocked($ip) {
        if (isset($this->blockedIPs[$ip])) {
            if (time() - $this->blockedIPs[$ip]['timestamp'] > 3600) { // Débloquer après 1h
                unset($this->blockedIPs[$ip]);
                $this->saveBlockedIPs();
                return false;
            }
            return true;
        }
        return false;
    }

    private function blockIP($ip, $reason) {
        $this->blockedIPs[$ip] = [
            'timestamp' => time(),
            'reason' => $reason
        ];
        $this->saveBlockedIPs();
        $this->logSecurityEvent("IP_BLOQUEE", $ip, "Raison: $reason");
    }

    private function saveBlockedIPs() {
        $blockedFile = 'pdfs/blocked_ips.json';
        file_put_contents($blockedFile, json_encode($this->blockedIPs));
    }

    // Gestion des données de rate limiting
    private function getRateLimitData($ip) {
        $file = 'pdfs/ratelimit_' . md5($ip) . '.json';
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true) ?? [];
        }
        return [];
    }

    private function saveRateLimitData($ip, $data) {
        $file = 'pdfs/ratelimit_' . md5($ip) . '.json';
        file_put_contents($file, json_encode($data));
    }

    // Nettoyage automatique des anciens fichiers de rate limiting
    public function cleanupOldRateLimitFiles() {
        $files = glob('pdfs/ratelimit_*.json');
        $now = time();

        foreach ($files as $file) {
            if (($now - filemtime($file)) > 7200) { // 2 heures
                unlink($file);
            }
        }
    }
}

// Fonction d'initialisation de la sécurité
function initSecurity() {
    global $config; // Assure que la config est accessible
    $security = new FRLimousineSecurity($config ?? []);

    // Nettoyer les anciens fichiers
    $security->cleanupOldRateLimitFiles();

    return $security;
}
?>