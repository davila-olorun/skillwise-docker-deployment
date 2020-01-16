<?php

class NotificationsController extends ApiController implements AppInterface{
    private $notifications = null;
    public function __construct() {
        parent::__construct();
        $this->verifyAccessToken();
        $this->notifications = new NotificationsClass();
    }
    public function view() {
        parent::view();
    }
    public function getNotifications($idUser = null){
        $this->checkMethode('GET');
        
        if($idUser === null){
           $this->response("Paramètres manquants");
        }
        
        $userId = self::cleanner($idUser);
        
        $notifs = $this->notifications->getUserNotifications($userId);
        
        $this->response($notifs);
    }
    public function setNotificationAsRead($notifUid = null){
        $this->checkMethode('POST');
        
        if($notifUid === null){
           $this->response("Paramètres manquants");
        }
        
        $uidNotif = self::cleanner($notifUid);
        
        $notifs = $this->notifications->setNotificationAsRead($uidNotif);
        
        if(is_int($notifs) && $notifs > 0){
            $this->response(array('mess'=>'success'));
        }else{
            $this->response("Echec");
        }
    }
}
