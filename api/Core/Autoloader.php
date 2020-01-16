<?php

/**
 * Description of Autoloader
 *  
 */
class Autoloader {
    public static function start(){
        spl_autoload_register(array(__CLASS__, 'load'));
    }
    public static function load($className){
        
        /*$path = ROOT.DS.$className.'.php';
        
        if(file_exists($path)){
            require_once $path;
        }*/
        if(preg_match('#Exception$#i', $className)){
            
            require_once _COREDIRPATH_.'Exceptions'.DS.$className.'.php';
            
        }else if(preg_match('#Controller$#i', $className)){
            
            require_once _CTRLDIRPATH_.$className.'.php';
            
        }else if(preg_match('#Ctrl$#i', $className)){
            
            require_once _APICTRLDIRPATH_.$className.'.php';
            
        }else if(preg_match('#Class$#i', $className) OR $className === "AppModel"){
            
            require_once _MODELDIRPATH_.$className.'.php';
            
        }else if(preg_match('#Dal$#i', $className)){
            
            require_once _DALDIRPATH_.$className.'.php';
            
        }else if(preg_match('#Interface$#i', $className)){
            
            require_once _COREDIRPATH_.'Interfaces'.DS.$className.'.php';
            
        }else if(preg_match('#Helper#i', $className)){
            
            require_once _HELPERDIRPATH_.DS.$className.'.php';
            
        }else if(preg_match('#PHPMailer#i', $className) || preg_match('#SMTP#i', $className)){
            
            require_once _PHPMAILDIRPATH_.'class.'. strtolower($className).'.php';
            
        }else{
            $path = _MODELDIRPATH_.DS.'Schema'.DS.$className.'.php';
            
            if(file_exists($path)){
                require_once $path;
            }else{
                require_once _COREDIRPATH_.$className.'.php'; 
            } 
        }
    }
}
