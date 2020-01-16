<?php

class UserConnexionsDal extends AppModel {

    public $tableName = 'user_connexions';
    
    public function __construct() {
        parent::__construct();
    }

    public function saveConnexion(UserConnexions $connect) {
        $response = array();

        $dataArray = ApiController::convertObjectIntoArray($connect);
        
        $reqData = $this->resolveRequestParams($dataArray);

        $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);

        return $response;
    }
    public function updateConnexion(UserConnexions $connect) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($connect);
        
        $reqData = $this->resolveRequestParams($dataArray, 'update');
        
        $whereCondition = "sessionId = '".$dataArray['sessionId']."' ";
        
        $response['rps'] = $this->_update($this->tableName, $reqData['bind'], $whereCondition, $reqData['data']);
        
        return $response;
    }
}
