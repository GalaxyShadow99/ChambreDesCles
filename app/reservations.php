<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/reservationUtils.php';
require_once __DIR__ . '/includes/components.php';

$data = getReservations($pdo);
$tab = 'reservations';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0 fw-bold">Gestion des réservations</h2>
        <div class="d-flex gap-2">
            <a href="generateExcel.php" class="btn btn-outline-success btn-sm fw-medium">Exporter Excel</a>
            <a href="createReservation.php" class="btn btn-dark btn-sm fw-medium">Ajouter une réservation</a>
        </div>
    </div>

    <!-- Infobulle d'astuce utilisateur -->
    <div class="alert alert-secondary py-2 mb-3 small border-0 bg-light text-muted ps-0">
        <strong>Astuce :</strong> <em>Cliquez n'importe où sur une ligne pour voir/modifier ses détails. Cliquez sur les en-têtes pour trier.</em>
    </div>

    <?php if (empty($data)): ?>
        <div class="alert alert-info">Aucune réservation trouvée.</div>
    <?php else: ?>
        <div class="table-container">
            <table class="table table-hover sortable">
                <thead>
                    <tr>
                        <th class="text-end" style="width: 80px;">ID</th>
                        <th class="text-start">Client</th>
                        <th class="text-start">Date début</th>
                        <th class="text-start">Date fin</th>
                        <th class="text-end" style="width: 120px;">Prix</th>
                        <th class="text-start">Plateforme</th>
                        <th class="text-center" style="width: 120px;">Statut</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $res): ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='reservationDetail.php?id_reservation=<?= $res['id_reservation'] ?>'">
                            <td class="text-end font-monospace text-muted"><?= $res['id_reservation'] ?></td>
                            <td class="text-start fw-semibold"><?= htmlspecialchars(($res['prenom'] ?? '') . ' ' . ($res['nom'] ?? '')) ?></td>
                            <td class="text-start"><?= htmlspecialchars($res['date_debut']) ?></td>
                            <td class="text-start">
                                <?= htmlspecialchars($res['date_fin']) ?>
                                <?php
                                $start = new DateTime($res['date_debut']);
                                $end = new DateTime($res['date_fin']);
                                $diff = $start->diff($end)->days;
                                echo '<span class="text-muted small">(' . $diff . ' j)</span>';
                                ?>
                            </td>
                            <td class="text-end fw-semibold"><?= number_format($res['prix'], 2, ',', ' ') ?> €</td>
                            <td class="text-start"><span class="badge bg-light text-dark border"><?= htmlspecialchars($res['plateforme']) ?></span></td>
                            <td class="text-center">
                                <?= StatusBadge(['valide' => $res['valide']]) ?>
                            </td>
                            <td class="text-center">
                                <a href="delete.php?type=reservation&id=<?= $res['id_reservation'] ?>" class="text-danger text-decoration-none small fw-medium" onclick="event.stopPropagation(); return confirm('Supprimer cette réservation ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
