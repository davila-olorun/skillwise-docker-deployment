<?php

class Notifications {
    private $id; 
    private $uid; 
    private $title; 
    private $detail; 
    private $path; 
    private $icon;
    private $isRead; 
    private $createdAt; 
    private $id_user;
    
    public function __construct() {
    }
    
    public function getId() {
        return $this->id;
    }

    public function getUid() {
        return $this->uid;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDetail() {
        return $this->detail;
    }

    public function getPath() {
        return $this->path;
    }

    public function getIcon() {
        return $this->icon;
    }

    public function getIsRead() {
        return $this->isRead;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getIdUser() {
        return $this->id_user;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUid($uid) {
        $this->uid = $uid;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDetail($detail) {
        $this->detail = $detail;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
    }

    public function setIsRead($isRead) {
        $this->isRead = $isRead;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setIdUser($id_user) {
        $this->id_user = $id_user;
    }


}
