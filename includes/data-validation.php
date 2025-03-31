<?php
/**
 * Validácia dát
 */

// Validácia základných údajov
function validateBasicData($data) {
    $errors = [];
    
    // Validácia mena a priezviska
    if (empty($data['meno']) || strlen($data['meno']) > 50) {
        $errors[] = "Meno je povinné a nesmie byť dlhšie ako 50 znakov.";
    }
    
    if (empty($data['priezvisko']) || strlen($data['priezvisko']) > 50) {
        $errors[] = "Priezvisko je povinné a nesmie byť dlhšie ako 50 znakov.";
    }
    
    // Validácia dátumu narodenia
    if (empty($data['datum_narodenia'])) {
        $errors[] = "Dátum narodenia je povinný.";
    } else {
        $birthDate = new DateTime($data['datum_narodenia']);
        $now = new DateTime();
        $age = $now->diff($birthDate)->y;
        
        // Vek v aktuálnom roku
        $currentYear = (int)$now->format('Y');
        $birthYear = (int)$birthDate->format('Y');
        $ageThisYear = $currentYear - $birthYear;
        
        if ($ageThisYear < 14 || $ageThisYear > 26) {
            $errors[] = "Vek účastníka musí byť medzi 14 a 26 rokov v tomto roku.";
        }
    }
    
    // Validácia pohlavia
    if (empty($data['pohlavie']) || !in_array($data['pohlavie'], ['M', 'F'])) {
        $errors[] = "Pohlavie je povinné a musí byť muž (M) alebo žena (F).";
    }
    
    // Validácia ubytovania
    if (empty($data['ubytovanie']) || !is_numeric($data['ubytovanie'])) {
        $errors[] = "Ubytovanie je povinné.";
    }
    
    // Validácia mládeže
    if (empty($data['mladez'])) {
        $errors[] = "Mládež je povinná.";
    }
    
    // Validácia emailu
    if (empty($data['mail']) || !filter_var($data['mail'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email je povinný a musí byť platný.";
    }
    
    // Validácia GDPR
    if (empty($data['gdpr'])) {
        $errors[] = "Je potrebné súhlasiť so spracovaním osobných údajov.";
    }
    
    // Validácia aktivít
    if (empty($data['aktivity_streda']) || !is_numeric($data['aktivity_streda'])) {
        $errors[] = "Aktivita na stredu je povinná.";
    }
    
    if (empty($data['aktivity_stvrtok']) || !is_numeric($data['aktivity_stvrtok'])) {
        $errors[] = "Aktivita na štvrtok je povinná.";
    }
    
    if (empty($data['aktivity_piatok']) || !is_numeric($data['aktivity_piatok'])) {
        $errors[] = "Aktivita na piatok je povinná.";
    }
    
    // Validácia alergií
    if (isset($data['alergie']) && is_array($data['alergie']) && in_array('other', $data['alergie']) && empty($data['alergie_other'])) {
        $errors[] = "Ak ste vybrali 'Iné' pri alergiách, musíte špecifikovať aké.";
    }
    
    return $errors;
}

// Sanitizácia dát
function sanitizeData($data) {
    $sanitized = [];
    
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
        } elseif (is_array($value)) {
            $sanitized[$key] = array_map(function($item) {
                return is_string($item) ? trim(htmlspecialchars($item, ENT_QUOTES, 'UTF-8')) : $item;
            }, $value);
        } else {
            $sanitized[$key] = $value;
        }
    }
    
    return $sanitized;
}
