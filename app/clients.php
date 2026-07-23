<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/clientUtils.php';
require_once __DIR__ . '/includes/components.php';

$data = getUsers($pdo);
$tab = 'clients';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0 fw-bold">Gestion des clients</h2>
        <a href="createClient.php" class="btn btn-dark btn-sm fw-medium">Ajouter un client</a>
    </div>

    <!-- Infobulle d'astuce utilisateur -->
    <div class="alert alert-secondary py-2 mb-3 small border-0 bg-light text-muted ps-0">
        <strong>Astuce :</strong> <em>Cliquez n'importe où sur une ligne pour voir/modifier la fiche du client. Cliquez sur les en-têtes pour trier.</em>
    </div>

    <?php if (empty($data)): ?>
        <div class="alert alert-info">Aucun client trouvé.</div>
    <?php else: ?>
        <div class="table-container">
            <table class="table table-hover sortable">
                <thead>
                    <tr>
                        <th class="text-end" style="width: 80px;">ID</th>
                        <th class="text-start" style="width: 200px;">Nom</th>
                        <th class="text-start" style="width: 200px;">Prénom</th>
                        <th class="text-start">Avis / Notes</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $client): ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='clientDetail.php?id_client=<?= $client['id_client'] ?>'">
                            <td class="text-end font-monospace text-muted"><?= $client['id_client'] ?></td>
                            <td class="text-start fw-semibold"><?= htmlspecialchars($client['nom']) ?></td>
                            <td class="text-start"><?= htmlspecialchars($client['prenom']) ?></td>
                            <td class="text-start text-secondary"><?= htmlspecialchars($client['avis'] ?? '—') ?></td>
                            <td class="text-center">
                                <a href="delete.php?type=client&id=<?= $client['id_client'] ?>" class="text-danger text-decoration-none small fw-medium" onclick="event.stopPropagation(); return confirm('Supprimer ce client et toutes ses réservations associées ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
