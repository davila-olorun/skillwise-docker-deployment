<?php

class SequencesDal extends AppModel{
    private $tableName = 'sequences';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function saveSequences(Sequences $sequence) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($sequence);
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(!$this->isSequencesExist($reqData['data'][':uid'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    public function getSequenceIdByUid($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        return $rps;
    }
    private function isSequencesExist($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
