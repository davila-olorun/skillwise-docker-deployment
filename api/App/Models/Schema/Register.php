<?php

class Register {
    private $id_reg;
    private $id_userStudent;
    private $id_group;
    
    public function __construct() {
    }
    
    public function getIdReg() {
        return $this->id_reg;
    }

    public function getIdUserStudent() {
        return $this->id_userStudent;
    }

    public function getIdGroup() {
        return $this->id_group;
    }

    public function setIdReg($id_reg) {
        $this->id_reg = $id_reg;
    }

    public function setIdUserStudent($id_userStudent) {
        $this->id_userStudent = $id_userStudent;
    }

    public function setIdGroup($id_group) {
        $this->id_group = $id_group;
    }
}
