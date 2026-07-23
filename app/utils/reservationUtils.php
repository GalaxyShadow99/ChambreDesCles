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

function createReservation($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO reservation (id_client, date_debut, date_fin, prix, valide, plateforme) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $data['id_client'],
        $data['date_debut'],
        $data['date_fin'],
        $data['prix'],
        $data['valide'] ?? FALSE,
        $data['plateforme'] ?? 'A'
    ]);
}

function updateReservation($pdo, $id, $data) {
    $stmt = $pdo->prepare("UPDATE reservation SET id_client = ?, date_debut = ?, date_fin = ?, prix = ?, valide = ?, plateforme = ? WHERE id_reservation = ?");
    return $stmt->execute([
        $data['id_client'],
        $data['date_debut'],
        $data['date_fin'],
        $data['prix'],
        $data['valide'] ?? FALSE,
        $data['plateforme'] ?? 'A',
        $id
    ]);
}

function deleteReservation($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM reservation WHERE id_reservation = ?");
    return $stmt->execute([$id]);
}

?>