<?php
$env = @parse_ini_file(__DIR__ . '/../.env') ?: [];

$apiUrl = $env['API_URL'] ?? 'http://127.0.0.1:8081/api';
$apiSecret = $env['API_SECRET_TOKEN'] ?? 'c5b8df8398de174f8087ea74c2d3080fa9d6bf3076c8c1dbdf625e1fa68fb3a5';

echo "API URL: $apiUrl/clients.php\n";
echo "API Secret: $apiSecret\n";

$data = [
    'nom'    => 'Alice',
    'prenom' => 'Bob',
    'avis'   => 'Test user'
];

$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "API-KEY: $apiSecret\r\nContent-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($data),
        'ignore_errors' => true
    ]
]);

$response = file_get_contents("$apiUrl/clients.php", false, $context);
echo "Réponse API: " . $response . "\n";
