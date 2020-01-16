<?php

class Modules {
    private $id; 
    private $uid; 
    private $name; 
    private $code;
    private $objective; 
    private $description; 
    private $shortDescription; 
    private $ectsCredits; 
    private $length;
    private $createdAt; 
    private $updatedAt;
    
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

    public function getCode() {
        return $this->code;
    }

    public function getObjective() {
        return $this->objective;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getShortDescription() {
        return $this->shortDescription;
    }

    public function getEctsCredits() {
        return $this->ectsCredits;
    }

    public function getLength() {
        return $this->length;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
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

    public function setCode($code) {
        $this->code = $code;
    }

    public function setObjective($objective) {
        $this->objective = $objective;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setShortDescription($shortDescription) {
        $this->shortDescription = $shortDescription;
    }

    public function setEctsCredits($ectsCredits) {
        $this->ectsCredits = $ectsCredits;
    }

    public function setLength($length) {
        $this->length = $length;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }
}
