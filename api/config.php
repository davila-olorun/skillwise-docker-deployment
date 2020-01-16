<?php

    define('PROJETDIRNAME', '');
    define('ROOT', dirname(__FILE__));
    define('DS', DIRECTORY_SEPARATOR);
    define('CORE', ROOT.DS.'Core');
    define('BASE_URL', dirname($_SERVER['SCRIPT_NAME']));
    define('ALLOW_ORIGIN', "http://localhost:8800");
    define('GENIUS_URL', "https://api.genius.estiam.com");
    define('BEARER_TOKEN', "a0da2984a0848ec194ea0b67ea2c5436eab830ebfd1f70c7ba0fdead4db31917");
    define('ROOT_SECRET', "6ce9bd3b328515f1f868a9ade0edd4bb7deb963d");
    
    $myServerScheme = $_SERVER["REQUEST_SCHEME"];
    $myServerName = $_SERVER["SERVER_NAME"];
	
    $tmpProjetDirName = '';
    
    define('_SERVERNAME_','http://'.$myServerName.'/'.$tmpProjetDirName);     // lien de l'application
    
    
    //////////////////////////// chemin vers les repertoires //////////////////////////////////////////////
    
    
    define('_VIEWDIRPATH_', ROOT.DS.'Views'.DS);  // repertoire des vues
    define('_CTRLDIRPATH_', ROOT.DS.'App'.DS.'Controllers'.DS);        // repertoire des contrôleurs
    define('_HELPERDIRPATH_', ROOT.DS.'App'.DS.'Helpers'.DS);        // repertoire des helpers
    define('_DALDIRPATH_', ROOT.DS.'App'.DS.'Models'.DS.'DAL'.DS); // repertoire de la DAL
    define('_SCHEMADIRPATH_', ROOT.DS.'App'.DS.'Models'.DS.'Schema'.DS); // repertoire des Schema de Table
    define('_MODELDIRPATH_', ROOT.DS.'App'.DS.'Models'.DS); // repertoire des modèles
    define('_COREDIRPATH_', ROOT.DS.'Core'.DS); // repertoire core
    define('_TPLDIRPATH_', ROOT.DS.'Views'.DS.'templates'.DS); // repertoire des templates
    define('_PHPMAILDIRPATH_', ROOT.DS.'App'.DS.'phpmail'.DS); // repertoire de la librairie php mail
    define('_JWTDIRPATH_', ROOT.DS.'App'.DS.'Jwt'.DS); // repertoire de la librairie php jwt
    define('_TMPDIRPATH_', ROOT.DS.'tmp'.DS); // repertoire temporaire
    define('_LOGDIRPATH_', ROOT.DS.'logs'.DS); // repertoire des logs
    
    define('_CSSDIRPATH_', ROOT.DS.'Assets'.DS.'css'.DS);  // repertoire des fichiers css
    define('_FONTDIRPATH_', ROOT.DS.'Assets'.DS.'fonts'.DS);  // repertoire des fichiers fonts
    define('_JSDIRPATH_', ROOT.DS.'Assets'.DS.'js'.DS);  // repertoire des fichiers js
    define('_IMGDIRPATH_', ROOT.DS.'Assets'.DS.'images'.DS);  // repertoire des fichiers images
    define('_AVATARIMGDIRPATH_', ROOT.DS.'Assets'.DS.'images'.DS.'avatars'.DS);  // repertoire des fichiers images avatars
    define('_FILESDIRPATH_', ROOT.DS.'Assets'.DS.'files'.DS);  // repertoire des fichiers
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    //////////////////////////// liens vers les repertoires //////////////////////////////////////////////
    
    define('_VIEWLINKPATH_', _SERVERNAME_.'Views/');  // lien vers des vues
    define('_CSSLINKPATH_', 'Assets/css/');  // lien vers des fichiers css
    define('_FONTLINKPATH_', 'Assets/fonts/');  // lien vers des fichiers fonts
    define('_JSLINKPATH_', 'Assets/js/');  // lien vers des fichiers js
    define('_IMGLINKPATH_', 'Assets/images/');  // lien vers des fichiers images
    define('_AVATARIMGLINKPATH_', 'Assets/images/avatars/');  // lien vers les fichiers images avatars
    define('_DEFAULTPROFILEIMG_', 'default-profile.jpg');  // image de profil par défaut
    define('_TMPLINKPATH_', _SERVERNAME_.'tmp/');
    define('_FILESLINKPATH_', 'Assets/files/'); // lien vers les fichiers
    /////////////////////////////////////////////////////////////////////////////////////////////////////
    