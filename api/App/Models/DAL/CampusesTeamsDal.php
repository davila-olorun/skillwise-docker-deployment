<?php

class CampusesTeamsDal extends AppModel{
    private $tableName = 'campuses_teams';
    
    public function __construct() {
        parent::__construct();
    }
    public function saveTeam(CampusesTeams $team) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($team);
        
        $reqData = $this->resolveRequestParams($dataArray);
               
        if(!$this->isExist($reqData['data'][':id_campus'], $reqData['data'][':id_user'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
            $reqUpdateData = $this->resolveRequestParams($dataArray, 'update');
            $whereCondition = "id_campus = '".$reqUpdateData['data'][':id_campus']."' AND id_user = '".$reqUpdateData['data'][':id_user']."' ";
            
            $this->_update($this->tableName, $reqUpdateData['bind'], $whereCondition, $reqUpdateData['data']);
            
            $response['rps'] = 'Existe';
        }
        
        return $response;
    }
    private function isExist($idCampus, $idUser) {
        $rps = $this->_select($this->tableName,'id', "id_campus = '".$idCampus."' AND id_user = '".$idUser."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
