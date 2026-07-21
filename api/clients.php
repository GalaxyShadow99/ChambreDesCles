<?php
require_once __DIR__ . '/bootstrap.php';

header("Content-Type: application/json; charset=utf-8");

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($method) {
    case 'GET':
        if (!empty($_GET['id'])) {
            // si ID on return UN client spécifique
            $stmt = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch() ?: []);
        } else {
            // sinon return TOUT les clients
            echo json_encode($pdo->query("SELECT * FROM client")->fetchAll());
        }
        break;

    case 'POST':
        if (empty($_POST['nom']) || empty($_POST['prenom'])) {
            http_response_code(400);
            die(json_encode(["message" => "Les champs 'nom' et 'prenom' sont requis"]));
        }

        $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, avis) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['nom'],
            $_POST['prenom'],
            !empty($_POST['avis']) ? $_POST['avis'] : null
        ]);
        echo json_encode(["message" => "Client ajouté avec succès"]);
        break;

    case 'PUT':
        $id = $_GET['id'] ?? $_POST['id_client'] ?? null;
        if (empty($id) || empty($_POST['nom']) || empty($_POST['prenom'])) {
            http_response_code(400);
            die(json_encode(["message" => "L'identifiant, le nom et le prénom sont requis"]));
        }

        $stmt = $pdo->prepare("UPDATE client SET nom = ?, prenom = ?, avis = ? WHERE id_client = ?");
        $stmt->execute([
            $_POST['nom'],
            $_POST['prenom'],
            !empty($_POST['avis']) ? $_POST['avis'] : null,
            $id
        ]);
        echo json_encode(["message" => "Client mis à jour avec succès"]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? $_POST['id_client'] ?? null;
        if (empty($id)) {
            http_response_code(400);
            die(json_encode(["message" => "Identifiant du client requis"]));
        }

        $stmt = $pdo->prepare("DELETE FROM client WHERE id_client = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Client supprimé avec succès"]);
        break;

    default:
        echo json_encode(["message" => "Méthode non autorisée"]);
        break;
}
