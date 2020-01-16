<?php

class ManagersController extends ApiController implements AppInterface {
    
    private $Managers = null;
    public $notifications;

    public function __construct() {
        parent::__construct();
        //$this->loadModel('Managers');
        $this->Managers = new ManagersClass();
        $this->verifyAccessToken();
        $this->notifications = new NotificationsClass();
    }

    public function view() {
        parent::view();
    }

    public function uploadSubjects() {
        //Verification et récupération des paramètres de la requète
        $requestData = $this->checkUploadParams();

        //Récupération des détails de l'évaluation
        $token = self::cleanner($requestData['token']);
        $EvalDetails = $this->Managers->getEvaluationDetails($token);

        //Vérification de l'état de l'évaluation
        $this->checkEvalStateBeforeAddSubject($EvalDetails);

        //Récupération du tableau contenant l'architecture des dossiers des sujets
        $architecture = $this->getFileArchitectureFolderArray($EvalDetails);

        //Création des dossiers
        $relativeDirPath = FileHelper::createDir($architecture);
        if ($relativeDirPath == false) {
            $this->response("Impossible d'ajouter ce sujet. Une erreur inattendue s'est produite. Reessayez plus tard svp !");
        }
        //url du dossier temporaire dans lequel les fichiers seront uploader
        $uploadDir = _FILESDIRPATH_ . $relativeDirPath;

        //Upload des sujets
        $this->uploadSubjectsToDir($uploadDir);

        $version = count($EvalDetails['details']['subjects']) + 1;

        $zipper = FileHelper::zipDir("sujet-eval-v" . $version, $uploadDir);

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
            'version' => $version,
            'name' => $architecture[count($architecture) - 1] . $zipper,
            'url' => str_replace('\\', '/', $relativeNewDirPath),
            'date' => self::getCurrentDate(true)
        );

        $rps = $this->Managers->uploadSubject($data);

        if (is_int($rps['rps']) && $rps['rps'] > 0) {
            $docData = array(
                'docCreatedDate' => $data['date'],
                'docId' => $rps['rps'],
                'docName' => $data['name'],
                'docUpdatedDate' => $data['date'],
                'docValidated' => 1,
                'docVersion' => $data['version'],
                'evalId' => $data['idEval']
            );
            $codeAndEvalName = $EvalDetails['details']['moduleCode'] . " " . $EvalDetails['details']['moduleName'];
            $evalUid = $token;
            if (!empty($EvalDetails['details']['teacher'])) {
                $this->notifications->notifyTeacherAboutNewFileUploading($EvalDetails['details']['teacher']['uid'], $evalUid, $codeAndEvalName, 'subject');
            }
            $this->response(array('success' => $docData));
        } else {
            //En cas d'échec, supprime le fichier zip
            FileHelper::deleteFile($data['name'], _FILESDIRPATH_ . $relativeNewDirPath);
            $this->response("Une erreur inattendue s'est produits. Reessayez svp !");
        }
    }

    public function uploadCorrections() {
        //Verification et récupération des paramètres de la requète
        $requestData = $this->checkUploadParams();

        //Récupération des détails de l'évaluation
        $token = self::cleanner($requestData['token']);
        $EvalDetails = $this->Managers->getEvaluationDetails($token);

        //Vérification de l'état de l'évaluation
        $this->checkEvalStateBeforeAddCorrection($EvalDetails);

        //Récupération du tableau contenant l'architecture des dossiers des sujets
        $architecture = $this->getFileArchitectureFolderArray($EvalDetails, "corrections");

        //Création des dossiers
        $relativeDirPath = FileHelper::createDir($architecture);
        if ($relativeDirPath == false) {
            $this->response("Impossible d'ajouter cette correction. Une erreur inattendue s'est produite. Reessayez plus tard svp !");
        }
        //url du dossier temporaire dans lequel les fichiers seront uploader
        $uploadDir = _FILESDIRPATH_ . $relativeDirPath;

        //Upload des sujets
        $this->uploadSubjectsToDir($uploadDir);

        $vers = $this->Managers->countEvalCorrections($EvalDetails['details']['evalId']);
        $version = $vers + 1;

        $zipper = FileHelper::zipDir("correction-eval-v" . $version, $uploadDir);

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
            'version' => $version,
            'name' => $architecture[count($architecture) - 1] . $zipper,
            'url' => str_replace('\\', '/', $relativeNewDirPath),
            'date' => self::getCurrentDate(true)
        );

        $rps = $this->Managers->uploadCorrection($data);

        if (is_int($rps['rps']) && $rps['rps'] > 0) {
            $docData = array(
                'docCreatedDate' => $data['date'],
                'docId' => $rps['rps'],
                'docName' => $data['name'],
                'docUpdatedDate' => $data['date'],
                'docValidated' => 1,
                'docVersion' => $data['version'],
                'evalId' => $data['idEval']
            );
            $codeAndEvalName = $EvalDetails['details']['moduleCode'] . " " . $EvalDetails['details']['moduleName'];
            $evalUid = $token;
            if (!empty($EvalDetails['details']['teacher'])) {
                $this->notifications->notifyTeacherAboutNewFileUploading($EvalDetails['details']['teacher']['uid'], $evalUid, $codeAndEvalName, 'correction');
            }
            $this->response(array('success' => $docData));
        } else {
            //En cas d'échec, supprime le fichier zip
            FileHelper::deleteFile($data['name'], _FILESDIRPATH_ . $relativeNewDirPath);
            $this->response("Une erreur inattendue s'est produits. Reessayez svp !");
        }
    }

    public function getEvaluations($page = null) {
        $this->checkMethode("GET");

        if ($page === null) {
            $page = 1;
        }

        $EvalStats = $this->Managers->getAllEvaluations(self::cleanner($page));

        $EvalStats['page'] = $page;

        $this->response($EvalStats);
    }

    public function getEvalDetails() {
        $this->checkMethode("GET");

        $requestData = $this->getRequestData();

        if (empty($requestData['token'])) {
            parent::view();
        }

        $EvalDetails = $this->Managers->getEvaluationDetails(self::cleanner($requestData['token']));

        if (empty($EvalDetails['details'])) {
            $this->response("Données non trouvées. Réessayez svp !");
        } else {
            $this->response($EvalDetails);
        }
    }

    public function getEvalsStats() {
        $this->checkMethode("GET");

        $EvalStats = $this->Managers->getAllEvaluationsStatistiques();

        $this->response($EvalStats);
    }

    public function getEvalsCopiesAndEvalsARisque() {
        $this->checkMethode("GET");

        $EvalCopies = $this->Managers->getAllEvaluationsCopiesStats();

        $EvalARisque = $this->Managers->getAllEvaluationsARisqueDetails();

        $evals = $EvalCopies;
        $evals['evalARisque'] = $EvalARisque;

        $this->response($evals);
    }

    public function getLastEvals() {
        $this->checkMethode("GET");

        $lastEvals = $this->Managers->getLastEvaluations();

        $evals = $lastEvals;

        $this->response($evals);
    }

    public function getGroupsAndStudents() {
        $this->checkMethode("GET");

        $data = $this->Managers->getGroupsAndStudents();

        $this->response($data);
    }

    public function processSubjects() {
        $this->checkMethode("POST");

        $requestData = $this->getRequestData();

        if (empty($requestData['token']) || empty($requestData['utoken']) || empty($requestData['ftoken']) || empty($requestData['atoken'])) {
            $this->response("Paramètres manquant");
        }

        $evalUid = self::cleanner($requestData['token']);
        $userUid = self::cleanner($requestData['utoken']);
        $fileId = self::cleanner($requestData['ftoken']);
        $action = self::cleanner($requestData['atoken']);

        //Vérification role de celui qui souhaite télécharger le document        
        if (!$this->Managers->checkMinRoleBeforeDownload($userUid, 500)) {
            $this->response("Accès refusé, vous n'êtes pas autorisé à effectuer cette action");
        }

        //Récupération des détails de l'évaluation
        $EvalDetails = $this->Managers->getEvaluationDetails($evalUid);

        //Vérification de l'état de l'évaluation
        $this->checkEvalStateBeforeProcessSubjects($EvalDetails);

        $doc = new Documents();
        $doc->setIdDoc($fileId);
        $doc->setUpdatedAt(self::getCurrentDate(true));

        if ($action == 'val') {
            $doc->setValidated(1);
        } else if ($action == 'ref') {
            $doc->setValidated(2);
        }

        $rep = $this->Managers->processSubjects($doc);

        if (is_int($rep['rps']) && $rep['rps'] >= 1) {
            $codeAndEvalName = $EvalDetails['details']['moduleCode'] . " " . $EvalDetails['details']['moduleName'];
            if ($action == 'val' && !empty($EvalDetails['details']['teacher'])) {
                $this->notifications->notifyTeacherAboutSujectState($EvalDetails['details']['teacher']['uid'], $evalUid, $codeAndEvalName, true);
            } else if ($action == 'ref' && !empty($EvalDetails['details']['teacher'])) {
                $this->notifications->notifyTeacherAboutSujectState($EvalDetails['details']['teacher']['uid'], $evalUid, $codeAndEvalName);
            }
            $this->response(array('success' => $action));
        } else {
            $this->response("Une erreur inattendue s'est produite, reessayez plus tard svp !");
        }

        $this->response("Erreur ! Les paramètres envoyés sont incorrects");
    }

    public function getProfil() {
        $this->checkMethode('GET');

        $requestData = $this->getRequestData();

        if (empty($requestData['uid'])) {
            $this->response("Erreur : paramètre incorrecte");
        }
        $this->loadModel("Students");
        $profil = $this->Students->userProfil(self::cleanner($requestData['uid']));
        $this->response($profil);
    }

    public function uploadProfile() {
        $this->checkMethode('POST');
        $requestData = $this->getRequestData();

        if (empty($requestData['uid']) AND empty($requestData['firstName']) AND
                empty($requestData['lastName']) AND empty($requestData['proemail'])) {
            $this->response("Erreur : paramètre incorrecte");
        }

        $user = new Users();
        $user->setUid(self::cleanner($requestData['uid']));
        $user->setFirstName(self::cleanner($requestData['firstName']));
        $user->setLastName(self::cleanner($requestData['lastName']));
        //$user->setUsername(self::cleanner($requestData['username']));
        $user->setSchoolEmail(self::cleanner($requestData['proemail']));
        $user->setPersonalEmail(self::cleanner($requestData['email']));
        $user->setPhoneMobile(self::cleanner($requestData['mobile']));
        $user->setBirthDate(self::cleanner($requestData['birthDate']));
        $user->setBirthDepartment(self::cleanner($requestData['birthDepart']));
        $user->setBirthCity(self::cleanner($requestData['birthCity']));
        $user->setBirthCountry(self::cleanner($requestData['birthCountry']));
        $user->setNationality(self::cleanner($requestData['nationality']));
        $user->setAddressField(self::cleanner($requestData['addressField']));
        $user->setAddressCity(self::cleanner($requestData['addressCity']));
        $user->setAddressPostalCode(self::cleanner($requestData['addressPostalCode']));
        $user->setAddressCountry(self::cleanner($requestData['addressCountry']));
        $user->setAddressExtra(self::cleanner($requestData['addressExtra']));
        $user->setUpdatedAt(self::getCurrentDate(true));


        $profil = $this->Managers->uploadUsProfile($user);

        if ($profil['rps'] === 1) {
            $this->response("Success");
        } else {
            $this->response("Erreur : paramètre incorrecte");
        }
    }

    public function uploadPass() {
        $this->checkMethode('POST');
        $requestData = $this->getRequestData();

        if (empty($requestData['uid']) AND empty($requestData['newpwd']) AND empty($requestData['currentpwd'])) {
            $this->response("Erreur : paramètre incorrecte");
        }

        $currentPassword = self::cleanner($requestData['currentpwd']);
        $newPassword = self::cleanner($requestData['newpwd']);
        
        $user = new Users();
 
        $user->setUid(self::cleanner($requestData['uid']));
        $user->setPassword($newPassword);
        $user->setUpdatedAt(self::getCurrentDate(true));
        
        if(!(new UsersDal())->checkUserCurrentPassword($currentPassword, $user)){
            $this->response("Le mot de passe actuel est incorrect");
        }

        $profil = $this->Managers->uploadPassword($user);
        
        if ($profil['rps'] === 1) {
            $this->response('ok');
        } else {
            $this->response($profil['rps']);
        }
    }

}
