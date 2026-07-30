<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
header('Content-Type: application/json');

$id_reservation = $_POST['id_reservation'] ?? null;
$valide = $_POST['valide'] ?? null;

if ($id_reservation !== null && $valide !== null) {
    try {
        $stmt = $pdo->prepare("UPDATE reservation SET valide = ? WHERE id_reservation = ?");
        echo json_encode(['success' => $stmt->execute([(int)$valide, (int)$id_reservation])]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
}