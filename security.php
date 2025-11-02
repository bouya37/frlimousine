<?php
class FRLimousineSecurity {

    private $logFile;
    private $maxRequestsPerMinute;
    private $maxRequestsPerHour;
    private $blockedIPs;

    public function __construct($config = []) {
        $this->logFile = 'pdfs/security.log';
        $this->maxRequestsPerMinute = $config['security']['rate_limit_minute'] ?? 20;
        $this->maxRequestsPerHour = $config['security']['rate_limit_hour'] ?? 100;
        $this->blockedIPs = [];
        $this->loadBlockedIPs();
    }
    public function checkRateLimit($ip = null) {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if ($this->isIPBlocked($ip)) {
            $this->logSecurityEvent("IP_BLOQUEE", $ip, "Tentative d'accès depuis IP bloquée");
            return false;
        }

        $rateData = $this->getRateLimitData($ip);

        if (isset($rateData['hourly']) && (time() - $rateData['hourly']['start_time']) > 3600) {
            unset($rateData['hourly']);
        }

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

    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }

        $sanitized = trim($data);
        $sanitized = stripslashes($sanitized);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');

        return $sanitized;
    }

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

    public function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            return false;
        }

        $tempDomains = ['10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
        $emailDomain = strtolower(substr(strrchr($email, "@"), 1));

        if (in_array($emailDomain, $tempDomains)) {
            return false;
        }

        return true;
    }

    public function preventXSS($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function detectAttack($input) {
        $attackPatterns = [
            '/(\%3D)|(=)[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/i',
            '/\w*((\%27)|(\'))((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i',
            '/((\%27)|(\'))union/i',
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/\.\.\//',
            '/\.\.\\\/',
            '/\.\.\/\.\.\//',
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

    private function logSecurityEvent($event, $ip, $details = '') {
        $timestamp = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $logEntry = "[$timestamp] [$event] IP:$ip UA:$userAgent $details\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

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

function initSecurity() {
    global $config;
    $security = new FRLimousineSecurity($config ?? []);
    $security->cleanupOldRateLimitFiles();
    return $security;
}
?>