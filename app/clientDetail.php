<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/utils/reservationUtils.php';
require_once __DIR__ . '/includes/components.php';

$id_client = isset($_GET['id_client']) ? (int)$_GET['id_client'] : null;

$client = null;
$reservations = [];
$success = isset($_GET['success']) ? "Les informations du client ont été mises à jour." : null;
$error = null;

if ($id_client) {
    $client = getUser($pdo, $id_client);
    if ($client) {
        $reservations = getReservationsByClient($pdo, $id_client);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier' && $id_client && $client) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $avis = trim($_POST['avis'] ?? '');

    if ($nom && $prenom) {
        $updateOk = updateUser($pdo, $id_client, $nom, $prenom, $avis ?: null);

        if ($updateOk) {
            header("Location: clientDetail.php?id_client=" . $id_client . "&success=1");
            exit;
        } else {
            $error = "Une erreur technique est survenue lors de la mise à jour.";
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

$tab = 'clients';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="mb-4">
        <a href="index.php?tab=clients" class="btn btn-outline-secondary btn-sm">
            ← Retour à la liste des clients
        </a>
    </div>

    <?php if (!$client): ?>
        <div class="alert alert-warning py-3 text-center border-0 bg-warning-subtle text-warning-emphasis">
            <h4 class="fw-bold mb-2">Client introuvable</h4>
            <p class="mb-0">Le numéro de client spécifié n'existe pas ou a été supprimé.</p>
        </div>
    <?php else: ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 bg-success-subtle text-success-emphasis py-2 px-3 mb-4 small" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close small" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger-subtle text-danger-emphasis py-2 px-3 mb-4 small" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close small" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row gy-4">
            <!-- Colonne Profil / Métriques -->
            <div class="col-lg-4">
                <div class="card p-4 mb-4 border-0" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                    <h5 class="fw-bold mb-1">
                        <?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?>
                    </h5>
                    <span class="text-secondary small">Client n°<?= $id_client ?></span>
                    
                    <hr class="my-3" style="border-color: #d0d7de;">
                    
                    <div class="small text-secondary">
                        <strong class="text-dark">Notes / Avis :</strong>
                        <p class="mt-1 mb-0">
                            <?= $client['avis'] ? '"' . htmlspecialchars($client['avis']) . '"' : '— Aucun avis enregistré —' ?>
                        </p>
                    </div>
                </div>

                <div class="card p-4 border-0" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                    <h6 class="fw-bold text-dark text-uppercase small mb-3">Statistiques de séjour</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-bold text-dark">
                                <?= count($reservations) ?>
                            </div>
                            <div class="text-secondary small">Réservation(s)</div>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold text-success">
                                <?php 
                                $totalPaid = 0;
                                foreach ($reservations as $r) {
                                    if ($r['valide']) {
                                        $totalPaid += $r['prix'];
                                    }
                                }
                                echo number_format($totalPaid, 2, ',', ' ') . ' €';
                                ?>
                            </div>
                            <div class="text-secondary small">Total validé</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne Informations de compte / Historique -->
            <div class="col-lg-8">
                <!-- Edition des données -->
                <div class="card p-4 mb-4 border-0" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="fw-bold text-dark h6 mb-0 text-uppercase">Informations du compte</h2>
                        <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#formModif" aria-expanded="false">
                            Modifier les données
                        </button>
                    </div>

                    <div class="row gy-2 small text-secondary mb-2" id="infoLecture">
                        <div class="col-sm-6"><strong class="text-dark">Nom</strong><br><?= htmlspecialchars($client['nom']) ?></div>
                        <div class="col-sm-6"><strong class="text-dark">Prénom</strong><br><?= htmlspecialchars($client['prenom']) ?></div>
                        <div class="col-12"><strong class="text-dark">Avis / Notes</strong><br><?= htmlspecialchars($client['avis'] ?? '—') ?></div>
                    </div>

                    <div class="collapse" id="formModif">
                        <hr class="my-3" style="border-color: #d0d7de;">
                        <form method="POST" action="clientDetail.php?id_client=<?= $id_client ?>">
                            <input type="hidden" name="action" value="modifier">
                            <div class="row gy-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-dark">Nom de famille <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control form-control-sm" value="<?= htmlspecialchars($client['nom']) ?>" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-dark">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" name="prenom" class="form-control form-control-sm" value="<?= htmlspecialchars($client['prenom']) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-dark">Avis / Notes</label>
                                    <textarea name="avis" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($client['avis'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-dark btn-sm fw-medium">
                                        Enregistrer les modifications
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#formModif">
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Historique des réservations -->
                <div class="card p-4 border-0" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                    <h2 class="fw-bold text-dark h6 mb-4 text-uppercase">Réservations effectuées par ce client</h2>

                    <?php if (empty($reservations)): ?>
                        <div class="text-center py-4 text-secondary">
                            <p class="mb-0">Aucun historique de réservation enregistré pour cet utilisateur.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table table-hover align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-end" style="width: 80px;">N° Résa</th>
                                        <th class="text-start">Arrivée</th>
                                        <th class="text-start">Départ</th>
                                        <th class="text-end" style="width: 120px;">Montant</th>
                                        <th class="text-start">Plateforme</th>
                                        <th class="text-center" style="width: 120px;">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $r): ?>
                                    <tr style="cursor: pointer;" onclick="window.location.href='reservationDetail.php?id_reservation=<?= $r['id_reservation'] ?>'">
                                        <td class="text-end font-monospace text-muted"><?= htmlspecialchars($r['id_reservation']) ?></td>
                                        <td class="text-start"><?= htmlspecialchars($r['date_debut']) ?></td>
                                        <td class="text-start">
                                            <?= htmlspecialchars($r['date_fin']) ?>
                                            <?php
                                            $start = new DateTime($r['date_debut']);
                                            $end = new DateTime($r['date_fin']);
                                            $diff = $start->diff($end)->days;
                                            echo '<span class="text-muted small">(' . $diff . ' j)</span>';
                                            ?>
                                        </td>
                                        <td class="text-end fw-semibold">
                                            <?= number_format((float)($r['prix'] ?? 0), 2, ',', ' ') ?> €
                                        </td>
                                        <td class="text-start">
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($r['plateforme']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?= StatusBadge(['valide' => $r['valide']]) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
