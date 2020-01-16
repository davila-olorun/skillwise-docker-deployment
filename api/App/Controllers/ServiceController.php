<?php

class ServiceController extends ApiController implements AppInterface{
    private $Service = null;
    public $today;
    
    public function __construct() {
        parent::__construct();
        //$this->loadModel('Service');
        $this->Service = new ServiceClass();
        $this->today = self::getCurrentDate(true);
    }
    public function view() {
        parent::view();
    }
    public function verifyToken(){
        $this->checkMethode('POST');
        $this->verifyAccessToken(true);
    }
    public function startExam(){
        $this->checkMethode('GET');
        $evals = $this->Service->startExam($this->today);
        if(!empty($evals)){
            $this->response($evals);
        }else{
            $this->response("RAS");
        }
    }
    public function endExam(){
        $this->checkMethode('GET');
        $evals = $this->Service->endExam();
        if(!empty($evals)){
            $this->response($evals);
        }else{
            $this->response("RAS");
        }
    }
    public function closeExam(){
        $this->checkMethode('GET');
        $evals = $this->Service->closeExam();
        if(!empty($evals)){
            $this->response($evals);
        }else{
            $this->response("RAS");
        }
    }
}
