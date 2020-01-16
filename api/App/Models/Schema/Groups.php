<?php

class Groups {
    private $id; 
    private $uid; 
    private $name; 
    private $startDate; 
    private $endDate;
    private $maxHours; 
    private $closed;
    private $createdAt; 
    private $updatedAt; 
    private $id_sequence; 
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

    public function getStartDate() {
        return $this->startDate;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public function getMaxHours() {
        return $this->maxHours;
    }

    public function getClosed() {
        return $this->closed;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function getIdSequence() {
        return $this->id_sequence;
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

    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    public function setMaxHours($maxHours) {
        $this->maxHours = $maxHours;
    }

    public function setClosed($closed) {
        $this->closed = $closed;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }

    public function setIdSequence($id_sequence) {
        $this->id_sequence = $id_sequence;
    }

    public function setIdCampus($id_campus) {
        $this->id_campus = $id_campus;
    }
}
