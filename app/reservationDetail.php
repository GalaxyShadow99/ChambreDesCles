<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/utils/reservationUtils.php';
require_once __DIR__ . '/includes/components.php';

$id_reservation = isset($_GET['id_reservation']) ? (int)$_GET['id_reservation'] : null;

$reservation = null;
$clients = [];
$success = isset($_GET['success']) ? "Les informations de la réservation ont été mises à jour." : null;
$error = null;

if ($id_reservation) {
    $reservation = getReservation($pdo, $id_reservation);
    if ($reservation) {
        $clients = getUsers($pdo);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier' && $id_reservation && $reservation) {
    $id_client = (int)($_POST['id_client'] ?? 0);
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $prix = (float)($_POST['prix'] ?? 0);
    $valide = !empty($_POST['valide']) ? 1 : 0;
    $plateforme = $_POST['plateforme'] ?? 'sans plateforme';

    if ($id_client && $date_debut && $date_fin && $prix) {
        if ($date_fin <= $date_debut) {
            $error = "La date de fin doit être strictement supérieure à la date de début.";
        } else {
            $updateOk = updateReservation(
                $pdo,
                $id_reservation,
                $id_client,
                $date_debut,
                $date_fin,
                $prix,
                $valide,
                $plateforme
            );

            if ($updateOk) {
                header("Location: reservationDetail.php?id_reservation=" . $id_reservation . "&success=1");
                exit;
            } else {
                $error = "Une erreur technique est survenue lors de la mise à jour.";
            }
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

$tab = 'reservations';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container d-flex flex-column align-items-center">
    <div class="w-100" style="max-width: 700px;">
        <div class="mb-4">
            <a href="index.php?tab=reservations" class="btn btn-outline-secondary btn-sm">
                ← Retour à la liste des réservations
            </a>
        </div>

        <?php if (!$reservation): ?>
            <div class="alert alert-warning py-3 text-center border-0 bg-warning-subtle text-warning-emphasis">
                <h4 class="fw-bold mb-2">Réservation introuvable</h4>
                <p class="mb-0">Le numéro de réservation spécifié n'existe pas ou a été supprimé.</p>
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
                <!-- Colonne Résumé / Client lié -->
                <div class="col-md-5">
                    <div class="card p-4 mb-4 border-0" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                        <h6 class="fw-bold text-dark text-uppercase small mb-3">Client rattaché</h6>
                        <h5 class="fw-bold mb-1">
                            <a href="clientDetail.php?id_client=<?= $reservation['id_client'] ?>" class="text-decoration-none text-dark link-primary">
                                <?= htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']) ?>
                            </a>
                        </h5>
                        <span class="text-secondary small">Fiche Client n°<?= $reservation['id_client'] ?></span>
                    </div>

                    <div class="card p-4 border-0" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                        <h6 class="fw-bold text-dark text-uppercase small mb-3">Informations de paiement</h6>
                        <div>
                            <div class="fs-4 fw-bold text-dark mb-1">
                                <?= number_format($reservation['prix'], 2, ',', ' ') ?> €
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($reservation['plateforme']) ?></span>
                            </div>
                            <div class="mb-3">
                                <?= StatusBadge(['valide' => $reservation['valide']]) ?>
                            </div>
                            <a href="generatePDF.php?id_reservation=<?= $reservation['id_reservation'] ?>" target="_blank" class="btn btn-sm btn-outline-dark w-100">
                                Imprimer la facture (PDF)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Colonne Détails & Modification -->
                <div class="col-md-7">
                    <div class="card p-4 border-0" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="fw-bold text-dark h6 mb-0 text-uppercase">Détails de la réservation</h2>
                            <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#formModif" aria-expanded="false">
                                Modifier
                            </button>
                        </div>

                        <div class="row gy-3 small text-secondary mb-2" id="infoLecture">
                            <div class="col-sm-6"><strong class="text-dark">Arrivée</strong><br><?= htmlspecialchars($reservation['date_debut']) ?></div>
                            <div class="col-sm-6">
                                <strong class="text-dark">Départ</strong><br>
                                <?= htmlspecialchars($reservation['date_fin']) ?>
                                <?php
                                $start = new DateTime($reservation['date_debut']);
                                $end = new DateTime($reservation['date_fin']);
                                $diff = $start->diff($end)->days;
                                echo '<span class="text-muted small">(' . $diff . ' j)</span>';
                                ?>
                            </div>
                            <div class="col-sm-6"><strong class="text-dark">Montant</strong><br><?= number_format($reservation['prix'], 2, ',', ' ') ?> €</div>
                            <div class="col-sm-6"><strong class="text-dark">Plateforme</strong><br><?= htmlspecialchars($reservation['plateforme']) ?></div>
                            <div class="col-12"><strong class="text-dark">Statut</strong><br><?= $reservation['valide'] ? 'Validée' : 'En attente' ?></div>
                        </div>

                        <div class="collapse" id="formModif">
                            <hr class="my-3" style="border-color: #d0d7de;">
                            <form method="POST" action="reservationDetail.php?id_reservation=<?= $id_reservation ?>">
                                <input type="hidden" name="action" value="modifier">
                                <div class="mb-3">
                                    <label for="id_client" class="form-label small fw-semibold text-dark">Associer au client <span class="text-danger">*</span></label>
                                    <select name="id_client" id="id_client" class="form-select form-select-sm" required>
                                        <?php foreach ($clients as $c): ?>
                                            <option value="<?= $c['id_client'] ?>" <?= $c['id_client'] == $reservation['id_client'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label small fw-semibold text-dark">Date de début <span class="text-danger">*</span></label>
                                        <input type="date" name="date_debut" class="form-control form-control-sm" value="<?= htmlspecialchars($reservation['date_debut']) ?>" required>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label small fw-semibold text-dark">Date de fin <span class="text-danger">*</span></label>
                                        <input type="date" name="date_fin" class="form-control form-control-sm" value="<?= htmlspecialchars($reservation['date_fin']) ?>" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label small fw-semibold text-dark">Montant (€) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="prix" class="form-control form-control-sm" value="<?= htmlspecialchars($reservation['prix']) ?>" required>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label small fw-semibold text-dark">Plateforme <span class="text-danger">*</span></label>
                                        <select name="plateforme" class="form-select form-select-sm" required>
                                            <option value="sans plateforme" <?= $reservation['plateforme'] === 'sans plateforme' ? 'selected' : '' ?>>Sans plateforme</option>
                                            <option value="booking" <?= $reservation['plateforme'] === 'booking' ? 'selected' : '' ?>>Booking</option>
                                            <option value="airbnb" <?= $reservation['plateforme'] === 'airbnb' ? 'selected' : '' ?>>Airbnb</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="valide" class="form-check-input" id="valide" value="1" <?= $reservation['valide'] ? 'checked' : '' ?>>
                                    <label class="form-check-label small fw-semibold text-dark" for="valide">Réservation validée</label>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-dark btn-sm fw-medium">
                                        Enregistrer les modifications
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#formModif">
                                        Annuler
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
