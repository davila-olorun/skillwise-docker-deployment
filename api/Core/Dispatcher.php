<?php

/**
 * Description of Dispatcher : permet de récupérer l'url et de savoir ce qu'il faut en faire
 *  
 */
class Dispatcher {
    
    public $request; //qui contient un objet de type Request
    
    public function __construct() {
        
        $this->request = new Request();
        
        Router::parse($this->request->url, $this->request);
        
        $controller = $this->loadController();
        
        if($this->request->isApiUrl){
            $controller->_activeAPI = true;
        }
        
        if(!in_array($this->request->action, get_class_methods($controller))){
            $this->error();
        }
        
        call_user_func_array( array($controller, $this->request->action), $this->request->params);
        
        //$controller->render($this->request->action);
        
    }
    /**
     * Permet d'appeler une page d'erreur avec un message personnalisé
     * 
     * @param string $message contient le message d'erreur à afficher à l'utilisateur
     */
    public function error() {
        $controller = new MainCtrl($this->request);
        if($controller->_activeAPI === false){
            header("HTTP/1.0 404 Not Found");
            $controller->setSpaceDir('errors');
            $controller->setLayout("userTpl");
            $controller->render('404','Page introuvable');
            die();
        }  else {
            $controller->response("Bad request, this url doesn't match any url in our API", 400);
            die();
        }
    }
    /**
     * Permet de charger un controlleur et de l'instancier automatiquement 
     *  
     * @return \name renvoie une instance du controlleur chargé
     */
    public function loadController() {
        $name = ucfirst($this->request->controller).'Controller';
        
        $file = _CTRLDIRPATH_.$name.'.php';
        
        if($this->request->isApiUrl AND $this->request->controller != "App"){
            $name = ucfirst($this->request->controller).'Controller';
            $file = _CTRLDIRPATH_.$name.'.php';
        }
        
        try{
            
            if(!file_exists($file)){
                throw new PageNotFoundException();
            }
            
            return new $name($this->request);
            
        } catch (PageNotFoundException $ex) {
            $this->error();
        }
    }
}
