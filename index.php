<?php
// index.php
// Frontend Super Simple en PHP Vanilla SSR - Style KISS (Keep It Simple, Stupid)

// 1. Chargement hyper simple du fichier .env via parse_ini_file()
$env = [];
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
}

// 2. Récupération des clés sans aucun opérateur ternaire complexe
$apiSecret = '';
if (isset($env['API_SECRET_'])) {
    $apiSecret = $env['API_SECRET_'];
}
if (empty($apiSecret) && isset($env['API_SECRET_TOKEN'])) {
    $apiSecret = $env['API_SECRET_TOKEN'];
}

$apiUrl = 'http://localhost:8081/api';
if (isset($env['API_URL'])) {
    $apiUrl = $env['API_URL'];
}

// Onglet actif
$tab = 'chambres';
if (isset($_GET['tab'])) {
    if ($_GET['tab'] === 'clients') {
        $tab = 'clients';
    }
}

// 3. Construction de la requête API propre
$endpoint = rtrim($apiUrl, '/') . '/' . $tab . '.php';

// Création des options de la requête HTTP
$options = [
    'http' => [
        'method' => 'GET',
        'header' => [
            "Authorization: Bearer " . $apiSecret,
            "User-Agent: ChambreDesCles-Frontend/1.0"
        ],
        'ignore_errors' => true // Permet de récupérer le message d'erreur du backend
    ]
];

$context = stream_context_create($options);
$responseJson = @file_get_contents($endpoint, false, $context);

// Récupération simple du code HTTP sans regex
$httpStatus = 500;
if (isset($http_response_header) && isset($http_response_header[0])) {
    $parts = explode(' ', $http_response_header[0]);
    if (isset($parts[1])) {
        $httpStatus = (int)$parts[1];
    }
}

// 4. Analyse de la réponse
$data = [];
$error = null;

if ($httpStatus === 200) {
    $data = json_decode($responseJson, true);
} elseif ($httpStatus === 401) {
    $error = "Accès refusé par l'API (Le jeton secret envoyé ne correspond pas). Réponse brute : " . htmlspecialchars($responseJson);
} elseif ($httpStatus === 404) {
    $error = "Fichier API introuvable à l'adresse : " . htmlspecialchars($endpoint);
} else {
    $error = "Impossible de contacter l'API (Code HTTP " . $httpStatus . "). Assurez-vous que le serveur API est lancé sur le port 8081 et n'utilise pas le même port que le frontend.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Chambre des Clés - Démo Simple</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="app-container">
    <header class="app-header">
      <h1>Chambre des Clés</h1>
      <span class="status-text font-medium">Mode : PHP Vanilla SSR (KISS)</span>
    </header>

    <main class="app-main">
      <div class="tabs-container">
        <a href="?tab=chambres" class="tab-btn <?php echo $tab === 'chambres' ? 'active' : ''; ?>">Chambres</a>
        <a href="?tab=clients" class="tab-btn <?php echo $tab === 'clients' ? 'active' : ''; ?>">Clients</a>
      </div>

      <section class="content-section">
        <h2>Liste des <?php echo ucfirst($tab); ?></h2>

        <?php if ($error !== null): ?>
          <div style="color: #ef4444; padding: 1.25rem; border: 1px solid #ef4444; border-radius: 8px; background: rgba(239, 68, 68, 0.05); line-height: 1.5;">
            <strong>Erreur de l'API :</strong><br>
            <?php echo $error; ?>
          </div>
        <?php else: ?>
          <div class="cards-grid">
            <?php if (empty($data)): ?>
              <p style="color: var(--text-muted);">Aucun enregistrement trouvé en base de données.</p>
            <?php endif; ?>
            
            <?php if ($tab === 'chambres'): ?>
              <?php foreach ($data as $chambre): ?>
                <div class="card">
                  <div class="card-header">
                    <span class="card-title"><?php echo htmlspecialchars($chambre['nom']); ?></span>
                    <span class="badge"><?php echo number_format($chambre['prix_nuit'], 2); ?> € / nuit</span>
                  </div>
                  <div class="card-body">
                    <p>Capacité : <?php echo $chambre['capacite']; ?> personnes</p>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; font-style: italic;">
                      <?php echo htmlspecialchars($chambre['description'] ?? 'Aucune description.'); ?>
                    </p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <?php foreach ($data as $client): ?>
                <div class="card">
                  <div class="card-header">
                    <span class="card-title"><?php echo htmlspecialchars($client['prenom'] . ' ' . $client['nom']); ?></span>
                    <span class="badge">Client #<?php echo $client['id']; ?></span>
                  </div>
                  <div class="card-body">
                    <p>Email : <?php echo htmlspecialchars($client['email'] ?? 'Non renseigné'); ?></p>
                    <p>Téléphone : <?php echo htmlspecialchars($client['telephone'] ?? 'Non renseigné'); ?></p>
                    <?php if (!empty($client['notes'])): ?>
                      <div class="notes-box">
                        <strong>Notes :</strong>
                        <p><?php echo htmlspecialchars($client['notes']); ?></p>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>
</body>
</html>
