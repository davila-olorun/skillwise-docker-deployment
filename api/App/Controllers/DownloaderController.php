<?php

class DownloaderController extends ApiController implements AppInterface{
    private $Managers = null;
    
    public function __construct() {
        parent::__construct();
        //$this->loadModel('Managers');
        $this->Managers = new ManagersClass();
        $this->checkMethode("GET");
        $this->verifyAccessToken();
    }
    public function view() {
        parent::view();
    }
    /**
     * Permet de télécharger un sujet d'évaluation
     * 
     * @param string $evalUid uid de l'évaluation
     * @param string $userUid uid de l'utilisateur connecté
     * @param string $docId id du document à télécharger
     * @param boolean $download true pour lancer le téléchargement et false pour renvoyer le nom du fichier
     */
    public function subject($evalUid = null, $userUid = null, $docId = null, $download = false){
        $this->checkParams($evalUid, $userUid, $docId);
        
        $docId = self::cleanner($docId);
        $userUid = self::cleanner($userUid);
        
        //Vérification role de celui qui souhaite télécharger le document        
        if(!$this->Managers->checkMinRoleBeforeDownload($userUid, 200)){
            $this->response("Accès refusé, vous n'êtes pas autorisé à télécharger ce fichier");
        }
        
        //Récupération du document
        $data = $this->Managers->getDocumentData($docId);
        
        $this->checkFileExiste($data);
        
        $fileName = $data[0]['name'];
        $fileUrl = $data[0]['url'].$data[0]['name'];
        
        $this->download($fileName, $fileUrl, $download);
    }
    /**
     * Permet de télécharger une correction pour les admins et les profs
     * 
     * @param string $evalUid uid de l'évaluation
     * @param string $userUid uid de l'utilisateur connecté
     * @param string $docId id du document à télécharger
     * @param boolean $download true pour lancer le téléchargement et false pour renvoyer le nom du fichier
     */
    public function correction($evalUid = null, $userUid = null, $docId = null, $download = false){
        $this->checkParams($evalUid, $userUid, $docId);
        
        $docId = self::cleanner($docId);
        $evalUid = self::cleanner($evalUid);
        $userUid = self::cleanner($userUid);
        
        //Vérification role de celui qui souhaite télécharger le document        
        if(!$this->Managers->checkMinRoleBeforeDownload($userUid, 200)){
            $this->response("Accès refusé, vous n'êtes pas autorisé à télécharger ce fichier");
        }
        
        //Récupération des détails de l'évaluation
        $EvalDetails = $this->Managers->getEvaluationDetails($evalUid);
        
        //Vérification de l'état de l'évaluation
        $this->checkEvalStateBeforeDownloadCorrection($EvalDetails);
        
        $data = $this->Managers->getDocumentData($docId);
        
        $this->checkFileExiste($data);
        
        $fileName = $data[0]['name'];
        $fileUrl = $data[0]['url'].$data[0]['name'];
        
        $this->download($fileName, $fileUrl, $download);
    }
    /**
     * Permet de télécharger le sujet d'évaluation pour un étudiant
     * 
     * @param string $evalUid uid de l'évaluation
     * @param string $userUid uid de l'utilisateur connecté
     * @param string $docId id du document à télécharger
     * @param boolean $download true pour lancer le téléchargement et false pour renvoyer le nom du fichier
     */
    public function subjectForStudent($evalUid = null, $userUid = null, $docId = null, $download = false){
        $this->checkParams($evalUid, $userUid, $docId);
        
        $docId = self::cleanner($docId);
        $evalUid = self::cleanner($evalUid);
        $userUid = self::cleanner($userUid);
        
        //Vérification role de celui qui souhaite télécharger le document        
        /*if(!$this->Managers->checkMinRoleBeforeDownload($userUid, 200)){
            $this->response("Accès refusé, vous n'êtes pas autorisé à télécharger ce fichier");
        }*/
        
        //Récupération des détails de l'évaluation
        $EvalDetails = $this->Managers->getEvaluationDetails($evalUid);
        
        //Vérification de l'état de l'évaluation
        $this->checkEvalStateBeforeDownloadSubjectForStudent($EvalDetails);
        
        $data = $this->Managers->downloadSubjectForStudent($EvalDetails['details']['evalId']);
        
        $this->checkFileExiste($data);
        
        $fileName = $data[0]['name'];
        $fileUrl = $data[0]['url'].$data[0]['name'];
        
        $this->download($fileName, $fileUrl, $download);
    }
    /**
     * Permet de télécharger la copie d'un étudiant
     * 
     * @param string $evalUid uid de l'évaluation
     * @param string $userUid uid de l'utilisateur connecté
     * @param string $docId id du document à télécharger
     * @param boolean $download true pour lancer le téléchargement et false pour renvoyer le nom du fichier
     */
    public function studentLeave($evalUid = null, $userUid = null, $docId = null, $download = false){
        $this->checkParams($evalUid, $userUid, $docId, false);
        
        $docId = self::cleanner($docId);
        $evalUid = self::cleanner($evalUid);
        $userUid = self::cleanner($userUid);
        
        $data = $this->Managers->downloadStudentLeave($docId, $evalUid, $userUid);
        
        $this->checkFileExiste($data);
        
        $getFileName = explode("/", $data[0]['leaveUrl']);
        
        $fileName = $getFileName[count($getFileName) - 1];
        $fileUrl = $data[0]['leaveUrl'];
        
        $this->download($fileName, $fileUrl, $download);
    }
    
    /**
     * Permet de télécharger une correction pour un étudiant
     * 
     * @param string $evalUid uid de l'évaluation
     * @param string $userUid uid de l'utilisateur connecté
     * @param string $docId id du document à télécharger
     * @param boolean $download true pour lancer le téléchargement et false pour renvoyer le nom du fichier
     */
    public function correctionForStudent($evalUid = null, $userUid = null, $docId = null, $download = false) {
        $this->checkParams($evalUid, $userUid, $docId);
        
        $docId = self::cleanner($docId);
        $evalUid = self::cleanner($evalUid);
        $userUid = self::cleanner($userUid);
        
        //Récupération des détails de l'évaluation
        $EvalDetails = $this->Managers->getEvaluationDetails($evalUid);
        
        //Vérification de l'état de l'évaluation
        $this->checkEvalStateBeforeDownloadCorrection($EvalDetails);
        
        $data = $this->Managers->getDocumentData($docId);
        
        $this->checkFileExiste($data);
        
        $fileName = $data[0]['name'];
        $fileUrl = $data[0]['url'].$data[0]['name'];
        
        $this->download($fileName, $fileUrl, $download);
    }
    /**
     * Permet de vérifier les paramètres requis pour télécharger un fichier
     * 
     * @param string $evalUid uid de l'évaluation
     * @param string $userUid uid de l'utilisateur connecté
     * @param string $docId id du document à télécharger
     * @param boolean $checkUser true pour dire que le paramètre $useruid est obligatoire
     */
    private function checkParams($evalUid = null, $userUid = null, $docId = null, $checkUser = true) {
        if($checkUser == true){
            if(empty($docId) || empty($evalUid) || empty($userUid)){
                $this->response("Paramètres manquants");
            }
        }else{
            if(empty($docId) || empty($evalUid)){
                $this->response("Paramètres manquants");
            }
        }
    }
    /**
     * Permet de vérifier si les données du fichier à télécharger existe
     * 
     * @param array $data contient les données du fichier à télécharger
     */
    private function checkFileExiste($data){
        if(empty($data)){
            $this->response("Fichier introuvable");
        }
    }
    /**
     * Permet de lancer un téléchargement
     * 
     * @param string $fileName nom du fichier
     * @param string $fileUrl url complète du fichier depuis la racine du disque
     * @param boolean $download true pour lancer le téléchargement et false pour renvoyer juste le nom du fichier
     */
    private function download($fileName, $fileUrl, $download = false){
        $fileUrl1 = _FILESLINKPATH_.$fileUrl;
        
        //$fileUrl2 = _FILESDIRPATH_.str_replace('/', '\\', $fileUrl); sur un système windows
        $fileUrl2 = _FILESDIRPATH_.$fileUrl; //sur un système linux
        
        if(!file_exists($fileUrl2)){
            $this->response("Fichier introuvable");
        }
        
        if($download == false){
            $this->response(array('success'=>$fileName));
        }
        
        // Création des headers, pour indiquer au navigateur qu'il s'agit d'un fichier à télécharger
        header('Content-Disposition: attachment; filename="'.$fileName.'"'); //Nom du fichier
        header("Content-Type: application/zip" );
        header('Content-Transfer-Encoding: binary'); //Transfert en binaire (fichier)
        header('Content-Length: ' . filesize($fileUrl1)); //Taille du fichier
        header("Pragma: no-cache" );
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0" );
        header("Expires: 0" );
        
        //Envoi du fichier dont le chemin est passé en paramètre
        readfile($fileUrl1);
    }
}
