<?php

class CampusesDal extends AppModel{
    private $tableName = 'campuses';
    
    public function __construct() {
        parent::__construct();
    }
    public function saveCampuses(Campuses $campus) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($campus);
        
        $reqData = $this->resolveRequestParams($dataArray);
               
        if(!$this->isCampusExist($reqData['data'][':uid'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $getCampus = $this->getCampusIdByUid($reqData['data'][':uid']);
           $response['rps'] = $getCampus[0]['id'] ;
        }
        
        return $response;
    }
    public function getCampusIdByUid($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        return $rps;
    }
    private function isCampusExist($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
