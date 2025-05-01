<?php
namespace app\controllers;
use Flight;

class ReactionImpactController {
    public function showImpact() {
        $reactionId = isset($_GET['reaction_id']) ? (int)$_GET['reaction_id'] : null;
        if (!$reactionId) {
            Flight::render('reaction_impact', ['executions' => [], 'reaction' => null, 'message' => 'Aucune réaction sélectionnée.']);
            return;
        }
        $generaliserModel = Flight::generaliserModel();
        $reactionModel = Flight::reactionModel();
        // Récupérer la réaction
        $reaction = $generaliserModel->getTableData('reaction', ['id' => $reactionId]);
        $reaction = $reaction ? $reaction[0] : null;
        // Récupérer toutes les exécutions de cette réaction
        $join = [
            ['action_effectue', [['reaction_effectue.action_effectue_id', 'action_effectue.id']]],
            ['client', [['action_effectue.user_id', 'client.id']]]
        ];
        $executions = $generaliserModel->getTableData('reaction_effectue', ['reaction_id' => $reactionId], [], $join);
        // Pour chaque exécution, calculer les stats avant/après et si le client est revenu
        foreach ($executions as &$exec) {
            $date = $exec['date_reaction'];
            $userId = $exec['user_id'];
            $exec['stats_avant'] = $this->getGlobalStats($date, -1);
            $exec['stats_apres'] = $this->getGlobalStats($date, 1);
            $exec['client_revenu'] = $this->clientRevenu($userId, $date);
        }
        unset($exec);
        Flight::render('reaction_impact', [
            'executions' => $executions,
            'reaction' => $reaction,
            'message' => null
        ]);
    }
    // Calcule les stats globales sur une période de 1 mois avant/après la date
    private function getGlobalStats($date, $offsetMonth) {
        $generaliserModel = Flight::generaliserModel();
        $actionModel = Flight::actionModel();
        $actions = $actionModel->getActionFrequencies();
        $reservationId = null;
        $arriveeId = null;
        $cout_action = 0;
        $cout_reaction = 0;

        foreach ($actions as $a) {
            $desc = strtolower(trim($a['description']));
            if ($desc === 'reservation') $reservationId = $a['id'];
            if ($desc === 'arrivee au salon' || $desc === 'arrivée au salon') $arriveeId = $a['id'];
        }
        
        $date = date('Y-m-d 00:00:00', strtotime("$date $offsetMonth month"));

        $timestamp = strtotime($date);
        if($offsetMonth < 0)
        {
            $start = date('Y-m-d 00:00:00', strtotime('first day of this month', $timestamp));
            $end = date('Y-m-d 23:59:59', strtotime('last day of this month', $timestamp));
        }
        else
        {
            $start = date('Y-m-d 00:00:00', strtotime('last day of this month', $timestamp));
            $end = date('Y-m-d 23:59:59', strtotime('first day of this month', $timestamp));
        }

        if ($offsetMonth > 0) list($start, $end) = [$end, $start];
        $join_action = [
            ['action', [['action_effectue.action_id', 'action.id']]]
        ];

        $join_reaction = [
            ['reaction', [['reaction_effectue.reaction_id', 'reaction.id']]]
        ];
        $actionsEff = $generaliserModel->getTableData('action_effectue', [], [], $join_action);
        $reactionsEff = $generaliserModel->getTableData('reaction_effectue', [], [], $join_reaction);
        $clients = $generaliserModel->getTableData('client');
        $nbClients = count($clients);
        $clientsAvecReservation = $clientsAvecArrivee = $clientsFideles = 0;
        foreach ($clients as $c) {
            $uid = $c['id'];
            $aReservation = $aArrivee = false;
            foreach ($actionsEff as $ae) {
                if ($ae['user_id'] == $uid && $ae['date_action'] >= $start && $ae['date_action'] <= $end) 
                {
                    if ($reservationId && $ae['action_id'] == $reservationId) $aReservation = true;
                    if ($arriveeId && $ae['action_id'] == $arriveeId) $aArrivee = true;
                }
            }

            if ($aReservation) $clientsAvecReservation++;
            if ($aArrivee) $clientsAvecArrivee++;
            if ($aReservation && $aArrivee) $clientsFideles++;
        }

        foreach ($actionsEff as $ae) 
        {
            if($ae['date_action'] <= $end)
            {
                $cout_action += $ae["cout"];
            }
        }

        foreach ($reactionsEff as $re) 
        {
            if($re['date_reaction'] <= $end)
            {
                $cout_reaction += $re["cout"];
            }
        }

        return [
            'benefice' => $cout_reaction - $cout_action,
            'start' => $start,
            'end' => $end,
            'taux_reservation' => $nbClients > 0 ? round($clientsAvecReservation / $nbClients * 100, 1) : 0,
            'taux_arrivee' => $nbClients > 0 ? round($clientsAvecArrivee / $nbClients * 100, 1) : 0,
            'taux_fidelite' => $nbClients > 0 ? round($clientsFideles / $nbClients * 100, 1) : 0
        ];
    }
    // Vérifie si le client est revenu dans le mois suivant la date
    private function clientRevenu($userId, $date) {
        $generaliserModel = Flight::generaliserModel();
        $actionsEff = $generaliserModel->getTableData('action_effectue', ['user_id' => $userId], []);
        $start = date('Y-m-d 00:00:00', strtotime("$date +1 day"));
        $end = date('Y-m-d 23:59:59', strtotime("$date +1 month"));
        foreach ($actionsEff as $ae) {
            if ($ae['date_action'] >= $start && $ae['date_action'] <= $end) {
                return 'Oui';
            }
        }
        return 'Non';
    }
}