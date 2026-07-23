<?php
// --- FONCTIONS UTILISATEURS (CLIENTS) ---

function getUsers($pdo) {
    return $pdo->query("SELECT id_client, nom, prenom, avis FROM client")->fetchAll();
}

function getUser($pdo, $id) {
    $stmt = $pdo->prepare("SELECT id_client, nom, prenom, avis FROM client WHERE id_client = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getUserDetails($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function createUser($pdo, $nom, $prenom, $avis) {
    $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, avis) VALUES (?, ?, ?)");
    return $stmt->execute([
        $nom,
        $prenom,
        $avis ?? null
    ]);
}

function updateUser($pdo, $id, $nom, $prenom, $avis ) {
    $stmt = $pdo->prepare("UPDATE client SET nom = ?, prenom = ?, avis = ? WHERE id_client = ?");
    return $stmt->execute([
        $nom,
        $prenom,
        $avis ?? null,
        $id
    ]);
}

function deleteUser($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM client WHERE id_client = ?");
    return $stmt->execute([$id]);
}

function clientExists($pdo, $nom, $prenom) {
    $stmt = $pdo->prepare("SELECT * FROM client WHERE nom = ? AND prenom = ?");
    $stmt->execute([$nom, $prenom]);
    $clients = $stmt->fetchAll();

    // Renvoie true s'il y a au moins 1 client
    return count($clients) > 0;
}


?>