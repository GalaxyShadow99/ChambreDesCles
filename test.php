<?php
// test.php - Test d'appel d'API avec en-tête API-KEY

$env = @parse_ini_file(__DIR__ . '/.env') ?: [];
$apiSecret = $env['API_SECRET_TOKEN'] ?? 'c5b8df8398de174f8087ea74c2d3080fa9d6bf3076c8c1dbdf625e1fa68fb3a5';
$apiUrl = $env['API_URL'] ?? 'http://localhost:8081/api';

// 1. Données à envoyer en POST
$data = [
    'nom'    => 'Dupont',
    'prenom' => 'Jean',
    'avis'   => 'Test réussi !'
];

// 2. Contexte HTTP avec l'en-tête API-KEY
$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "API-KEY: $apiSecret\r\nContent-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($data),
        'ignore_errors' => true
    ]
]);

// 3. Exécution de la requête vers l'API
$response = file_get_contents("$apiUrl/clients.php", false, $context);

echo "Réponse de l'API :\n" . $response . "\n";
