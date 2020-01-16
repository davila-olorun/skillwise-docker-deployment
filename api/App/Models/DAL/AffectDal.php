<?php

class AffectDal extends AppModel{
    private $tableName = 'affect';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function saveModulesAndSequences(Affect $affect) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($affect);
        
        if(!empty($dataArray['id_module'])){
            $modules = new ModulesDal();
            $modulesData = $modules->getModuleIdByUid($dataArray['id_module']);
            $dataArray['id_module'] = (is_array($modulesData) && !empty($modulesData)) ? $modulesData[0]['id'] : null;
        }
        
        if(!empty($dataArray['id_sequence'])){
            $sequence = new SequencesDal();
            $sequenceData = $sequence->getSequenceIdByUid($dataArray['id_sequence']);
            $dataArray['id_sequence'] = (is_array($sequenceData) && !empty($sequenceData)) ? $sequenceData[0]['id'] : null;
        }
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(count($reqData['data']) < 2){
            return $response['rps'] = 'Paramètre incomplet : '. $reqData['props'];
        }
        
        if(!$this->isAffectLineExist($reqData['data'][':id_module'], $reqData['data'][':id_sequence'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    private function isAffectLineExist($idModule, $idSequence) {
        
        $rps = $this->_select($this->tableName,'*', "id_module = '".$idModule."' AND id_sequence = '".$idSequence."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
