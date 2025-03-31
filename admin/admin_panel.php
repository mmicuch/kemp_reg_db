// admin_panel.php
<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf_utils.php';
session_start();

// Kontrola prihlásenia
if (!isset($_SESSION['admin'])) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        // Kontrola prihlasovacích údajov v databáze
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $user['id'];
        } else {
            die("Nesprávne prihlasovacie údaje");
        }
    } else {
        header('Location: admin_login.php');
        exit;
    }
}

// Načítaj registrácie
$query = "
    SELECT ou.id, ou.meno, ou.priezvisko, ou.datum_narodenia, ou.pohlavie, 
           ou.mladez, ou.poznamka, ou.mail, ou.novy, ou.ucastnik,
           u.izba as ubytovanie,
           GROUP_CONCAT(DISTINCT a.nazov SEPARATOR ', ') as aktivity,
           GROUP_CONCAT(DISTINCT al.nazov SEPARATOR ', ') as alergie
    FROM os_udaje ou
    LEFT JOIN os_udaje_ubytovanie ouu ON ou.id = ouu.os_udaje_id
    LEFT JOIN ubytovanie u ON ouu.ubytovanie_id = u.id
    LEFT JOIN os_udaje_aktivity oua ON ou.id = oua.os_udaje_id
    LEFT JOIN aktivity a ON oua.aktivita_id = a.id
    LEFT JOIN os_udaje_alergie ouall ON ou.id = ouall.os_udaje_id
    LEFT JOIN alergie al ON ouall.alergie_id = al.id
    GROUP BY ou.id
";
$participants = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Export do CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=registrations.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array_keys($participants[0]));
    
    foreach ($participants as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// HTML pre admin panel
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel - Registrácie na kemp</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Administrácia registrácií</h1>
        
        <div class="controls">
            <a href="?export=csv" class="button">Exportovať do CSV</a>
            <a href="logout.php" class="button logout">Odhlásiť sa</a>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Meno</th>
                    <th>Priezvisko</th>
                    <th>Dátum narodenia</th>
                    <th>Typ účastníka</th>
                    <th>Mládež</th>
                    <th>Ubytovanie</th>
                    <th>Aktivity</th>
                    <th>Alergie</th>
                    <th>Akcie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participants as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['meno']) ?></td>
                    <td><?= htmlspecialchars($p['priezvisko']) ?></td>
                    <td><?= htmlspecialchars($p['datum_narodenia']) ?></td>
                    <td><?= htmlspecialchars($p['ucastnik']) ?></td>
                    <td><?= htmlspecialchars($p['mladez']) ?></td>
                    <td><?= htmlspecialchars($p['ubytovanie']) ?></td>
                    <td><?= htmlspecialchars($p['aktivity']) ?></td>
                    <td><?= htmlspecialchars($p['alergie']) ?></td>
                    <td>
                        <a href="edit_participant.php?id=<?= $p['id'] ?>" class="button small">Upraviť</a>
                        <a href="delete_participant.php?id=<?= $p['id'] ?>" class="button small delete" onclick="return confirm('Naozaj chcete vymazať tohto účastníka?')">Vymazať</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>