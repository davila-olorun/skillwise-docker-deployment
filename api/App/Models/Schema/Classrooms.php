<?php

class Classrooms {
    private $id; 
    private $uid; 
    private $name; 
    private $capacity; 
    private $createdAt; 
    private $updatedAt; 
    private $id_campus;
    
    public function __construct() {
    }
    
    public function getId() {
        return $this->id;
    }

    public function getUid() {
        return $this->uid;
    }

    public function getName() {
        return $this->name;
    }

    public function getCapacity() {
        return $this->capacity;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function getIdCampus() {
        return $this->id_campus;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUid($uid) {
        $this->uid = $uid;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setCapacity($capacity) {
        $this->capacity = $capacity;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }

    public function setIdCampus($id_campus) {
        $this->id_campus = $id_campus;
    }
}
