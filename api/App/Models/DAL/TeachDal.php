<?php

class TeachDal extends AppModel{
    private $tableName = 'teach';
    
    public function __construct() {
        parent::__construct();
    }
    public function saveTeacherModules(Teach $teach) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($teach);
                
        if(!empty($dataArray['id_module'])){
            $modules = new ModulesDal();
            $modulesData = $modules->getModuleIdByUid($dataArray['id_module']);
            $dataArray['id_module'] = (is_array($modulesData) && !empty($modulesData)) ? $modulesData[0]['id'] : null;
        }
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(count($reqData['data']) < 2){
            return $response['rps'] = 'Paramètre incomplet : '. $reqData['props'];
        }
        
        if(!$this->isTeacherModulesExist($reqData['data'][':id_userTeacher'], $reqData['data'][':id_module'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    private function isTeacherModulesExist($idUserTeacher, $idModule) {
        
        $rps = $this->_select($this->tableName,'*', "id_userTeacher = '".$idUserTeacher."' AND id_module = '".$idModule."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
