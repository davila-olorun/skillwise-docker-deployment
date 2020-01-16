<?php

class ServiceClass extends GroupsExamsDatesDal {

    public function __construct() {
        parent::__construct();
    }
    /**
     * Permet de marquer les évaluations comme étant en cours quand la date de début est arrivée
     * 
     * @return Array
     */
    public function startExam() {
        $evals = $this->getPlansEvaluations();
        
        if(empty($evals)){
            return $evals;
        }
        $rps = null;
        foreach ($evals as $key => $value) {
            $date = $this->getExamDates($value['id']);
            
            if($this->checkDates($date[0]['startDate'], $date[count($date) - 1]['endDate'])){
                $groupExamDate = $this->renderGroupsExamsDatesObject(2, 1, 2, 2);
                $rps[] = $this->setExamState($groupExamDate, $value['id']);
                (new NotificationsDal())->notifyStudentsAboutSubjectDownloading($value['uid']);
            }
        }
        return $rps;
    }
    /**
     * Permet de terminer les évaluations en cours
     * 
     * @return Array
     */
    public function endExam() {
        $evals = $this->getStartedEvaluations();
        
        if(empty($evals)){
            return $evals;
        }
        $rps = null;
        foreach ($evals as $key => $value) {
            $date = $this->getExamDates($value['id']);
            
            if($this->checkDates(null, $date[count($date) - 1]['endDate'])){
                $groupExamDate = $this->renderGroupsExamsDatesObject(2, 2, 1, 2);
                $rps[] = $this->setExamState($groupExamDate, $value['id']);
            }
        }
        return $rps;
    }
    /**
     * Permet de clôturer les évaluations terminées depuis plus de deux semaines
     * 
     * @return Array
     */
    public function closeExam() {
        $evals = $this->getEndedEvaluations();
        
        if(empty($evals)){
            return $evals;
        }
        $rps = null;
        foreach ($evals as $key => $value) {
            $date = $this->getExamDates($value['id']);
            
            if($this->checkDates(null, $date[count($date) - 1]['endDate'], true)){
                $groupExamDate = $this->renderGroupsExamsDatesObject(2, 2, 2, 1);
                $rps[] = $this->setExamState($groupExamDate, $value['id']);
            }
        }
        return $rps;
    }
    private function checkDates($dateDebut, $dateFin, $checkForCloseEval = false) {
        //Je vérifie à travers les dates que l'examen peut être marqué comme étant en cours
        if ($dateDebut !== null && $dateFin !== null) {
            $todayIs = new DateTime(ApiController::getCurrentDate(true));

            $dateDebutIs = new DateTime($dateDebut);
            $dateFinIs = new DateTime($dateFin);
            
            //Si on est entre la date de debut et de fin
            if (($todayIs >= $dateDebutIs) /*&& ($todayIs <= $dateFinIs)*/) {
                return true;
            } else {
                return false;
            }
            
        //Je vérifie à travers les dates que l'examen peut être marqué comme étant terminé
        } else if($dateFin !== null && $checkForCloseEval == false){
            $todayIs = new DateTime(ApiController::getCurrentDate(true));

            $dateFinIs = new DateTime($dateFin);
            
            //Si la date de fin est passée
            if ($todayIs > $dateFinIs) {
                return true;
            } else {
                return false;
            }
            
        //Je vérifie à travers les dates que l'examen peut être marqué comme étant clôturé
        } else if($dateFin !== null && $checkForCloseEval == true){
            $todayIs = new DateTime(ApiController::getCurrentDate(true));
            $dateFinIs = new DateTime($dateFin);
            $dateCloseIs = new DateTime(ApiController::getCurrentDate(true, $dateFinIs->getTimestamp(), "+".ApiController::$DAYS_BEFORE_CLOSE_EVAL." day"));
            
            //Si la date de clôture est passée
            if ($todayIs >= $dateCloseIs) {
                return true;
            } else {
                return false;
            }
        } else {
          return false;  
        }
    }
    /**
     * Récupération des évaluations planifiées du jour
     * 
     * @return Array
     */
    private function getPlansEvaluations() {
        //$todayDate = ApiController::getCurrentDate();
        $todayDate = ApiController::getCurrentDate(true);
        
        //$whereCondition = "gexd.id = examss.id_group_exam AND gexd.isPlanned = 1 AND examss.startDate LIKE '" . $todayDate . '%' . "' ";
        $whereCondition = "gexd.id = examss.id_group_exam AND gexd.isPlanned = 1 AND examss.startDate <= '" . $todayDate. "' ";
        
        $planEval = $this->_select("groups_exams_dates as gexd, exams_sessions as examss", "DISTINCT gexd.id, gexd.uid", $whereCondition);
        
        return $planEval;
    }
    /**
     * Récupération des évaluations en cours
     * 
     * @return Array
     */
    private function getStartedEvaluations() {
        $whereCondition = "gexd.id = examss.id_group_exam AND gexd.isStarted = 1 ";
        
        $planEval = $this->_select("groups_exams_dates as gexd, exams_sessions as examss", "DISTINCT gexd.id, gexd.uid", $whereCondition);
        
        return $planEval;
    }
    /**
     * Récupération des évaluations terminées
     * 
     * @return Array
     */
    private function getEndedEvaluations() {
        $whereCondition = "gexd.id = examss.id_group_exam AND gexd.isEnded = 1 ";
        
        $planEval = $this->_select("groups_exams_dates as gexd, exams_sessions as examss", "DISTINCT gexd.id, gexd.uid", $whereCondition);
        
        return $planEval;
    }
    /**
     * Création d'un object de type \GroupsExamsDates
     * 
     * @param int $isPlanned 1 pour marquer comme planifié et 2 pour le contraire
     * @param int $isStarted 1 pour marquer comme en cours et 2 pour le contraire
     * @param int $isEnded 1 pour marquer comme terminé et 2 pour le contraire
     * @param int $isClosed 1 pour marquer comme cloturé et 2 pour le contraire
     * @return \GroupsExamsDates
     */
    private function renderGroupsExamsDatesObject($isPlanned, $isStarted, $isEnded, $isClosed){
        $groupExamDate = new GroupsExamsDates();
        
        $groupExamDate->setIsPlanned($isPlanned);
        $groupExamDate->setIsStarted($isStarted);
        $groupExamDate->setIsEnded($isEnded);
        $groupExamDate->setIsClosed($isClosed);
        
        return $groupExamDate;
    }
}
