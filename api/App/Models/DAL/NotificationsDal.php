<?php

class NotificationsDal extends AppModel{
    private $tableName = 'notifications';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function saveNotification(Notifications $notif){
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($notif);
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        
        return $response;
    }
    public function getUserNotifications($idUser){
        return $this->_select($this->tableName, "uid, title, detail, path, icon, isRead, createdAt as date", "id_user = '".$idUser."' AND isRead = 0 ", 'createdAt DESC');
    }
    public function setNotificationAsRead($uidNotif) {
        return $this->_update($this->tableName, "isRead = :isread", "uid = '".$uidNotif."' ", array(':isread' => 1));
    }
    public function notifyTeacherAboutSujectState($teacherUid, $evalUid, $codeAndEvalName, $validated = false){
        $user = (new UsersDal())->getUserIdByUid($teacherUid);
        
        if(empty($user)){
            return "utilisateur introuvable";
        }
        
        //Titre et Message de la notification
        $notif = ($validated === true) ? NotificationMessageHelper::getSujetValideMsg($codeAndEvalName) : NotificationMessageHelper::getSujetRejeteMsg($codeAndEvalName);
        
        $icon = ($validated === true) ? IconsHelper::SUJET_VALIDE : IconsHelper::SUJET_REJETE;
        $path = NotificationMessageHelper::TEACHER_DETAIL_EVALUATION_URL.$evalUid;
        
        $notifications = $this->getNotificationObject($user[0]['id'], 
                $notif['title'], $notif['message'], $path, $icon);
        
        return $this->saveNotification($notifications);
    }
    /**
     * Envoie une notification au professeur pour l'informer de l'uploade d'un nouveau fichier concernant une évaluation
     * 
     * @param string $teacherUid uid du prof
     * @param string $evalUid uid de l'évaluation
     * @param string $codeAndEvalName nom complet de l'évaluation (nom + code)
     * @param string $fileType le type de fichier uploadé. Exple : 'subject' ou 'correction'
     * @return array un tableau avec une clé rps
     */
    public function notifyTeacherAboutNewFileUploading($teacherUid, $evalUid, $codeAndEvalName, $fileType){
        $user = (new UsersDal())->getUserIdByUid($teacherUid);
        
        if(empty($user)){
            return "utilisateur introuvable";
        }
        
        //Titre et Message de la notification
        $notif = NotificationMessageHelper::getNouveauFichierMsg($codeAndEvalName, $fileType);
        
        $icon = ($fileType === 'subject') ? IconsHelper::SUJET_VALIDE : IconsHelper::CORRECTION_DISPONIBLE;
        $path = NotificationMessageHelper::TEACHER_DETAIL_EVALUATION_URL.$evalUid;
        
        if(empty($notif)){
            return array('rps' => 'Notification message missing');
        }
        
        $notifications = $this->getNotificationObject($user[0]['id'], 
                $notif['title'], $notif['message'], $path, $icon);
        
        return $this->saveNotification($notifications);
    }
    public function notifyStudentsAboutSubjectDownloading($evalUid) {
        //Récupération des données de l'évaluation
        $groupExamDate = new GroupsExamsDatesDal();
        $examDetail = $groupExamDate->getEvalBasicDetails($evalUid);
        
        //Si on retrouve pas les données de l'évaluation
        if(empty($examDetail)){
            return "Eval not found";
        }
        //Données d'évaluation trouvée
        
        //Vérifions que l'évaluation possède un sujet validé
        $validSuject = $groupExamDate->evalHasSubject($examDetail[0]['evalId'], false);
        
        //Si on trouve pas de sujet validé
        if(empty($validSuject)){
            return "Eval does not have validated subject";
        }
        //L'évaluation possède des sujets validés
        
        //Récupération des étudiants du groupe
        $groupStudents = (new GroupsDal())->getGroupStudents($examDetail[0]['groupId']);
        
        //Si on retrouve pas les étudiants du groupe
        if(empty($groupStudents)){
            return "Group does not have students";
        }
        //On a la liste des étudiants
        
        //On prépare les données de la notification
        $tileAndMessage = NotificationMessageHelper::getSujetDisponibleMsg($examDetail[0]['moduleCode']." ".$examDetail[0]['moduleName']);
        $title = $tileAndMessage['title'];
        $detail = $tileAndMessage['message'];
        $path = NotificationMessageHelper::STUDENT_DETAIL_EVALUATION_URL.$evalUid;
        $icon = IconsHelper::EVAL_ENCOURS_SD;
        
        $rps = array();
        $notif = null;
        foreach ($groupStudents as $key => $value) {
            $notification = $this->getNotificationObject($value['userId'], $title, $detail, $path, $icon);
            $rps[] = $this->saveNotification($notification);
        }
        return $rps;
    }
}
