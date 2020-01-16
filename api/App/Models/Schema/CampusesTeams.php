<?php

class CampusesTeams {
    private $id; 
    private $id_campus; 
    private $id_user; 
    private $isDirector;
    
    public function __construct() {
    }
    
    public function getId() {
        return $this->id;
    }

    public function getIdCampus() {
        return $this->id_campus;
    }

    public function getIdUser() {
        return $this->id_user;
    }

    public function getIsDirector() {
        return $this->isDirector;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setIdCampus($id_campus) {
        $this->id_campus = $id_campus;
    }

    public function setIdUser($id_user) {
        $this->id_user = $id_user;
    }

    public function setIsDirector($isDirector) {
        $this->isDirector = $isDirector;
    }
}
