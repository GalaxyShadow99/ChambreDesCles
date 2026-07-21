<?php
$env = @parse_ini_file(__DIR__ . '/.env') ?: [];
$apiSecret = $env['API_SECRET_TOKEN'] ?? '';
$apiUrl = $env['API_URL'] ?? 'ENV_ERROR';

$tab = ($_GET['tab'] ?? '') === 'clients' ? 'clients' : 'reservations';

$context = stream_context_create([
    'http' => [
        'header' => "API-KEY: $apiSecret",
        'ignore_errors' => true
    ]
]);

if($apiUrl === 'ENV_ERROR') {
    die("Erreur : impossible de lire le fichier .env");
}

$json = @file_get_contents("$apiUrl/$tab.php", false, $context);
$data = json_decode($json, true) ?: [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chambre des Clés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h1 class="mb-4">Chambre des Clés</h1>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'reservations' ? 'active' : '' ?>" href="?tab=reservations">Réservations</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'clients' ? 'active' : '' ?>" href="?tab=clients">Clients</a>
        </li>
    </ul>

    <?php if (empty($data)): ?>
        <div class="alert alert-info">Aucun enregistrement trouvé.</div>
    <?php else: ?>
        <?php if ($tab === 'reservations'): ?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Prix</th>
                        <th>Plateforme</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $res): ?>
                        <tr>
                            <td><?= $res['id_reservation'] ?></td>
                            <td><?= htmlspecialchars(($res['prenom'] ?? '') . ' ' . ($res['nom'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($res['date_debut']) ?></td>
                            <td><?= htmlspecialchars($res['date_fin']) ?></td>
                            <td><?= number_format($res['prix'], 2) ?> €</td>
                            <td><?= htmlspecialchars($res['plateforme']) ?></td>
                            <td>
                                <span class="badge <?= $res['valide'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= $res['valide'] ? 'Validée' : 'En attente' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Avis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $client): ?>
                        <tr>
                            <td><?= $client['id_client'] ?></td>
                            <td><?= htmlspecialchars($client['nom']) ?></td>
                            <td><?= htmlspecialchars($client['prenom']) ?></td>
                            <td><?= htmlspecialchars($client['avis'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
