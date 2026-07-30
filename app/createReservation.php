<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/utils/reservationUtils.php';

$POST = $_POST;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_client = $POST['id_client'] ?? null;

    // 1. Interception : Si l'utilisateur a choisi de créer un nouveau client
    if ($id_client === 'new') {
        $nom = trim($POST['nom'] ?? '');
        $prenom = trim($POST['prenom'] ?? '');
        $avis = trim($POST['avis'] ?? '');
        
        if (!empty($nom) && !empty($prenom)) {
            // Création du client en base
            createUser($pdo, $nom, $prenom, $avis);
            // Récupération automatique de l'ID du client fraîchement créé
            $id_client = (int)$pdo->lastInsertId();
        } else {
            $error = "Veuillez renseigner le nom et le prénom du nouveau client.";
        }
    } else {
        $id_client = (int)$id_client;
    }

    // 2. Traitement de la réservation (seulement si la création client s'est bien passée)
    if (!$error && !empty($id_client) && !empty($POST['date_debut']) && !empty($POST['date_fin']) && isset($POST['prix'])) {
        if ($POST['date_fin'] <= $POST['date_debut']) {
            $error = "La date de fin doit être strictement supérieure à la date de début.";
        } elseif ((float)$POST['prix'] < 0) {
            $error = "Le prix ne peut pas être négatif.";
        } else {
            $res = createReservation(
                $pdo,
                $id_client,
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
    } elseif (!$error) {
        $error = "Tous les champs obligatoires de la réservation doivent être renseignés.";
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
            <a href="reservations.php?tab=reservations" class="btn btn-outline-secondary btn-sm">Retour</a>
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
                    <select class="form-select form-select-sm" id="id_client" name="id_client" onchange="toggleClientFields()" required>
                        <option value="new" class="fw-bold text-primary">+ Créer un nouveau client</option>
                        <option disabled>───────────────────────</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id_client'] ?>">
                                <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Bloc masquable pour le nouveau client -->
                <div id="new_client_fields" class="p-3 mb-3 bg-light rounded" style="border: 1px dashed #d0d7de;">
                    <h6 class="fw-semibold small text-secondary mb-3">Informations du nouveau client</h6>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control form-control-sm" id="nom" name="nom" placeholder="Nom *">
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control form-control-sm" id="prenom" name="prenom" placeholder="Prénom *">
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control form-control-sm" name="avis" placeholder="Avis / Notes (Optionnel)">
                        </div>
                    </div>
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
                        <input type="number" step="0.01" class="form-control form-control-sm" id="prix" name="prix" placeholder="ex: 150" required>
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

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="valide" name="valide" value="1">
                    <label class="form-check-label fw-semibold small text-secondary" for="valide">Réservation validée</label>
                </div>

                <button type="submit" class="btn btn-dark btn-sm w-100 fw-medium">Enregistrer la réservation</button>
            </form>
        </div>
    </div>
</div>

<script>
// Script pour afficher ou masquer les champs de création de client
function toggleClientFields() {
    const select = document.getElementById('id_client');
    const newClientFields = document.getElementById('new_client_fields');
    const inputNom = document.getElementById('nom');
    const inputPrenom = document.getElementById('prenom');

    if (select.value === 'new') {
        newClientFields.style.display = 'block';
        inputNom.setAttribute('required', 'required');
        inputPrenom.setAttribute('required', 'required');
    } else {
        newClientFields.style.display = 'none';
        inputNom.removeAttribute('required');
        inputPrenom.removeAttribute('required');
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', toggleClientFields);
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>