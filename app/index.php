<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/utils/reservationUtils.php';
require_once __DIR__ . '/includes/components.php';

// Calcul des indicateurs clés pour le dashboard
$clientsList = getUsers($pdo);
$reservationsList = getReservations($pdo);

$totalClients = count($clientsList);
$totalReservations = count($reservationsList);

$totalRevenue = 0;
foreach ($reservationsList as $res) {
    if ($res['valide']) {
        $totalRevenue += $res['prix'];
    }
}

// Réservations en cours et à venir
$enCours = getResaEnCours($pdo);
$avenir = getResaAVenir($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="row mb-5 text-center mt-4">
        <div class="col-lg-8 mx-auto">
            <h1 class="display-6 fw-bold mb-3">Chambre des Clés</h1>
            <p class="lead text-secondary">
                Bienvenue dans votre outil d'administration. Suivez vos revenus, vos réservations et vos clients en un clin d'œil.
            </p>
        </div>
    </div>

    <!-- Ligne de cartes de statistiques -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card p-4 border-0 text-center" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                <span class="text-secondary small text-uppercase fw-semibold">Revenus Validés</span>
                <h3 class="fs-2 fw-bold text-success mt-2 mb-0">
                    <?= number_format($totalRevenue, 2, ',', ' ') ?> €
                </h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 border-0 text-center" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                <span class="text-secondary small text-uppercase fw-semibold">Total Réservations</span>
                <h3 class="fs-2 fw-bold text-dark mt-2 mb-0">
                    <?= $totalReservations ?>
                </h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 border-0 text-center" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                <span class="text-secondary small text-uppercase fw-semibold">Total Clients</span>
                <h3 class="fs-2 fw-bold text-dark mt-2 mb-0">
                    <?= $totalClients ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- Raccourcis de navigation -->
    <div class="row justify-content-center g-4 mb-5">
        <div class="col-md-6">
            <div class="card p-4 border-0 h-100" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                <h5 class="fw-bold mb-2">Réservations</h5>
                <p class="text-secondary small">Visualisez, filtrez, triez et modifiez les plannings et tarifs de toutes les réservations.</p>
                <a href="reservations.php" class="btn btn-dark btn-sm fw-medium mt-auto align-self-start">Gérer les réservations →</a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4 border-0 h-100" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                <h5 class="fw-bold mb-2">Clients</h5>
                <p class="text-secondary small">Consultez les profils des clients, mettez à jour leurs coordonnées et consultez leurs historiques.</p>
                <a href="clients.php" class="btn btn-dark btn-sm fw-medium mt-auto align-self-start">Gérer les clients →</a>
            </div>
        </div>
    </div>

    <!-- Tableau des 5 dernières réservations -->
    <div class="card p-4 border-0 mb-5" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
        <h5 class="fw-bold mb-3">Dernières réservations enregistrées</h5>
        
        <?php
        $reservations = array_merge($enCours, $avenir);
        ?>
<div class="card p-4 border-0 mb-5" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
    <h5 class="fw-bold mb-3">Réservations en cours / à venir</h5>
    <?php if (empty($reservations)): ?>
        <p class="text-muted small mb-0">Aucune réservation en cours ou à venir.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table table-hover align-middle small mb-0">
                <thead>
                    <tr>
                        <th class="text-end" style="width: 80px;">ID</th>
                        <th class="text-start">Client</th>
                        <th class="text-start">Arrivée</th>
                        <th class="text-start">Départ</th>
                        <th class="text-end" style="width: 120px;">Montant</th>
                        <th class="text-start">Plateforme</th>
                        <th class="text-center" style="width: 120px;">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $res): ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='reservationDetail.php?id_reservation=<?= $res['id_reservation'] ?>'">
                            <td class="text-end font-monospace text-muted"><?= $res['id_reservation'] ?></td>
                            <td class="text-start fw-semibold">
                                <a href="clientDetail.php?id_client=<?= $res['id_client'] ?>" class="text-decoration-none text-dark link-primary" onclick="event.stopPropagation();">
                                    <?= htmlspecialchars(($res['prenom'] ?? '') . ' ' . ($res['nom'] ?? '')) ?>
                                </a>
                            </td>
                            <td class="text-start"><?= htmlspecialchars($res['date_debut']) ?></td>
                            <td class="text-start">
                                <?= htmlspecialchars($res['date_fin']) ?>
                                <?php
                                $start = new DateTime($res['date_debut']);
                                $end = new DateTime($res['date_fin']);
                                $diff = $start->diff($end)->days;
                                echo '<span class="text-muted small">(départ dans ' . $diff . ' j)</span>';
                                ?>
                            </td>
                            <td class="text-end fw-semibold"><?= number_format($res['prix'], 2, ',', ' ') ?> €</td>
                            <td class="text-start"><span class="badge bg-light text-dark border"><?= htmlspecialchars($res['plateforme']) ?></span></td>
                            <td class="text-center"><?= StatusBadge(['valide' => $res['valide']]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>