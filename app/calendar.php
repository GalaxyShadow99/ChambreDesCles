<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/reservationUtils.php';

// Récupération de la liste des réservations
$reservations = getReservations($pdo);

// Préparation des événements pour FullCalendar
$events = [];
foreach ($reservations as $res) {
    $statusText = $res['valide'] ? 'Validée' : 'En attente';
    $colorClass = $res['valide'] ? 'success' : 'warning';
    
    // Titre descriptif
    $clientName = ($res['prenom'] ?? '') . ' ' . ($res['nom'] ?? '');

    $events[] = [
        'title' => $clientName,
        'start' => $res['date_debut'],
        // FullCalendar traite la date de fin comme exclusive (non incluse),
        // on ajoute donc 1 jour à date_fin pour que le jour de départ soit bien coloré.
        'end'   => date('Y-m-d', strtotime($res['date_fin'] . ' +1 day')),
        'allDay' => true,
        'extendedProps' => [
            'status' => $statusText,
            'colorClass' => $colorClass,
            'prix' => number_format($res['prix'], 2, ',', ' ') . ' €',
            'dates' => 'Du ' . date('d/m', strtotime($res['date_debut'])) . ' au ' . date('d/m/Y', strtotime($res['date_fin']))
        ],
        'url'   => 'reservationDetail.php?id_reservation=' . $res['id_reservation']
    ];
}

$tab = 'calendar';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/bootstrap5@6.1.11/index.global.min.js"></script>

<?php // style honteusement volé sur la page de payement d'une template premium ?>
<style>
:root {
    --fc-breezy-border: #e9ecef;
    --fc-breezy-muted-border: #f8f9fa;
    --fc-breezy-background: #ffffff;
    --fc-breezy-now: #0d6efd;
    --fc-breezy-primary: #24292f;
    --fc-breezy-secondary: #f6f8fa;
    --fc-breezy-faint: #f8f9fa;
}

#calendar {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    --fc-border-color: var(--fc-breezy-border);
    --fc-today-bg-color: #f3f8ff;
    --fc-page-bg-color: var(--fc-breezy-background);
}

/* Grille de calendrier style Breezy (bordures fines et claires) */
.fc-theme-bootstrap5 td, .fc-theme-bootstrap5 th {
    border-color: var(--fc-breezy-border) !important;
}

/* Boutons de navigation natifs du thème Bootstrap */
.fc .btn-primary {
    background-color: #ffffff !important;
    border-color: var(--fc-breezy-border) !important;
    color: #24292f !important;
    font-weight: 600;
    border-radius: 6px !important;
    padding: 6px 12px !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
}

.fc .btn-primary:hover {
    background-color: var(--fc-breezy-faint) !important;
    border-color: var(--fc-breezy-border) !important;
}

.fc .btn-primary:disabled {
    background-color: #f8f9fa !important;
    border-color: var(--fc-breezy-border) !important;
    color: #8c959f !important;
}

.fc .btn-primary.active, .fc .btn-primary:active {
    background-color: #24292f !important;
    border-color: #24292f !important;
    color: #ffffff !important;
}

.fc-col-header-cell {
    background-color: var(--fc-breezy-faint) !important;
    border-bottom: 2px solid var(--fc-breezy-border) !important;
    padding: 12px 0 !important;
}

.fc-col-header-cell-cushion {
    text-decoration: none !important;
    color: #495057 !important;
    font-weight: 700 !important;
    font-size: 0.75rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px;
}

.fc-daygrid-day-number {
    font-weight: 700;
    color: #6c757d;
    padding: 10px !important;
    font-size: 0.85rem;
    text-decoration: none !important;
}

.fc-day-today .fc-daygrid-day-number {
    color: var(--fc-breezy-now) !important;
}

.fc-event {
    cursor: pointer;
    border-radius: 6px !important; /* fc-breezy-KzJ */
    padding: 4px 8px !important;  /* fc-breezy-ZrE / fc-breezy-dl6 */
    font-weight: 600 !important;
    border: none !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04) !important; /* fc-breezy-qNs */
    transition: transform 0.15s ease, box-shadow 0.15s ease !important;
}

.fc-event:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}

.tooltip-inner {
    text-align: left !important;
    max-width: 250px;
    padding: 8px 12px;
}

.fc a {
    text-decoration: none !important;
}
</style>

<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 mb-0 fw-bold">Planning des séjours</h2>
        <a href="createReservation.php" class="btn btn-primary btn-sm fw-medium shadow-sm">Nouvelle réservation</a>
    </div>

    <!-- Conteneur principal avec thème natif Bootstrap 5 -->
    <div class="card border-0 shadow-sm mb-5" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
        <div class="card-body p-4">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        themeSystem: 'bootstrap5',
        
        initialView: 'dayGridMonth',
        locale: 'fr',
        firstDay: 1,
        editable: false,
        eventOverlap: false,
        height: 'auto',
        
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        buttonText: {
            today: "Aujourd'hui",
            month: "Mois",
            week: "Semaine"
        },
        
        events: <?= json_encode($events) ?>,
        
        displayEventTime: false, 

        eventDidMount: function(info) {
            // 1. Initialisation de l'infobulle (votre code existant)
            const props = info.event.extendedProps;
            const content = `
                <strong>${info.event.title}</strong><br>
                <small class="text-white-50">${props.dates}</small><br>
                <span class="badge bg-${props.colorClass} mt-1">${props.status}</span><br>
                <strong>Montant :</strong> ${props.prix}
            `;
            
            new bootstrap.Tooltip(info.el, {
                title: content,
                html: true,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });

            // 2. GESTION DES DEMI-JOURNÉES (Arrivées et Départs)
            // On récupère la largeur d'une case jour du calendrier
            const dayCell = document.querySelector('.fc-daygrid-day');
            if (dayCell) {
                // On calcule la moitié de la case
                const halfCellWidth = (dayCell.offsetWidth / 2) + 'px';
                
                // Si c'est le jour de début (Arrivée en fin de journée)
                if (info.isStart) {
                    info.el.style.marginLeft = halfCellWidth;
                    info.el.classList.add('is-checkin'); // Pour le CSS
                }
                
                // Si c'est le jour de fin (Départ le matin)
                if (info.isEnd) {
                    info.el.style.marginRight = halfCellWidth;
                    info.el.classList.add('is-checkout'); // Pour le CSS
                }
            }
        },
        
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        },

        // Si l'utilisateur redimensionne la fenêtre, on relance le rendu pour recalculer les marges
        windowResize: function() {
            calendar.render();
        }
    });
    calendar.render();
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>