<?php
include_once _JWTDIRPATH_.'BeforeValidException.php';
include_once _JWTDIRPATH_.'ExpiredException.php';
include_once _JWTDIRPATH_.'SignatureInvalidException.php';
include_once _JWTDIRPATH_.'JWT.php';

class JWTEncoderHelper {
    private static $key = "6ce9bd3b328515f1f868a9ade0edd4bb7deb963d";
    private static $token = array(
        "iss" => "skillwise",
        "aud" => array('all')
    );
    public static function encode($payload){
        self::$token['iat'] = AppController::getCurrentDate(true, null, null, 'time');
        self::$token['nbf'] = AppController::getCurrentDate(true, null, '+3 second', 'time');
        /*self::$token['exp'] = AppController::getCurrentDate(true, null, '+1 minute', 'time');*/
        self::$token['data'] = $payload;
        return Firebase\JWT\JWT::encode($payload, self::$key);
    }
    public static function decode($jwt){
        try{
            return Firebase\JWT\JWT::decode($jwt, self::$key, array('HS256'));
        } catch (Firebase\JWT\ExpiredException $ex) {
            return $ex->getMessage();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
}
