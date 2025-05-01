<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Impact de la réaction</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Impact de la réaction</h1>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($reaction): ?>
        <h2><?= htmlspecialchars($reaction['description']) ?></h2>
        <div class="card mb-4">
            <!-- <?php foreach ($executions as $exec): ?>
            <p> Date Precedent Start : <?= $exec['stats_avant']['start'] ?> </p>
            <p> Date Precedent Fin : <?= $exec['stats_avant']['end'] ?> </p>
            <p> Date Suivant Start : <?= $exec['stats_apres']['start'] ?> </p>
            <p> Date Suivant Fin : <?= $exec['stats_apres']['end'] ?> </p>
            <?php endforeach; ?> -->
            
            <div class="card-header">Toutes les exécutions de cette réaction</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Date d'exécution</th>
                            <th>Bénéfice avant </th>
                            <th>Bénéfice après </th>
                            <th> Pourcentage de Gain (%) </th>
                            <th>Taux réservation avant (%)</th>
                            <th>Taux réservation après (%)</th>
                            <th>Taux arrivée avant (%)</th>
                            <th>Taux arrivée après (%)</th>
                            <th>Taux fidélité avant (%)</th>
                            <th>Taux fidélité après (%)</th>
                            <th>Client revenu ?</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($executions as $exec): ?>
                        <tr>
                            <td><?= htmlspecialchars(($exec['nom'] ?? '') . ' ' . ($exec['prenom'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($exec['date_reaction']) ?></td>
                            <td><?= $exec['stats_avant']['benefice'] ?></td>
                            <td><?= $exec['stats_apres']['benefice'] ?></td>
                            <td><?= number_format((($exec['stats_avant']['benefice']/$exec['stats_apres']['benefice'])*100), 2, '.', ',') ?></td>
                            <td><?= $exec['stats_avant']['taux_reservation'] ?></td>
                            <td><?= $exec['stats_apres']['taux_reservation'] ?></td>
                            <td><?= $exec['stats_avant']['taux_arrivee'] ?></td>
                            <td><?= $exec['stats_apres']['taux_arrivee'] ?></td>
                            <td><?= $exec['stats_avant']['taux_fidelite'] ?></td>
                            <td><?= $exec['stats_apres']['taux_fidelite'] ?></td>
                            <td><?= $exec['client_revenu'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    <a href="reaction-client" class="btn btn-secondary">Retour aux réactions</a>
</div>
</body>
</html>
