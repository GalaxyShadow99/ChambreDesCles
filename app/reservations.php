<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/reservationUtils.php';
require_once __DIR__ . '/includes/components.php';

$data = getReservations($pdo);
$tab = 'reservations';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">
<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0 fw-bold">Gestion des réservations</h2>
        <div class="d-flex gap-2">
            <a href="generateExcel.php" class="btn btn-outline-success btn-sm fw-medium">Exporter Excel</a>
            <a href="createReservation.php" class="btn btn-dark btn-sm fw-medium">Ajouter une réservation</a>
        </div>
    </div>

    <!-- Barre de recherche & Astuce -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-8 mb-2 mb-md-0">
            <div class="alert alert-secondary py-2 mb-0 small border-0 bg-light text-muted h-100 d-flex align-items-center">
                <div><strong>Astuce :</strong> <em>Cliquez sur les en-têtes du tableau pour trier. Utilisez la barre de recherche pour filtrer un client ou une date.</em></div>
            </div>
        </div>
        <div class="col-md-4">
            <input type="text" id="searchInput" class="form-control" placeholder="🔍 Filtrer (ex: Dupont, booking, 2026...)" onkeyup="filterTable()">
        </div>
    </div>

    <?php if (empty($data)): ?>
        <div class="alert alert-info">Aucune réservation trouvée.</div>
    <?php else: ?>
        <div class="table-container">
            <table class="table table-hover sortable" id="reservationsTable">
                <thead>
                    <tr>
                        <th class="text-end" style="width: 80px;">ID</th>
                        <th class="text-start">Client</th>
                        <th class="text-start">Date début</th>
                        <th class="text-start">Date fin</th>
                        <th class="text-end" style="width: 120px;">Prix</th>
                        <th class="text-start">Plateforme</th>
                        <th class="text-center" style="width: 120px;">Statut</th>
                        <!-- sorttable_nosort empêche de trier la colonne des boutons -->
                        <th class="text-center sorttable_nosort" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $res): ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='reservationDetail.php?id_reservation=<?= $res['id_reservation'] ?>'">
                            <td class="text-end font-monospace text-muted"><?= $res['id_reservation'] ?></td>
                            <td class="text-start fw-semibold"><?= htmlspecialchars(($res['prenom'] ?? '') . ' ' . ($res['nom'] ?? '')) ?></td>
                            
                            <!-- L'attribut sorttable_customkey donne la vraie valeur informatique au script de tri -->
                            <td class="text-start" sorttable_customkey="<?= $res['date_debut'] ?>"><?= htmlspecialchars($res['date_debut']) ?></td>
                            <td class="text-start" sorttable_customkey="<?= $res['date_fin'] ?>">
                                <?= htmlspecialchars($res['date_fin']) ?>
                                <?php
                                $start = new DateTime($res['date_debut']);
                                $end = new DateTime($res['date_fin']);
                                $diff = $start->diff($end)->days;
                                echo '<span class="text-muted small">(' . $diff . ' j)</span>';
                                ?>
                            </td>
                            <td class="text-end fw-semibold" sorttable_customkey="<?= $res['prix'] ?>"><?= number_format($res['prix'], 2, ',', ' ') ?> €</td>
                            <td class="text-start"><span class="badge bg-light text-dark border"><?= htmlspecialchars($res['plateforme']) ?></span></td>
                            <td class="text-center" sorttable_customkey="<?= $res['valide'] ? '1' : '0' ?>">
                                <?= StatusBadge(['valide' => $res['valide']]) ?>
                            </td>
                            <td class="text-center">
                                <a href="delete.php?type=reservation&id=<?= $res['id_reservation'] ?>" class="text-danger text-decoration-none small fw-medium" onclick="event.stopPropagation(); return confirm('Supprimer cette réservation ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Script de la barre de recherche rapide -->
<script>
function filterTable() {
    // Récupérer la valeur saisie dans la barre de recherche
    var input = document.getElementById("searchInput");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("reservationsTable");
    var tr = table.getElementsByTagName("tr");

    // Parcourir toutes les lignes (en ignorant l'en-tête [0])
    for (var i = 1; i < tr.length; i++) {
        tr[i].style.display = "none"; // Cacher par défaut
        var td = tr[i].getElementsByTagName("td");
        
        // On cherche dans toutes les colonnes de la ligne (sauf la dernière "Actions")
        for (var j = 0; j < td.length - 1; j++) {
            if (td[j]) {
                var txtValue = td[j].textContent || td[j].innerText;
                // Si la ligne contient le mot recherché
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = ""; // Afficher
                    break; // On a trouvé, on passe à la ligne suivante
                }
            }
        }
    }
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>