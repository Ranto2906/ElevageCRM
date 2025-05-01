<?php

namespace app\models;

use Ahc\Cli\Exception;
use Flight;

class GeneraliserModel
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    public function getSumOfColumn($table, $column)
    {
        try {
            if (empty($table) || empty($column)) {
                return ["message" =>  "Le nom de la table et de la colonne sont obligatoires"];
            }
            $query = "SELECT SUM(`$column`) FROM `$table`";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute();
            $sum = $stmt->fetchColumn();
            return (float)($sum ?? 0.0);
        } catch (Exception $e) {
            return ["message" =>  "Erreur lors du calcul de la somme : " . $e->getMessage()];
        }

    }

    public function getAverageOfColumn($table, $column)
    {
        try {
            if (empty($table) || empty($column)) {
                return ["message" => "Le nom de la table et de la colonne sont obligatoires"];
            }
            $query = "SELECT AVG(`$column`) FROM `$table`";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute();
            $average = $stmt->fetchColumn();
            return (float)($average ?? 0.0);
        } catch (Exception $e) {
            return ["message" => "Erreur lors du calcul de la moyenne : " . $e->getMessage()];
        }
    }

    public function getExtremumRow($table, $column, $extremum = 'max')
    {
        try {
            if (empty($table) || empty($column)) {
               return ["message" => "Table et colonne obligatoires"]  ;
            }
            $extremum = strtolower($extremum);
            if (!in_array($extremum, ['min', 'max'])) {
                return ["message" => "Choix invalide - utiliser 'min' ou 'max'"];
            }
            $query = "SELECT * FROM `$table` 
                    WHERE `$column` = (SELECT $extremum(`$column`) FROM `$table`) 
                    LIMIT 1";

            $stmt = $this->bdd->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Exception $e) {
            return ["message" => "Erreur lors de la récupération de l'extremum : " . $e->getMessage()];
        }
    }

    public function getFormData($table, $omitColumns = [], $method = 'POST')
    {
        $query = "DESCRIBE $table";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll();
        $formData = [];
        $dataSource = ($method == 'POST') ? Flight::request()->data : Flight::request()->query;
        foreach ($columns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, $omitColumns)) {
                continue;
            }
            if (isset($dataSource[$columnName])) {
                $formData[$columnName] = $dataSource[$columnName];
            } else {
                $formData[$columnName] = null;
            }
        }

        return $formData;
    }

    public function insertData($table, $omitColumns = [], $method = 'POST')
    {
        try {
            $formData = $this->getFormData($table, $omitColumns, $method);
            foreach ($formData as $key => $value) {
                if ($value === null) {
                    $formDataStr = print_r($formData, true); 
                    return [
                        'success' => false,
                        'message' => "Le champ `$key` est obligatoire mais n'a pas été fourni. Contenu complet de \$formData : " . $formDataStr
                    ];
                }
            }
            $columns = array_keys($formData);
            $values = array_values($formData);
            $columnNames = implode(", ", $columns);
            $placeholders = implode(", ", array_fill(0, count($columns), '?'));
            $query = "INSERT INTO $table ($columnNames) VALUES ($placeholders)";
            $stmt = $this->bdd->prepare($query);
            if ($stmt->execute($values)) {
                return [
                    'success' => true,
                    'message' => "Les données ont été insérées avec succès dans la table `$table`."
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Échec de l'insertion des données dans la table `$table`."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de l'insertion : " . $e->getMessage()
            ];
        }
    }

    public function checkLogin($table, $omitColumns = [], $method = 'POST', $return = [])
    {
        try {
            $formData = $this->getFormData($table, $omitColumns, $method);
            $requiredColumns = array_diff(array_keys($formData), $omitColumns);
            
            foreach ($requiredColumns as $column) {
                if (!isset($formData[$column]) || $formData[$column] === null) {
                    return [
                        'success' => false,
                        'message' => "Le champ `$column` est obligatoire mais n'a pas été fourni. Contenu complet : " . json_encode($formData)
                    ];
                }
            }
            $conditions = [];
            $values = [];
            foreach ($formData as $key => $value) {
                if (!in_array($key, $omitColumns)) {
                    $conditions[] = "$key = ?";
                    $values[] = $value;
                }
            }
            $whereClause = implode(' AND ', $conditions);
            $query = "SELECT * FROM $table WHERE $whereClause";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute($values);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch();
                if (!empty($return)) {
                    $filteredData = [];
                    foreach ($return as $column) {
                        if (array_key_exists($column, $data)) {
                            $filteredData[$column] = $data[$column];
                        }
                    }
                    return [
                        'success' => true,
                        'message' => "Connexion réussie.",
                        'data' => $filteredData
                    ];
                }
                return [
                    'success' => true,
                    'message' => "Connexion réussie.",
                    'data' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Nom d'utilisateur ou mot de passe incorrect.",
                    'data' => $formData
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la vérification des identifiants : " . $e->getMessage()
            ];
        }
    }

    public function insererDonnee($nomTable, $donnee)
    {
        try {
            if (empty($donnee)) {
                return ["message" => "Les données sont vides.", "status" => "error"];
            }
            $colonnes = array_keys($donnee);
            $colonnesListe = implode(", ", $colonnes);
            $placeholders = implode(", ", array_map(function ($col) {
                return ":$col";
            }, $colonnes));
            $query = "INSERT INTO $nomTable ($colonnesListe) VALUES ($placeholders)";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute($donnee);
            return ["message" => "Insertion avec succès", "status" => "success"];
        } catch (Exception $e) {
            return ["message" => "Erreur lors de l'insertion: " . $e->getMessage(), "status" => "error"];
        }
    }

    public function insererDonnees($tableName, $data) {
        try {
            $columns = array_keys($data[0]);
            $columnList = implode(", ", $columns);
            $placeholders = implode(", ", array_fill(0, count($columns), "?"));

            $query = "INSERT INTO $tableName ($columnList) VALUES ($placeholders)";
            $stmt = $this->bdd->prepare($query);

            foreach ($data as $row) {
                $values = array_values($row);
                $stmt->execute($values);
            }

            return [
                'success' => true,
                'message' => "Les données ont été insérées avec succès dans la table `$tableName`."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de l'insertion : " . $e->getMessage()
            ];
        }
    }

    function getTableData($tableName, $conditions = [], $omitColumns = [], $join = null) {
        if (empty($tableName)) {
            return ["message" => 'Le nom de la table ne peut pas être vide.'] ;
        }
        $sql = "SELECT * FROM $tableName";
        if ($join !== null && is_array($join)) {
            foreach ($join as $joinInfo) {
                if (isset($joinInfo[0], $joinInfo[1]) && is_array($joinInfo[1])) {
                    $table2 = $joinInfo[0];       
                    $joinColumns = $joinInfo[1];  
                    $onClauses = [];
                    foreach ($joinColumns as $columnPair) {
                        if (count($columnPair) === 2) {
                            $onClauses[] = "$columnPair[0] = $columnPair[1]";
                        }
                    }
                    if (!empty($onClauses)) {
                        $sql .= " INNER JOIN $table2 ON " . implode(' AND ', $onClauses);
                    }
                } else {
                    return ["message" => 'Les informations de jointure sont incorrectes.'];
                }
            }
        }
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $escapedValue = Flight::bdd()->quote($value);
                $whereClauses[] = "$column = $escapedValue";
            }
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
        }
        $data = Flight::bdd()->query($sql)->fetchAll();
        if (!empty($omitColumns)) {
            foreach ($data as &$row) {
                foreach ($omitColumns as $omit) {
                    if (array_key_exists($omit, $row)) {
                        unset($row[$omit]);
                    }
                }
            }
        }
        return $data;
    }
    

    function isIdUsedInTable($cell, $tableName) {
        if (empty($tableName) || empty($cell)) {
            return ["message" => 'Le nom de la table et la cellule ne peuvent pas être vides.'];
        }
        if (!is_array($cell) || count($cell) !== 1) {
            return ["message" => 'Format de cellule invalide. Utilisez ["nom_colonne" => valeur].'];
        }
        $column = key($cell);
        $value = current($cell);
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return ["message" => 'Caractères non autorisés dans le nom de table ou colonne.'];
        }
        try {
            $sql = "SELECT EXISTS(SELECT 1 FROM `$tableName` WHERE `$column` = :value) AS is_used";
            $stmt = Flight::bdd()->prepare($sql);
            $stmt->execute([':value' => $value]);
            $result = $stmt->fetch();
            return ["used" => $result['is_used']];
        } catch (Exception $e) {
            return ["message" => "Erreur de base de données : " . $e->getMessage()];
        }
    }
    
    public function getLastInsertedId($table, $idColumn)
    {
        try {
            $query = "SELECT MAX($idColumn) AS last_id FROM $table";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result && isset($result['last_id'])) {
                return [
                    'success' => true,
                    'last_id' => $result['last_id'],
                    'message' => "Dernier ID récupéré avec succès."
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Aucun ID trouvé dans la table `$table`."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la récupération du dernier ID : " . $e->getMessage()
            ];
        }
    }




    public function updateData($table, $omitColumns = [], $method = 'POST', $conditions = [])
    {
        try {
            $formData = $this->getFormData($table, $omitColumns, $method);
            foreach ($formData as $key => $value) {
                if ($value === null) {
                    $formDataStr = print_r($formData, true);
                    return [
                        'success' => false,
                        'message' => "Le champ `$key` est obligatoire mais n'a pas été fourni. Contenu complet de \$formData : " . $formDataStr
                    ];
                }
            }
            $setClauses = [];
            $values = [];
            foreach ($formData as $column => $value) {
                $setClauses[] = "$column = ?";
                $values[] = $value;
            }
            $setClause = implode(", ", $setClauses);
            if (empty($conditions)) {
                return [
                    'success' => false,
                    'message' => "Aucune condition fournie pour la mise à jour. Cela empêcherait une mise à jour accidentelle de toutes les lignes."
                ];
            }
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "$column = ?";
                $values[] = $value;
            }
            $whereClause = implode(" AND ", $whereClauses);
            $query = "UPDATE $table SET $setClause WHERE $whereClause";
            $stmt = $this->bdd->prepare($query);
            if ($stmt->execute($values)) {
                return [
                    'success' => true,
                    'message' => "Les données ont été mises à jour avec succès dans la table `$table`."
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Échec de la mise à jour des données dans la table `$table`."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la mise à jour : " . $e->getMessage()
            ];
        }
    }

    public function importCsv($csvFilePath, $delimiter = ';')
    {
        if (!file_exists($csvFilePath) || !is_readable($csvFilePath)) {
            return ["message" => "Le fichier CSV est introuvable ou illisible.", "status" => "error"];
        }
        $header = null;
        $data = [];
        if (($handle = fopen($csvFilePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    // Ignore les lignes vides ou mal formées
                    if (count($row) === count($header)) {
                        $data[] = array_combine($header, $row);
                    }
                }
            }
            fclose($handle);
        }
        if (empty($data)) {
            return ["message" => "Le fichier CSV est vide ou mal formaté.", "status" => "error"];
        }
        return ["data" => $data, "status" => "success"];
    }

    function updateTableData($tableName, $data, $conditions = []) {
        if (empty($tableName)) {
            return 'Le nom de la table ne peut pas être vide.';
        }
        if (empty($data)) {
            return 'Les données à mettre à jour ne peuvent pas être vides.';
        }
        $setClauses = [];
        foreach ($data as $column => $value) {
            $escapedValue = Flight::bdd()->quote($value);
            $setClauses[] = "$column = $escapedValue";
        }
        $sql = "UPDATE $tableName SET " . implode(', ', $setClauses);
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $escapedValue = Flight::bdd()->quote($value);
                $whereClauses[] = "$column = $escapedValue";
            }
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
        }
        $result = Flight::bdd()->exec($sql);
        return ['status'=>'success'];
    }

    public function deleteData($table, $conditions = [])
    {
        try {
            if (empty($conditions)) {
                return [
                    'success' => false,
                    'message' => "Aucune condition fournie pour la suppression. Cela empêcherait une suppression accidentelle de toutes les lignes."
                ];
            }
            $whereClauses = [];
            $values = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "$column = ?";
                $values[] = $value;
            }
            $whereClause = implode(" AND ", $whereClauses);
            $query = "DELETE FROM $table WHERE $whereClause";
            $stmt = $this->bdd->prepare($query);
            if ($stmt->execute($values)) {
                return [
                    'success' => true,
                    'message' => "Les données ont été supprimées avec succès de la table `$table`."
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Échec de la suppression des données de la table `$table`."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la suppression : " . $e->getMessage()
            ];
        }
    }


}
