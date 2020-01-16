<?php

class Affect {
    private $id_affect;
    private $id_module; 
    private $id_sequence;
    
    public function __construct() {
    }
    
    public function getIdAffect() {
        return $this->id_affect;
    }

    public function getIdModule() {
        return $this->id_module;
    }

    public function getIdSequence() {
        return $this->id_sequence;
    }

    public function setIdAffect($id_affect) {
        $this->id_affect = $id_affect;
    }

    public function setIdModule($id_module) {
        $this->id_module = $id_module;
    }

    public function setIdSequence($id_sequence) {
        $this->id_sequence = $id_sequence;
    }
}
