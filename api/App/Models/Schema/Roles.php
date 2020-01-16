<?php

class Roles {
    private $id;
    private $uid;
    private $name;
    private $slug;
    private $level;
    
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

    public function getLevel() {
        return $this->level;
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

    public function setLevel($level) {
        $this->level = $level;
    }
}
