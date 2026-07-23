<?php
// app/delete.php - Suppression sécurisée de clients et de réservations

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/utils/reservationUtils.php';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    if ($type === 'client') {
        deleteUser($pdo, $id);
        header('Location: index.php');
        exit;
    } elseif ($type === 'reservation') {
        deleteReservation($pdo, $id);
        header('Location: index.php');
        exit;
    }
}

header('Location: index.php');
exit;
