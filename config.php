<?php
if (!file_exists(__DIR__ . '/.env')) {
    die("Environment file not found. Please create a .env file.");
}

$dotenv = parse_ini_file(__DIR__ . '/.env');

$host = $dotenv['DB_HOST'] ?? '';
$db   = $dotenv['DB_NAME'] ?? '';
$user = $dotenv['DB_USER'] ?? '';
$pass = $dotenv['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}
?>
