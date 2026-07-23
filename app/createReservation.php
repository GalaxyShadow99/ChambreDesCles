<?php
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
        $retCode = "<div class='alert alert-danger'>Le client existe déjà.</div>";
    } else {
        // Créer le client
        createUser($pdo, $nom, $prenom, $avis);
        $retCode = "<div class='alert alert-success'>Client créé avec succès.</div>";
    }
}

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

    <?php 
    if(isset($retCode) && $retCode != "") {
        echo $retCode;
    }
    ?>

    <form action="createReservation.php" method="post">
        <div class="mb-3">
            <label for="nom" class="form-label">Nom du client</label>
            <input type="text" class="form-control" id="nom" name="nom" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom du client</label>
            <input type="text" class="form-control" id="prenom" name="prenom" required>
        </div>
        <div class="mb-3">
            <label for="avis" class="form-label">Avis sur le client</label>
            <input type="text" class="form-control" id="avis" name="avis" required>
        </div>
        <button type="submit" class="btn btn-primary">Créer le client</button>

    </form>


</body>
</html>
