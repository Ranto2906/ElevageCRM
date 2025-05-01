<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Clients</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/client.js"></script>
    <script>
        window.baseUrl = "<?= Flight::get('flight.base_url') ?>";
    </script>
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Gestion des Clients</h1>

    <div id="client-message">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>

    <!-- Import/Export CSV -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form id="importClientsForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required class="form-control">
                <button type="submit" class="btn btn-primary">Importer CSV</button>
            </form>
        </div>
        <div class="col-md-6 text-end">
            <form id="exportClientsPdfForm">
                <button type="submit" class="btn btn-success">Exporter PDF</button>
            </form>
        </div>
    </div>

    <!-- Tableau des clients -->
    <div class="card mb-4">
        <div class="card-header">Liste des clients</div>
        <div class="card-body p-0">
            <table id="clientsTable" class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Âge</th>
                        <th>Type de cheveux</th>
                        <th>Préférences</th>
                        <th>Taux de fidélité (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nom']) ?></td>
                            <td><?= htmlspecialchars($c['prenom']) ?></td>
                            <td><?= htmlspecialchars($c['age']) ?></td>
                            <td><?= htmlspecialchars($c['TypeCheveux'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['preferences']) ?></td>
                            <td><?= isset($c['taux_fidelite']) ? $c['taux_fidelite'] : '0' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="card mb-4">
        <div class="card-header">Statistiques globales</div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4 mb-3">
                    <label for="gaugeReservation" class="form-label">Taux de réservation global</label>
                    <canvas id="gaugeReservation" width="180" height="120"></canvas>
                    <div><strong id="gaugeReservationValue"><?= $taux_reservation_global ?>%</strong></div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="gaugeArrivee" class="form-label">Taux d'arrivée au salon global</label>
                    <canvas id="gaugeArrivee" width="180" height="120"></canvas>
                    <div><strong id="gaugeArriveeValue"><?= $taux_arrivee_global ?>%</strong></div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="gaugeFidelite" class="form-label">Taux de fidélité global</label>
                    <canvas id="gaugeFidelite" width="180" height="120"></canvas>
                    <div><strong id="gaugeFideliteValue"><?= $taux_fidelite_global ?>%</strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Répartition par âge</div>
                <div class="card-body">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Types de cheveux</div>
                <div class="card-body">
                    <canvas id="typecheveuxChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    var table = $('#clientsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        }
    });
});

const ageData = {
    labels: <?= json_encode(array_keys($ages)) ?>,
    datasets: [{
        label: 'Nombre de clients',
        data: <?= json_encode(array_values($ages)) ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.5)'
    }]
};
const typecheveuxData = {
    labels: <?= json_encode(array_keys($types)) ?>,
    datasets: [{
        label: 'Nombre de clients',
        data: <?= json_encode(array_values($types)) ?>,
        backgroundColor: [
            'rgba(255, 99, 132, 0.5)',
            'rgba(54, 162, 235, 0.5)',
            'rgba(255, 206, 86, 0.5)',
            'rgba(75, 192, 192, 0.5)',
            'rgba(153, 102, 255, 0.5)',
            'rgba(255, 159, 64, 0.5)'
        ]
    }]
};
new Chart(document.getElementById('ageChart'), {type: 'bar', data: ageData});
new Chart(document.getElementById('typecheveuxChart'), {type: 'pie', data: typecheveuxData});

function renderGauge(ctx, value, color) {
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [value, 100-value],
                backgroundColor: [color, '#e9ecef'],
                borderWidth: 0
            }]
        },
        options: {
            rotation: -90,
            circumference: 180,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
}
document.addEventListener('DOMContentLoaded', function() {
    renderGauge(document.getElementById('gaugeReservation').getContext('2d'), parseFloat(document.getElementById('gaugeReservationValue').textContent), '#3578e5');
    renderGauge(document.getElementById('gaugeArrivee').getContext('2d'), parseFloat(document.getElementById('gaugeArriveeValue').textContent), '#28a745');
    renderGauge(document.getElementById('gaugeFidelite').getContext('2d'), parseFloat(document.getElementById('gaugeFideliteValue').textContent), '#ffc107');
});
</script>
</body>
</html>
