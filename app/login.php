<?php
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'));
    session_start([
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_secure' => $isSecure,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_only_cookies' => true
    ]);
}

$env = @parse_ini_file(__DIR__ . '/../.env') ?: [];

$adminPassword = $_ENV['ADMIN_PASSWORD'] ?? getenv('ADMIN_PASSWORD') ?? $env['ADMIN_PASSWORD'] ?? 'erreur';
$adminUsername = $_ENV['ADMIN_LOGIN'] ?? getenv('ADMIN_LOGIN') ?? $env['ADMIN_LOGIN'] ?? 'erreur';

if($adminPassword === 'erreur' || $adminUsername === 'erreur') {
    die("Erreur : Les variables d'environnement ADMIN_LOGIN et ADMIN_PASSWORD doivent être définies dans le fichier .env.");
}
// Si déjà connecté, redirection immédiate vers index.php
if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = null;
$AlertConnexion = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if ($password === $adminPassword) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = "Mot de passe incorrect.";
    }
}

if (empty($_SESSION['logged_in'])) {
    $AlertConnexion = true;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Chambre des Clés</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            background-color: #f6f8fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .login-card {
            background: #ffffff;
            border: 1px solid #d0d7de;
            border-radius: 6px;
            padding: 32px;
            width: 100%;
            max-width: 340px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2 class="h5 mb-4 text-center fw-bold">Chambre des Clés</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 small border-0 bg-danger-subtle text-danger-emphasis mb-3">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($AlertConnexion): ?>
        <div class="alert alert-info py-2 px-3 small border-0 bg-info-subtle text-info-emphasis mb-3">
            Vous devez vous connecter pour accéder à l'application.
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small text-secondary">Mot de passe d'administration</label>
            <input type="password" name="password" id="password" class="form-control form-control-sm" required autofocus>
        </div>
        <button type="submit" class="btn btn-dark btn-sm w-100 fw-medium">Se connecter</button>
    </form>
</div>

</body>
</html>
