<?php
// db.php - Connexion BDD PDO et Fonctions Utilitaires

$env = @parse_ini_file(__DIR__ . '/../../.env') ?: [];

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? $env['DB_HOST'] ?? 'error';
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? $env['DB_PORT'] ?? 'error';
$db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? $env['DB_NAME'] ?? 'error';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? $env['DB_USER'] ?? 'error';
$pass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? $env['DB_PASSWORD'] ?? 'error';

if ($host === 'error' || $port === 'error' || $db === 'error' || $user === 'error' || $pass === 'error') {
    echo "<pre>";
    print_r([
        'DB_HOST' => $host,
        'DB_PORT' => $port,
        'DB_NAME' => $db,
        'DB_USER' => $user,
        'DB_PASSWORD' => $pass
    ]);
    echo "</pre>";
    die("Erreur BDD : Fichier .env manquant ou incomplet");
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    if($e->getCode() === 1049) {
        die("Erreur BDD : Base de données '$db' introuvable. Veuillez vérifier le fichier .env.");
    } else if($e->getCode() === 2002) {
        die("Erreur BDD : Impossible de se connecter au serveur MariaDB.");
    }
    die("Erreur BDD : " . $e->getMessage());
}
