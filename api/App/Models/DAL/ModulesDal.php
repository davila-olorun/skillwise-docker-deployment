<?php

class ModulesDal extends AppModel{
    private $tableName = 'modules';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function saveModules(Modules $module) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($module);
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(!$this->isModuleExist($reqData['data'][':uid'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    public function getModuleIdByUid($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        return $rps;
    }
    private function isModuleExist($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
