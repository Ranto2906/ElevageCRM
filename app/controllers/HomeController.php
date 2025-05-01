<?php

namespace app\controllers;

use Flight;
use FPDF;

class HomeController {

    public function __construct() {
    }

    public function getHome() {
        if (!isset($_SESSION['department_id'])) {
            Flight::redirect('/');
            exit;
        }
        $generaliserModel = Flight::generaliserModel();
        $departments = $generaliserModel->getTableData('department', ['department_is_deleted' => 0]);
        $categories = $generaliserModel->getTableData('category', []);
        $types = $generaliserModel->getTableData('type', ['type_is_deleted' => 0]);
        $exercises = $generaliserModel->getTableData('exercise', ['exercise_is_deleted' => 0]);
        $budgetElements = $generaliserModel->getTableData('budget_element', ['department_id' => $_SESSION['department_id'], 'budget_element_is_deleted' => 0]);
        $priorities = $generaliserModel->getTableData('priority', []);
        $selectedExerciseId = Flight::request()->query->exercise ?? null;
        $selectedExercise = $selectedExerciseId ? $generaliserModel->getTableData('exercise', ['exercise_id' => $selectedExerciseId ,'exercise_is_deleted'=>0])[0] : null;
        $transactions = $selectedExercise ? $this->getTransactions($selectedExerciseId) : [];
        $data = [
            'departments' => $departments,
            'categories' => $categories,
            'types' => $types,
            'exercises' => $exercises,
            'budgetElements' => $budgetElements,
            'priorities' => $priorities,
            'selectedExercise' => $selectedExercise,
            'transactions' => $transactions,
            'message' => Flight::get('message')
        ];
        Flight::render('home', $data);
    }

    private function getTransactions($exerciseId) {
        $generaliserModel = Flight::generaliserModel();
        $join = [
            ['budget_element', [['transaction.budget_element_id', 'budget_element.budget_element_id']]],
            ['category', [['budget_element.category_id', 'category.category_id']]]
        ];
        $conditions = [
            'transaction.exercise_id' => $exerciseId,
            'budget_element.department_id' => $_SESSION['department_id'],
            'budget_element.budget_element_is_deleted' => 0
        ];
        $transactions = $generaliserModel->getTableData('transaction', $conditions, [], $join);
        $groupedTransactions = [];

        foreach ($transactions as $transaction) {
            $budgetElementId = $transaction['budget_element_id'];
            $periodNum = $transaction['period_num'];
            $nature = $transaction['nature'];

            if (!isset($groupedTransactions[$budgetElementId])) {
                $groupedTransactions[$budgetElementId] = [
                    'description' => $transaction['description'],
                    'category' => $transaction['name'], 
                    'periods' => []
                ];
            }

            if (!isset($groupedTransactions[$budgetElementId]['periods'][$periodNum])) {
                $groupedTransactions[$budgetElementId]['periods'][$periodNum] = [
                    'prevision' => 0,
                    'realisation' => 0,
                ];
            }

            if ($nature == 1) {
                $groupedTransactions[$budgetElementId]['periods'][$periodNum]['prevision'] = $transaction['amount'];
                $groupedTransactions[$budgetElementId]['periods'][$periodNum]['statusP'] = $transaction['status'];
            } else {
                $groupedTransactions[$budgetElementId]['periods'][$periodNum]['realisation'] = $transaction['amount'];
                $groupedTransactions[$budgetElementId]['periods'][$periodNum]['statusR'] = $transaction['status'];
            }
        }

        return $groupedTransactions;
    }

    public function insertBudgetElement() {
        $generaliserModel = Flight::generaliserModel();
        $departmentId = $_SESSION['department_id'];
        $categoryId = Flight::request()->data->category_id;
        $typeId = Flight::request()->data->type_id;
        $description = Flight::request()->data->description;
        $data = [
            'department_id' => $departmentId,
            'category_id' => $categoryId,
            'type_id' => $typeId,
            'description' => $description
        ];
        $result = $generaliserModel->insererDonnee('budget_element', $data);
        if ($result['status'] === 'success') {
            $budgetElementId = $generaliserModel->getLastInsertedId('budget_element', 'budget_element_id');
            $exercises = $generaliserModel->getTableData('exercise', ['exercise_is_deleted' => 0]);
            foreach ($exercises as $exercise) {
                for ($i = 1; $i <= $exercise['nb_period']; $i++) {
                    $transactionData = [
                        'nature' => 1, 
                        'exercise_id' => $exercise['exercise_id'],
                        'budget_element_id' => $budgetElementId['last_id'],
                        'period_num' => $i,
                        'amount' => 0,
                        'status' => 1, 
                        'priority_id' => 1 
                    ];
                    $generaliserModel->insererDonnee('transaction', $transactionData);
                    $transactionData['nature'] = 2; 
                    $generaliserModel->insererDonnee('transaction', $transactionData);
                }
            }
            Flight::json(['success' => true, 'message' => 'Budget element created successfully.']);
        } else {
            Flight::json(['success' => false, 'message' => 'Error creating budget element: ' . $result['message']]);
        }
    }
    
    public function updateBudgetElement() {
        $data = Flight::request()->data;
        Flight::generaliserModel()->updateTableData('budget_element', [
            'department_id' => $_SESSION['department_id'],
            'category_id' => $data->category_id,
            'type_id' => $data->type_id,
            'description' => $data->description
        ], ['budget_element_id' => $data->id]);
        Flight::json(['success' => true, 'message' => 'Budget element updated successfully.']);
    }
    
    public function deleteBudgetElement() {
        $id = Flight::request()->data->id;
        Flight::generaliserModel()->updateTableData('budget_element', ['budget_element_is_deleted' => 1], ['budget_element_id' => $id]);
        Flight::json(['success' => true, 'message' => 'Budget element deleted successfully.']);
    }

    public function updateTransactions() {
        $generaliserModel = Flight::generaliserModel();
        $transactions = Flight::request()->data->transactions;

        foreach ($transactions as $budgetElementId => $periods) {
            foreach ($periods as $periodNum => $amounts) {
                foreach ($amounts as $nature => $amount) {
                    $transactionData = [
                        'amount' => $amount,
                    ];
                    if ($amount == 0) {
                        $transactionData['status'] = 1;
                    } else {
                        $transactionData['status'] = 0;
                    }

                    $generaliserModel->updateTableData('transaction', $transactionData, [
                        'budget_element_id' => $budgetElementId,
                        'exercise_id' => Flight::request()->data->exercise_id,
                        'period_num' => $periodNum,
                        'nature' => $nature
                    ]);
                }
            }
        }

        Flight::set('message', 'Transactions updated successfully.');
        Flight::redirect('home');
    }

    public function getPeriods() {
        $exerciseId = Flight::request()->query->exercise_id;
        $generaliserModel = Flight::generaliserModel();
        $exercise = $generaliserModel->getTableData('exercise', ['exercise_id' => $exerciseId, 'exercise_is_deleted'=>0])[0];
        $nbPeriod = $exercise['nb_period'];

        $options = '';
        for ($i = 1; $i <= $nbPeriod; $i++) {
            $options .= "<option value=\"$i\">Period $i</option>";
        }

        echo $options;
    }

    public function importBudgetElementsCsv() {
        if (isset($_FILES['budget_elements_csv']) && $_FILES['budget_elements_csv']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['budget_elements_csv']['tmp_name'];
            $generaliserModel = Flight::generaliserModel();
            $result = $generaliserModel->importCsv($fileTmpPath, ';');

            if ($result['status'] === 'success') {
                $data = $result['data'];
                foreach ($data as $row) {
                    $budgetElementData = [
                        'department_id' => $_SESSION['department_id'],
                        'category_id' => $row['category_id'],
                        'type_id' => $row['type_id'],
                        'description' => $row['description']
                    ];
                    $generaliserModel->insererDonnee('budget_element', $budgetElementData);
                    if ($result['status'] === 'success') {
                        $budgetElementId = $generaliserModel->getLastInsertedId('budget_element','budget_element_id');
                        $exercises = $generaliserModel->getTableData('exercise', ['exercise_is_deleted' => 0]);
                        foreach ($exercises as $exercise) {
                            for ($i = 1; $i <= $exercise['nb_period']; $i++) {
                                $transactionData = [
                                    'nature' => 1, // Prevision
                                    'exercise_id' => $exercise['exercise_id'],
                                    'budget_element_id' => $budgetElementId['last_id'],
                                    'period_num' => $i,
                                    'amount' => 0,
                                    'status' => 1, // Confirmed
                                    'priority_id' => 1 // Default priority
                                ];
                                $generaliserModel->insererDonnee('transaction', $transactionData);
            
                                $transactionData['nature'] = 2; // Realisation
                                $generaliserModel->insererDonnee('transaction', $transactionData);
                            }
                        }
                    } 
                    
                }
                Flight::set('message', 'Budget elements imported successfully.');
            } else {
                Flight::set('message', $result['message']);
            }
        } else {
            Flight::set('message', 'Error uploading file.');
        }
        Flight::redirect('home');
    }

    public function importTransactionsCsv() {
        if (isset($_FILES['transactions_csv']) && $_FILES['transactions_csv']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['transactions_csv']['tmp_name'];
            $generaliserModel = Flight::generaliserModel();
            $result = $generaliserModel->importCsv($fileTmpPath, ';');

            if ($result['status'] === 'success') {
                $data = $result['data'];
                foreach ($data as $row) {
                    $transactionData = [
                        'amount' => $row['amount'],
                        'status' => 0
                    ];
                    $conditions = [
                        'nature' => $row['nature'],
                        'exercise_id' => $row['exercise_id'],
                        'budget_element_id' => $row['budget_element_id'],
                        'period_num' => $row['period_num']
                    ];
                    $generaliserModel->updateTableData('transaction', $transactionData, $conditions);
                }
                Flight::set('message', 'Transactions imported successfully.');
            } else {
                Flight::set('message', $result['message']);
            }
        } else {
            Flight::set('message', 'Error uploading file.');
        }
        Flight::redirect('home');
    }

    public function exportTransactionsPdf() {
        $exerciseId = Flight::request()->query->exercise;
        $transactions = $this->getTransactions($exerciseId);
        $exercise = Flight::generaliserModel()->getTableData('exercise', ['exercise_id' => $exerciseId, 'exercise_is_deleted'=>0])[0];
        $exerciseYear = date('Y', strtotime($exercise['start_date']));
        $budgetElementId = array_key_first($transactions);
        $departmentId = Flight::generaliserModel()->getTableData('budget_element', ['budget_element_id' => $budgetElementId, 'budget_element_is_deleted'=>0])[0]['department_id'];
        $departmentName = Flight::generaliserModel()->getTableData('department', ['department_id' => $departmentId, 'department_is_deleted'=>0])[0]['name'];
        require('assets/fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 10, 'Transactions - ' . $exerciseYear, 0, 1, 'C');
        $pdf->Cell(0, 10, 'Department: ' . $departmentName, 0, 1, 'C');
        $pdf->Ln(10);
        $numColumns = 2 + 2 * $exercise['nb_period']; 
        $maxWidth = 190; 
        $cellWidth = $maxWidth / $numColumns;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($cellWidth, 10, 'Category', 1, 0, 'C');
        $pdf->Cell($cellWidth, 10, 'Rubric', 1, 0, 'C');
        for ($i = 1; $i <= $exercise['nb_period']; $i++) {
            $pdf->Cell($cellWidth, 10, "P$i (P)", 1, 0, 'C');
            $pdf->Cell($cellWidth, 10, "P$i (R)", 1, 0, 'C');
        }
        $pdf->Ln();
        foreach ($transactions as $budgetElementId => $transaction) {
            $pdf->Cell($cellWidth, 10, $transaction['category'], 1);
            $pdf->Cell($cellWidth, 10, $transaction['description'], 1);
            for ($i = 1; $i <= $exercise['nb_period']; $i++) {
                if ($transaction['periods'][$i]['statusP'] == 0 || $transaction['periods'][$i]['prevision'] == 0) {
                    $pdf->Cell($cellWidth, 10, '', 1, 0, 'C');
                } else {
                    $pdf->Cell($cellWidth, 10, $transaction['periods'][$i]['prevision'], 1, 0, 'R');
                }
                if ($transaction['periods'][$i]['statusR'] == 0 || $transaction['periods'][$i]['realisation'] == 0) {
                    $pdf->Cell($cellWidth, 10, '', 1, 0, 'C');
                } else {
                    $pdf->Cell($cellWidth, 10, $transaction['periods'][$i]['realisation'], 1, 0, 'R');
                }
            }
            $pdf->Ln();
        }
        $pdf->Ln(20); 
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 10, '(P) Prevision    (R) Realisation ', 0, 1, 'C');

        $pdf->Output('D', "transactions_{$exerciseYear}.pdf");
        exit;
    }

    public function exportTransactionsCsv() {
        $exerciseId = Flight::request()->query->exercise;
        $transactions = $this->getTransactions($exerciseId);
        $exercise = Flight::generaliserModel()->getTableData('exercise', ['exercise_id' => $exerciseId, 'exercise_is_deleted'=>0])[0];
        $exerciseYear = date('Y', strtotime($exercise['start_date']));
        $budgetElementId = array_key_first($transactions);
        $departmentId = Flight::generaliserModel()->getTableData('budget_element', ['budget_element_id' => $budgetElementId, 'budget_element_is_deleted'=>0])[0]['department_id'];
        $departmentName = Flight::generaliserModel()->getTableData('department', ['department_id' => $departmentId,'department_is_deleted'=>0])[0]['name'];
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="transactions_' . $exerciseYear . '.csv"');
        $output = fopen('php://output', 'w');
        $headers = ['Category', 'Rubric'];
        for ($i = 1; $i <= $exercise['nb_period']; $i++) {
            $headers[] = "Period $i (Prevision)";
            $headers[] = "Period $i (Realisation)";
        }
        fputcsv($output, $headers, ';');
        foreach ($transactions as $budgetElementId => $transaction) {
            $row = [$transaction['category'], $transaction['description']];
            for ($i = 1; $i <= $exercise['nb_period']; $i++) {
                if ($transaction['periods'][$i]['statusP'] == 0 || $transaction['periods'][$i]['prevision'] == 0) {
                    $row[] = '';
                } else {
                    $row[] = $transaction['periods'][$i]['prevision'];
                }
                if ($transaction['periods'][$i]['statusR'] == 0 || $transaction['periods'][$i]['realisation'] == 0) {
                    $row[] = '';
                } else {
                    $row[] = $transaction['periods'][$i]['realisation'];
                }
            }
            fputcsv($output, $row, ';');
        }

        fclose($output);
        exit;
    }
}