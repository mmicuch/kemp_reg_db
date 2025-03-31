<?php
require 'config.php';
require_once 'csrf_utils.php';
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

// Načítanie údajov o účastníkovi
$stmt = $pdo->prepare("
    SELECT ou.*, 
           u.id as ubytovanie_id,
           GROUP_CONCAT(DISTINCT oua.aktivita_id) as aktivity,
           GROUP_CONCAT(DISTINCT oual.alergie_id) as alergie
    FROM os_udaje ou
    LEFT JOIN os_udaje_ubytovanie ouu ON ou.id = ouu.os_udaje_id
    LEFT JOIN ubytovanie u ON ouu.ubytovanie_id = u.id
    LEFT JOIN os_udaje_aktivity oua ON ou.id = oua.os_udaje_id
    LEFT JOIN os_udaje_alergie oual ON ou.id = oual.os_udaje_id
    WHERE ou.id = ?
    GROUP BY ou.id
");
$stmt->execute([$id]);
$participant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$participant) {
    die("Účastník nebol nájdený");
}

// Spracovanie POST požiadavky
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tu pridať validáciu a spracovanie formulára podobne ako v process_registration.php
    // ...

    header('Location: admin_panel.php');
    exit;
}

// Načítanie potrebných údajov pre formulár
$queryYouth = $pdo->query("SELECT nazov FROM mladez")->fetchAll(PDO::FETCH_ASSOC);
$queryAllergies = $pdo->query("SELECT id, nazov FROM alergie")->fetchAll(PDO::FETCH_ASSOC);
$queryAccommodation = $pdo->query("SELECT id, izba, typ FROM ubytovanie")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upraviť účastníka</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Upraviť účastníka</h1>
        <form method="POST">
            <?= csrfField() ?>
            <!-- Tu pridať všetky polia formulára s predvyplnenými hodnotami -->
            <!-- ... -->
            <button type="submit">Uložiť zmeny</button>
            <a href="admin_panel.php" class="button">Späť</a>
        </form>
    </div>
</body>
</html>