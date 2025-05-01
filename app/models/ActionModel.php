<?php
namespace app\models;
use PDO;

class ActionModel {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    // Fréquence de chaque action et nombre de clients distincts, filtré par phase
    public function getActionFrequencies($phase = null) {
        $sql = "SELECT a.id, a.description, a.cout, a.phase, COUNT(ae.id) AS frequence, COUNT(DISTINCT ae.user_id) AS nb_clients
                FROM action a
                LEFT JOIN action_effectue ae ON a.id = ae.action_id";
        $params = [];
        if ($phase !== null) {
            $sql .= " WHERE a.phase = :phase";
            $params['phase'] = $phase;
        }
        $sql .= " GROUP BY a.id, a.description, a.cout, a.phase ORDER BY frequence DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actions les plus fréquentes par tranche d'âge, filtré par phase
    public function getActionFrequenciesByAgeRange($phase = null, $ranges = [[18,25],[26,35],[36,45],[46,60],[61,120]]) {
        $results = [];
        foreach ($ranges as $range) {
            $sql = "SELECT a.id, a.description, a.cout, a.phase, COUNT(ae.id) AS frequence
                    FROM action a
                    JOIN action_effectue ae ON a.id = ae.action_id
                    JOIN client c ON ae.user_id = c.id
                    WHERE c.age BETWEEN :min_age AND :max_age";
            $params = ['min_age' => $range[0], 'max_age' => $range[1]];
            if ($phase !== null) {
                $sql .= " AND a.phase = :phase";
                $params['phase'] = $phase;
            }
            $sql .= " GROUP BY a.id, a.description, a.cout, a.phase ORDER BY frequence DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results["{$range[0]}-{$range[1]}"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $results;
    }
}
