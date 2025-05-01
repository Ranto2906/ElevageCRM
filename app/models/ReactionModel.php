<?php
namespace app\models;
use PDO;
use DateTime;
class ReactionModel {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    // Fréquence de chaque réaction et nombre de clients distincts, filtré par phase
    public function getReactionFrequencies($phase = null) {
        $sql = "SELECT r.id, r.description, r.phase, r.cout, COUNT(re.id) AS frequence, COUNT(DISTINCT ae.user_id) AS nb_clients
                FROM reaction r
                LEFT JOIN reaction_effectue re ON r.id = re.reaction_id
                LEFT JOIN action_effectue ae ON re.action_effectue_id = ae.id";
        $params = [];
        if ($phase !== null) {
            $sql .= " WHERE r.phase = :phase";
            $params['phase'] = $phase;
        }
        $sql .= " GROUP BY r.id, r.description, r.phase, r.cout ORDER BY frequence DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Réactions les plus fréquentes par tranche d'âge, filtré par phase
    public function getReactionFrequenciesByAgeRange($phase = null, $ranges = [[18,25],[26,35],[36,45],[46,60],[61,120]]) {
        $results = [];
        foreach ($ranges as $range) {
            $sql = "SELECT r.id, r.description, r.phase, COUNT(re.id) AS frequence
                    FROM reaction r
                    JOIN reaction_effectue re ON r.id = re.reaction_id
                    JOIN action_effectue ae ON re.action_effectue_id = ae.id
                    JOIN client c ON ae.user_id = c.id
                    WHERE c.age BETWEEN :min_age AND :max_age";
            $params = ['min_age' => $range[0], 'max_age' => $range[1]];
            if ($phase !== null) {
                $sql .= " AND r.phase = :phase";
                $params['phase'] = $phase;
            }
            $sql .= " GROUP BY r.id, r.description, r.phase ORDER BY frequence DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results["{$range[0]}-{$range[1]}"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $results;
    }


public function getExerciseIdByDate($date)
{
    $sql = "
        SELECT e.exercise_id, e.start_date, 
               COALESCE(
                   (SELECT MIN(e2.start_date) 
                    FROM exercise e2 
                    WHERE e2.start_date > e.start_date), 
                   DATE_ADD(e.start_date, INTERVAL 1 YEAR)
               ) AS end_date
        FROM exercise e
        WHERE :date BETWEEN e.start_date AND 
              COALESCE(
                  (SELECT MIN(e2.start_date) 
                   FROM exercise e2 
                   WHERE e2.start_date > e.start_date), 
                  DATE_ADD(e.start_date, INTERVAL 1 YEAR)
              )
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['date' => $date]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['exercise_id'] : null;
}

public function getPeriodNumberByDate($date)
{
    $sql = "
        SELECT e.exercise_id, e.start_date, e.nb_period, 
               COALESCE(
                   (SELECT MIN(e2.start_date) 
                    FROM exercise e2 
                    WHERE e2.start_date > e.start_date), 
                   DATE_ADD(e.start_date, INTERVAL 1 YEAR)
               ) AS end_date
        FROM exercise e
        WHERE :date BETWEEN e.start_date AND 
              COALESCE(
                  (SELECT MIN(e2.start_date) 
                   FROM exercise e2 
                   WHERE e2.start_date > e.start_date), 
                  DATE_ADD(e.start_date, INTERVAL 1 YEAR)
              )
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['date' => $date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $startDate = new DateTime($result['start_date']);
        $endDate = new DateTime($result['end_date']);
        $givenDate = new DateTime($date);

        $exerciseDuration = $startDate->diff($endDate)->days; // Durée totale de l'exercice en jours
        $periodDuration = $exerciseDuration / $result['nb_period']; // Durée d'une période en jours

        $daysSinceStart = $startDate->diff($givenDate)->days; // Jours écoulés depuis le début de l'exercice
        $periodNumber = (int)floor($daysSinceStart / $periodDuration) + 1; // Calcul du numéro de période

        return $periodNumber;
    }

    return null;
}


public function reactionEffectueAvecBudget($reactionId, $actionEffectueId, $dateReaction)
{
    try {
        // Démarrer une transaction
        $this->db->beginTransaction();

        // Insérer dans la table reaction_effectue
        $sql = "INSERT INTO reaction_effectue (reaction_id, action_effectue_id, date_reaction) 
                VALUES (:reaction_id, :action_effectue_id, :date_reaction)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'reaction_id' => $reactionId,
            'action_effectue_id' => $actionEffectueId,
            'date_reaction' => $dateReaction
        ]);

        // Récupérer l'ID de la réaction effectuée insérée
        $reactionEffectueId = $this->db->lastInsertId();

        // Insérer dans la table reaction_effectue_validation avec status = 0
        $sql = "INSERT INTO reaction_effectue_validation (reaction_effectue_id, status) 
                VALUES (:reaction_effectue_id, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'reaction_effectue_id' => $reactionEffectueId,
            'status' => 0 // En attente
        ]);

        // Valider la transaction
        $this->db->commit();
    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        $this->db->rollBack();
        throw $e;
    }
}
}
