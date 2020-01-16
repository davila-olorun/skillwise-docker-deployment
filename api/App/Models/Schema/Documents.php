<?php

class Documents {
    private $id_doc; 
    private $id_group_exam; 
    private $id_documentType; 
    private $name; 
    private $url; 
    private $version; 
    private $validated; 
    private $createdAt; 
    private $updatedAt;
    
    public function __construct() {
    }
    
    public function getIdDoc() {
        return $this->id_doc;
    }

    public function getIdGroupExam() {
        return $this->id_group_exam;
    }

    public function getIdDocumentType() {
        return $this->id_documentType;
    }

    public function getName() {
        return $this->name;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getValidated() {
        return $this->validated;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function setIdDoc($id_doc) {
        $this->id_doc = $id_doc;
    }

    public function setIdGroupExam($id_group_exam) {
        $this->id_group_exam = $id_group_exam;
    }

    public function setIdDocumentType($id_documentType) {
        $this->id_documentType = $id_documentType;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function setValidated($validated) {
        $this->validated = $validated;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }
}
