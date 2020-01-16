<?php

class AuthenticationController extends ApiController implements AppInterface {

    private $Authentication = null;
    
    public function __construct() {
        parent::__construct();
        //$this->loadModel('Authentication');
        $this->Authentication = new AuthenticationClass();
    }

    public function view() {
        parent::view();
        //$hash = '$2y$10$Ez9gKCeVTZRfjBg1wZNBLew3wFB/5NS3bxnvwdxrTBjR13cAt7L92';
        //var_dump(password_hash("demo",PASSWORD_DEFAULT ));
        //var_dump(password_verify("demo", $hash));        
        //echo 'sha1 : '.sha1("SwiseEstiamFr");
    }
    
    public function __initRootPassword($password = null, $secret = null){
        
        if($password == null || $secret == null){
            $this->response("Paramètres incorrects");
        }
        
        $user = new Users();
        $user->setPassword(self::cleanner($password));
        
        $rps = $this->Authentication->updateRootPassword($user, self::cleanner($secret));

        if(is_string($rps)){
            $this->response($rps);
        }else if($rps > 0){
            $this->response('Mot de passe enregistré avec succès'); 
        }else{
            $this->response('Echec de l\'enregistrement du mot de passe'); 
        }
        
    }
    
    public function __generateATokenPass($string){
        $user = new Users();
        $user->setPassword($string);
        $this->response(array('token' => $user->getPassword()));
    }

    public function azureLogin() {
        
    }

    public function simpleLogin() {
        $this->checkMethode('POST');

        $requestData = $this->getRequestData();

        if (isset($requestData['email']) AND isset($requestData['password'])) {

            $this->verifierTypeChamps($requestData);

            $email = self::cleanner($requestData['email']);
            $pass = self::cleanner($requestData['password']);

            $rps = $this->Authentication->connectUser($email, $pass);
            
            if (is_array($rps)) {
                unset($rps['password']);
                $rps['stoken'] = sha1(session_id());
                $this->Authentication->saveTolog($rps['id'],$rps['stoken']);
                $jwt = JWTEncoderHelper::encode($rps);
                $this->response(array('success' => 'success', 'accesstoken' => $jwt));
            } else {
                $this->response("Email ou mot de passe incorrect");
            }
        }
        $this->response('Renseignez votre email et votre mot de passe SVP');
    }

    public function logout() {
        $this->checkMethode('POST');

        $this->verifyAccessToken();
        
        $rps = $this->Authentication->disconnectUser($this->loggedUser['stoken']);

        if (is_int($rps['rps']) && $rps['rps'] > 0) {
            $this->response(array('success' => true));
        } else {
            $this->response("Nous ne parvenons pas à vous déconnecter, reessayez plus tard svp !");
        }
    }

}
