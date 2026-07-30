<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
header('Content-Type: application/json');

$id_client = $_POST['id_client'] ?? null;
$avis = $_POST['avis'] ?? '';

if ($id_client !== null) {
    try {
        $stmt = $pdo->prepare("UPDATE client SET avis = ? WHERE id_client = ?");
        echo json_encode(['success' => $stmt->execute([$avis, (int)$id_client])]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
}