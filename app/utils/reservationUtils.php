<?php
// --- FONCTIONS RESERVATIONS ---

function getReservations($pdo) {
    return $pdo->query("SELECT * FROM reservation LEFT JOIN client USING (id_client)")->fetchAll();
}

function getReservation($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM reservation LEFT JOIN client USING (id_client) WHERE id_reservation = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getResaEnCours($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM reservation LEFT JOIN client USING (id_client) WHERE date_fin >= CURDATE() and date_debut <= CURDATE() ORDER BY date_debut ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getResaAVenir($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM reservation LEFT JOIN client USING (id_client) WHERE date_debut > CURDATE() ORDER BY date_debut ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function createReservation($pdo, $id_client, $date_debut, $date_fin, $prix, $valide = FALSE, $plateforme = 'sans plateforme') {
    $stmt = $pdo->prepare("INSERT INTO reservation (id_client, date_debut, date_fin, prix, valide, plateforme) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $id_client,
        $date_debut,
        $date_fin,
        $prix,
        $valide,
        $plateforme
    ]);
}

function updateReservation($pdo, $id, $id_client, $date_debut, $date_fin, $prix, $valide = FALSE, $plateforme = 'sans plateforme') {
    $stmt = $pdo->prepare("UPDATE reservation SET id_client = ?, date_debut = ?, date_fin = ?, prix = ?, valide = ?, plateforme = ? WHERE id_reservation = ?");
    return $stmt->execute([
        $id_client  ,
        $date_debut,
        $date_fin,
        $prix,
        $valide,
        $plateforme,
        $id
    ]);
}

function deleteReservation($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM reservation WHERE id_reservation = ?");
    return $stmt->execute([$id]);
}

function getReservationsByClient($pdo, $id_client) {
    $stmt = $pdo->prepare("SELECT * FROM reservation WHERE id_client = ?");
    $stmt->execute([$id_client]);
    return $stmt->fetchAll();
}
?>