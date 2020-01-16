<?php

class UserRoles {
    private $id_userRole; 
    private $id_role; 
    private $id_user; 
    private $active;
    
    public function __construct() {
    }
    
    public function getIdUserRole() {
        return $this->id_userRole;
    }

    public function getIdRole() {
        return $this->id_role;
    }

    public function getIdUser() {
        return $this->id_user;
    }

    public function getActive() {
        return $this->active;
    }

    public function setIdUserRole($id_userRole) {
        $this->id_userRole = $id_userRole;
    }

    public function setIdRole($id_role) {
        $this->id_role = $id_role;
    }

    public function setIdUser($id_user) {
        $this->id_user = $id_user;
    }

    public function setActive($active) {
        $this->active = $active;
    }
}
