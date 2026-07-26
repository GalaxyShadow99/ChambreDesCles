<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/reservationUtils.php';
require_once __DIR__ . '/utils/clientUtils.php';

$data = getBestClient($pdo);

// Préparation des données pour le graphique Donut Chart.js
$labels = ["Airbnb", "Booking.com", "Direct (sans plateforme)"];

$nbResas = [];
$nbResas[0] = getReservationCountByPlateforme($pdo, "airbnb");
$nbResas[1] = getReservationCountByPlateforme($pdo, "booking");
$nbResas[2] = getReservationCountByPlateforme($pdo, "sans plateforme");

$totalReservations = array_sum($nbResas);
$bestChannel = "N/A";
if ($totalReservations > 0) {
    $maxIndex = array_search(max($nbResas), $nbResas);
    $bestChannel = $labels[$maxIndex];
}

$tab = 'stats';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>

<!-- CDNs requis : Chart.js et Datalabels -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<style>
/* Style épuré et aligné sur la charte existante du site */
.stats-card {
    background-color: #ffffff;
    border: 1px solid #d0d7de !important;
    border-radius: 6px !important;
}

.kpi-box {
    background-color: #ffffff;
    border: 1px solid #d0d7de !important;
    border-radius: 6px;
    padding: 1.25rem;
}
</style>

<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container mt-4 pb-5">
    
    <!-- En-tête simple -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h5 mb-0 fw-bold">Activité & Statistiques</h2>
        </div>
        <a href="index.php" class="btn btn-primary btn-sm fw-medium">Retour à l'accueil</a>
    </div>

    <!-- Section Indicateurs (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-box d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Volume de réservations</span>
                    <h4 class="fw-bold mb-0 text-dark mt-1"><?= $totalReservations ?> séjours</h4>
                </div>
                <div class="fs-2 text-primary opacity-50"><i class="bi bi-calendar-check"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-box d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Canal principal</span>
                    <h4 class="fw-bold mb-0 text-success mt-1"><?= $bestChannel ?></h4>
                </div>
                <div class="fs-2 text-success opacity-50"><i class="bi bi-graph-up-arrow"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-box d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Clients étoiles</span>
                    <h4 class="fw-bold mb-0 text-warning mt-1"><?= count($data) ?> fidèles</h4>
                </div>
                <div class="fs-2 text-warning opacity-50"><i class="bi bi-stars"></i></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Tableau des meilleurs clients -->
        <div class="col-lg-7">
            <div class="card stats-card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h3 class="h6 mb-0 fw-bold text-dark">
                        <i class="bi bi-people-fill text-primary me-2"></i> Clients les plus fidèles (Ex aequo)
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Prénom & Nom</th>
                                    <th class="text-center pe-4" style="width: 200px;">Nombre de séjours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $client): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold">
                                            <a href="clientDetail.php?id_client=<?= $client['id_client'] ?>" class="text-decoration-none text-primary hover-underline">
                                                <?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?>
                                            </a>
                                        </td>
                                        <td class="text-center pe-4">
                                            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill fs-7">
                                                <?= $client['NbResa'] ?> réservations
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique Donut -->
        <div class="col-lg-5">
            <div class="card stats-card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h3 class="h6 mb-0 fw-bold text-dark">
                        <i class="bi bi-pie-chart-fill text-success me-2"></i> Canaux d'acquisition
                    </h3>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                    <div style="width: 100%; max-width: 250px; margin: auto;">
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('donutChart').getContext('2d');
    Chart.register(ChartDataLabels);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Réservations',
                data: <?= json_encode($nbResas) ?>,
                backgroundColor: [
                    '#FF5A5F', // Airbnb
                    '#003580', // Booking.com
                    '#198754'  // Direct
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            weight: '600',
                            size: 11
                        }
                    }
                },
                datalabels: {
                    formatter: (value, ctx) => {
                        let sum = 0;
                        let dataArr = ctx.chart.data.datasets[0].data;
                        dataArr.map(data => {
                            sum += data;
                        });
                        if (sum === 0) return '';
                        let percentage = (value * 100 / sum).toFixed(0) + "%";
                        return (value * 100 / sum) > 5 ? percentage : '';
                    },
                    color: '#ffffff',
                    font: {
                        weight: 'bold',
                        size: 13
                    },
                    anchor: 'center',
                    align: 'center'
                }
            },
            cutout: '60%'
        }
    });
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
