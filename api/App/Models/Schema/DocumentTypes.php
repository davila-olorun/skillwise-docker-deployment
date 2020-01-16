<?php

class DocumentTypes {
    private $id; 
    private $uid; 
    private $name; 
    private $slug; 
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

    public function getSlug() {
        return $this->slug;
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

    public function setSlug($slug) {
        $this->slug = $slug;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }
}
