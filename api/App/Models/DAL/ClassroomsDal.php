<?php

class ClassroomsDal extends AppModel{
    private $tableName = 'classrooms';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function saveCampusesClassrooms(Classrooms $classroom) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($classroom);
        
        $reqData = $this->resolveRequestParams($dataArray);
                
        if(!empty($reqData['data'][':id_campus'])){
            $campus = new CampusesDal();
            $campusData = $campus->getCampusIdByUid($reqData['data'][':id_campus']);
            
            if(is_array($campusData) && !empty($campusData)){
                $reqData['data'][':id_campus'] = $campusData[0]['id'];
            }
        }
        
        if(!$this->isClassroomsExist($reqData['data'][':uid'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    public function getClassroomIdByUid($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        return $rps;
    }
    private function isClassroomsExist($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
