<?php

class GroupsExamsDates {
    private $id;
    private $uid;
    private $isPlanned; 
    private $isStarted; 
    private $isEnded; 
    private $isClosed;
    private $createdAt;
    private $updatedAt;
    private $id_exam;
    private $id_group;
    private $id_module;
    private $id_userTeacher;
    private $id_classroom;

    public function __construct() {
    }

    public function getId() {
        return $this->id;
    }

    public function getUid() {
        return $this->uid;
    }
    
    public function getIsPlanned() {
        return $this->isPlanned;
    }

    public function getIsStarted() {
        return $this->isStarted;
    }

    public function getIsEnded() {
        return $this->isEnded;
    }

    public function getIsClosed() {
        return $this->isClosed;
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function getIdExam() {
        return $this->id_exam;
    }

    public function getIdGroup() {
        return $this->id_group;
    }

    public function getIdModule() {
        return $this->id_module;
    }

    public function getIdUserTeacher() {
        return $this->id_userTeacher;
    }

    public function getIdClassroom() {
        return $this->id_classroom;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUid($uid) {
        $this->uid = $uid;
    }
    
    public function setIsPlanned($isPlanned) {
        $this->isPlanned = $isPlanned;
    }

    public function setIsStarted($isStarted) {
        $this->isStarted = $isStarted;
    }

    public function setIsEnded($isEnded) {
        $this->isEnded = $isEnded;
    }

    public function setIsClosed($isClosed) {
        $this->isClosed = $isClosed;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }

    public function setIdExam($id_exam) {
        $this->id_exam = $id_exam;
    }

    public function setIdGroup($id_group) {
        $this->id_group = $id_group;
    }

    public function setIdModule($id_module) {
        $this->id_module = $id_module;
    }

    public function setIdUserTeacher($id_userTeacher) {
        $this->id_userTeacher = $id_userTeacher;
    }

    public function setIdClassroom($id_classroom) {
        $this->id_classroom = $id_classroom;
    }
}
