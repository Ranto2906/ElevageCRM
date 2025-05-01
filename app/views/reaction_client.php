<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques Réactions Clients</title>
    <link rel="stylesheet" href="<?= Flight::get('flight.base_url') ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= Flight::get('flight.base_url') ?>/assets/js/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script>
        window.baseUrl = "<?= Flight::get('flight.base_url') ?>";
    </script>
    <script src="assets/js/reaction.js"></script>
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Statistiques des Réactions Clients</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>


    <!-- Filtre phase -->
    <form method="get" class="mb-4" id="phaseFilterForm">
        <div class="row align-items-end">
            <div class="col-md-4">
                <label for="phase" class="form-label">Filtrer par phase :</label>
                <select name="phase" id="phase" class="form-select">
                    <option value="">Toutes les phases</option>
                    <option value="1" <?= isset($selectedPhase) && $selectedPhase == 1 ? 'selected' : '' ?>>Avant l'échange</option>
                    <option value="2" <?= isset($selectedPhase) && $selectedPhase == 2 ? 'selected' : '' ?>>Pendant l'échange</option>
                    <option value="3" <?= isset($selectedPhase) && $selectedPhase == 3 ? 'selected' : '' ?>>Après l'échange</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </div>
        </div>
    </form>

    <!-- Import/Export CSV -->
    <label for="">Reactions</label>
    <div class="row mb-4">
        <div class="col-md-6">
            <form id="importReactionsBaseForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data" >
                <input type="file" name="csv_file" accept=".csv" required class="form-control">
                <button type="submit" class="btn btn-primary">Importer CSV</button>
            </form>
        </div>
        <div class="col-md-6 text-end">
            <form id="exportReactionsBasePdfForm">
                <button type="submit" class="btn btn-success">Exporter PDF</button>
            </form>
        </div>
    </div>
    
    <label for="">Effectuer des reactions</label>
    <div class="row mb-4">
        <div class="col-md-6">
            <form id="importReactionsForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data" >
                <input type="file" name="csv_file" accept=".csv" required class="form-control">
                <button type="submit" class="btn btn-primary">Importer CSV</button>
            </form>
        </div>
        <div class="col-md-6 text-end">
            <form id="exportReactionsPdfForm">
                <button type="submit" class="btn btn-success">Exporter PDF</button>
            </form>
        </div>
    </div>

     <!-- Bouton Effectuer Réaction -->
     <div class="mb-4 text-end">
        <a href="<?= htmlspecialchars(Flight::get('flight.base_url') . '/reaction-client/effectuer-reaction') ?>" class="btn btn-primary">
            Effectuer Réaction
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">Fréquence des réactions</div>
        <div class="card-body p-0">
            <table id="reactionsTable" class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Réaction</th>
                        <th>Phase</th>
                        <th>Coût</th>
                        <th>Nombre d'exécutions</th>
                        <th>Nombre de clients distincts</th>
                        <th>Voir impact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($frequencies as $reaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($reaction['description']) ?></td>
                            <td><?= htmlspecialchars($reaction['phase']) ?></td>
                            <td><?= htmlspecialchars($reaction['cout'] ?? '') ?></td>
                            <td><?= htmlspecialchars($reaction['frequence']) ?></td>
                            <td><?= htmlspecialchars($reaction['nb_clients']) ?></td>
                            <td>
                                <a href="reaction-impact?reaction_id=<?= $reaction['id'] ?>" class="btn btn-outline-primary btn-sm">Voir impact</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        $('#reactionsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
            }
        });
    });
    </script>

    <h2>Réactions les plus fréquentes par tranche d'âge</h2>
    <div class="row">
        <?php foreach ($byAgeRange as $range => $reactions): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Tranche d'âge : <?= htmlspecialchars($range) ?> ans</div>
                <div class="card-body">
                    <canvas id="chart-<?= $range ?>"></canvas>
                    <script>
                    const data_<?= str_replace('-', '_', $range) ?> = {
                        labels: <?= json_encode(array_column($reactions, 'description')) ?>,
                        datasets: [{
                            label: 'Fréquence',
                            data: <?= json_encode(array_column($reactions, 'frequence')) ?>,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)'
                        }]
                    };
                    new Chart(document.getElementById('chart-<?= $range ?>'), {
                        type: 'bar',
                        data: data_<?= str_replace('-', '_', $range) ?>,
                        options: {
                            plugins: {
                                legend: { display: false },
                                tooltip: { enabled: true }
                            },
                            scales: {
                                x: { ticks: { display: false } }
                            }
                        }
                    });
                    </script>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
