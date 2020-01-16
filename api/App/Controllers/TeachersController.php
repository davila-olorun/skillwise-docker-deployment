<?php

class TeachersController extends ApiController implements AppInterface{
    private $Teachers = null;
    
    public function __construct() {
        parent::__construct();
        //$this->loadModel('Teachers');
        $this->Teachers = new TeachersClass();
        $this->verifyAccessToken();
    }
    public function view() {
        parent::view();
    }
    public function uploadSubjects(){
        //Verification et récupération des paramètres de la requète
        $requestData = $this->checkUploadParams();
        
        if(empty($requestData['userToken'])){
            $this->response("Paramètre user token manquant");
        }
        
        //Récupération des détails de l'évaluation
        $token = self::cleanner($requestData['token']);
        $EvalDetails = $this->Teachers->getEvaluationDetails($token);
        
        //Vérifions si c'est bien le prof du module
        $userToken = self::cleanner($requestData['userToken']);
        if(isset($EvalDetails['details']['teacher']['uid']) && $userToken !== $EvalDetails['details']['teacher']['uid']){
            $this->response("Désolez vous n'êtes pas le professeur de cette évaluation");
        }
        //Vérification de l'état de l'évaluation
        $this->checkEvalStateBeforeAddSubject($EvalDetails);
        
        //Récupération du tableau contenant l'architecture des dossiers des sujets
        $architecture = $this->getFileArchitectureFolderArray($EvalDetails);
        
        //Création des dossiers
        $relativeDirPath = FileHelper::createDir($architecture);
        if($relativeDirPath == false){
            $this->response("Impossible d'ajouter ce sujet. Une erreur inattendue s'est produite. Reessayez plus tard svp !"); 
        }
        //url du dossier temporaire dans lequel les fichiers seront uploader
        $uploadDir = _FILESDIRPATH_.$relativeDirPath;
        
        //Upload des sujets
        $this->uploadSubjectsToDir($uploadDir);
        
        $version = count($EvalDetails['details']['subjects']) + 1;
        
        $zipper = FileHelper::zipDir("sujet-eval-v".$version,$uploadDir);
        
        if($zipper == false){
            FileHelper::deleteDir($uploadDir);
            $this->response("Impossible de zipper les fichiers téléchargés. Reessayez svp !"); 
        }
        //Suppression du dossier archiver
        FileHelper::deleteDir($uploadDir);
        
        //Retrait de l'antislash à la fin
        $zipFileRelativeUrl = substr($relativeDirPath, 0, -1);
        
        //retrait du nom du fichier temporaire dans l'url
        $relativeNewDirPath = str_replace($architecture[count($architecture)-1], '', $zipFileRelativeUrl);
        
        $data = array(
            'idEval' => $EvalDetails['details']['evalId'],
            'version' => $version,
            'name' =>$architecture[count($architecture)-1].$zipper,
            'url' => str_replace('\\', '/', $relativeNewDirPath),
            'date' => self::getCurrentDate(true)
        );
        
        $rps = $this->Teachers->uploadSubject($data);
        
        if(is_int($rps['rps']) && $rps['rps'] > 0){
            $docData = array(
                'docCreatedDate' => $data['date'],
                'docId' => $rps['rps'],
                'docName' => $data['name'],
                'docUpdatedDate' => $data['date'],
                'docValidated' => null,
                'docVersion' => $data['version'],
                'evalId' => $data['idEval']
            );
            $this->response(array('success' => $docData));
        }else{
            //En cas d'échec, supprime le fichier zip
            FileHelper::deleteFile($data['name'], _FILESDIRPATH_.$relativeNewDirPath);
            $this->response("Une erreur inattendue s'est produits. Reessayez svp !");
        }
    }
    public function uploadCorrections(){
        //Verification et récupération des paramètres de la requète
        $requestData = $this->checkUploadParams();
        
        if(empty($requestData['userToken'])){
            $this->response("Paramètre user token manquant");
        }
        
        //Récupération des détails de l'évaluation
        $token = self::cleanner($requestData['token']);
        $EvalDetails = $this->Teachers->getEvaluationDetails($token);
        
        //Vérifions si c'est bien le prof du module
        $userToken = self::cleanner($requestData['userToken']);
        if(isset($EvalDetails['details']['teacher']['uid']) && $userToken !== $EvalDetails['details']['teacher']['uid']){
            $this->response("Désolez vous n'êtes pas le professeur de cette évaluation");
        }
        
        //Vérification de l'état de l'évaluation
        $this->checkEvalStateBeforeAddCorrection($EvalDetails);
        
        //Récupération du tableau contenant l'architecture des dossiers des sujets
        $architecture = $this->getFileArchitectureFolderArray($EvalDetails, "corrections");
        
        //Création des dossiers
        $relativeDirPath = FileHelper::createDir($architecture);
        if($relativeDirPath == false){
            $this->response("Impossible d'ajouter cette correction. Une erreur inattendue s'est produite. Reessayez plus tard svp !"); 
        }
        //url du dossier temporaire dans lequel les fichiers seront uploader
        $uploadDir = _FILESDIRPATH_.$relativeDirPath;
        
        //Upload des sujets
        $this->uploadSubjectsToDir($uploadDir);
        
        $vers = $this->Teachers->countEvalCorrections($EvalDetails['details']['evalId']);
        $version = $vers + 1;
        
        $zipper = FileHelper::zipDir("correction-eval-v".$version,$uploadDir);
        
        if($zipper == false){
            FileHelper::deleteDir($uploadDir);
            $this->response("Impossible de zipper les fichiers téléchargés. Reessayez svp !"); 
        }
        //Suppression du dossier archiver
        FileHelper::deleteDir($uploadDir);
        
        //Retrait de l'antislash à la fin
        $zipFileRelativeUrl = substr($relativeDirPath, 0, -1);
        
        //retrait du nom du fichier temporaire dans l'url
        $relativeNewDirPath = str_replace($architecture[count($architecture)-1], '', $zipFileRelativeUrl);
        
        $data = array(
            'idEval' => $EvalDetails['details']['evalId'],
            'version' => $version,
            'name' =>$architecture[count($architecture)-1].$zipper,
            'url' => str_replace('\\', '/', $relativeNewDirPath),
            'date' => self::getCurrentDate(true)
        );
        
        $rps = $this->Teachers->uploadCorrection($data);
        
        if(is_int($rps['rps']) && $rps['rps'] > 0){
            $docData = array(
                'docCreatedDate' => $data['date'],
                'docId' => $rps['rps'],
                'docName' => $data['name'],
                'docUpdatedDate' => $data['date'],
                'docValidated' => 1,
                'docVersion' => $data['version'],
                'evalId' => $data['idEval']
            );
            $this->response(array('success' => $docData));
        }else{
            //En cas d'échec, supprime le fichier zip
            FileHelper::deleteFile($data['name'], _FILESDIRPATH_.$relativeNewDirPath);
            $this->response("Une erreur inattendue s'est produits. Reessayez svp !");
        }
    }
    public function getEvaluations($page = null) {
        $this->checkMethode("GET");
        
        if($page === null){
           $page = 1;
        }
        
        $requestData = $this->getRequestData();
        $token = $requestData['ttoken'];
        
        if(empty($token)){
            parent::view();
        }
        
        $EvalStats = $this->Teachers->getAllEvaluations(self::cleanner($token), self::cleanner($page));
        
        $EvalStats['page'] = $page;
        
        $this->response($EvalStats);
    }
    public function getEvalDetails() {
        $this->checkMethode("GET");
        
        $requestData = $this->getRequestData();
        
        if(empty($requestData['token'])){
            parent::view();
        }
        
        $EvalDetails = $this->Teachers->getEvaluationDetails(self::cleanner($requestData['token']));
        
        if(empty($EvalDetails['details'])){
            $this->response("Données non trouvées. Réessayez svp !");
        }else{
           $this->response($EvalDetails); 
        }
        
    }
    public function getEvalsStats(){
        $this->checkMethode("GET");
        
        $requestData = $this->getRequestData();
        $token = $requestData['ttoken'];
        
        if(empty($token)){
            parent::view();
        }
        
        $EvalStats = $this->Teachers->getAllEvaluationsStatistiques(self::cleanner($token));
        
        $this->response($EvalStats);
    }
    public function getEvalsCopiesAndEvalsARisque(){
        $this->checkMethode("GET");
        
        $requestData = $this->getRequestData();
        $token = $requestData['ttoken'];
        
        if(empty($token)){
            parent::view();
        }
        
        $EvalCopies = $this->Teachers->getAllEvaluationsCopiesStats(self::cleanner($token));
        
        $EvalARisque = $this->Teachers->getAllEvaluationsARisqueDetails(self::cleanner($token));
        
        $evals = $EvalCopies;
        $evals['evalARisque'] = $EvalARisque;
        
        $this->response($evals); 
        /*= array( 'totalCopies' => array(
            'rendues' => 0,
            'nonRendues' => 0
        ), 'evalsTerm' => array(), 'evalARisque' => array())*/
    }
    public function getNextEvals(){
        $this->checkMethode("GET");
        
        $requestData = $this->getRequestData();
        $token = $requestData['ttoken'];
        
        if(empty($token)){
            parent::view();
        }
        
        $nextEvals = $this->Teachers->getNextEvaluations(self::cleanner($token));
        
        $evals = $nextEvals;
        
        $this->response($evals);
    }
    public function getGroupsAndStudents(){
        $this->checkMethode("GET");
        
        $data = $this->Teachers->getGroupsAndStudents();
        
        $this->response($data);
    }
}
