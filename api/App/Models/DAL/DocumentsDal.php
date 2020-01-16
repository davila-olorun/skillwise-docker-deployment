<?php

class DocumentsDal extends AppModel {

    private $tableName = 'documents';
    
    public function __construct() {
        parent::__construct();
    }
    public function saveDocuments(Documents $doc){
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($doc);
        
        $reqData = $this->resolveRequestParams($dataArray);

        $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        //$response['data'] = $dataArray;
        return $response;
    }
    public function processSubjects(Documents $doc) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($doc);
        
        $reqData = $this->resolveRequestParams($dataArray, 'update');
        
        $whereCondition = "id_doc = ".$dataArray['id_doc'];
        
        if($reqData['data'][':validated'] > 1){
            $reqData['data'][':validated'] = 0;
        }
        
        $response['rps'] = $this->_update($this->tableName, $reqData['bind'], $whereCondition, $reqData['data']);
        
        return $response;
    }
    public function getSubjectForStudent($evalId) {
        $getMaxDoc = $this->_select($this->tableName." as docs, document_types as doct", "MAX(id_doc) as id", 
                "docs.id_documentType = doct.id AND doct.slug = 'sujet' AND docs.validated = 1 AND docs.id_group_exam = '".$evalId."' ");
        
        if(empty($getMaxDoc)){
            return $getMaxDoc;
        }
        
        return $this->getDocumentData($getMaxDoc[0]['id']);
    }
    public function getDocumentData($docId){
        return $this->_select($this->tableName, "*", "id_doc = '".$docId."'");
    }
}
