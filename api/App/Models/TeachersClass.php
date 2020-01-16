<?php

class TeachersClass extends AppModel{
    public function __construct() {
        parent::__construct();
    }
    public function getAllEvaluations($teacherUidToken, $page){
        return (new GroupsExamsDatesDal())->getEvaluationsWithDetails($teacherUidToken, $page);
    }
    public function getAllEvaluationsStatistiques($teacherUidToken){
        return (new GroupsExamsDatesDal())->getAllEvaluationStats($teacherUidToken);
    }
    public function getAllEvaluationsCopiesStats($teacherUidToken){
        return (new GroupsExamsDatesDal())->getEvalsCopiesStats($teacherUidToken);
    }
    public function getAllEvaluationsARisqueDetails($teacherUidToken){
        return (new GroupsExamsDatesDal())->getAllRiskyEvalDetails($teacherUidToken);
    }
    public function getEvaluationDetails($evalUid) {
        return (new GroupsExamsDatesDal())->getEvalDetails($evalUid);
    }
    public function getNextEvaluations($teacherUidToken){
        return (new GroupsExamsDatesDal())->getNextEvals($teacherUidToken);
    }
    public function uploadSubject(array $data) {
        $doc = new Documents();
        $doc->setIdGroupExam($data['idEval']);
        $idDocType = (new DocumentTypesDal())->getDocumentTypeId('sujet');
        $doc->setIdDocumentType((!empty($idDocType[0]['id'])) ? $idDocType[0]['id'] : null);
        $doc->setName($data['name']);
        $doc->setUrl($data['url']);
        $doc->setValidated(null);
        $doc->setVersion($data['version']);
        $doc->setCreatedAt($data['date']);
        $doc->setUpdatedAt($data['date']);
        
        return (new DocumentsDal())->saveDocuments($doc);
    }
    public function uploadCorrection(array $data) {
        $doc = new Documents();
        $doc->setIdGroupExam($data['idEval']);
        $idDocType = (new DocumentTypesDal())->getDocumentTypeId('corrige');
        $doc->setIdDocumentType((!empty($idDocType[0]['id'])) ? $idDocType[0]['id'] : null);
        $doc->setName($data['name']);
        $doc->setUrl($data['url']);
        $doc->setValidated(1);
        $doc->setVersion($data['version']);
        $doc->setCreatedAt($data['date']);
        $doc->setUpdatedAt($data['date']);
        
        return (new DocumentsDal())->saveDocuments($doc);
    }
}
