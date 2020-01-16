<?php

/**
 * Description of Model : c'est le model principal du système
 *  
 */
class Model {

    protected static $pdo; //objet pdo qui contiendra la connexion à la base de données
    protected static $_CONF = ""; //qui contient la configuration par defaut de la connexion à la base de données
    protected static $prefixe_table = "swise_"; //qui contient le préfixe des tables de la base de données
    protected $debug = false;

    public function __construct() {
        self::$_CONF = $this->getDBConfig();
        $config = Config::$database[self::$_CONF];

        if (!is_null(self::$pdo)) {
            return true;
        }
        // connexion à la base de données
        try {
            self::$pdo = new PDO(
                    'mysql:host=' . $config['host'] . ';' .
                    'dbname=' . $config['db'],
                    $config['user'],
                    $config['password'],
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (Exception $e) {
            //var_dump($e);
            die(Config::$dberror['db_connexion_error']);
        }
    }

    /**
     * Permet de récupérer la bonne configuration de la base de donnée
     * 
     * @return string la clé correspondant à la configuration de la base de donnée
     */
    private function getDBConfig() {
        $server = explode(".", _SERVERNAME_);
        if (is_array($server) && !empty($server) && $server[count($server) - 1] == "dvl/") {
            return "default";
        } else {
            return "server";
        }
    }

    /**
     * Cette methode permet de faire des insertions dans une table
     * 
     * @param @param string $table le nom de la table
     * @param string $cols contient les propriétés dans lesquelles on souhaite enregistrer les informations exple: 'nom_util,prenoms_util'
     * @param string $values contient les valeurs préchargées exple : ':nom,:prenoms'
     * @param array $dataToInsert contient les données à inserrer exple: array(':nom' => 'Assoko',':prenoms' => 'davila')
     * @return string 'table vide' si la valeur de $table est vide
     * @return string 'colonne vide' si les colonnes dans lesquelles on souhaite inserrer sont vides
     * @return string 'condition vide' si la condition d'insertion est vide
     * @return string 'donnees vide' si le tableau de données à inserrer est vide
     * @return int l'identifiant de la ligne inserrée si la requète est un succès
     * @return boolean false si la requète est un échec
     */
    protected function _insert($table, $cols, $values, array $dataToInsert) {

        try {

            if ($table === null) {
                return 'table vide';
            }

            if ($cols === null) {
                return 'colonne vide';
            }
            if ($values === null) {
                return 'valeur vide';
            }
            if (isset($dataToInsert) AND empty($dataToInsert)) {
                return 'donnees vide';
            }

            $table = self::$prefixe_table . $table;

            $val = self::cleanData($values);

            $data = self::cleanData($dataToInsert);

            $req = self::$pdo->prepare("INSERT INTO {$table} ({$cols}) VALUES({$val})") or exit("Erreur!");

            if ($this->debug) {
                var_dump($req);
                return;
            }

            $req->execute($data);

            return intval(self::$pdo->lastInsertId());
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Cette methode permet de faire des selections sur une table
     * 
     * @param string $table le nom de la table
     * @param string $fieldToSelect contient les champs à selectionner
     * @param string $whereCondition contient la condition de la selection exple: 'sexe_util = M'
     * @param string $orderby contient la condition de regroupement des données exple: 'nom_util DESC'
     * @param string $limite contient la limite de la selection exple: '10'
     * @return string 'table vide' si la valeur de $table est vide
     * @return array contient les données sectionnées dans la requète ou un tableau vide
     */
    protected function _select($table, $fieldToSelect = '*', $whereCondition = '1 = 1', $orderby = null, $limite = null) {
        if ($table === null) {
            return 'table vide';
        }

        $order = ($orderby != null) ? " ORDER BY {$orderby}" : "";
        $limit = ($limite != null) ? " LIMIT {$limite}" : "";

        $where = self::cleanData($whereCondition);

        $table = self::transformMultiTableName($table);

        $req = self::$pdo->prepare("SELECT {$fieldToSelect} FROM {$table} WHERE {$where} {$order} {$limit}") or exit("Erreur!");

        if ($this->debug) {
            var_dump($req);
            return;
        }

        $req->execute();

        return self::removeSpecificCharFromData($req->fetchAll(PDO::FETCH_ASSOC));
    }

    protected function _ExecuteProcedure($procedureNameWithParamIfExiste, array $paramArrayInOrder = array()) {

        if ($procedureNameWithParamIfExiste === null) {
            return 'nom procedure vide';
        }

        $req = self::$pdo->prepare("CALL {$procedureNameWithParamIfExiste}");

        if (is_array($paramArrayInOrder) AND ! empty($paramArrayInOrder)) {
            $req->execute($paramArrayInOrder);
        } else {
            $req->execute();
        }

        return self::removeSpecificCharFromData($req->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Cette methode permet de faire des modifications dans une table
     * 
     * @param string $table le nom de la table
     * @param string $colsEtParams contient les champs à modifier et leurs valeurs précharger exple : 'nom_util=:nom'
     * @param string $whereCondition contient la condition de la modification
     * @param array $dataToUpdate contient les données à modifier exple: array(':nom' => 'Assoko')
     * @return string 'table vide' si la valeur de $table est vide
     * @return string 'colonne vide' si les colonnes et les paramètres ne sont pas préchargés
     * @return string 'condition vide' si la condition de modification est vide
     * @return string 'donnees vide' si le tableau de données à jour est vide
     * @return int l'identifiant de la ligne mise à jour si la requète est un succès
     * @return boolean false si la requète est un échec
     */
    protected function _update($table, $colsEtParams, $whereCondition, array $dataToUpdate) {

        try {
            if ($table === null) {
                return 'table vide';
            }
            if ($colsEtParams === null) {
                return 'colonne vide';
            }
            if ($whereCondition === null) {
                return 'condition vide';
            }
            if (isset($dataToUpdate) AND empty($dataToUpdate)) {
                return 'donnees vide';
            }

            $where = self::cleanData($whereCondition);

            $data = self::cleanData($dataToUpdate);

            $table = self::$prefixe_table . $table;

            $req = self::$pdo->prepare("UPDATE {$table} SET {$colsEtParams} WHERE {$where}") or exit("Erreur!");

            if ($this->debug) {
                var_dump($req);
                return;
            }

            $req->execute($data);
            $rps = $req->rowCount();

            if ($rps >= 1) {
                return 1;
            } else {
                return 0;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Cette methode permet de faire des suppressions dans une table
     * 
     * @param string $table le nom de la table dans laquelle vous voulez supprimer
     * @param string $whereCondition contient la condition de la suppression exple: "id_util = 1"
     * @return string 'table vide' si la valeur de $table est vide
     * @return string 'condition vide' si la condition de suppression est vide
     * @return boolean true si la requète est un succès
     * @return boolean false si la requète est un échec
     */
    protected function _delete($table, $whereCondition) {
        if ($table === null) {
            return 'table vide';
        }
        if ($whereCondition === null) {
            return 'condition vide';
        }

        $where = self::cleanData($whereCondition);

        $table = self::$prefixe_table . $table;

        $req = self::$pdo->prepare("DELETE FROM {$table} WHERE {$where}") or exit("Erreur!");

        if ($this->debug) {
            var_dump($req);
            return;
        }

        $req->execute();
        $rps = $req->rowCount();

        if ($rps >= 1) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Permet de nettoyé les champs des injections
     * 
     * @param string|array $data contient le tableau à nettoyer ou le champ à nettoyer
     * @return string|array retourne un tableau nettoyé ou un champ nettoyé
     */
    protected static function cleanData($data) {

        $clean_input = array();

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = self::cleanData($v);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $data = trim(stripslashes($data));
            }
            $clean_input = trim($data);
        }
        return $clean_input;
    }

    /**
     * Cette methode permet de remplacer des anti-slash dans une chaine de caractère par une chaine vide 
     * ou de remplacer une lettre ou une sous chaine dans une chain de caractère ou un tableau
     * 
     * @param string | array $data contient la chaine de caractère globale ou le tableau
     * @param string $char contient la lettre ou la sous chaine à remplacer. 
     * NB: s'il s'agit d'un anti-slash il faut le doubler Exple : "\\"
     * @param string $charNewValue contient la nouvelle chaine qui sera remplacer
     * @return string | array
     */
    protected static function removeSpecificCharFromData($data, $char = "\\", $charNewValue = "") {
        $clean_input = array();

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = self::removeSpecificCharFromData($v, $char, $charNewValue);
            }
        } else {
            $clean_input = str_replace($char, $charNewValue, $data);
        }
        return $clean_input;
    }

    private static function transformMultiTableName($tables) {

        $tableau = explode(",", $tables);

        if (is_array($tableau)) {

            $tableWithPrefixe = "";

            foreach ($tableau as $value) {
                $tmp = trim($value, ' ');
                $tableWithPrefixe .= self::$prefixe_table . $tmp . ",";
            }
            return substr($tableWithPrefixe, 0, -1);
        } else {
            return self::$prefixe_table . $tables;
        }
    }

}
