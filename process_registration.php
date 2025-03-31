<?php
require 'config.php';
require_once 'csrf_utils.php';
require_once 'validation.php';
require_once 'rate_limiting.php';

// Kontrola rate limitu
$rateLimiter = new RateLimiter($pdo, 'registration_submit', 5, 3600); // 5 registrácií za hodinu
if (!$rateLimiter->check()) {
    $remainingTime = $rateLimiter->getRemainingTime();
    $minutes = ceil($remainingTime / 60);
    die("Prekročili ste povolený počet registrácií. Skúste to prosím znova o $minutes minút.");
}

// Získanie typu registrácie z URL, default "taborujuci"
$linkType = isset($_GET['type']) ? $_GET['type'] : 'taborujuci';
$allowedTypes = ['taborujuci', 'veduci', 'host'];
if (!in_array($linkType, $allowedTypes)) {
    $linkType = 'taborujuci';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kontrola CSRF tokenu
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        die("Neplatný alebo chýbajúci CSRF token. Skúste obnoviť stránku a skúste to znova.");
    }
    
    // Sanitizácia a získanie údajov z formulára
    $data = sanitizeData($_POST);
    
    // Validácia údajov
    $errors = validateBasicData($data);
    
    if (!empty($errors)) {
        echo "<h2>Chyby pri validácii:</h2>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
        echo "<p><a href='javascript:history.back()'>Vrátiť sa späť</a></p>";
        exit;
    }
    
    // Načítanie údajov z formulára
    $meno            = $data['meno'] ?? '';
    $priezvisko      = $data['priezvisko'] ?? '';
    $datum_narodenia = $data['datum_narodenia'] ?? '';
    $pohlavie        = $data['pohlavie'] ?? '';
    $ubytovanie      = $data['ubytovanie'] ?? '';
    $mladez          = $data['mladez'] ?? '';
    $poznamka        = $data['poznamka'] ?? '';
    $mail            = $data['mail'] ?? '';
    $novy            = isset($data['novy']) ? 1 : 0;
    $gdpr            = isset($data['gdpr']) ? 1 : 0;
    $aktivity_streda  = $data['aktivity_streda'] ?? '';
    $aktivity_stvrtok = $data['aktivity_stvrtok'] ?? '';
    $aktivity_piatok  = $data['aktivity_piatok'] ?? '';
    $alergie          = $data['alergie'] ?? [];
    $alergie_other    = $data['alergie_other'] ?? '';

    try {
        // Začiatok transakcie
        $pdo->beginTransaction();
        
        // Vloženie údajov do tabuľky os_udaje
        $stmt = $pdo->prepare("
            INSERT INTO os_udaje 
            (meno, priezvisko, datum_narodenia, pohlavie, mladez, poznamka, mail, novy, GDPR, ucastnik)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$meno, $priezvisko, $datum_narodenia, $pohlavie, $mladez, $poznamka, $mail, $novy, $gdpr, $linkType]);
        $os_udaje_id = $pdo->lastInsertId();

        // Kontrola, či ubytovanie ešte má voľné miesta
        $stmt = $pdo->prepare("
            SELECT kapacita - (
                SELECT COUNT(*) FROM os_udaje_ubytovanie 
                WHERE ubytovanie_id = ?
            ) AS volne_miesta
            FROM ubytovanie
            WHERE id = ?
        ");
        $stmt->execute([$ubytovanie, $ubytovanie]);
        $capacity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($capacity['volne_miesta'] <= 0) {
            throw new Exception("Vybrané ubytovanie je už plné. Prosím, vyberte iné ubytovanie.");
        }

        // Uloženie údajov o ubytovaní
        $stmt = $pdo->prepare("
            INSERT INTO os_udaje_ubytovanie (os_udaje_id, ubytovanie_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$os_udaje_id, $ubytovanie]);

        // Uloženie aktivít
        $stmt = $pdo->prepare("
            INSERT INTO os_udaje_aktivity (os_udaje_id, aktivita_id)
            VALUES (?, ?)
        ");

        foreach ([$aktivity_streda, $aktivity_stvrtok, $aktivity_piatok] as $aktivita) {
            if ($aktivita) {
                $stmt->execute([$os_udaje_id, $aktivita]);
            }
        }

        // Uloženie alergií
        if (!in_array('none', $alergie)) {
            $stmt = $pdo->prepare("
                INSERT INTO os_udaje_alergie (os_udaje_id, alergie_id)
                VALUES (?, ?)
            ");

            foreach ($alergie as $alergia) {
                if ($alergia === 'other') {
                    if (!empty($alergie_other)) {
                        // Vytvorenie novej alergie
                        $stmtNewAllergy = $pdo->prepare("
                            INSERT INTO alergie (nazov)
                            VALUES (?)
                            ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)
                        ");
                        $stmtNewAllergy->execute([$alergie_other]);
                        $alergiaId = $pdo->lastInsertId();
                        $stmt->execute([$os_udaje_id, $alergiaId]);
                    }
                } else {
                    $stmt->execute([$os_udaje_id, $alergia]);
                }
            }
        }

        // Commit transakcie
        $pdo->commit();

        // Poslanie potvrdzovacieho emailu
        $userData = [
            'meno' => $meno,
            'priezvisko' => $priezvisko,
            'mail' => $mail,
            'mladez' => $mladez,
            'ucastnik' => $linkType
        ];
        sendConfirmationEmail($userData);

        // Presmerovanie na thank-you stránku
        header("Location: thank-you.html");
        exit;

    } catch (Exception $e) {
        // Rollback v prípade chyby
        $pdo->rollBack();
        die("Chyba pri spracovaní registrácie: " . $e->getMessage());
    }
}
?>