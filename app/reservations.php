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
                        <!-- On ajoute un ID ici pour pouvoir cliquer dessus automatiquement -->
                        <th class="text-start" id="th-date-debut">Date début</th>
                        <th class="text-start">Date fin</th>
                        <th class="text-end" style="width: 120px;">Prix</th>
                        <th class="text-start">Plateforme</th>
                        <th class="text-start" style="width: 200px;">Note Client</th>
                        <th class="text-center" style="width: 120px;">Statut</th>
                        <th class="text-center sorttable_nosort" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $res): ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='reservationDetail.php?id_reservation=<?= $res['id_reservation'] ?>'">
                            <td class="text-end font-monospace text-muted"><?= $res['id_reservation'] ?></td>
                            <td class="text-start fw-semibold"><?= htmlspecialchars(($res['prenom'] ?? '') . ' ' . ($res['nom'] ?? '')) ?></td>
                            
                            <!-- Utilisation de Ymd pour un tri chronologique parfait -->
                            <td class="text-start" sorttable_customkey="<?= date('Ymd', strtotime($res['date_debut'])) ?>">
                                <?= date('d/m/Y', strtotime($res['date_debut'])) ?>
                            </td>
                            <td class="text-start" sorttable_customkey="<?= date('Ymd', strtotime($res['date_fin'])) ?>">
                                <?= date('d/m/Y', strtotime($res['date_fin'])) ?>
                                <?php
                                $start = new DateTime($res['date_debut']);
                                $end = new DateTime($res['date_fin']);
                                $diff = $start->diff($end)->days;
                                echo '<span class="text-muted small">(' . $diff . ' j)</span>';
                                ?>
                            </td>
                            <td class="text-end fw-semibold" sorttable_customkey="<?= $res['prix'] ?>"><?= number_format($res['prix'], 2, ',', ' ') ?> €</td>
                            <td class="text-start"><span class="badge bg-light text-dark border"><?= htmlspecialchars($res['plateforme']) ?></span></td>
                            
                            <!-- NOTE CLIENT -->
                            <td class="text-start">
                                <input type="text" 
                                       class="form-control form-control-sm client-note-input" 
                                       data-client-id="<?= htmlspecialchars($res['id_client']) ?>" 
                                       value="<?= htmlspecialchars($res['avis'] ?? '') ?>" 
                                       placeholder="Notes, préférences..." 
                                       style="min-width: 150px; font-size: 0.9em; cursor: text;" />
                            </td>

                            <!-- STATUT CLIQUABLE -->
                            <td class="text-center" sorttable_customkey="<?= $res['valide'] ? '1' : '0' ?>">
                                <button class="badge status-toggle-btn <?= $res['valide'] ? 'bg-success text-white' : 'bg-warning text-dark' ?>" 
                                        data-id="<?= htmlspecialchars($res['id_reservation']) ?>" 
                                        data-status="<?= $res['valide'] ? 'Validée' : 'En attente' ?>"
                                        style="border: none; cursor: pointer;">
                                    <?= $res['valide'] ? 'Validée' : 'En attente' ?>
                                </button>
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

<script>
// --- AUTO-TRI AU CHARGEMENT DE LA PAGE ---
window.addEventListener('load', function() {
    // Petit délai de 100ms pour s'assurer que sorttable.js a fini d'activer le tableau
    setTimeout(function() {
        const dateHeader = document.getElementById('th-date-debut');
        if (dateHeader) {
            dateHeader.click(); // Simule le clic utilisateur
        }
    }, 100);
});

// --- RECHERCHE ---
function filterTable() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("reservationsTable");
    if (!table) return;
    var tr = table.getElementsByTagName("tr");
    
    for (var i = 1; i < tr.length; i++) {
        tr[i].style.display = "none";
        var td = tr[i].getElementsByTagName("td");
        for (var j = 0; j < td.length - 1; j++) {
            if (td[j]) {
                // On récupère le texte, ou la valeur si c'est un input
                var txtValue = td[j].textContent || td[j].innerText || td[j].querySelector('input')?.value || "";
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    break;
                }
            }
        }
    }
}

// --- GESTION AJAX ---
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Bouton Statut
    document.querySelectorAll('.status-toggle-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation(); // Évite de cliquer sur la ligne du tableau
            
            const resId = this.dataset.id;
            const currentStatus = this.dataset.status;
            const newValide = (currentStatus === 'Validée') ? 0 : 1;
            const newStatusText = newValide ? 'Validée' : 'En attente';

            // Mise à jour visuelle instantanée
            this.dataset.status = newStatusText;
            this.textContent = newStatusText;
            
            // Mise à jour de la custom key pour que le tri fonctionne toujours après changement
            this.parentElement.setAttribute('sorttable_customkey', newValide ? '1' : '0');

            if (newValide) {
                this.classList.replace('bg-warning', 'bg-success');
                this.classList.replace('text-dark', 'text-white');
            } else {
                this.classList.replace('bg-success', 'bg-warning');
                this.classList.replace('text-white', 'text-dark');
            }

            // Envoi au serveur
            fetch('api_update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_reservation=${resId}&valide=${newValide}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) alert("Erreur serveur lors de la sauvegarde.");
            })
            .catch(err => console.error(err));
        });
    });

    // 2. Champ Note Client
    document.querySelectorAll('.client-note-input').forEach(input => {
        input.addEventListener('click', function(e) {
            e.stopPropagation(); // Évite de cliquer sur la ligne quand on veut écrire
        });
        
        input.addEventListener('change', function() {
            const clientId = this.dataset.clientId;
            const newAvis = this.value;

            fetch('api_update_note.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_client=${clientId}&avis=${encodeURIComponent(newAvis)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.style.borderColor = '#198754';
                    this.style.boxShadow = '0 0 0 0.25rem rgba(25, 135, 84, 0.25)';
                    setTimeout(() => {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }, 1200);
                } else {
                    alert("Erreur lors de la sauvegarde de la note.");
                }
            })
            .catch(err => console.error(err));
        });
    });
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>