<?php

class ExamsSessionsDal extends AppModel{
    private $tableName = 'exams_sessions';
    
    public function __construct() {
        parent::__construct();
    }
    public function saveSessions(ExamsSessions $examSession) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($examSession);
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(!$this->isSessionExist($reqData['data'][':uid'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    private function isSessionExist($uid){
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
