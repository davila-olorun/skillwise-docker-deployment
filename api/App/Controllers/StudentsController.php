<?php

class StudentsController extends ApiController implements AppInterface {

    private $Students = null;
    
    public function __construct() {
        parent::__construct();
        //$this->loadModel("Students");
        $this->Students = new StudentsClass();
        $this->verifyAccessToken();
    }

    /*
     * Methode permettant de recuperer les examens d'un etudiant: planifié, encours, fini, cloturé
     * @param string $uid unique ID de l'étudiant connecté recupere de manière implicite
     * @return array $rps tableau de reponse 
     */

    public function getStudentByUid() {
        $this->checkMethode('GET');
        $requestData = $this->getRequestData();

        if (isset($requestData['uid'])) {
            $this->verifierTypeChamps($requestData);
            $uid = self::cleanner($requestData['uid']);

            $rps = $this->Students->getUserData($uid);

            if (is_array($rps) and ! empty($rps)) {
                $this->response($rps);
            }
            $this->response($rps);
        }
        $this->response('ko');
    }

    public function getDetailExamStudent() {
        $this->checkMethode('GET');
        $requestData = $this->getRequestData();

        if (isset($requestData['uid'])) {
            // $this->verifierTypeChamps($requestData);
            $uid = self::cleanner($requestData['uid']);
            $rps = $this->Students->getDetailExam($uid);

            if (is_array($rps) and ! empty($rps)) {
                $this->response($rps);
            }
            $this->response($rps);
        }
        $this->response('ko');
    }

    /*
     * Methode permettant de recuperer les examens d'un etudiant: terminés et ou cloturés avec ou sans copies
     * @param string $uid unique ID de l'étudiant connecté recupere de manière implicite
     * @return array $rps tableau de reponse 
     */

    public function getHistoriqueExam() {
        $this->checkMethode('GET');
        $requestData = $this->getRequestData();

        if (isset($requestData['uid'])) {
            $uid = self::cleanner($requestData['uid']);
            $rps = $this->Students->getAllExamTerminateClose($uid);

            if (is_array($rps) and ! empty($rps)) {
                $this->response($rps);
            }
            $this->response($rps);
        }
        $this->response('ko');
    }

    public function getDocument() {
        $this->checkMethode('GET');
        $requestData = $this->getRequestData();

        if (isset($requestData['uid']) AND ! empty($requestData['uid'])) {
            $this->verifierTypeChamps($requestData);
            $uidexam = self::cleanner($requestData['uid']);

            $rps = $this->Users->getDoc($uidexam);

            $this->response($rps);
        }
        $this->response('ko');
    }

    public function downloadFile() {
        $this->checkMethode('GET');
        $requestData = $this->getRequestData();

        if (isset($requestData['uid']) AND ! empty($requestData['uid'])) {
            //$this->saveFile($file, $prefix_name);
        }
    }

    public function getStudentsEvalsAndCopiesStats() {
        $this->checkMethode('GET');

        /*$requestData = $this->getRequestData();

        if (empty($requestData['token'])) {
            $this->response("Erreur : paramètre incorrecte");
        }*/

        $result['data'] = $this->Students->getEvalsAndCopiesStats(self::cleanner($this->loggedUser['uid']));

        $this->response($result);
    }

    public function uploadCopies() {
        //Verification et récupération des paramètres de la requète
        $requestData = $this->checkUploadParams();

        if (empty($requestData['userToken'])) {
            $this->response("Paramètre user token manquant");
        }

        $this->loadModel('Teachers');

        //Récupération des détails de l'évaluation
        $token = self::cleanner($requestData['token']);
        $EvalDetails = $this->Teachers->getEvaluationDetails($token);

        //Vérifions que l'etudiant est inscrit dans le group
        $userToken = self::cleanner($requestData['userToken']);
        $registerInfo = $this->Students->studentIsRegiterInGroup($EvalDetails['details']['groupId'], $userToken);

        if (empty($registerInfo)) {
            $this->response("Désolez vous n'êtes pas inscrit à ce module");
        }

        //Vérification de l'état de l'évaluation
        $this->checkEvalStateBeforeAddCopy($EvalDetails);

        //Récupération du tableau contenant l'architecture des dossiers des sujets
        $architecture = $this->getFileArchitectureFolderArray($EvalDetails, "copies");

        //Création des dossiers
        $relativeDirPath = FileHelper::createDir($architecture);
        if ($relativeDirPath == false) {
            $this->response("Impossible d'ajouter votre copie. Une erreur inattendue s'est produite. Reessayez plus tard svp !");
        }
        //url du dossier temporaire dans lequel les fichiers seront uploader
        $uploadDir = _FILESDIRPATH_ . $relativeDirPath;

        //Upload des sujets
        $this->uploadSubjectsToDir($uploadDir);

        $zipper = FileHelper::zipDir($registerInfo[0]['lastName'], $uploadDir);

        if ($zipper == false) {
            FileHelper::deleteDir($uploadDir);
            $this->response("Impossible de zipper les fichiers téléchargés. Reessayez svp !");
        }
        //Suppression du dossier archiver
        FileHelper::deleteDir($uploadDir);

        //Retrait de l'antislash à la fin
        $zipFileRelativeUrl = substr($relativeDirPath, 0, -1);

        //retrait du nom du fichier temporaire dans l'url
        $relativeNewDirPath = str_replace($architecture[count($architecture) - 1], '', $zipFileRelativeUrl);

        $data = array(
            'idEval' => $EvalDetails['details']['evalId'],
            'idUser' => $registerInfo[0]['userId'],
            'url' => str_replace('\\', '/', $relativeNewDirPath) . $architecture[count($architecture) - 1] . $zipper,
            'date' => self::getCurrentDate(true)
        );

        $rps = $this->Students->uploadCopies($data);

        if (is_int($rps['rps']) && $rps['rps'] > 0) {
            $docData = array(
                'idgrade' => $rps['rps'],
                'url' => $architecture[count($architecture) - 1] . $zipper,
                'daterendu' => $data['date']
            );
            $this->response(array('success' => $docData));
        } else if ($rps['rps'] == 'Existe') {
            $this->response("Vous avez déjà enregistré une copie");
        } else {
            //En cas d'échec, supprime le fichier zip
            FileHelper::deleteFile($data['name'], _FILESDIRPATH_ . $relativeNewDirPath);
            $this->response("Une erreur inattendue s'est produits. Reessayez svp !");
        }
    }
}
