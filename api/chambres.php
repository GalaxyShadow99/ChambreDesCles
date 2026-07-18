<?php
// api/chambres.php
// API - Récupération de toutes les chambres depuis la base de données

// 1. Chargement manuel et simple du fichier .env via parse_ini_file() (dossier parent)
$env = [];
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
}

// 2. Récupération de la clé attendue
$apiSecret = '';
if (isset($env['API_SECRET_'])) {
    $apiSecret = $env['API_SECRET_'];
}
if (empty($apiSecret) && isset($env['API_SECRET_TOKEN'])) {
    $apiSecret = $env['API_SECRET_TOKEN'];
}

// 3. Récupération du header Authorization
$authHeader = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    }
}

// Extraction du token (retire "Bearer ")
$receivedToken = '';
if (strpos($authHeader, 'Bearer ') === 0) {
    $receivedToken = substr($authHeader, 7);
}

// Vérification de sécurité avec réponse de debug claire en cas d'erreur
if ($receivedToken !== $apiSecret) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "error" => "Jeton invalide",
        "debug" => [
            "jeton_recu" => $receivedToken,
            "jeton_attendu" => $apiSecret,
            "header_complet" => $authHeader
        ]
    ]);
    exit;
}

// 4. Connexion PDO à la base de données
$host = 'localhost';
if (isset($env['DB_HOST'])) {
    $host = $env['DB_HOST'];
}
$port = '3306';
if (isset($env['DB_PORT'])) {
    $port = $env['DB_PORT'];
}
$dbName = 'chambredescles_db';
if (isset($env['DB_NAME'])) {
    $dbName = $env['DB_NAME'];
}
$user = 'root';
if (isset($env['DB_USER'])) {
    $user = $env['DB_USER'];
}
$password = '';
if (isset($env['DB_PASSWORD'])) {
    $password = $env['DB_PASSWORD'];
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Exécution du SELECT * sur la table 'chambres'
    $stmt = $pdo->query("SELECT * FROM chambres");
    $chambres = $stmt->fetchAll();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($chambres, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "error" => "Erreur de base de données",
        "message" => $e->getMessage()
    ]);
}
