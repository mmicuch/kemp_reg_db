<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/..'));
}

// Find .env file
$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile)) {
    die("Environment file not found. Please create a .env file");
}

$dotenv = parse_ini_file($envFile);

$host = $dotenv['DB_HOST'] ?? '';
$db   = $dotenv['DB_NAME'] ?? '';
$user = $dotenv['DB_USER'] ?? '';
$pass = $dotenv['DB_PASS'] ?? '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Could not connect to the database. Please check error log for details.");
}