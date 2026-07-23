<?php
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/utils/reservationUtils.php';

$tab = ($_GET['tab'] ?? '') === 'clients' ? 'clients' : 'reservations';

$data = ($tab === 'clients') ? getUsers($pdo) : getReservations($pdo);
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

    <div class="alert alert-secondary py-2 mb-3 small">
        💡 <em>Astuce : Cliquez sur les en-têtes des colonnes ci-dessous pour trier la table.</em>
    </div>

    <?php if (empty($data)): ?>
        <div class="alert alert-info">Aucun enregistrement trouvé.</div>
    <?php else: ?>
        <?php if ($tab === 'reservations'): ?>
            <table class="table table-striped table-bordered table-hover sortable">
                <thead class="table-dark text-white">
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
            <table class="table table-striped table-bordered table-hover sortable">
                <thead class="table-dark text-white">
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
<!-- pour pouvoir trier les tables -->
<script src="scripts/sorttable.js"></script>

</body>
</html>
