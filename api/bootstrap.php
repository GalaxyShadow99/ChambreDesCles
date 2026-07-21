<?php
// api/bootstrap.php - Authentification et Connexion BDD
$env = @parse_ini_file(__DIR__ . '/../.env') ?: [];

$headers = function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [];

// 1. Récupération du jeton (supporte API-KEY ou Authorization: Bearer)
$auth = $_SERVER['HTTP_API_KEY'] ?? $headers['api-key'] ?? '';
if (empty($auth)) {
    $bearer = $_SERVER['HTTP_AUTHORIZATION'] ?? $headers['authorization'] ?? '';
    $auth = str_replace('Bearer ', '', $bearer);
}

$expectedToken = $env['API_SECRET_TOKEN'] ?? 'c5b8df8398de174f8087ea74c2d3080fa9d6bf3076c8c1dbdf625e1fa68fb3a5';

if (empty($auth) || $auth !== $expectedToken) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(["error" => "Jeton invalide"]));
}

try {
    $host = $env['DB_HOST'] ?? 'localhost';
    $port = $env['DB_PORT'] ?? '3306';
    $db   = $env['DB_NAME'] ?? 'chambredescles_db';
    $user = $env['DB_USER'] ?? 'root';
    $pass = $env['DB_PASSWORD'] ?? '';

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(["error" => "Erreur BDD", "message" => $e->getMessage()]));
}
