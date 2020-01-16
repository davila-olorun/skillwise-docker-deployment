<?php

/**
 * Description of Controller : c'est le controlleur principal du système
 *  
 */
class MainCtrl {
    
    public  $request; //qui contient la requète (url)
    public  $_activeAPI = false;
    
    protected $_content_type = "application/json";
    protected $_data = array();
    
    protected $spaceDir = 'espace-public'; //qui contient le nom d'un espace du système
    
    protected $layout = 'default'; //qui contient le nom d'un template
    
    private $_code = 200;
    
    //qui contient l'ensemble des variables à transmettre à une vue
    public $vars = array(
    );
    
    private $rendered = false; //qui permet de savoir si la vue est déja rendu ou pas
    
    private $additionnalFont = "";
    private $additionnalCss = "";
    private $additionnalCssCode = "";
    private $additionnalJsHeader = "";
    private $additionnalJsHeaderCode = "";
    private $additionnalJsFooter = "";
    private $additionnalJsFooterCode = "";

    /**
     * Permet d'initialiser la propriété $request
     * 
     * @param Request $request qui contient la requête d'un utilisateur
     */
    public function __construct($request = null) {
        if(isset($request->isApiUrl)){
            if($request->isApiUrl){
                $this->_activeAPI = true;
            }
            $this->request = $request;
        }
        $this->vars['AppName'] = Config::$appName;
    }
    /**
     * Cette méthode permet d'activer le mode API qui permet d'afficher les réponses en format JSON, 
     * de désactiver les renders et les redirections
     * 
     * @param boolean $val contient une valeur de type booleen qui permet d'activer le mode api: 
     * Exple : true pour activer et false pour désactiver
     */
    protected function activeApi($val = true){
        if($val == true){
            $this->_activeAPI = $val;
        }else if($val == false){
            $this->_activeAPI = $val;
        }
    }
    /**
     * Permet de rendre (appeler) une vue dans un template
     * 
     * @param string $view qui contient le nom de la vue (nom du fichier .php qui se trouve dans le repertoire de votre espace)
     * @param string $pagetitle qui contient le nom de la page (nom qu'on souhaite affichier dans la page)
     */
    public function render($view, $pagetitle = null, $notemplate = false) {
        
        if($this->_activeAPI == true){
            return true;
        }
        
        if($this->rendered)   { return false; }
        
        $myPageTitle = isset($pagetitle) ? ucfirst($pagetitle) : ucfirst($view);
        
        extract($this->vars);
        
        $view = _VIEWDIRPATH_.$this->spaceDir.DS.$view.'.php';
        
        ob_start();
        
            require $view;
        
        $base_style_header_files = $this->_baseStyleHeaderFiles();
        
        $base_script_header_files = $this->_baseScriptHeaderFiles();
        
        $base_script_footer_files = $this->_baseScriptFooterFiles();
            
        $additionnal_font_content = $this->additionnalFont;
        
        $additionnal_css_content = $this->additionnalCss;
        
        $additionnal_css_code_content = $this->additionnalCssCode;
        
        $additionnal_js_header_content = $this->additionnalJsHeader;
        
        $additionnal_js_header_code_content = $this->additionnalJsHeaderCode;
        
        $additionnal_js_footer_content = $this->additionnalJsFooter;
        
        $additionnal_js_footer_code_content = $this->additionnalJsFooterCode;
        
        $content_for_layout = ob_get_clean();
        
        if(!$notemplate){
            require _TPLDIRPATH_.$this->layout.'.php';
        }else {
            require $view;
        }
        
        $this->rendered = true;
    }
    /**
     * Permet de faire une redirection en passant le lien en paramètre
     * 
     * @param string $path qui contient le lien vers lequel on souhaite rediriger
     */
    protected function redirect($path) {
        $path = trim($path, "/");
        
        header('location:/?path='.$path.'/');
        
        die();
    }
    private function _baseStyleHeaderFiles(){
        $scripts = "";
        if(!empty(Config::$base_css_and_font_files[$this->layout])){
            $scripts .= $this->_cssScripts(Config::$base_css_and_font_files[$this->layout]);
        }
        $scripts .= "\n\n";
        
        return $scripts;
    }
    private function _baseScriptHeaderFiles(){
        $scripts = "";
        if(!empty(Config::$base_js_header_files[$this->layout])){
            $scripts .= $this->_jsScripts(Config::$base_js_header_files[$this->layout]);
        }
        $scripts .= "\n\n";
        
        return $scripts;
    }
    private function _baseScriptFooterFiles(){
        $scripts = "";
        if(!empty(Config::$base_js_footer_files[$this->layout])){
            $scripts .= $this->_jsScripts(Config::$base_js_footer_files[$this->layout]);
        }
        $scripts .= "\n\n";
        
        return $scripts;
    }
    /**
     * Permet de modifier la valeur de la propriété $spaceDir (nom du repertoire de votre l'espace)
     * 
     * @param string $spaceName nom du repertoire
     */
    public function setSpaceDir($spaceName) {
        $this->spaceDir = $spaceName;
    }
    /**
     * Permet de modifier la valeur de la propriété $layout (nom du template à utiliser)
     * 
     * @param string $layoutName nom du template
     */
    public function setLayout($layoutName) {
        $this->layout = $layoutName;
    }
    /**
     * Permet de definir une variable qui sera accessible dans la vue
     * 
     * @param array $key si $value n'existe pas (le tableau contient les noms des variables et leurs valeurs) ou 
     * string $key si $value existe (contenant le nom de la variable)
     * @param string $value contenant la valeur de la variable
     */
    public function setVar($key,$value = null) {
        if(is_array($key)){
            $this->vars += $key;
        }
        else {
            $this->vars[$key] = $value;
        }
    }
    /**
     * Permet de charger un model et de créer automatiquement une instance de ce model
     * 
     * @param string $modelName qui contient le nom du model
     */
    protected function loadModel($modelName){
                
        $instanceName = $modelName.'Class';
                
        $file = _MODELDIRPATH_.ucfirst($modelName).'Class.php';
        
        try{
            if(!file_exists($file)){
                throw new PageNotFoundException("Impossible de trouver le model associé à cette vue");
            }
        } catch (Exception $ex) {
            var_dump($ex);
            die();
        }
        
        if(!isset($this->$modelName) AND file_exists($file)){
            $this->$modelName = new $instanceName();
        }
    }
    /**
     * 
     * @param array $tabCssFileName qui contient les fichiers à inclure
     */
    protected function addCss(array $tabCssFileName){
        
        $this->additionnalCss = $this->_cssScripts($tabCssFileName)."\n\n";
    }
    /**
     * 
     * @param array $tabFontFileName qui contient les fichiers à inclure
     */
    protected function addFont(array $tabFontFileName){
        
        $this->additionnalFont = $this->_cssScripts($tabFontFileName)."\n\n";
    }
    /**
     * 
     * @param array $tabCssFileName qui contient les fichiers à inclure
     * @return string renvoie le code des fichiers à inclure
     */
    private function _cssScripts(array $tabCssFileName) {
        $cssFile = "";
        if(is_array($tabCssFileName)){
            foreach ($tabCssFileName as $key => $value) {
                $cssFile .= '<link rel="stylesheet" type="text/css" href="'._CSSLINKPATH_.$value.'"/>'."\n";
            }
        }
        return $cssFile;
    }
    /**
     * 
     * @param array $tabJsFileName qui contient les fichiers à inclure
     */
    protected function addJsHeader(array $tabJsFileName){
        
        $this->additionnalJsHeader = $this->_jsScripts($tabJsFileName)."\n\n";
    }
    /**
     * 
     * @param array $tabJsFileName qui contient les fichiers à inclure
     */
    protected function addJsFooter(array $tabJsFileName){
        
        $this->additionnalJsFooter = $this->_jsScripts($tabJsFileName)."\n\n";
    }
    /**
     * 
     * @param array $tabJsFileName qui contient les fichiers à inclure
     * @return string renvoie le code des fichiers à inclure
     */
    private function _jsScripts(array $tabJsFileName){
        $jsFile = "";
        if(is_array($tabJsFileName)){
            foreach ($tabJsFileName as $key => $value) {
                $jsFile .= '<script type="text/javascript" src="'._JSLINKPATH_.$value.'"></script>'."\n";
            }
        }
        return $jsFile;
    }
    /**
     * 
     * @param type $code qui contient le code à inclure
     */
    protected function addCssCode($code){
        $cssCode = "";
        if(!empty($code)){
            $cssCode .= "<style>\n\n";
            $cssCode .= $code;
            $cssCode .= "\n\n</style>";
        }
        $this->additionnalCssCode = $cssCode."\n\n";
    }
    /**
     * 
     * @param string $code qui contient le code à inclure
     * @param string $place qui contient la valeur indiquant l'emplacement du script. Exple : "top" ou rien 
     * pour afficher le script dans la balise head ou "bottom" pour afficher le script avant la fermeture de la balise body
     */
    protected function addJsCode($code, $place = "top"){
        $jsCode = "";
        if(!empty($code)){
            $jsCode .= "<script type=\"text/javascript\">\n\n";
            $jsCode .= $code;
            $jsCode .= "\n\n</script>";
        }
        
        if($place == "top"){
            $this->additionnalJsHeaderCode = $jsCode."\n\n";
        } else if($place == "bottom"){
            $this->additionnalJsFooterCode = $jsCode."\n\n";
        }
    }
    /**
     * Permet de transformer une chaine ou un tableau en json. dans le cas d'une chaine la clé de la colonne sera 'rps'
     * 
     * @param string | array $response une chaine de caractère ou un tableau
     * @param string $nomdelavarible le nom de la variable
     * @return boolean true
     */
    private function getJson($response, $nomdelavarible = 'rps') {
        if(is_string($response)) {
            $data[$nomdelavarible] = $response;
            return json_encode($data);
        }
        if(is_array($response)){
            return json_encode($response);
        }
    }
    public function response($data, $status = 200){
	$this->_code = $status;
        $this->setHeaders();
        echo $this->getJson($data);
        exit;
    }
    protected static function cleanner($data){
        
        $clean_input = array();
        
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = self::cleanner($v);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $data = trim(stripslashes($data));
            }
            
            $data = addslashes(htmlspecialchars(strip_tags($data)));
            $clean_input = trim($data);
        }
        return $clean_input;
    }
    protected function getRequestMethod(){
	   return $_SERVER['REQUEST_METHOD'];
    }
    protected function getRequestData() {
        switch ($this->getRequestMethod()) {
            case "POST":
                return self::cleanner($_REQUEST);
            case "GET":
                return self::cleanner($_GET);
            case "DELETE":
                return self::cleanner($_GET);
            default:
                $this->response('Request not acceptable', 406);
                break;
        }
    }
    private function setHeaders() {
        header("HTTP/1.1 " . $this->_code . " " . $this->getStatusMessage());
        header("Content-Type:" . $this->_content_type);
    }
    private function getStatusMessage(){
	$status = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported');
        return (isset($status[$this->_code])) ? $status[$this->_code] : $status[500];
    }
}
