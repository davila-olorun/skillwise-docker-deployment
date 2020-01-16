<?php

class DocumentTypesDal extends AppModel {

    private $tableName = 'document_types';
    
    public function __construct() {
        parent::__construct();
    }
    public function getDocumentTypeId($slug) {
        return $this->_select($this->tableName, 'id', "slug = '".$slug."'");
    }
}
