<?php
/**
 * Rate limiting
 */

require_once 'config.php';

class RateLimiter {
    private $pdo;
    private $ip;
    private $action;
    private $limit;
    private $timeWindow;
    
    /**
     * Konštruktor
     * 
     * @param PDO $pdo PDO inštancia pre databázu
     * @param string $action Akcia, ktorú chceme limitovať (napr. "registration")
     * @param int $limit Počet povolených pokusov
     * @param int $timeWindow Časové okno v sekundách
     */
    public function __construct($pdo, $action, $limit = 5, $timeWindow = 3600) {
        $this->pdo = $pdo;
        $this->ip = $this->getIpAddress();
        $this->action = $action;
        $this->limit = $limit;
        $this->timeWindow = $timeWindow;
        
        // Vytvorenie tabuľky rate_limits, ak neexistuje
        $this->createTableIfNotExists();
    }
    
    /**
     * Vytvorenie tabuľky rate_limits, ak neexistuje
     */
    private function createTableIfNotExists() {
        $sql = "
        CREATE TABLE IF NOT EXISTS rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            action VARCHAR(50) NOT NULL,
            count INT NOT NULL DEFAULT 1,
            last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_action (ip, action)
        )
        ";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            // Ignoruj chybu, ak tabuľka už existuje
        }
    }
    
    /**
     * Získanie IP adresy
     */
    private function getIpAddress() {
        // CloudFlare
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            return $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        
        // Proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        // Normálna IP
        return $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * Zaznamenanie pokusu
     */
    private function logAttempt() {
        $sql = "
        INSERT INTO rate_limits (ip, action, count, last_attempt)
        VALUES (:ip, :action, 1, NOW())
        ON DUPLICATE KEY UPDATE 
            count = IF(last_attempt < DATE_SUB(NOW(), INTERVAL :timeWindow SECOND), 1, count + 1),
            last_attempt = NOW()
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':ip', $this->ip, PDO::PARAM_STR);
        $stmt->bindParam(':action', $this->action, PDO::PARAM_STR);
        $stmt->bindParam(':timeWindow', $this->timeWindow, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    /**
     * Kontrola, či užívateľ neprekročil limit
     */
    private function checkLimit() {
        $sql = "
        SELECT count, last_attempt
        FROM rate_limits
        WHERE ip = :ip AND action = :action
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':ip', $this->ip, PDO::PARAM_STR);
        $stmt->bindParam(':action', $this->action, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return true; // Žiadny záznam, limit nebol prekročený
        }
        
        $lastAttempt = new DateTime($result['last_attempt']);
        $now = new DateTime();
        $interval = $now->getTimestamp() - $lastAttempt->getTimestamp();
        
        // Ak uplynulo viac času ako je časové okno, reset počítadla
        if ($interval > $this->timeWindow) {
            return true;
        }
        
        // Kontrola, či neprekročil limit
        return $result['count'] < $this->limit;
    }
    
    /**
     * Kontrola rate limitingu
     * 
     * @return bool true, ak limit nebol prekročený, inak false
     */
    public function check() {
        $canProceed = $this->checkLimit();
        
        if ($canProceed) {
            $this->logAttempt();
            return true;
        }
        
        return false;
    }
    
    /**
     * Získanie zostávajúceho času do resetu limitu
     * 
     * @return int Zostávajúci čas v sekundách
     */
    public function getRemainingTime() {
        $sql = "
        SELECT last_attempt
        FROM rate_limits
        WHERE ip = :ip AND action = :action
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':ip', $this->ip, PDO::PARAM_STR);
        $stmt->bindParam(':action', $this->action, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return 0;
        }
        
        $lastAttempt = new DateTime($result['last_attempt']);
        $resetTime = clone $lastAttempt;
        $resetTime->modify("+{$this->timeWindow} seconds");
        
        $now = new DateTime();
        $remaining = $resetTime->getTimestamp() - $now->getTimestamp();
        
        return max(0, $remaining);
    }
}
