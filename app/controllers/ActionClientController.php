<?php
namespace app\controllers;
use Flight;

class ActionClientController {
    public function showActionStats() {
        $phase = (isset($_GET['phase']) && $_GET['phase'] !== '') ? (int)$_GET['phase'] : null;
        $actionModel = Flight::actionModel();
        $frequencies = $actionModel->getActionFrequencies($phase);
        $byAgeRange = $actionModel->getActionFrequenciesByAgeRange($phase);

        Flight::render('action_client', [
            'frequencies' => $frequencies,
            'byAgeRange' => $byAgeRange,
            'selectedPhase' => $phase,
        ]);
    }

    public function importActionsEffectueesCsv()
    {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['csv_file']['tmp_name'];
            $generaliserModel = Flight::generaliserModel();
            $result = $generaliserModel->importCsv($fileTmpPath, ',');

            if ($result['status'] === 'success') {
                $data = $result['data'];
                foreach ($data as $row) {
                    // CSV doit contenir : action_id, user_id, date_action (optionnel)
                    $actionEffectuee = [
                        'action_id' => $row['action_id'] ?? null,
                        'user_id' => $row['user_id'] ?? null,
                        'date_action' => $row['date_action'] ?? null
                    ];
                    $generaliserModel->insererDonnee('action_effectue', $actionEffectuee);
                }
                Flight::set('message', 'Import CSV terminé.');
            } else {
                Flight::set('message', 'Erreur import CSV : ' . $result['message']);
            }
        } else {
            Flight::set('message', 'Erreur lors de l\'upload du fichier.');
        }
        Flight::redirect('/action-client');
    }

    public function exportActionsEffectueesPdf()
    {
        $generaliserModel = Flight::generaliserModel();
        $join = [
            ['action', [['action_effectue.action_id', 'action.id']]],
            ['client', [['action_effectue.user_id', 'client.id']]]
        ];
        $actions = $generaliserModel->getTableData('action_effectue', [], [], $join);

        require_once('assets/fpdf/fpdf.php');
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des actions effectuees', 0, 1, 'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        // Suppression de la colonne ID
        $pdf->Cell(120, 8, 'Action', 1); // colonne plus large
        $pdf->Cell(40, 8, 'Client', 1);
        $pdf->Cell(35, 8, 'Date', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);
        foreach ($actions as $a) {
            // Pas d'ID exporté
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell(120, 8, $a['description'], 1);
            $pdf->SetXY($x + 120, $y);
            $pdf->Cell(40, 8, ($a['nom'] ?? '') . ' ' . ($a['prenom'] ?? ''), 1);
            $pdf->Cell(35, 8, $a['date_action'] ?? '', 1);
            $pdf->Ln();
        }
        $pdf->Output('I', 'actions_effectuees.pdf');
        exit;
    }

    public function importActionsCsv()
    {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['csv_file']['tmp_name'];
            $generaliserModel = Flight::generaliserModel();
            $result = $generaliserModel->importCsv($fileTmpPath, ',');
            if ($result['status'] === 'success') {
                $data = $result['data'];
                foreach ($data as $row) {
                    // CSV doit contenir : description, phase, cout
                    $action = [
                        'description' => $row['description'] ?? '',
                        'phase' => isset($row['phase']) ? (int)$row['phase'] : null,
                        'cout' => isset($row['cout']) ? (float)$row['cout'] : 0
                    ];
                    $generaliserModel->insererDonnee('action', $action);
                }
                Flight::set('message', 'Import CSV action terminé.');
            } else {
                Flight::set('message', 'Erreur import CSV action : ' . $result['message']);
            }
        } else {
            Flight::set('message', 'Erreur lors de l\'upload du fichier action.');
        }
        // Flight::redirect('/action-client');
    }

    public function exportActionsPdf()
    {
        $generaliserModel = Flight::generaliserModel();
        $actions = $generaliserModel->getTableData('action', []);
        require_once('assets/fpdf/fpdf.php');
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des actions', 0, 1, 'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(15, 8, 'ID', 1);
        $pdf->Cell(80, 8, 'Description', 1);
        $pdf->Cell(25, 8, 'Cout', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);
        foreach ($actions as $a) {
            $pdf->Cell(15, 8, $a['id'], 1);
            $pdf->Cell(80, 8, $a['description'], 1);
            $pdf->Cell(25, 8, isset($a['cout']) ? $a['cout'] : '0', 1);
            $pdf->Ln();
        }
        $pdf->Output();
        exit;
    }
}
