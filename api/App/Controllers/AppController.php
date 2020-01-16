<?php

/**
 * Description of AppController : c'est le controlleur par defaut des pages
 * 
 */
class AppController extends MainCtrl implements AppInterface{
    
    /////////////////////////////////// Les propriétés par défaut de la classe \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    
      
    public $_activeAPI = true;
    
    ///////////////////////////////////////////////////////// Fin \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    
    /////////////////////////////////// Les méthodes par défaut de la classe \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function __construct() {
        parent::__construct();
    }
    protected function AuthorizedCrossOrigin(){
        header('Access-Control-Allow-Origin: '.ALLOW_ORIGIN);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        /*if($this->getRequestMethod() == 'OPTIONS'){
          $this->response('ok');  
        }*/
    }
    protected function checkMethode($requestType){
        if($this->getRequestMethod() != $requestType){
            $this->response('Methode non autorisée', 405);
        }
    }
    /**
     * Permet de vérifier si l'utilisateur a le rôle de faire ou de voir quelque chose
     * 
     * @param array $TabRole le tableau des rôles contenus dans le tableau de session exple : $_SESSION[self::$sessionName]['roles']
     * @param string $levelrole le level du rôle à vérifier
     * @return boolean true si l'utilisateur possède le rôle et false dans le cas contraire
     */
    public static function isAuthorisedToDo($TabRole,$levelrole) {
        if(isset($TabRole)){
            foreach ($TabRole['level'] as $value) {
                if($value >= $levelrole){
                    return true;
                }  
            }
            
            return false;
        }
        else {
            return false;
        }
    }
    public static function cutLongString($chaine,$charNumber = 22){
        if(is_string($chaine) AND is_int($charNumber)){
            $strLength = strlen($chaine);
            
            if($charNumber >= $strLength){
                return $chaine;
            }else{
                return substr($chaine, 0, $charNumber)."...";
            }
            
        }else{
            return $chaine;
        }
    }
    public static function seperateChars($stringToSeperate, $step = 3) {

        if (empty($stringToSeperate)) {
            return null;
        }
        
        $explode = explode('.', $stringToSeperate);

        if (is_array($explode) AND ! empty($explode[1])) {
            $stringArray = str_split($explode[0]);

            $j = 0;

            $str_output = '';

            for ($i = count($stringArray) - 1; $i >= 0; $i--) {
                if ($j == $step) {
                    $j = 0;
                    $str_output .= ' ' . $stringArray[$i];
                } else {
                    $str_output .= $stringArray[$i];
                }
                $j++;
            }

            $stringArray2 = str_split($str_output);

            $str_output2 = '';

            for ($i = count($stringArray2) - 1; $i >= 0; $i--) {
                $str_output2 .= $stringArray2[$i];
            }
            return $str_output2 . '.' . $explode[1];
        } else {
            $stringArray = str_split($stringToSeperate);

            $j = 0;

            $str_output = '';

            for ($i = count($stringArray) - 1; $i >= 0; $i--) {
                if ($j == $step) {
                    $j = 0;
                    $str_output .= ' ' . $stringArray[$i];
                } else {
                    $str_output .= $stringArray[$i];
                }
                $j++;
            }

            $stringArray2 = str_split($str_output);

            $str_output2 = '';

            for ($i = count($stringArray2) - 1; $i >= 0; $i--) {
                $str_output2 .= $stringArray2[$i];
            }
            return $str_output2;
        }
    }
    public static function getAvatarUrl($avatar = null){
        
        if(empty($avatar)){
            return _DEFAULTPROFILEIMG_;
        }
        
        $filePath = _AVATARIMGDIRPATH_.$avatar;
        $fileLink = _AVATARIMGLINKPATH_.$avatar;
                
        if(!file_exists($filePath)){
           return _DEFAULTPROFILEIMG_;
        }
        
        return $fileLink;
        
    }
    public static function formatDate($date, $delimiter = "/", $separateBy = '-') {
        
        if(empty($date) || $date == NULL){
            return '';
        }
        
        $getTime = explode(" ", $date);
        
        if(is_array($getTime)){
            $my_array = explode($delimiter, $getTime[0]);
        
            if(is_array($my_array)){
                
                $time = substr($getTime[1], 0, 5);
                if(substr($time, -1) == ':'){
                    $time = substr($getTime[1], 0, 4);
                }
                return ''.$my_array[2].$separateBy.$my_array[1].$separateBy.$my_array[0].' '. $time;
            }
            else {
                return $date;
            } 
        }else{
            $my_array = explode($delimiter, $date);
        
            if(is_array($my_array)){
                return ''.$my_array[2].$separateBy.$my_array[1].$separateBy.$my_array[0].'';
            }
            else {
                return $date;
            } 
        }
    }
    public static function getCurrentDate($withTime = false, $time = null, $strtotime = null, $returnDateOrTime = 'date') {
        
        date_default_timezone_set('Europe/Paris');
        
        $date = ($time != null) ? getdate($time) : getdate();
        $date = ($strtotime != null) ? getdate(strtotime($strtotime,$date[0])) : $date;
        
        if($returnDateOrTime != 'date'){
            return $date[0];
        }
                    
        $jour = ($date['mday'] < 10) ? '0'.$date['mday'] : $date['mday'];
        $mois = ($date['mon'] < 10) ? '0'.$date['mon'] : $date['mon'];
        
        if($withTime == false){
            return $date['year'].'-'.$mois.'-'.$jour;
        }else {
            return $date['year'].'-'.$mois.'-'.$jour.' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
        }
    }
    /**
     * Permet de vérifier les caractères autorisés dans un type de champ
     * 
     * @param string $type le type (time , date, integer, numeric, tel, email, alphanumeric, alpha)
     * @param string $field le nom du champ
     * @param string $value la valeur du champ
     * @param string $required permet de vérifier si la valeur peut être vide ou non
     * @param int $minLength la taille minimale du champ
     * @param int $maxLength la taille maximale du champ
     * @return boolean|string retourne true si la valeur du champ est conforme à son type ou un message d'érreur dans le cas contraire 
     */
    public static function checkField($type, $field, $value, $required = true, $minLength = 0, $maxLength = 500) {
        if (empty($value) && $required) {
            return "Le champ {$field} est vide";
        }
        if (!empty($value)) {
            switch ($type) {
                case 'time' : if (!preg_match("/^[0-9:.]+$/i", $value))
                        return "Le champ {$field} ne doit contenir que des entiers et un ( : )";
                    break;
                case 'date' : if (!preg_match("#^[0-9/-.]+$#", $value))
                        return "Le champ {$field} ne doit contenir que des entiers et des slashes";
                    break;
                case 'integer' : if (!preg_match("/^[0-9.]+$/i", $value))
                        return "Le champ {$field} ne doit contenir que des chiffres";
                    break;
                case 'tel' : if (!preg_match("/^[0-9 -+]+$/i", $value))
                        return "Le champ {$field} ne doit contenir que des chiffres";
                    break;
                case 'numeric' : if (!is_numeric($value))
                        return "Le champ {$field} ne doit contenir que des chiffres";
                    break;
                case 'email' : if (!filter_var($value,FILTER_VALIDATE_EMAIL))
                        return "Le champ {$field} n'est pas valide";
                    break;
                case 'alphanumeric' : if (!is_string($value))
                        return "Le champ {$field} doit contenir des caractères alpha-numériques ";
                    break;
                case 'alpha' : if (!is_string($value))
                        return "Le champ {$field} ne doit contenir que des lettres ";
                    break;
            }
        }
        if ($required and ( strlen($value) < $minLength) and $minLength > 0) return "Le champ {$field} doit contenir au moins {$minLength} caractères";
        if ($required and ( strlen($value) > $maxLength) and $maxLength > 0) return "Le champ {$field} doit contenir au plus {$maxLength} caractères";

        return true;
    }
    /**
     * Cette methode permet de remplacer des anti-slash dans une chaine de caractère par une chaine vide 
     * ou de remplacer une lettre ou une sous chaine dans une chain de caractère ou un tableau
     * 
     * @param string | array $chaine contient la chaine de caractère globale
     * @param string $char contient la lettre ou la sous chaine à remplacer. 
     * NB: s'il s'agit d'un anti-slash il faut le doubler Exple : "\\"
     * @param string $charNewValue contient la nouvelle chaine qui sera remplacer
     * @return string | array
     */
    public static function removeSpecificChar($chaine,$char = "\\",$charNewValue = ""){
        return str_replace($char, $charNewValue, $chaine);
    }
    /**
     * Permet d'enregistrer une image de profile
     * 
     * @param string $file le fichier image
     * @param string $sortie chemin de sortie de l'image redimensionnée
     * @return boolean|string la méthode retourne false si c'est un échec ou le nom de l'image rédimensionnée si c'est un succès
     */
    public static function getImageContent($file) {
        if(isset($file) && !$file['error']){
    
            $img_content = getimagesize($file['tmp_name']);

            if ($img_content && $img_content[2] < 4) {

                $imgfile = $file['tmp_name'];
    
                $openimg = fopen($imgfile, 'r');
                $filesize = $file['size'];

                $content = fread($openimg, $filesize);

                fclose($openimg);
                
                return $content;
            }
            else {
                unlink($file['tmp_name']);
                unset($file);
                return false;
            }
        }
        return false;
    }
    /**
     * Permet d'envoyer un message d'erreur à une vue dans une variable par defaut qui est $rps
     * 
     * @param string $msg le message d'erreur
     * @param string $vue le nom de la vue
     * @param string $nicename le nom à afficher lorsqu'on rend la vue
     * @param string $nomdelavarible le nom de la variable
     * @return boolean true
     */
    protected function sendErrorMsg($msg, $vue, $nicename = null, $nomdelavarible = 'rps') {
        
        if (is_string($msg)) {
            if($this->_activeAPI == true){
                $this->response($msg);
                die();
            }
            $this->setVar(
                array(
                   $nomdelavarible => $msg
            ));
            if($nicename == null){
                $this->render($vue);
                die();
            }
            else {
                $this->render($vue, $nicename);
                die();
            }
        }
    }
    /**
     * Permet de transformer une chaine ou un tableau en json. dans le cas d'une chaine la clé de la colonne sera 'rps'
     * 
     * @param string | array $response une chaine de caractère ou un tableau
     * @param string $nomdelavarible le nom de la variable
     * @return boolean true
     */
    protected function showJsonMsg($response, $nomdelavarible = 'rps') {
        if(is_string($response)) {
            $data[$nomdelavarible] = $response;
            echo json_encode($data);
            die();
        }
        if(is_array($response)){
            echo json_encode($response);
            die();
        }
    }
    /**
     * Cette methode permet de créer des liens conforment à notre système de navigation. 
     * A utiliser dans les propriétés href des balises lien (<a href=""></a>)
     * 
     * @param string $controllerAndAction contient le nom du controller et de l'action exple : 'User/connect/'
     */
    public static function setRoute($controllerAndAction = '' ){
        
        if(isset($controllerAndAction) AND !empty($controllerAndAction)) {
            
            $path = trim($controllerAndAction, '/');
            
            return _SERVERNAME_.'?path='.$path; 
        }
        else {
            return _SERVERNAME_;
        }
    }
    /**
     * Methode appelée par defaut à l'accueil.
     */
    public function view() {
        $this->response("Url invalide", 400);
    }
    
    ///////////////////////////////////////////////////////// Fin \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    
}
