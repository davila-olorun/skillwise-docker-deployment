<?php

class ManagersClass extends AppModel{
    
    public function __construct() {
        parent::__construct();
    }
    public function getAllEvaluations($page){
        return (new GroupsExamsDatesDal())->getEvaluationsWithDetails(null,$page);
    }
    public function getAllEvaluationsStatistiques(){
        return (new GroupsExamsDatesDal())->getAllEvaluationStats();
    }
    public function getAllEvaluationsCopiesStats(){
        return (new GroupsExamsDatesDal())->getEvalsCopiesStats();
    }
    public function getAllEvaluationsARisqueDetails(){
        return (new GroupsExamsDatesDal())->getAllRiskyEvalDetails();
    }
    public function getEvaluationDetails($evalUid) {
        return (new GroupsExamsDatesDal())->getEvalDetails($evalUid);
    }
    public function getLastEvaluations(){
        return (new GroupsExamsDatesDal())->getLastEvals();
    }
    public function processSubjects(Documents $doc) {
        return (new DocumentsDal())->processSubjects($doc);
    }
    public function uploadSubject(array $data) {
        $doc = new Documents();
        $doc->setIdGroupExam($data['idEval']);
        $idDocType = (new DocumentTypesDal())->getDocumentTypeId('sujet');
        $doc->setIdDocumentType((!empty($idDocType[0]['id'])) ? $idDocType[0]['id'] : null);
        $doc->setName($data['name']);
        $doc->setUrl($data['url']);
        $doc->setValidated(1);
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
    
    public function uploadUsProfile(Users $user){
        return (new UsersDal())->uploadUser($user);
    }    
    public function uploadPassword(Users $user){
        return (new UsersDal())->upPass($user);
    }
}
