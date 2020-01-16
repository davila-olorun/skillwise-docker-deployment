<?php

class UserRolesDal extends AppModel{
    private $tableName = 'user_roles';
    
    public function __construct() {
        parent::__construct();
    }
       
    public function saveUserRoles(UserRoles $userRoles, $roleSlug = null) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($userRoles);
        
        if(!empty($roleSlug)){
            $roles = new RolesDal();
            $rolesData = $roles->getRoleIdBySlug($roleSlug);
            $dataArray['id_role'] = (is_array($rolesData) && !empty($rolesData)) ? $rolesData[0]['id'] : null;
        }
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(count($reqData['data']) < 3){
            return $response['rps'] = 'Paramètre incomplet : '. $reqData['props'];
        }
        
        if(!$this->isUserRoleExist($reqData['data'][':id_user'], $reqData['data'][':id_role'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $response['rps'] = 'Existe' ;
        }
        
        return $response;
    }
    
    public function getUserRolesByUserId($idUser){
        $select = "slug";
        $this->tableName = $this->tableName." as urol, roles as rol, users as usr";
        $where = "rol.id = urol.id_role AND usr.id = urol.id_user AND "
                . "urol.active = 1 AND usr.id = ".$idUser;
        
        return $this->_select($this->tableName, $select, $where);
    }
    
    public function getUserRolesLevel($userUid) {
        $rps = $this->_select($this->tableName." as urole, roles as role, users as usr",'role.level', 
                "role.id = urole.id_role AND usr.id = urole.id_user AND urole.active = 1 AND  usr.uid = '".$userUid."' ",
                "role.level ASC");
        return $rps;
    }
    private function isUserRoleExist($idUser, $idRole) {
        
        $rps = $this->_select($this->tableName,'*', "id_user = '".$idUser."' AND id_role = '".$idRole."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        }else{
            return false;
        }
    }
}
