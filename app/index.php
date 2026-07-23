<?php
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/utils/reservationUtils.php';
require_once __DIR__ . '/includes/components.php';

$tab = ($_GET['tab'] ?? '') === 'clients' ? 'clients' : 'reservations';

$data = ($tab === 'clients') ? getUsers($pdo) : getReservations($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">

<!-- Inclusion de la barre de navigation commune -->
<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
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
                                <!-- Utilisation du composant StatusBadge avec affichage direct (echo) -->
                                <?= StatusBadge(['valide' => $res['valide']]) ?>
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

<?php include_once __DIR__ . '/includes/footer.php'; ?>