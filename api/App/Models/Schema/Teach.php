<?php

class Teach {
    private $id_teach;
    private $id_userTeacher;
    private $id_module;
    private $id_group;
    
    public function __construct() {
    }
    
    public function getIdTeach() {
        return $this->id_teach;
    }

    public function getIdUserTeacher() {
        return $this->id_userTeacher;
    }

    public function getIdModule() {
        return $this->id_module;
    }
    
    public function getIdGroup() {
        return $this->id_group;
    }

    public function setIdTeach($id_teach) {
        $this->id_teach = $id_teach;
    }

    public function setIdUserTeacher($id_userTeacher) {
        $this->id_userTeacher = $id_userTeacher;
    }

    public function setIdModule($id_module) {
        $this->id_module = $id_module;
    }
    
    public function setIdGroup($id_group) {
        $this->id_group = $id_group;
    }
}
