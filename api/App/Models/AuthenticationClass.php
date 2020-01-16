<?php

class AuthenticationClass extends UsersDal{
    public function __construct() {
        parent::__construct();
    }
    public function updateRootPassword(Users $user, $secret){
        
        if(sha1($secret) !== ROOT_SECRET){
            return "Secret do not match";
        }
        
        return (new UsersDal())->updateUsers($user, 1);
    }

    public function connectUser($email, $password){
        $user = new Users();
        $user->setSchoolEmail($email);
        $user->setPassword($password, false);
        
        $rps = $this->authentication($user);
        
        return $rps;
    }
    public function saveTolog($userId, $userSessionToken){
        $connexion = new UserConnexions();
        $connexion->setIdUser($userId);
        $connexion->setLoginDate(ApiController::getCurrentDate(true));
        $connexion->setSessionId($userSessionToken);
        
        return (new UserConnexionsDal())->saveConnexion($connexion);
    }
    public function disconnectUser($userSessionToken){
        $connexion = new UserConnexions();
        $connexion->setLogoutDate(ApiController::getCurrentDate(true));
        $connexion->setSessionId($userSessionToken);
        return (new UserConnexionsDal())->updateConnexion($connexion);;
    }
}
