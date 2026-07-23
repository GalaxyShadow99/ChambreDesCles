<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/utils/reservationUtils.php';

$POST = $_POST;

if(!empty($POST['nom']) && !empty($POST['prenom']) && !empty($POST['avis'])) {
    $nom = $POST['nom'];
    $prenom = $POST['prenom'];
    $avis = $POST['avis'];

    // Vérifier si le client existe déjà
    if (clientExists($pdo, $nom, $prenom)) {
        $retCode = "<div class='alert alert-danger py-2 px-3 border-0 bg-danger-subtle text-danger-emphasis mb-3 small'>Le client existe déjà.</div>";
    } else {
        // Créer le client
        createUser($pdo, $nom, $prenom, $avis);
        $retCode = "<div class='alert alert-success py-2 px-3 border-0 bg-success-subtle text-success-emphasis mb-3 small'>Client créé avec succès.</div>";
    }
}

$tab = 'clients';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container d-flex flex-column align-items-center">
    <div class="w-100" style="max-width: 500px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h5 mb-0 fw-bold">Créer un client</h2>
            <a href="index.php?tab=clients" class="btn btn-outline-secondary btn-sm">Retour</a>
        </div>

        <?php 
        if(isset($retCode) && $retCode != "") {
            echo $retCode;
        }
        ?>

        <div class="card border-0 shadow-sm p-4" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
            <form action="createClient.php" method="post">
                <div class="mb-3">
                    <label for="nom" class="form-label fw-semibold small text-secondary">Nom du client</label>
                    <input type="text" class="form-control form-control-sm" id="nom" name="nom" required>
                </div>
                <div class="mb-3">
                    <label for="prenom" class="form-label fw-semibold small text-secondary">Prénom du client</label>
                    <input type="text" class="form-control form-control-sm" id="prenom" name="prenom" required>
                </div>
                <div class="mb-3">
                    <label for="avis" class="form-label fw-semibold small text-secondary">Avis sur le client</label>
                    <input type="text" class="form-control form-control-sm" id="avis" name="avis" required>
                </div>
                <button type="submit" class="btn btn-dark btn-sm w-100 fw-medium">Créer le client</button>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
