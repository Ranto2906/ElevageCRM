<?php
namespace app\controllers;
use Flight;

class ReactionClientController {
    public function showReactionStats() {
        $phase = (isset($_GET['phase']) && $_GET['phase'] !== '') ? (int)$_GET['phase'] : null;
        $reactionModel = Flight::reactionModel();
        $frequencies = $reactionModel->getReactionFrequencies($phase);
        $byAgeRange = $reactionModel->getReactionFrequenciesByAgeRange($phase);

        
        Flight::render('reaction_client', [
            'frequencies' => $frequencies,
            'byAgeRange' => $byAgeRange,
            'selectedPhase' => $phase
        ]);
    }

    public function showEffectuerReactionForm()
    {
        $generaliserModel = Flight::generaliserModel();
    
        // Récupérer les actions effectuées avec un JOIN pour inclure les informations des actions et des utilisateurs
        $join = [
            ['action', [['action_effectue.action_id', 'action.id']]],
            ['client', [['action_effectue.user_id', 'client.id']]]
        ];
        $actions = $generaliserModel->getTableData('action_effectue', [], [], $join);
    
        // Récupérer les réactions
        $reactions = $generaliserModel->getTableData('reaction', []);
    
        // Rendre la vue avec les données
        Flight::render('effectuer_reaction', [
            'actions' => $actions,
            'reactions' => $reactions
        ]);
    }

    public function importReactionsEffectueesCsv()
    {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['csv_file']['tmp_name'];
            $generaliserModel = Flight::generaliserModel();
            $reactionModel = Flight::reactionModel();
            $result = $generaliserModel->importCsv($fileTmpPath, ',');

            if ($result['status'] === 'success') {
                $data = $result['data'];
                foreach ($data as $row) {
                    // CSV doit contenir : reaction_id, action_effectue_id, date_reaction (optionnel)
                    $reactionEffectue = [
                        'reaction_id' => $row['reaction_id'] ?? null,
                        'action_effectue_id' => $row['action_effectue_id'] ?? null,
                        'date_reaction' => $row['date_reaction'] ?? null
                    ];
                    $reactionModel->reactionEffectueAvecBudget($reactionEffectue["reaction_id"], $reactionEffectue["action_effectue_id"],$reactionEffectue["date_reaction"]);
                }
                Flight::set('message', 'Import CSV terminé.');
            } else {
                Flight::set('message', 'Erreur import CSV : ' . $result['message']);
            }
        } else {
            Flight::set('message', 'Erreur lors de l\'upload du fichier.');
        }
        Flight::redirect('/reaction-client');
    }

    public function exportReactionsEffectueesPdf()
    {
        $generaliserModel = Flight::generaliserModel();
        $join = [
            ['reaction', [['reaction_effectue.reaction_id', 'reaction.id']]],
            ['action_effectue', [['reaction_effectue.action_effectue_id', 'action_effectue.id']]],
            ['client', [['action_effectue.user_id', 'client.id']]]
        ];
        $reactions = $generaliserModel->getTableData('reaction_effectue', [], [], $join);

        require_once('assets/fpdf/fpdf.php');
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des réactions effectuées', 0, 1, 'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 8, 'Réaction', 1);
        $pdf->Cell(30, 8, 'Coût (Ar)', 1);
        $pdf->Cell(40, 8, 'Client', 1);
        $pdf->Cell(35, 8, 'Date', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);
        foreach ($reactions as $r) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell(90, 8, $r['description'], 1);
            $pdf->SetXY($x + 90, $y);
            $pdf->Cell(30, 8, isset($r['cout']) ? $r['cout'] . ' Ar' : '', 1);
            $pdf->Cell(40, 8, ($r['nom'] ?? '') . ' ' . ($r['prenom'] ?? ''), 1);
            $pdf->Cell(35, 8, $r['date_reaction'] ?? '', 1);
            $pdf->Ln();
        }
        $pdf->Output('I', 'reactions_effectuees.pdf');
        exit;
    }


    public function importReactionsCsv()
    {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['csv_file']['tmp_name'];
            $generaliserModel = Flight::generaliserModel();
            $result = $generaliserModel->importCsv($fileTmpPath, ',');
    
            if ($result['status'] === 'success') {
                $data = $result['data'];
                foreach ($data as $row) {
                    // CSV doit contenir : description, phase
                    $reaction = [
                        'description' => $row['description'] ?? '',
                        'phase' => isset($row['phase']) ? (int)$row['phase'] : null,
                        'cout' => isset($row['cout']) ? (int)$row['cout'] : null
                    ];
    
                    // Insérer la réaction
                    $generaliserModel->insererDonnee('reaction', $reaction);
    
                }
                Flight::set('message', 'Import CSV réaction terminé.');
            } else {
                Flight::set('message', 'Erreur import CSV réaction : ' . $result['message']);
            }
        } else {
            Flight::set('message', 'Erreur lors de l\'upload du fichier réaction.');
        }
        Flight::redirect('/reaction-client');
    }

    public function exportReactionsPdf()
    {
        $generaliserModel = Flight::generaliserModel();
        $reactions = $generaliserModel->getTableData('reaction', []);
        require_once('assets/fpdf/fpdf.php');
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des réactions', 0, 1, 'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 8, 'Description', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);
        foreach ($reactions as $r) {
            $pdf->Cell(80, 8, $r['description'], 1);
            $pdf->Ln();
        }
        $pdf->Output();
        exit;
    }


    public function handleEffectuerReactionForm()
    {
        // Vérifier si les données du formulaire sont présentes
        if (isset($_POST['reaction'], $_POST['reaction_date'], $_POST['action_id'])) {
            $reactionId = (int)$_POST['reaction'];
            $reactionDate = $_POST['reaction_date'];
            $actionId = (int)$_POST['action_id'];
    
            // Utiliser ReactionModel pour obtenir id_exercice et num_periode
            $reactionModel = Flight::reactionModel();
    
            try {
                // Appeler la fonction pour gérer la réaction et le budget
                $reactionModel->reactionEffectueAvecBudget($reactionId, $actionId, $reactionDate);
    
                if (Flight::request()->ajax) {
                    Flight::json(['status' => 'success', 'message' => 'Réaction effectuée avec succès et transaction enregistrée.']);
                } else {
                    Flight::set('message', 'Réaction effectuée avec succès et transaction enregistrée.');
                    Flight::redirect('/reaction-client');
                }
            } catch (\Exception $e) {
                if (Flight::request()->ajax) {
                    Flight::json(['status' => 'error', 'message' => 'Erreur : ' . $e->getMessage()]);
                } else {
                    Flight::set('message', 'Erreur : ' . $e->getMessage());
                    Flight::redirect('/reaction-client');
                }
            }
        } else {
            if (Flight::request()->ajax) {
                Flight::json(['status' => 'error', 'message' => 'Tous les champs du formulaire sont requis.']);
            } else {
                Flight::set('message', 'Erreur : Tous les champs du formulaire sont requis.');
                Flight::redirect('/reaction-client');
            }
        }
    }


    function afficherListeReactionPending()
    {
        $generaliserModel = Flight::generaliserModel();
        $join = [
            ['reaction_effectue_validation', [['reaction_effectue.id', 'reaction_effectue_validation.reaction_effectue_id']]],
            ['reaction', [['reaction_effectue.reaction_id', 'reaction.id']]],
            ['action_effectue', [['reaction_effectue.action_effectue_id', 'action_effectue.id']]],
            ['client', [['action_effectue.user_id', 'client.id']]]
        ];
    
        // Ajouter la condition pour que reaction_effectue_validation.status = 0
        $conditions = ['reaction_effectue_validation.status' => 0];
    
        // Récupérer les données avec les conditions et les jointures
        $reactions = $generaliserModel->getTableData('reaction_effectue', $conditions, [], $join);
    
        // Rendre la vue avec les données
        Flight::render('liste_reaction_pending', [
            'reactions' => $reactions
        ]);
    }


    public function validerReaction()
    {
        $reactionEffectueId = (int)$_POST['reaction_effectue_id'];
        if (empty($reactionEffectueId)) {
            Flight::set('message', 'Erreur : ID de la réaction effectuée manquant.');
            Flight::redirect('/reaction-client/liste-reaction-pending');
            return;
        }
        $generaliserModel = Flight::generaliserModel();
        $reactionModel = Flight::reactionModel();
    
        // Récupérer les informations de la réaction effectuée
        $join = [
            ['reaction', [['reaction_effectue.reaction_id', 'reaction.id']]],
        ];
        $reactionEffectue = $generaliserModel->getTableData('reaction_effectue', ['reaction_effectue.id' => $reactionEffectueId], [], $join);
    
        if (empty($reactionEffectue)) {
            Flight::set('message', 'Erreur : Réaction effectuée introuvable.');
            Flight::redirect('/reaction-client/liste-reaction-pending');
            return;
        }
    
        $reactionEffectue = $reactionEffectue[0]; // Récupérer la première ligne
        $dateReaction = $reactionEffectue['date_reaction'];
        $coutReaction = $reactionEffectue['cout'];
    
        // Obtenir l'exercice et le numéro de période correspondant à la date de la réaction
        $exerciseId = $reactionModel->getExerciseIdByDate($dateReaction);
        $periodNum = $reactionModel->getPeriodNumberByDate($dateReaction);
    
        if ($exerciseId === null || $periodNum === null) {
            Flight::set('message', 'Erreur : Impossible de déterminer l\'exercice ou la période pour la date donnée.');
            Flight::redirect('/reaction-client/liste-reaction-pending');
            return;
        }
    
        // Récupérer l'ID du budget_element pour le département CRM (department_id = 4)
        $budgetElement = $generaliserModel->getTableData('budget_element', ['department_id' => 4]);
    
        if (empty($budgetElement)) {
            Flight::set('message', 'Erreur : Budget élément pour le département CRM introuvable.');
            Flight::redirect('/reaction-client/liste-reaction-pending');
            return;
        }
    
        $budgetElementId = $budgetElement[0]['budget_element_id'];
    
        // Vérifier si une transaction existe déjà pour cet exercice, période et budget_element
        $transaction = $generaliserModel->getTableData('transaction', [
            'exercise_id' => $exerciseId,
            'period_num' => $periodNum,
            'budget_element_id' => $budgetElementId,
            'nature' => 2 // Réalisation
        ]);
    
        if (!empty($transaction)) {
            // Mettre à jour le montant de la transaction existante
            $transactionId = $transaction[0]['transaction_id'];
            $newAmount = $transaction[0]['amount'] + $coutReaction;
    
            $generaliserModel->updateTableData('transaction', ['amount' => $newAmount], ['transaction_id' => $transactionId]);
        } else {
            // Insérer une nouvelle transaction
            $generaliserModel->insererDonnee('transaction', [
                'nature' => 2,
                'exercise_id' => $exerciseId,
                'budget_element_id' => $budgetElementId,
                'period_num' => $periodNum,
                'amount' => $coutReaction,
                'status' => 1,
                'priority_id' => 2
            ]);
        }
    
        // Mettre à jour le statut de la réaction effectuée à validée
        $generaliserModel->updateTableData('reaction_effectue_validation', ['status' => 1], ['reaction_effectue_id' => $reactionEffectueId]);
    
        Flight::set('message', 'Réaction validée avec succès.');
        Flight::redirect('/reaction-client/liste-reaction-pending');
    }

    public function refuserReaction()
{
    if (isset($_POST['reaction_effectue_id'])) {
        $reactionEffectueId = (int)$_POST['reaction_effectue_id'];
        $generaliserModel = Flight::generaliserModel();
        $generaliserModel->updateDonnee('reaction_effectue_validation', ['status' => -1], ['reaction_effectue_id' => $reactionEffectueId]);
        Flight::set('message', 'Réaction refusée avec succès.');
        Flight::redirect('/reaction-client/liste-reaction-pending');
    } else {
        Flight::set('message', 'Erreur : ID de la réaction manquant.');
        Flight::redirect('/reaction-client/liste-reaction-pending');
    }
}
}
