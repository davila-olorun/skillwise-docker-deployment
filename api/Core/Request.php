<?php

/**
 * Description of Request : permet de récupérer le lien de url 
 *  
 */
class Request {
    
    public $url;     // url appélée par l'utilisateur
    public $isApiUrl = false;
    
    public function __construct() {
        //$this->url = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null;
        
        if(isset($_GET['path'])){
            $this->url = $_GET['path'];
        }else {
            if(isset($_GET['url'])){
                $this->isApiUrl = true;
                $this->url = $_GET['url'];
            }else {
                $this->url = null;
                $this->apiUrl = null;
            }
        }
    }
}
