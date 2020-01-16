<?php
    session_start();
    
    require_once 'config.php';
    require_once _COREDIRPATH_.'includes.php';
    
    Autoloader::start();
    
    new Dispatcher();
