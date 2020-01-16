<?php

class Campuses {
    private $id;
    private $uid;
    private $name;
    private $phoneNumber;
    private $addressField;
    private $addressCity;
    private $addressPostalCode;
    private $addressCountry;
    private $addressExtra;
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

    public function getPhoneNumber() {
        return $this->phoneNumber;
    }

    public function getAddressField() {
        return $this->addressField;
    }

    public function getAddressCity() {
        return $this->addressCity;
    }

    public function getAddressPostalCode() {
        return $this->addressPostalCode;
    }

    public function getAddressCountry() {
        return $this->addressCountry;
    }

    public function getAddressExtra() {
        return $this->addressExtra;
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

    public function setPhoneNumber($phoneNumber) {
        $this->phoneNumber = $phoneNumber;
    }

    public function setAddressField($addressField) {
        if(empty($addressField)){
            return false;
        }
        if(empty($this->addressField)){
            $this->addressField = $addressField;
        }else{
            $this->addressField .= ' '.$addressField;
        }
    }

    public function setAddressCity($addressCity) {
        $this->addressCity = $addressCity;
    }

    public function setAddressPostalCode($addressPostalCode) {
        $this->addressPostalCode = $addressPostalCode;
    }

    public function setAddressCountry($addressCountry) {
        $this->addressCountry = $addressCountry;
    }

    public function setAddressExtra($addressExtra) {
        if(empty($addressExtra)){
            return false;
        }
        if(empty($this->addressExtra)){
            $this->addressExtra = $addressExtra;
        }else{
            $this->addressExtra .= ' '.$addressExtra;
        }
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }
}
