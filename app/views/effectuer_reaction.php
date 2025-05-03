<!-- filepath: c:\xampp\htdocs\L2\BarberShop\app\views\effectuer_reaction.php -->

<!DOCTYPE html>
<html lang="fr">
<head>
<?php include __DIR__ . '/layouts/header.php'; ?>
<?php include __DIR__ . '/layouts/sidebar.php'; ?>
<?php include __DIR__ . '/layouts/topbar.php'; ?>

    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?= Flight::get('flight.base_url') ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="<?= Flight::get('flight.base_url') ?>/assets/js/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        window.baseUrl = "<?= Flight::get('flight.base_url') ?>";
    </script>
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Effectuer une Réaction</h1>

    <table id="actionsTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Description de l'Action</th>
                <th>Client</th>
                <th>Date de l'Action</th>
                <th>Réaction</th>
                <th>Date de Réaction</th>
                <th>Valider</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($actions as $action): ?>
                <tr>
                    <form method="post" class="effectuer-reaction-form">
                        <td><?= htmlspecialchars($action['description']) ?></td>
                        <td><?= htmlspecialchars($action['nom'] . ' ' . $action['prenom']) ?></td>
                        <td><?= htmlspecialchars($action['date_action']) ?></td>
                        <td>
                            <select name="reaction" class="form-select" required>
                                <option value="">Sélectionnez une réaction</option>
                                <?php foreach ($reactions as $reaction): ?>
                                    <option value="<?= $reaction['id'] ?>"><?= htmlspecialchars($reaction['description']) ?> (<?= htmlspecialchars($reaction['cout']) ?> Ar)</option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="date" name="reaction_date" class="form-control" required>
                        </td>
                        <td>
                            <input type="hidden" name="action_id" value="<?= $action['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-valider-reaction">Valider</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div id="responseMessage"></div>
</div>

<script>
    $(document).ready(function () {
        // Initialiser la DataTable
        $('#actionsTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
            }
        });

        // Gestion AJAX pour valider une réaction
        $('.effectuer-reaction-form').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const formData = form.serialize();

            $.ajax({
                url: window.baseUrl + '/reaction-client/valider-reaction',
                type: 'POST',
                data: formData,
                success: function (response) {
                    $('#responseMessage').html('<div class="alert alert-success">Réaction effectuée avec succès.</div>');
                },
                error: function () {
                    $('#responseMessage').html('<div class="alert alert-danger">Erreur lors de l\'exécution de la réaction.</div>');
                }
            });
        });
    });
</script>
</body>
</html>