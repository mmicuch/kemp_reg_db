<?php
require 'config.php';

// Získanie typu registrácie z URL, default "taborujuci"
$linkType = isset($_GET['type']) ? $_GET['type'] : 'taborujuci';
$allowedTypes = ['taborujuci', 'veduci', 'host'];
if (!in_array($linkType, $allowedTypes)) {
    $linkType = 'taborujuci';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Načítanie údajov z formulára
    $meno            = $_POST['meno'] ?? '';
    $priezvisko      = $_POST['priezvisko'] ?? '';
    $datum_narodenia = $_POST['datum_narodenia'] ?? '';
    $pohlavie       = $_POST['pohlavie'] ?? '';
    $ubytovanie     = $_POST['ubytovanie'] ?? '';
    $mladez         = $_POST['mladez'] ?? '';
    $poznamka       = $_POST['poznamka'] ?? '';
    $mail           = $_POST['mail'] ?? '';
    $novy           = isset($_POST['novy']) ? 1 : 0;
    $gdpr           = isset($_POST['gdpr']) ? 1 : 0;
    $aktivity_streda  = $_POST['aktivity_streda'] ?? '';
    $aktivity_stvrtok = $_POST['aktivity_stvrtok'] ?? '';
    $aktivity_piatok  = $_POST['aktivity_piatok'] ?? '';
    $alergie         = $_POST['alergie'] ?? [];
    $alergie_other   = $_POST['alergie_other'] ?? '';

    if (!$meno || !$priezvisko || !$datum_narodenia || !$pohlavie || !$ubytovanie || !$mladez || !$mail || !$gdpr) {
        die("Niektoré povinné údaje chýbajú.");
    }

    try {
        // Vloženie údajov do tabuľky os_udaje
        $stmt = $pdo->prepare("
            INSERT INTO os_udaje 
            (meno, priezvisko, datum_narodenia, pohlavie, mladez, poznamka, mail, novy, GDPR, ucastnik)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$meno, $priezvisko, $datum_narodenia, $pohlavie, $mladez, $poznamka, $mail, $novy, $gdpr, $linkType]);
        $os_udaje_id = $pdo->lastInsertId();

        // Uloženie údajov o ubytovaní
        $stmt = $pdo->prepare("
            INSERT INTO os_udaje_ubytovanie (os_udaje_id, ubytovanie_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$os_udaje_id, $ubytovanie]);

        // Uloženie aktivít
        $activities = [$aktivity_streda, $aktivity_stvrtok, $aktivity_piatok];
        foreach ($activities as $activity) {
            if ($activity) {
                $stmt = $pdo->prepare("INSERT INTO os_udaje_aktivity (os_udaje_id, aktivita_id) VALUES (?, ?)");
                $stmt->execute([$os_udaje_id, $activity]);
            }
        }

        // Uloženie alergií
        if (in_array('none', $alergie)) {
            // Ak užívateľ zvolil "Žiadne", ignorujeme ostatné voľby
        } else {
            foreach ($alergie as $allergy) {
                if ($allergy === 'other') {
                    if (!empty($alergie_other)) {
                        // Najprv skontrolujeme, či takáto alergia už neexistuje
                        $stmt = $pdo->prepare("SELECT id FROM alergie WHERE nazov = ?");
                        $stmt->execute([$alergie_other]);
                        $existingAllergy = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($existingAllergy) {
                            // Ak áno, použijeme jej ID
                            $stmt = $pdo->prepare("INSERT INTO os_udaje_alergie (os_udaje_id, alergie_id) VALUES (?, ?)");
                            $stmt->execute([$os_udaje_id, $existingAllergy['id']]);
                        } else {
                            // Ak nie, vytvoríme novú alergiu
                            $stmt = $pdo->prepare("INSERT INTO alergie (nazov) VALUES (?)");
                            $stmt->execute([$alergie_other]);
                            $newAllergyId = $pdo->lastInsertId();
                            
                            $stmt = $pdo->prepare("INSERT INTO os_udaje_alergie (os_udaje_id, alergie_id) VALUES (?, ?)");
                            $stmt->execute([$os_udaje_id, $newAllergyId]);
                        }
                    }
                } else {
                    $stmt = $pdo->prepare("INSERT INTO os_udaje_alergie (os_udaje_id, alergie_id) VALUES (?, ?)");
                    $stmt->execute([$os_udaje_id, $allergy]);
                }
            }
        }

        header("Location: /thank-you.html");
        exit;
    } catch (PDOException $e) {
        die("Chyba pri ukladaní: " . $e->getMessage());
    }
} else {
    die("Neplatná metóda požiadavky.");
}

// Pridaj do process_registration.php po úspešnej registrácii

function sendConfirmationEmail($user) {
    $to = $user['mail'];
    $subject = 'Potvrdenie registrácie na kemp';
    
    $message = "
    <html>
    <head>
      <title>Potvrdenie registrácie na kemp</title>
    </head>
    <body>
      <h1>Ďakujeme za registráciu!</h1>
      <p>Vážený/á {$user['meno']} {$user['priezvisko']},</p>
      <p>Vaša registrácia na náš kemp bola úspešne prijatá.</p>
      
      <h2>Detaily vašej registrácie:</h2>
      <ul>
        <li><strong>Meno:</strong> {$user['meno']} {$user['priezvisko']}</li>
        <li><strong>Mládež:</strong> {$user['mladez']}</li>
        <li><strong>Typ účastníka:</strong> {$user['ucastnik']}</li>
      </ul>
      
      <p>Ďalšie informácie o kempe vám budú zaslané bližšie k termínu konania.</p>
      <p>S pozdravom,<br>Organizačný tím kempu</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: kemp@example.com' . "\r\n";
    
    mail($to, $subject, $message, $headers);
}

// Získaj kompletné údaje o užívateľovi
$stmt = $pdo->prepare("SELECT * FROM os_udaje WHERE id = ?");
$stmt->execute([$os_udaje_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Pošli potvrdzovací email
sendConfirmationEmail($userData);