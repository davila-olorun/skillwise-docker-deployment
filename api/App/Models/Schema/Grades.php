<?php

class Grades {
    private $id; 
    private $uid; 
    private $grade; 
    private $comment; 
    private $absent; 
    private $cheat; 
    private $studentExamLeaveUrl; 
    private $createdAt; 
    private $updatedAt; 
    private $id_exam; 
    private $id_userStudent;
    
    public function __construct() {
    }
    
    public function getId() {
        return $this->id;
    }

    public function getUid() {
        return $this->uid;
    }

    public function getGrade() {
        return $this->grade;
    }

    public function getComment() {
        return $this->comment;
    }

    public function getAbsent() {
        return $this->absent;
    }

    public function getCheat() {
        return $this->cheat;
    }

    public function getStudentExamLeaveUrl() {
        return $this->studentExamLeaveUrl;
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

    public function getIdUserStudent() {
        return $this->id_userStudent;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUid($uid) {
        $this->uid = $uid;
    }

    public function setGrade($grade) {
        $this->grade = $grade;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }

    public function setAbsent($absent) {
        $this->absent = $absent;
    }

    public function setCheat($cheat) {
        $this->cheat = $cheat;
    }

    public function setStudentExamLeaveUrl($studentExamLeaveUrl) {
        $this->studentExamLeaveUrl = $studentExamLeaveUrl;
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

    public function setIdUserStudent($id_userStudent) {
        $this->id_userStudent = $id_userStudent;
    } 
}
