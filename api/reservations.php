<?php
require_once __DIR__ . '/bootstrap.php';

header("Content-Type: application/json; charset=utf-8");

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($method) {
    case 'GET':
        if (!empty($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM reservation LEFT JOIN client USING (id_client) WHERE id_reservation = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch() ?: []);
        } else {
            echo json_encode($pdo->query("SELECT * FROM reservation LEFT JOIN client USING (id_client)")->fetchAll());
        }
        break;

    case 'POST':
        if (empty($_POST['id_client']) || empty($_POST['date_debut']) || empty($_POST['date_fin']) || empty($_POST['prix'])) {
            http_response_code(400);
            die(json_encode(["message" => "Les champs 'id_client', 'date_debut', 'date_fin' et 'prix' sont requis"]));
        }

        $stmt = $pdo->prepare("INSERT INTO reservation (id_client, date_debut, date_fin, prix, valide, plateforme) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['id_client'],
            $_POST['date_debut'],
            $_POST['date_fin'],
            $_POST['prix'],
            !empty($_POST['valide']) ? $_POST['valide'] : 0,
            !empty($_POST['plateforme']) ? $_POST['plateforme'] : 'A'
        ]);
        echo json_encode(["message" => "Réservation ajoutée avec succès"]);
        break;

    case 'PUT':
        $id = $_GET['id'] ?? $_POST['id_reservation'] ?? null;
        if (empty($id) || empty($_POST['id_client']) || empty($_POST['date_debut']) || empty($_POST['date_fin']) || empty($_POST['prix'])) {
            http_response_code(400);
            die(json_encode(["message" => "L'identifiant de réservation et toutes les infos sont requis"]));
        }

        $stmt = $pdo->prepare("UPDATE reservation SET id_client = ?, date_debut = ?, date_fin = ?, prix = ?, valide = ?, plateforme = ? WHERE id_reservation = ?");
        $stmt->execute([
            $_POST['id_client'],
            $_POST['date_debut'],
            $_POST['date_fin'],
            $_POST['prix'],
            !empty($_POST['valide']) ? $_POST['valide'] : 0,
            !empty($_POST['plateforme']) ? $_POST['plateforme'] : 'A',
            $id
        ]);
        echo json_encode(["message" => "Réservation mise à jour avec succès"]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? $_POST['id_reservation'] ?? null;
        if (empty($id)) {
            http_response_code(400);
            die(json_encode(["message" => "Identifiant de réservation requis"]));
        }

        $stmt = $pdo->prepare("DELETE FROM reservation WHERE id_reservation = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Réservation supprimée avec succès"]);
        break;

    default:
        echo json_encode(["message" => "Méthode non autorisée"]);
        break;
}
