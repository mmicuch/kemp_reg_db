<?php
/**
 * CSRF ochrana - utility functions
 */

// Inicializácia session ak ešte nie je inicializovaná
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Generovanie CSRF tokenu
function generateCsrfToken() {
    initSession();
    
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    
    // Vyčistenie starých tokenov (starších ako 2 hodiny)
    foreach ($_SESSION['csrf_tokens'] as $key => $tokenData) {
        if ($tokenData['time'] < time() - 7200) {
            unset($_SESSION['csrf_tokens'][$key]);
        }
    }
    
    // Vygeneruj nový token
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$token] = [
        'time' => time(),
        'used' => false
    ];
    
    return $token;
}

// Validácia CSRF tokenu
function validateCsrfToken($token) {
    initSession();
    
    if (
        !isset($_SESSION['csrf_tokens']) || 
        !isset($_SESSION['csrf_tokens'][$token]) ||
        $_SESSION['csrf_tokens'][$token]['used'] === true
    ) {
        return false;
    }
    
    // Označ token ako použitý (jednorazové použitie)
    $_SESSION['csrf_tokens'][$token]['used'] = true;
    
    return true;
}

// Vrátenie CSRF input field
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}
