<?php

class RegisterDal extends AppModel{
    private $tableName = 'register';
    
    public function __construct() {
        parent::__construct();
    }
    public function saveUserStudentRegistration(Register $register) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($register);
        
        if(!empty($dataArray['id_group'])){
            $groups = new GroupsDal();
            $groupsData = $groups->getGroupIdByUid($dataArray['id_group']);
            $dataArray['id_group'] = (is_array($groupsData) && !empty($groupsData)) ? $groupsData[0]['id'] : null;
        }
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(count($reqData['data']) < 2){
            return $response['rps'] = 'Paramètre incomplet : '. $reqData['props'];
        }
        
        if(!$this->isUserRegistrationExist($reqData['data'][':id_userStudent'], $reqData['data'][':id_group'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    private function isUserRegistrationExist($idUserStudent, $idGroup) {
        
        $rps = $this->_select($this->tableName,'*', "id_userStudent = '".$idUserStudent."' AND id_group = '".$idGroup."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
