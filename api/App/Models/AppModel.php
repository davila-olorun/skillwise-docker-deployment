<?php

class AppModel extends Model {

    protected $prefixe;
    
    public function __construct() {
        $this->prefixe = self::$prefixe_table;
        parent::__construct();
    }
    public function getNotificationObject($idUser, $title, $detail, $path, $icon) {
        $notif = new Notifications();
        $notif->setCreatedAt(ApiController::getCurrentDate(true));
        $notif->setIdUser($idUser);
        $notif->setTitle($title);
        $notif->setDetail($detail);
        $notif->setPath($path);
        $notif->setIcon($icon);
        $notif->setUid(uniqid());
        
        return $notif;
    }
    /**
     * Permet de récupérer les détails d'un document
     * 
     * @param in $docId id du document
     * @return Array
     */
    public function getDocumentData($docId){
       return (new DocumentsDal())->getDocumentData($docId);
    }
    /**
     * Permet de récupérer l'id du derminer sujet validé de l'évaluation
     * 
     * @param int $evalId id de l'évaluation
     * @return type
     */
    public function downloadSubjectForStudent($evalId){
        return (new DocumentsDal())->getSubjectForStudent($evalId);
    }
    /**
     * Vérifie le rôle d'un utilisateur avant de lui permettre de télécharger un document
     * 
     * @param string $userUid le uid de l'utilisateur
     * @param int $minRoleLevel le level du role minimum requit
     * @return boolean true s'il est autorisé et false dans le cas contraire
     */
    public function checkMinRoleBeforeDownload($userUid, $minRoleLevel){
        $roles = (new UserRolesDal())->getUserRolesLevel($userUid);
        
        if(empty($roles)){
            return false;
        }
        
        return $minRoleLevel >= $roles[0]['level'];
    }
    /**
     * Permet de vérifier qu'un etudiant appartient bel et bien à un groupe donné
     * 
     * @param int $groupId id du group
     * @param string $userUid le uid de l'utilisateur
     * @return Array un tableau avec les données de l'utilisateur s'il appartient vraiment au groupe ou un tableau vide dans le cas contraire
     */
    public function studentIsRegiterInGroup($groupId,$userUid){
        return (new GroupsDal())->studentIsRegiterInGroup($groupId,$userUid);
    }
    /**
     * Permet de récupérer les details de la feuille de copie de l'etudiant
     * 
     * @param int $gradeId l'id du grade
     * @param string $examUid le uid de l'examen
     * @param string $userStudentUid le uid de l'etudiant
     * @return Array contenant les details de la feuille
     */
    public function downloadStudentLeave($gradeId, $examUid, $userStudentUid = null){
        return (new GradesDal())->getStudentLeaveForDownload($gradeId, $examUid, $userStudentUid);
    }
    /**
     * Permet de résoudre les propriétés et les paramètres pour une requête insert ou update
     * 
     * @param array $data contient le tableau des données à insérrer
     * @param string $requestType le type de requête effectué 'insert' ou 'update'
     * @return array avec les clés 'props' => correspondant aux propriétés dans une requête insert, 
     * 'bind' => correspondant aux paramètres dans une requête insert mais aussi pour une requête update et 
     * 'data' => correspondant aux paramètres et leurs valeurs dans un tableau associatif
     */
    protected function resolveRequestParams(array $data, $requestType = "insert"){
        $properties = null;
        $propertiesBinding = null;
        $dataBinding = array();
        
        $i = 0;
        if($requestType == "insert"){
            foreach ($data as $key => $value) {
                $i++;
                if($i === 1 OR empty($value)){
                    continue;
                }
                $properties .= $key.", ";
                $propertiesBinding .= ":".$key.", ";
                $dataBinding[":".$key] = $value;
            }
        }else if($requestType == "update"){
            foreach ($data as $key => $value) {
                $i++;
                if(empty($value) || $i === 1){
                    continue;
                }
                $propertiesBinding .= $key." = :".$key.", ";
                $dataBinding[":".$key] = $value;
            }
        }
        
        return array(
            "props" => substr($properties, 0, -2),
            "bind" => substr($propertiesBinding, 0, -2),
            "data" => $dataBinding
        );
    }
    /**
     * Permet de compter le nombre correction d'une évaluation
     * @param int $evalId correspond à l'id de l'évaluation
     * @return int correspondant au nombre des corrections 
     */
    public function countEvalCorrections($evalId) {
        return (new GroupsExamsDatesDal())->countEvalCorrections($evalId);
    }
    /**
     * Permet de récupérer les groupes et les etudiants du groupe
     * 
     * @return array
     */
    public function getGroupsAndStudents() {
        $groups = $this->_select("groups", "id, name");
        
        if(empty($groups)){
            return $groups;
        }
        
        foreach ($groups as $key => $value) {
            $students = $this->_select('groups as gps, register as regi, users as usr', 
                    "usr.id, usr.firstName, usr.lastName, usr.username, usr.schoolEmail, usr.personalEmail", 
                    "gps.id = regi.id_group AND regi.id_userStudent = usr.id AND regi.id_group = {$value['id']}");
            
            $groups[$key]['studentsSize'] = count($students);
            $groups[$key]['students'] = $students;
            
            $exams = $this->_select("groups_exams_dates as gexd, exams_sessions as ess", "DISTINCT gexd.id, ess.startDate", 
                    "gexd.id = ess.id_group_exam AND ess.startDate >= '".ApiController::getCurrentDate(true)."' AND gexd.id_group = {$value['id']}");
            
            $groups[$key]['exam'] = $exams;
            
            /*if(empty($exams) || empty($students)){
                unset($groups[$key]);
            }*/
        }
        
        return $groups;
    }
    
}
