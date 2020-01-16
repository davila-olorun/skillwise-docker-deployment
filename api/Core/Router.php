<?php

/**
 * Description of Router : Permet de mettre en place les routes du système
 *  
 */
class Router {
    /**
     * Permet de parser une url
     * 
     * @param string $url URL à parser
     * @param Request $request objet request
     * @return array contenant la requète de l'utilisateur (nom du controlleur, de l'action et les paramètres)
     */
    public static function parse($url, $request){
        $url = trim($url, '/');
        $params = explode('/', $url);
                
        if($params[0] !== ''){
            
            $request->controller = $params[0];
            $request->action = isset($params[1]) ? $params[1] : 'view';
            $request->params = array_slice($params, 2);
            
        }
        else {
            $request->controller = 'App';
            $request->action = 'view';
            $request->params = array();
        }
        
        return true;
    }
}
