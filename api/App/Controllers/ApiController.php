<?php

class ApiController extends AppController implements AppInterface {

    private $userAgent = 'Oloruntech GraphQL client';
    private $endpoint = GENIUS_URL;
    private $bearerToken = BEARER_TOKEN;
    public static $sessionName = '_skillwise_estiam_';
    protected $loggedUser = null;
    public static $rolesSlug = array(
        'superadmin' => 'superadmin',
        'admin' => 'admin',
        'director' => 'director',
        'staff' => 'staff',
        'teacher-team-manager' => 'staff',
        'teacher' => 'teacher',
        'student' => 'student'
    );
    public static $EVAL_RISKY_HOURS_DELAY = 48;
    public static $EVAL_RISKY_DAYS_DELAY = 2;
    public static $NUMBER_OF_EVALS_PER_PAGE = 10;
    public static $DAYS_BEFORE_CLOSE_EVAL = 14;

    public function __construct() {
        parent::__construct();
        $this->AuthorizedCrossOrigin();
    }

    public function view() {
        parent::view();
    }

    protected function verifyAccessToken($sendSuccessResponse = false) {

        $requestData = $this->getRequestData();

        if (empty($requestData['accesstoken'])) {
            $this->response(array('rps' => "Paramètre incorrect, token d'accès manquant"));
        }

        $jwt = self::cleanner($requestData['accesstoken']);

        $decodeJwt = JWTEncoderHelper::decode($jwt);

        if (is_string($decodeJwt)) {
            $this->response(array('rps' => "Votre token d'accès est invalide ou a expiré, veuillez vous reconnecter svp !"));
        }

        if ($sendSuccessResponse) {
            $this->response(array('isValidatedAccessToken' => true));
        }

        $this->loggedUser = (array) $decodeJwt;
    }

    public static function removeAccents($string) {
        $reg = array(
            'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ã' => 'a', 'Ä' => 'a', 'Å' => 'a', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o', 'Ø' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ç' => 'c', 'ç' => 'c', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ÿ' => 'y', 'Ñ' => 'n', 'ñ' => 'n',);

        return strtr($string, $reg);
    }

    protected function checkEvalStateBeforeAddSubject($EvalDetails) {

        if (empty($EvalDetails['details'])) {
            $this->response("Données non trouvées. Réessayez svp !");
        }

        if ($EvalDetails['details']['ended'] == 1 || $EvalDetails['details']['closed'] == 1) {
            $this->response("Impossible d'ajouter un sujet lorsque l'évaluation est terminée ou clôturée");
        }

        if ($EvalDetails['details']['started'] == 1 && $EvalDetails['details']['hasValidatedSubject'] == 1) {
            $this->response("Impossible d'ajouter un sujet.L'évaluation est en cours et possède déjà un sujet validé");
        }
    }

    protected function checkEvalStateBeforeAddCorrection($EvalDetails) {

        if (empty($EvalDetails['details'])) {
            $this->response("Données non trouvées. Réessayez svp !");
        }

        if ($EvalDetails['details']['planned'] == 1 || $EvalDetails['details']['started'] == 1) {
            $this->response("Impossible d'ajouter un sujet lorsque l'évaluation est dans l'état planifié ou en cours");
        }

        /* if($EvalDetails['details']['started'] == 1 && $EvalDetails['details']['hasValidatedSubject'] == 1){
          $this->response("Impossible d'ajouter un sujet.L'évaluation est en cours et possède déjà un sujet validé");
          } */
    }

    protected function checkEvalStateBeforeAddCopy($EvalDetails) {

        if (empty($EvalDetails['details'])) {
            $this->response("Données non trouvées. Réessayez svp !");
        }

        if ($EvalDetails['details']['ended'] == 1 || $EvalDetails['details']['closed'] == 1 || $EvalDetails['details']['planned'] == 1) {
            $this->response("Impossible d'ajouter votre copie, l'évaluation est planifiée, terminée ou clôturée");
        }

        if ($EvalDetails['details']['started'] == 1) {
            //Je recherche les dates de debut et de fin
            $getSessions = $EvalDetails['details']['sessions'];

            $size = count($getSessions);

            $dateDebut = null;
            $dateFin = null;

            if ($size == 1) {
                $dateDebut = $getSessions[0]['startDate'];
                $dateFin = $getSessions[0]['endDate'];
            } else if ($size > 1) {
                $dateDebut = $getSessions[0]['startDate'];
                $dateFin = $getSessions[$size - 1]['endDate'];
            } else {
                $this->response("Une erreur inattendue s'est produite. Reessayez plus tard");
            }
            //Je vérifie à travers les dates que l'examen est toujours en cours
            if ($dateDebut !== null && $dateFin !== null) {
                $todayIs = new DateTime(ApiController::getCurrentDate(true));

                $dateDebutIs = new DateTime($dateDebut);
                $dateFinIs = new DateTime($dateFin);

                if (($todayIs >= $dateDebutIs) && ($todayIs <= $dateFinIs)) {
                    
                } else {
                    $this->response("Impossible d'ajouter votre copie, le délai est passé");
                }
            } else {
                $this->response("Une erreur inattendue s'est produite. Reessayez plus tard");
            }
        } else {
            $this->response("Une erreur inattendue s'est produite. Reessayez plus tard");
        }
    }

    protected function checkEvalStateBeforeDownloadCorrection($EvalDetails) {

        if (empty($EvalDetails['details'])) {
            $this->response("Données non trouvées. Réessayez svp !");
        }

        if ($EvalDetails['details']['ended'] == 1 || $EvalDetails['details']['closed'] == 1) {
            //Je recherche les dates de debut et de fin
            $getSessions = $EvalDetails['details']['sessions'];

            $size = count($getSessions);

            $dateDebut = null;
            $dateFin = null;

            if ($size == 1) {
                $dateDebut = $getSessions[0]['startDate'];
                $dateFin = $getSessions[0]['endDate'];
            } else if ($size > 1) {
                $dateDebut = $getSessions[0]['startDate'];
                $dateFin = $getSessions[$size - 1]['endDate'];
            } else {
                $this->response("Une erreur inattendue s'est produite. Reessayez plus tard");
            }
            //Je vérifie que la date de fin de l'examen est bel et bien passée
            if ($dateFin !== null) {
                $todayIs = new DateTime(ApiController::getCurrentDate(true));

                $dateFinIs = new DateTime($dateFin);

                if ($todayIs < $dateFinIs) {
                    $this->response("Impossible de télécharger cette correction pour l'instant, attendez que l'évaluation soit terminée !");
                }
            } else {
                $this->response("Une erreur inattendue s'est produite. Reessayez plus tard");
            }
        } else {
            $this->response("Impossible de télécharger cette correction pour l'instant, l'évaluation n'est pas encore terminée/clôturée");
        }
    }

    protected function checkEvalStateBeforeDownloadSubjectForStudent($EvalDetails) {

        if (empty($EvalDetails['details'])) {
            $this->response("Données non trouvées. Réessayez svp !");
        }

        if ($EvalDetails['details']['ended'] == 1 || $EvalDetails['details']['closed'] == 1 || $EvalDetails['details']['started'] == 1) {
            //Je recherche les dates de debut et de fin
            $getSessions = $EvalDetails['details']['sessions'];

            $size = count($getSessions);

            $dateDebut = null;
            $dateFin = null;

            if ($size == 1) {
                $dateDebut = $getSessions[0]['startDate'];
                $dateFin = $getSessions[0]['endDate'];
            } else if ($size > 1) {
                $dateDebut = $getSessions[0]['startDate'];
                $dateFin = $getSessions[$size - 1]['endDate'];
            } else {
                $this->response("Une erreur inattendue s'est produite. Reessayez plus tard");
            }
            //Je vérifie que la date de fin de l'examen est bel et bien passée
            if ($dateDebut !== null) {
                $todayIs = new DateTime(ApiController::getCurrentDate(true));

                $dateDebutIs = new DateTime($dateDebut);

                if ($todayIs < $dateDebutIs) {
                    $this->response("Impossible de télécharger le sujet pour l'instant, attendez le début de l'évaluation !");
                }
            } else {
                $this->response("Une erreur inattendue s'est produite. Reessayez plus tard");
            }
        } else {
            $this->response("Impossible de télécharger le sujet pour l'instant, attendez le début de l'évaluation !");
        }
    }

    protected function checkEvalStateBeforeProcessSubjects($EvalDetails) {
        if (empty($EvalDetails['details'])) {
            $this->response("Données non trouvées. Réessayez svp !");
        }

        if ($EvalDetails['details']['ended'] == 1 || $EvalDetails['details']['closed'] == 1) {
            $this->response("Impossible de valider/rejeter un sujet lorsque l'évaluation est terminée ou clôturée");
        }

        if ($EvalDetails['details']['started'] == 1 && $EvalDetails['details']['hasValidatedSubject'] == 1) {
            $this->response("Impossible de valider/rejeter un sujet lorsque l'évaluation est en cours et possède un sujet validé");
        }
    }

    protected function checkUploadParams() {

        $this->checkMethode("POST");

        $requestData = $this->getRequestData();

        if (empty($requestData['token'])) {
            $this->response("Paramètre token manquant");
        }
        if (empty($_FILES['files'])) {
            $this->response("Paramètre files manquant");
        }
        return $requestData;
    }

    protected static function cleanSpecialCaractere($string) {
        $special = array('/', '\\', '*', ':', '?', '<', '>', '|', '"');

        foreach ($special as $value) {
            $string = str_replace($value, ' ', $string);
        }

        return $string;
    }

    protected function getFileArchitectureFolderArray($EvalDetails, $uploadFileDirName = "sujets") {
        $startDate = $EvalDetails['details']['sessions'][0]['startDate'];

        return array(
            self::cleanSpecialCaractere($EvalDetails['details']['campusName']),
            self::cleanSpecialCaractere($EvalDetails['details']['groupName']),
            self::cleanSpecialCaractere($EvalDetails['details']['moduleName']),
            self::removeSpecificChar($startDate, ':', '-'),
            $uploadFileDirName,
            uniqid()
        );
    }

    public function uploadSubjectsToDir($uploadDirUrl) {
        $countUploadedFiles = 0;

        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            $file = FileHelper::saveMultipleFiles($_FILES['files'], $i, null, $uploadDirUrl);

            if ($file !== false) {
                $countUploadedFiles++;
            }
        }

        if ($countUploadedFiles !== count($_FILES['files']['name'])) {
            $this->response("Impossible d'ajouter ce sujet. Tous les fichiers n'ont pas été téléchargés. Reessayez svp !");
        }
    }

    public static function convertObjectIntoArray($objectInstance) {
        $result = (array) $objectInstance;

        $newArray = array();

        $className = get_class($objectInstance);
        $classLength = strlen($className);

        foreach ($result as $key => $value) {
            $pos = strpos($key, $className);

            if ($pos !== false) {
                $newArray[trim(substr_replace($key, "", $pos, $classLength))] = $value;
            } else {
                $newArray[trim($key)] = $value;
            }
        }
        return $newArray;
    }

    /**
     * 
     * @param datetime $date1 la date la plus petite au format anglais exple : 2019-08-08 07:00:00
     * @param datetime $date2 la date la plus grande au format anglais exple : 2019-08-15 07:00:00
     */
    public static function getDatesDiffIntoHours($date1, $date2) {
        $diff = strtotime($date2) - strtotime($date1);

        return $diff / 3600;
    }

    /**
     * Permet de transformer les dates de genius en format anglais
     * 
     * @param datetime $date contient la date au format genius
     * @return datetime la date au format anglais exple : 2019-08-08 07:00:00
     */
    public static function formatGeniusDate($date) {

        if (empty($date)) {
            return null;
        }

        $format = explode('.', $date);

        if (is_array($format) && !empty($format[0])) {
            return str_replace('T', ' ', $format[0]);
        } else {
            return $date;
        }
    }

    /**
     * Renvoie une info de la date courante
     * 
     * @param string $infos l'information que vous souhaitez recupérer ('year'; 'mounth' ; 'day' ; 'hour' ; 'min' ; 'sec')
     * @return string l'information demandée
     */
    public static function getCurrentDateInfos($infos = 'year') {

        date_default_timezone_set('Europe/Paris');

        $date = getdate();

        $jour = ($date['mday'] < 10) ? '0' . $date['mday'] : $date['mday'];
        $mois = ($date['mon'] < 10) ? '0' . $date['mon'] : $date['mon'];

        if (strtolower($infos) == 'year') {
            return $date['year'];
        } else if (strtolower($infos) == 'mounth') {
            return $mois;
        } else if (strtolower($infos) == 'day') {
            return $jour;
        } else if (strtolower($infos) == 'hour') {
            return $date['hours'];
        } else if (strtolower($infos) == 'min') {
            return $date['minutes'];
        } else if (strtolower($infos) == 'sec') {
            return $date['seconds'];
        } else {
            return null;
        }
    }

    /**
     * Permet de faire de requète graphQL
     * 
     * @param string $query contient le code graphQL qui contitue la requète
     * @param array $variables contient les valeurs des variables du code graphQL. Les noms des variables 
     * du code grapphQL constituent les clés du tableau 
     * @param string $endpoint contient l'url de l'API graphQL
     * @param string $token contient le token bearer
     * @return array un tableau avec les données de la requète demandée
     * @throws \ErrorException
     */
    protected function graphqlQuery($query, array $variables = [], $endpoint = null, $token = null) {
        $headers = ['Content-Type: application/json', "User-Agent: {$this->userAgent}"];
        if (null !== $token) {
            $headers[] = "Authorization: Bearer {$token}";
        } else {
            $headers[] = "Authorization: Bearer {$this->bearerToken}";
        }

        if (null === $endpoint) {
            $endpoint = $this->endpoint;
        }

        if (false === $data = @file_get_contents($endpoint, false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => $headers,
                        'content' => json_encode(['query' => $query, 'variables' => $variables]),
                    ]
                ]))) {
            $error = error_get_last();
            throw new \ErrorException($error['message'], $error['type']);
        }
        return json_decode($data, true);
    }

    protected function checkRoleAndRedirect(array $table) {
        if ($this->_activeAPI == true) {
            $this->response($table);
            die();
        }
        if (in_array('global-admin', $table['roles']['slug']) ||
                in_array('admin', $table['roles']['slug']) ||
                in_array('low-admin', $table['roles']['slug'])) {
            $_SESSION[self::$sessionName] = $table;
            $this->redirect('AdminHome/');
        }
    }

    protected function checkRole(array $table) {
        if ($this->_activeAPI == true) {
            $this->response($table);
            die();
        }
        if (in_array('global-admin', $table['roles']['slug']) || in_array('admin', $table['roles']['slug']) || in_array('low-admin', $table['roles']['slug'])) {
            $_SESSION[self::$sessionName] = $table;
            $this->redirect('AdminHome/');
        }
    }

    protected function verifierTypeChamps($tableOfData, $view = null, $title = null) {
        foreach ($tableOfData as $key => $value) {

            if ($key == 'token') {
                $controle_answer = self::checkField('alphanumeric', 'token', $value, true, 0, 100);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
            if ($key == 'uid') {
                $controle_answer = self::checkField('alphanumeric', 'unique uid', $value, true, 0, 100);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
            if ($key == 'date') {
                $controle_answer = self::checkField('alphanumeric', 'date', $value, true, 0, 10);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
            if ($key == 'nom') {
                $controle_answer = self::checkField('alphanumeric', 'nom', $value, true, 0, 20);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
            if ($key == 'prenom' || $key == 'prenoms') {
                $controle_answer = self::checkField('alphanumeric', 'prenom', $value, true, 0, 20);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
            if ($key == 'number' || $key == 'numero') {
                $controle_answer = self::checkField('alphanumeric', $key, $value, true, 0, 20);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
            if ($key == 'email') {
                $controle_answer = self::checkField('email', 'Email', $value, true, 0, 50);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
            if ($key == 'ancien_password') {
                $controle_answer = self::checkField('alpha', 'Mot de passe actuel', $value, true, 0, 100);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
            if ($key == 'password') {
                $controle_answer = self::checkField('alpha', 'Mot de passe', $value, true, 0, 100);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
            if ($key == 'cpassword') {
                $controle_answer = self::checkField('alpha', 'Confirmez Nouveau Mot de passe', $value, true, 0, 100);
                ($view === null AND is_string($controle_answer)) ? $this->response($controle_answer) : $this->sendErrorMsg($controle_answer, $view, $title);
            }
        }
    }

    /**
     * Méthode appelée lors de la déconnexion d'un utilisateur
     * 
     */
    protected function deconnexion() {

        // On démarre la session
        //session_start();
        // On détruit les variables de notre session
        session_unset();

        // On détruit notre session
        session_destroy();
    }

}
