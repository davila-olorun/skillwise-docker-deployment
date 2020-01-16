<?php

/**
 * Description of ControllerNotFoundException
 * 
 */
class PageNotFoundException extends Exception {
    
    public function __construct($message = "", $code = 0, $previous = null) {
        if(empty($message)){
            $message = "Impossible de trouver la page";
        }
        parent::__construct($message, $code, $previous);
    }
}
