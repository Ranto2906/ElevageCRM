<?php
namespace app\controllers;
use Flight;

class ClientController 
{
    public function showClientPage()
    {
        $generaliserModel = Flight::generaliserModel();
        // Récupérer tous les clients
        $clients = $generaliserModel->getTableData('client');
        // Récupérer tous les types de cheveux (id => nom)
        $typesCheveux = [];
        foreach ($generaliserModel->getTableData('typecheveux') as $t) {
            $typesCheveux[$t['id']] = $t['nom'];
        }
        // Ajouter le nom du type de cheveux à chaque client
        foreach ($clients as &$c) {
            $c['TypeCheveux'] = isset($typesCheveux[$c['typecheveux_id']]) ? $typesCheveux[$c['typecheveux_id']] : '';
            $c['Nom'] = $c['nom'] ?? '';
            $c['Prenom'] = $c['prenom'] ?? '';
            $c['Age'] = $c['age'] ?? '';
            $c['Email'] = $c['email'] ?? '';
            $c['Telephone'] = $c['telephone'] ?? '';
            $c['Preferences'] = $c['preferences'] ?? '';
        }
        unset($c);
        // Récupérer les ids des actions "reservation" et "Arrivée au salon"
        $actionModel = Flight::actionModel();
        $actions = $actionModel->getActionFrequencies();
        $reservationId = null;
        $arriveeId = null;
        // Correction de la détection des ids d'action
        foreach ($actions as $a) {
            $desc = strtolower(trim($a['description']));
            if ($desc === 'reservation') {
                $reservationId = $a['id'];
            }
            if ($desc === 'arrivee au salon' || $desc === 'arrivée au salon') {
                $arriveeId = $a['id'];
            }
        }
        // Récupérer toutes les actions effectuées
        $actionEffectuees = $generaliserModel->getTableData('action_effectue');
        // Indexer par client
        $actionsParClient = [];
        foreach ($actionEffectuees as $ae) {
            $uid = $ae['user_id'];
            $actionsParClient[$uid][] = $ae;
        }
        // Calculer les taux pour chaque client (clés cohérentes)
        foreach ($clients as &$c) {
            $uid = $c['id'];
            $total = isset($actionsParClient[$uid]) ? count($actionsParClient[$uid]) : 0;
            $nbReservation = 0;
            $nbArrivee = 0;
            if ($total > 0) {
                foreach ($actionsParClient[$uid] as $ae) {
                    if ($reservationId && $ae['action_id'] == $reservationId) $nbReservation++;
                    if ($arriveeId && $ae['action_id'] == $arriveeId) $nbArrivee++;
                }
                $c['taux_reservation'] = round($nbReservation / $total * 100, 1);
                $c['taux_arrivee'] = round($nbArrivee / $total * 100, 1);
            } else {
                $c['taux_reservation'] = 0;
                $c['taux_arrivee'] = 0;
            }
        }
        unset($c);
        // Calcul du taux de fidélité numérique (normalisé)
        $maxFidelite = 1; // éviter division par zéro
        foreach ($clients as &$c) {
            $uid = $c['id'];
            $nbReservation = 0;
            $nbArrivee = 0;
            if (isset($actionsParClient[$uid])) {
                foreach ($actionsParClient[$uid] as $ae) {
                    if ($reservationId && $ae['action_id'] == $reservationId) $nbReservation++;
                    if ($arriveeId && $ae['action_id'] == $arriveeId) $nbArrivee++;
                }
            }
            $c['nb_reservation'] = $nbReservation;
            $c['nb_arrivee'] = $nbArrivee;
            $c['taux_fidelite_brut'] = $nbReservation + $nbArrivee;
            if ($c['taux_fidelite_brut'] > $maxFidelite) $maxFidelite = $c['taux_fidelite_brut'];
        }
        unset($c);
        foreach ($clients as &$c) {
            $c['taux_fidelite'] = round($c['taux_fidelite_brut'] / $maxFidelite * 100, 1);
        }
        unset($c);
        // Calculer les stats globales par client
        $nbClients = count($clients);
        $clientsAvecReservation = 0;
        $clientsAvecArrivee = 0;
        $clientsFideles = 0;
        foreach ($clients as &$c) {
            $uid = $c['id'];
            $aReservation = false;
            $aArrivee = false;
            if (isset($actionsParClient[$uid])) {
                foreach ($actionsParClient[$uid] as $ae) {
                    if ($reservationId && $ae['action_id'] == $reservationId) $aReservation = true;
                    if ($arriveeId && $ae['action_id'] == $arriveeId) $aArrivee = true;
                }
            }
            if ($aReservation) $clientsAvecReservation++;
            if ($aArrivee) $clientsAvecArrivee++;
            // Fidèle = a fait au moins une réservation ET une arrivée
            $c['fidele'] = ($aReservation && $aArrivee) ? 'Oui' : 'Non';
            if ($aReservation && $aArrivee) $clientsFideles++;
        }
        unset($c);
        // Calcul des taux globaux comme moyennes réelles des taux individuels
        $taux_reservation_global = $nbClients > 0 ? round(array_sum(array_column($clients, 'taux_reservation')) / $nbClients, 1) : 0;
        $taux_arrivee_global = $nbClients > 0 ? round(array_sum(array_column($clients, 'taux_arrivee')) / $nbClients, 1) : 0;
        $taux_fidelite_global = $nbClients > 0 ? round(array_sum(array_column($clients, 'taux_fidelite')) / $nbClients, 1) : 0;
        // Statistiques par âge
        $ages = [];
        foreach ($clients as $c) {
            if (!empty($c['Age'])) {
                $ages[$c['Age']] = ($ages[$c['Age']] ?? 0) + 1;
            }
        }
        // Statistiques par type de cheveux
        $types = [];
        foreach ($clients as $c) {
            $type = $c['TypeCheveux'] ?? '';
            if (!empty($type)) {
                $types[$type] = ($types[$type] ?? 0) + 1;
            }
        }
        Flight::render('client', [
            'clients' => $clients,
            'ages' => $ages,
            'types' => $types,
            'taux_reservation_global' => $taux_reservation_global,
            'taux_arrivee_global' => $taux_arrivee_global,
            'taux_fidelite_global' => $taux_fidelite_global,
            'message' => Flight::get('message') ?? null
        ]);
    }

    public function importClientsCsv()
    {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['csv_file']['tmp_name'];
            $generaliserModel = Flight::generaliserModel();
            $result = $generaliserModel->importCsv($fileTmpPath, ',');

            if ($result['status'] === 'success') {
                $data = $result['data'];
                foreach ($data as $row) {
                    // Gestion du typecheveux
                    $typecheveuxNom = $row['typecheveux'] ?? '';
                    $typecheveux_id = null;
                    if ($typecheveuxNom) {
                        $typeRow = $generaliserModel->getTableData('typecheveux', ['nom' => $typecheveuxNom]);
                        if ($typeRow && isset($typeRow[0]['id'])) {
                            $typecheveux_id = $typeRow[0]['id'];
                        } else {
                            $insertType = $generaliserModel->insererDonnee('typecheveux', ['nom' => $typecheveuxNom]);
                            if ($insertType['status'] === 'success') {
                                $typeRow = $generaliserModel->getTableData('typecheveux', ['nom' => $typecheveuxNom]);
                                $typecheveux_id = $typeRow[0]['id'] ?? null;
                            }
                        }
                    }
                    $clientData = [
                        'nom' => $row['nom'] ?? '',
                        'prenom' => $row['prenom'] ?? '',
                        'age' => $row['age'] ?? null,
                        'typecheveux_id' => $typecheveux_id,
                        'email' => $row['email'] ?? '',
                        'telephone' => $row['telephone'] ?? '',
                        'preferences' => $row['preferences'] ?? ''
                    ];
                    $generaliserModel->insererDonnee('client', $clientData);
                }
                Flight::set('message', 'Import CSV terminé.');
            } else {
                Flight::set('message', 'Erreur import CSV : ' . $result['message']);
            }
        } else {
            Flight::set('message', 'Erreur lors de l\'upload du fichier.');
        }
        Flight::redirect('/client');
    }

    public function exportClientsPdf()
    {
        $generaliserModel = Flight::generaliserModel();
        $join = [
            ['typecheveux', [['client.typecheveux_id', 'typecheveux.id']]]
        ];
        $clients = $generaliserModel->getTableData('client', [], [], $join);

        require_once('assets/fpdf/fpdf.php');
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des clients', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 8, 'Nom', 1);
        $pdf->Cell(30, 8, 'Prénom', 1);
        $pdf->Cell(10, 8, 'Âge', 1);
        $pdf->Cell(25, 8, 'Type cheveux', 1);
        $pdf->Cell(40, 8, 'Email', 1);
        $pdf->Cell(25, 8, 'Téléphone', 1);
        $pdf->Cell(30, 8, 'Préférences', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        foreach ($clients as $c) {
            $pdf->Cell(30, 8, $c['nom'], 1);
            $pdf->Cell(30, 8, $c['prenom'], 1);
            $pdf->Cell(10, 8, $c['age'], 1);
            $pdf->Cell(25, 8, $c['nom_1'] ?? '', 1); // typecheveux.nom
            $pdf->Cell(40, 8, $c['email'], 1);
            $pdf->Cell(25, 8, $c['telephone'], 1);
            $pdf->Cell(30, 8, $c['preferences'], 1);
            $pdf->Ln();
        }
        $pdf->Output();
        exit;
    }
}
