<?php
require 'config.php';
session_start();

// Kontrola admin prihlásenia
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Chýba ID účastníka");
}

try {
    $pdo->beginTransaction();

    // Vymazanie všetkých závislých záznamov
    $stmt = $pdo->prepare("DELETE FROM os_udaje_aktivity WHERE os_udaje_id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM os_udaje_alergie WHERE os_udaje_id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM os_udaje_ubytovanie WHERE os_udaje_id = ?");
    $stmt->execute([$id]);

    // Vymazanie hlavného záznamu
    $stmt = $pdo->prepare("DELETE FROM os_udaje WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();
    header('Location: admin_panel.php');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Chyba pri mazaní účastníka: " . $e->getMessage());
}