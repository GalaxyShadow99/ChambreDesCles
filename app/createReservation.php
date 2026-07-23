<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/utils/reservationUtils.php';

$POST = $_POST;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($POST['id_client']) && !empty($POST['date_debut']) && !empty($POST['date_fin']) && !empty($POST['prix'])) {
        if ($POST['date_fin'] <= $POST['date_debut']) {
            $error = "La date de fin doit être strictement supérieure à la date de début.";
        } else {
            $res = createReservation(
                $pdo,
                (int)$POST['id_client'],
                $POST['date_debut'],
                $POST['date_fin'],
                (float)$POST['prix'],
                !empty($POST['valide']) ? 1 : 0,
                $POST['plateforme'] ?? 'sans plateforme'
            );
            if ($res) {
                $retCode = "<div class='alert alert-success py-2 px-3 border-0 bg-success-subtle text-success-emphasis mb-3 small'>Réservation créée avec succès.</div>";
            } else {
                $retCode = "<div class='alert alert-danger py-2 px-3 border-0 bg-danger-subtle text-danger-emphasis mb-3 small'>Une erreur est survenue lors de l'enregistrement de la réservation.</div>";
            }
        }
    } else {
        $error = "Tous les champs obligatoires doivent être renseignés.";
    }
}

// Charger la liste des clients pour le menu déroulant
$clients = getUsers($pdo);
$tab = 'reservations';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container d-flex flex-column align-items-center">
    <div class="w-100" style="max-width: 500px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h5 mb-0 fw-bold">Créer une réservation</h2>
            <a href="index.php?tab=reservations" class="btn btn-outline-secondary btn-sm">Retour</a>
        </div>

        <?php 
        if (isset($retCode) && $retCode != "") {
            echo $retCode;
        }
        if ($error) {
            echo "<div class='alert alert-danger py-2 px-3 border-0 bg-danger-subtle text-danger-emphasis mb-3 small'>" . htmlspecialchars($error) . "</div>";
        }
        ?>

        <div class="card border-0 shadow-sm p-4" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
            <form action="createReservation.php" method="post">
                <div class="mb-3">
                    <label for="id_client" class="form-label fw-semibold small text-secondary">Client</label>
                    <select class="form-select form-select-sm" id="id_client" name="id_client" required>
                        <option value="">-- Sélectionner un client --</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id_client'] ?>">
                                <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date_debut" class="form-label fw-semibold small text-secondary">Date de début</label>
                        <input type="date" class="form-control form-control-sm" id="date_debut" name="date_debut" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_fin" class="form-label fw-semibold small text-secondary">Date de fin</label>
                        <input type="date" class="form-control form-control-sm" id="date_fin" name="date_fin" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="prix" class="form-label fw-semibold small text-secondary">Prix total (€)</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="prix" name="prix" placeholder="ex: 150.00" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="plateforme" class="form-label fw-semibold small text-secondary">Plateforme</label>
                        <select class="form-select form-select-sm" id="plateforme" name="plateforme" required>
                            <option value="sans plateforme">Sans plateforme</option>
                            <option value="booking">Booking</option>
                            <option value="airbnb">Airbnb</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="valide" name="valide" value="1">
                    <label class="form-check-label fw-semibold small text-secondary" for="valide">Réservation validée</label>
                </div>
                <button type="submit" class="btn btn-dark btn-sm w-100 fw-medium">Créer la réservation</button>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
