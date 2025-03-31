<?php
require 'config.php';
require_once 'csrf_utils.php';
session_start();

// If already logged in, redirect to admin panel
if (isset($_SESSION['admin'])) {
    header('Location: admin_panel.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin'] = true;
        $_SESSION['admin_username'] = $user['username'];
        header('Location: admin_panel.php');
        exit;
    } else {
        $error = "Nesprávne prihlasovacie údaje";
    }
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>

</head>
<body>
    <div class="login-container">
        <h2>Admin Prihlásenie</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label for="username">Používateľské meno:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Heslo:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Prihlásiť</button>
        </form>
    </div>
</body>
</html>