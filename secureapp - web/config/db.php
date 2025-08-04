<?php
// Cargar variables del archivo .env
$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    die("❌ Archivo .env no encontrado.");
}

$env = parse_ini_file($envPath);

$host = $env['DB_HOST'] ?? 'localhost';
$db   = $env['DB_NAME'] ?? 'secure_app';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}
?>
