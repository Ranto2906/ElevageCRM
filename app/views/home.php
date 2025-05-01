<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/home.css" rel="stylesheet">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/home.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="#">Budget Monitoring</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#createBudgetElement">Create Budget Element</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#importBudgetElements">Import Budget Elements</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#importTransactions">Import Transactions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#transactions">Transactions</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link btn btn-danger text-white" href="logout">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="text-center">Home</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-info">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <section id="transactions" class="mt-5 section">
            <h3>Transactions</h3>
            <form id="filterForm" class="form-inline mb-3">
                <div class="form-group">
                    <label for="exercise" class="mr-2">Select Exercise</label>
                    <select name="exercise" id="exercise" class="form-control">
                        <?php foreach ($exercises as $exercise): ?>
                            <option value="<?= $exercise['exercise_id'] ?>" <?= $selectedExercise && $selectedExercise['exercise_id'] == $exercise['exercise_id'] ? 'selected' : '' ?>><?= $exercise['start_date'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit filter" class="btn btn-primary ml-2">Filter</button>
            </form>
            <?php if ($selectedExercise): ?>
                <form id="updateTransactionsForm">
                    <input type="hidden" name="exercise_id" value="<?= $selectedExercise['exercise_id'] ?>">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Rubric</th>
                                <?php for ($i = 1; $i <= $selectedExercise['nb_period']; $i++): ?>
                                    <th>Period <?= $i ?> (Prevision)</th>
                                    <th>Period <?= $i ?> (Realisation)</th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $budgetElementId => $transaction): ?>
                                <tr>
                                    <td><?= $transaction['category'] ?></td>
                                    <td><?= $transaction['description'] ?></td>
                                    <?php for ($i = 1; $i <= $selectedExercise['nb_period']; $i++): ?>
                                        <td>
                                            <?php if ($transaction['periods'][$i]['statusP'] == 0 || $transaction['periods'][$i]['prevision']==0): ?>
                                                <input type="number" step="0.01" name="transactions[<?= $budgetElementId ?>][<?= $i ?>][1]" value="<?= $transaction['periods'][$i]['prevision'] ?>" class="form-control">
                                            <?php else: ?>
                                                <?= $transaction['periods'][$i]['prevision'] ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($transaction['periods'][$i]['statusR'] == 0 || $transaction['periods'][$i]['realisation']==0): ?>
                                                <input type="number" step="0.01" name="transactions[<?= $budgetElementId ?>][<?= $i ?>][2]" value="<?= $transaction['periods'][$i]['realisation'] ?>" class="form-control">
                                            <?php else: ?>
                                                <?= $transaction['periods'][$i]['realisation'] ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Update Transactions</button>
                </form>
                <button id="exportCsv" class="btn btn-secondary mt-3">Export CSV</button>
                <button id="exportPdf" class="btn btn-secondary mt-3">Export PDF</button>
            <?php endif; ?>
        </section>

        <section id="crudBudgetElement"  class="mt-5 section">
            <h3>CRUD Budget Elements</h3>
            <div class="row">
                <div class="col-md-6">
                    <form id="createBudgetElementForm">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['category_id'] ?>"><?= $category['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type_id">Type</label>
                            <select name="type_id" id="type_id" class="form-control" required>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= $type['type_id'] ?>"><?= $type['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" required></textarea>
                        </div>
                        <button type="button" id="createBudgetElement" class="btn btn-primary">Create</button>
                    </form>
                </div>
            </div>
            <div class="mt-4">
                <h4>Manage Budget Elements</h4>
                <table class="table table-bordered" id="budgetElementTable">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($budgetElements as $element): ?>
                            <tr data-id="<?= $element['budget_element_id'] ?>">
                                <td>
                                    <select class="form-control category-id">
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['category_id'] ?>" <?= $category['category_id'] == $element['category_id'] ? 'selected' : '' ?>><?= $category['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control type-id">
                                        <?php foreach ($types as $type): ?>
                                            <option value="<?= $type['type_id'] ?>" <?= $type['type_id'] == $element['type_id'] ? 'selected' : '' ?>><?= $type['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control description" value="<?= $element['description'] ?>">
                                </td>
                                <td>
                                    <button class="btn btn-success update-budget-element">Update</button>
                                    <button class="btn btn-danger delete-budget-element">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="importBudgetElements" class="mt-5 section">
            <h3>Import Budget Elements</h3>
            <form action="home/importBudgetElementsCsv" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="budget_elements_csv">Upload CSV</label>
                    <input type="file" name="budget_elements_csv" id="budget_elements_csv" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
            </form>
        </section>

        <section id="importTransactions" class="mt-5 section">
            <h3>Import Transactions</h3>
            <form action="home/importTransactionsCsv" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="transactions_csv">Upload CSV</label>
                    <input type="file" name="transactions_csv" id="transactions_csv" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
            </form>
        </section>

    </div>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>