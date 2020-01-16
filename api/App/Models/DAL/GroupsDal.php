<?php

/**
 * Description of GroupsDal
 *
 * @author D'Avila ASSOKO
 * @email davilaassoko@gmail.com
 * @phonenumber +225 49004206 
 */
class GroupsDal extends AppModel{
    private $tableName = 'groups';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function saveGroups(Groups $group) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($group);
        
        if(!empty($dataArray['id_campus'])){
            $campus = new CampusesDal();
            $campusData = $campus->getCampusIdByUid($dataArray['id_campus']);
            $dataArray['id_campus'] = (is_array($campusData) && !empty($campusData)) ? $campusData[0]['id'] : null;
        }
        
        if(!empty($dataArray['id_sequence'])){
            $sequence = new SequencesDal();
            $sequenceData = $sequence->getSequenceIdByUid($dataArray['id_sequence']);
            $dataArray['id_sequence'] = (is_array($sequenceData) && !empty($sequenceData)) ? $sequenceData[0]['id'] : null;
        }
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(!$this->isGroupsExist($reqData['data'][':uid'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    /**
     * Permet de récupérer le nombre d'étudiants d'un group
     * 
     * @param int $id_group contient l'identifiant du group
     * @return array contenant une clé nbre avec le nombre d'étudiants du group
     */
    public function getGroupSize($id_group) {
        return $this->_select($this->tableName." as gps, users as usr, register as regis", "COUNT(id_reg) as nbre", 
                    "usr.id = regis.id_userStudent AND gps.id = regis.id_group AND regis.id_group = '".$id_group."' ");
    }
    public function getGroupStudents($id_group) {
        return $this->_select($this->tableName." as gps, users as usr, register as regis", 
                "usr.id as userId, usr.uid as userUid, usr.firstName, usr.lastName, usr.username as userName, usr.schoolEmail as email", 
                "usr.id = regis.id_userStudent AND gps.id = regis.id_group AND regis.id_group = '".$id_group."' ");
    }
    public function getGroupIdByUid($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        return $rps;
    }
    public function studentIsRegiterInGroup($groupId,$userUid){
        return $this->_select($this->tableName." as gps, users as usr, register as regis", "gps.id as groupId, usr.id as userId, usr.firstName, usr.lastName", 
                    "usr.id = regis.id_userStudent AND gps.id = regis.id_group AND regis.id_group = '".$groupId."' AND usr.uid = '".$userUid."' ");
    }
    private function isGroupsExist($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
    
     public function getStudentClass($id){
        
        $tables ="groups as grp, register as regter, users as user";
        $cols = "grp.id as id, grp.name as groupe, grp.uid as uid";
        $where = "(grp.id = regter.id_group AND user.id = regter.id_userStudent)"
                . " AND regter.id_userStudent = {$id}";
        
        return $this->_select($tables, $cols, $where);
    
    }
}
