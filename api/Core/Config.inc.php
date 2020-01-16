<?php

/**
 * Description of Config : permet de définir les paramètres de configuration de l'application
 *  
 */
class Config {
    
    public static $appName = "ESTIAM SKILLWISE API";


    //message d'erreur à afficher lorsque la connexion echoue
    public static $dberror = array(
        'db_connexion_error' => '<h2>Erreur de connexion à la base de données.Vérifiez les paramètres de connexion SVP !</h2>'
    );
    
    //les paramètres de connexion à la base de données
    
    public static $database = array(
        'default' => array(
            'host' => 'localhost',
            'db' => 'olorun_skillwise',
            'user' => 'root',
            'password' => ''
        ),
        /*'server' => array(
            'host' => 'localhost',
            'db' => 'faccyah14530com27678_dbskillwise',
            'user' => 'faccy_skilluser',
            'password' => 'fmV4@f48'
        )*/
        'server' => array(
            'host' => 'mysql',
            'db' => 'skillwisedb',
            'user' => 'skill-user',
            'password' => 'skill@db-pass'
        )
    );
    
    public static $base_css_and_font_files = array(
        
    );
    public static $base_js_header_files = array(
        
    );
    public static $base_js_footer_files = array(
        
    );
}

    
