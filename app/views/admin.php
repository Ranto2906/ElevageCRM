<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/admin.js"></script>
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
                    <a class="nav-link" href="#budgetMonitoring">Budget Monitoring</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#importTransactions">Import Transactions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#pendingTransactions">Pending Transactions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#crudDepartment">CRUD Department</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#crudType">CRUD Type</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#crudExercise">CRUD Exercise</a>
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
        <h1 class="text-center">Admin Dashboard</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-info">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <section id="budgetMonitoring" class="mt-5 section">
            <h3>Budget Monitoring</h3>
            <form id="filterForm" class="form-inline mb-3">
                <div class="form-group">
                    <label for="exercise" class="mr-2">Select Exercise</label>
                    <select name="exercise" id="exercise" class="form-control">
                        <?php foreach ($exercises as $exercise): ?>
                            <option value="<?= $exercise['exercise_id'] ?>" <?= $selectedExercise == $exercise['exercise_id'] ? 'selected' : '' ?>><?= $exercise['start_date'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary ml-2">Filter</button>
            </form>
            <div id="budgetMonitoringContent">
                <!-- Content will be updated via AJAX -->
            </div>
            <button id="exportCsv" class="btn btn-secondary mt-3">Export CSV</button>
            <button id="exportPdf" class="btn btn-secondary mt-3">Export PDF</button>
            <button id="exportListsPdf" class="btn btn-secondary mt-3">Export Lists PDF</button>
        </section>

        <section id="importTransactions" class="mt-5 section">
            <h3>Import Transactions</h3>
            <form action="admin/importTransactionsCsv" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="transactions_csv">Upload CSV</label>
                    <input type="file" name="transactions_csv" id="transactions_csv" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
            </form>
        </section>


        <section id="pendingTransactions" class="mt-5 section">
            <h3>Pending Transactions</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Rubrique</th>
                        <th>Exercise</th>
                        <th>Periode</th>
                        <th>Amount</th>
                        <th>Priority</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr data-id="<?= $transaction['transaction_id'] ?>">
                            <td><?= $transaction['description'] ?></td>
                            <td><?= $transaction['start_date'] ?></td>
                            <td><?= $transaction['period_num'] ?></td>
                            <td><?= $transaction['amount'] ?></td>
                            <td><?= $transaction['name'] ?></td>
                            <td>
                                <button class="btn btn-success confirm-transaction">Confirm</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary"><a href="reaction-client/liste-reaction-pending" style="color: white; text-decoration:none;">Pending CRM reactions</a></button>
        </section>

        <section id="crudDepartment" class="mt-5 section">
            <h3>CRUD Department</h3>
            <div class="row">
                <div class="col-md-6">
                    <form id="createDepartmentForm">
                        <div class="form-group">
                            <label for="department">Department Name</label>
                            <input type="text" name="name" id="department" class="form-control" required>
                        </div>
                        <button type="button" id="createDepartment" class="btn btn-primary">Create</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="admin/importDepartmentsCsv" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="departmentsCsv">CSV File</label>
                            <input type="file" name="csv_file" id="departmentsCsv" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Import CSV</button>
                    </form>
                </div>
            </div>
            <div class="mt-4">
                <h4>Manage Departments</h4>
                <table class="table table-bordered" id="departmentTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $department): ?>
                            <tr data-id="<?= $department['department_id'] ?>">
                                <td><?= $department['department_id'] ?></td>
                                <td>
                                    <input type="text" class="form-control department-name" value="<?= $department['name'] ?>">
                                </td>
                                <td>
                                    <button class="btn btn-success update-department">Update</button>
                                    <button class="btn btn-danger delete-department">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="crudType" class="mt-5 section">
            <h3>CRUD Type</h3>
            <div class="row">
                <div class="col-md-6">
                    <form id="createTypeForm">
                        <div class="form-group">
                            <label for="type">Type Name</label>
                            <input type="text" name="name" id="type" class="form-control" required>
                        </div>
                        <button type="button" id="createType" class="btn btn-primary">Create</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="admin/importTypesCsv" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="typesCsv">CSV File</label>
                            <input type="file" name="csv_file" id="typesCsv" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Import CSV</button>
                    </form>
                </div>
            </div>
            <div class="mt-4">
                <h4>Manage Types</h4>
                <table class="table table-bordered" id="typeTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($types as $type): ?>
                            <tr data-id="<?= $type['type_id'] ?>">
                                <td><?= $type['type_id'] ?></td>
                                <td>
                                    <input type="text" class="form-control type-name" value="<?= $type['name'] ?>">
                                </td>
                                <td>
                                    <button class="btn btn-success update-type">Update</button>
                                    <button class="btn btn-danger delete-type">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="crudExercise" class="mt-5 section">
            <h3>CRUD Exercise</h3>
            <div class="row">
                <div class="col-md-6">
                    <form id="createExerciseForm">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="nb_period">Number of Periods</label>
                            <input type="number" name="nb_period" id="nb_period" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="start_balance">Start Balance</label>
                            <input type="number" step="0.01" name="start_balance" id="start_balance" class="form-control" required>
                        </div>
                        <button type="button" id="createExercise" class="btn btn-primary">Create</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="admin/importExercisesCsv" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="exercisesCsv">CSV File</label>
                            <input type="file" name="csv_file" id="exercisesCsv" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Import CSV</button>
                    </form>
                </div>
            </div>
            <div class="mt-4">
                <h4>Manage Exercises</h4>
                <table class="table table-bordered" id="exerciseTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Start Date</th>
                            <th>Number of Periods</th>
                            <th>Start Balance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exercises as $exercise): ?>
                            <tr data-id="<?= $exercise['exercise_id'] ?>">
                                <td><?= $exercise['exercise_id'] ?></td>
                                <td>
                                    <input type="date" class="form-control exercise-start-date" value="<?= $exercise['start_date'] ?>">
                                </td>
                                <td>
                                    <input type="number" class="form-control exercise-nb-period" value="<?= $exercise['nb_period'] ?>">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control exercise-start-balance" value="<?= $exercise['start_balance'] ?>">
                                </td>
                                <td>
                                    <button class="btn btn-success update-exercise">Update</button>
                                    <button class="btn btn-danger delete-exercise">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>