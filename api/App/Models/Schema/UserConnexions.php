<?php

class UserConnexions {
    private $id; 
    private $sessionId; 
    private $loginDate; 
    private $logoutDate; 
    private $id_user;
    
    public function __construct() {
    }
    
    public function getId() {
        return $this->id;
    }

    public function getSessionId() {
        return $this->sessionId;
    }

    public function getLoginDate() {
        return $this->loginDate;
    }

    public function getLogoutDate() {
        return $this->logoutDate;
    }

    public function getIdUser() {
        return $this->id_user;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    public function setLoginDate($loginDate) {
        $this->loginDate = $loginDate;
    }

    public function setLogoutDate($logoutDate) {
        $this->logoutDate = $logoutDate;
    }

    public function setIdUser($id_user) {
        $this->id_user = $id_user;
    }
}
