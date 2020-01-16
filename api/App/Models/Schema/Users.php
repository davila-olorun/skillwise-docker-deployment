<?php

class Users {
    private $id;
    private $uid;
    private $firstName;
    private $lastName;
    private $username;
    private $schoolEmail;
    private $personalEmail;
    private $password;
    private $phoneMobile;
    private $accountStatus;
    private $birthDate;
    private $birthDepartment;
    private $birthCity;
    private $birthCountry;
    private $nationality;
    private $addressField;
    private $addressCity;
    private $addressPostalCode;
    private $addressCountry;
    private $addressExtra;
    private $defaultView;
    private $photoUrl;
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

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getSchoolEmail() {
        return $this->schoolEmail;
    }

    public function getPersonalEmail() {
        return $this->personalEmail;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getPhoneMobile() {
        return $this->phoneMobile;
    }

    public function getAccountStatus() {
        return $this->accountStatus;
    }

    public function getBirthDate() {
        return $this->birthDate;
    }

    public function getBirthDepartment() {
        return $this->birthDepartment;
    }

    public function getBirthCity() {
        return $this->birthCity;
    }

    public function getBirthCountry() {
        return $this->birthCountry;
    }

    public function getNationality() {
        return $this->nationality;
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

    public function getDefaultView() {
        return $this->defaultView;
    }
    
    public function getPhotoUrl() {
        return $this->photoUrl;
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

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setSchoolEmail($schoolEmail) {
        $this->schoolEmail = $schoolEmail;
    }

    public function setPersonalEmail($personalEmail) {
        $this->personalEmail = $personalEmail;
    }

    public function setPassword($password, $hashPassword = true) {
        if($hashPassword){
            $this->password = password_hash($password,PASSWORD_DEFAULT );
        }else{
            $this->password = $password;
        }
    }

    public function setPhoneMobile($phoneMobile) {
        $this->phoneMobile = $phoneMobile;
    }

    public function setAccountStatus($accountStatus) {
        $this->accountStatus = $accountStatus;
    }

    public function setBirthDate($birthDate) {
        $this->birthDate = $birthDate;
    }

    public function setBirthDepartment($birthDepartment) {
        $this->birthDepartment = $birthDepartment;
    }

    public function setBirthCity($birthCity) {
        $this->birthCity = $birthCity;
    }

    public function setBirthCountry($birthCountry) {
        $this->birthCountry = $birthCountry;
    }

    public function setNationality($nationality) {
        $this->nationality = $nationality;
    }

    public function setAddressField($addressField) {
        if (empty($addressField)) {
            return false;
        }
        if (empty($this->addressField)) {
            $this->addressField = $addressField;
        } else {
            $this->addressField .= " ".$addressField;
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
        if (empty($addressExtra)) {
            return false;
        }
        if (empty($this->addressExtra)) {
            $this->addressExtra = $addressExtra;
        } else {
            $this->addressExtra .= " ".$addressExtra;
        }
    }

    public function setDefaultView($defaultView) {
        $this->defaultView = $defaultView;
    }
    
    public function setPhotoUrl($photoUrl) {
        $this->photoUrl = $photoUrl;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }
}
