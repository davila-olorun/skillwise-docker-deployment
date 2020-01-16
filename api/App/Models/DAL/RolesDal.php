<?php

class RolesDal extends AppModel{
    public $tableName = 'roles';
    
    public function __construct() {
        parent::__construct();
    }
    public function saveRole(Roles $role) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($role);
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(!$this->isRoleExist($reqData['data'][':uid'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    public function getRoleIdByUid($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        return $rps;
    }
    public function getRoleIdBySlug($slug) {
        $rps = $this->_select($this->tableName,'id', "slug = '".$slug."' ");
        return $rps;
    }
    private function isRoleExist($uid){
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
