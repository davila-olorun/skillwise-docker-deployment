<?php

class ExamsDal extends AppModel {

    private $tableName = 'exams';
    
    private $id;
    private $uid;
    private $name;
    private $description;
    private $type;
    private $createdAt;
    private $updatedAt;
        
    public function __construct() {
        parent::__construct();
    }
    public function saveExams(Exams $exam) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($exam);
        
        $reqData = $this->resolveRequestParams($dataArray);
                
        if(!$this->isExamExist($reqData['data'][':uid'])){
           $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
           $rps = $this->getExamIdByUid($reqData['data'][':uid']);
           $response['rps'] = $rps[0]['id'] ;
        }

        return $response;
    }

    public function getExamIdByUid($uid) {
        $rps = $this->_select($this->tableName, 'id', "uid = '" . $uid . "' ");
        return $rps;
    }
    private function isExamExist($uid) {
        $rps = $this->_select($this->tableName,'id', "uid = '".$uid."' ");
        
        if(is_array($rps) && !empty($rps)){
            return true;
        } else {
            return false;
        }
    }

    /*
     * Permet de recuperer les details d'un exam en fonction de l'unique ID envoyé
     * @param string $uid uniqueID d'un exam palnifié
     * @return array $detail retourne un tableau de données relatif à l'exam 
     */

    public function getStudentExamDetail($uid) {
        
        $detail = array();
        $tables = "groups_exams_dates as gexamdate, exams as exams, groups as groups, modules as mods";

        $cols = "gexamdate.id as idgexam, gexamdate.isPlanned as isplanned, gexamdate.isStarted as isstarted, "
                . "gexamdate.isEnded as isended, gexamdate.isClosed as isclosed, "
                . "gexamdate.id_module as idmod ,gexamdate.id_group as idgroup, gexamdate.id_classroom as idclassroom, "
                . "exams.name as examname, exams.description as examdescript, exams.type as examtype, "
                . "groups.name as groupname, groups.id_sequence as idseq, groups.id_campus as idcpus, "
                . "mods.name as modname, mods.code as modcode, mods.objective as modobj, "
                . "mods.description as moddescip";

        $where = "exams.id = gexamdate.id_exam AND groups.id = gexamdate.id_group AND "
                . "mods.id = gexamdate.id_module AND gexamdate.uid = '{$uid}' ";
        
                $tab = $this->_select($tables, $cols, $where);

        $detail['groupexam'] = $this->fusiontableau($tab);
        if (is_array($detail) AND empty($detail['groupexam'])) {
            return 'Data not found';
        }
        
        $detail['dates'] = $this->getDates($detail['groupexam']['idgexam']);
        
        if (!empty($detail['dates'])) {
            $detail['copy'] = $this->checkEvalCopy($detail['groupexam']['idgexam']);
            $detail['correction'] = $this->getEvalCorrection($detail['groupexam']['idgexam']);
        } 

        $detail['campus'] = $this->fusiontableau($this->getCampus($detail['groupexam']['idcpus']));
        $detail['sequence'] = $this->fusiontableau($this->getSequence($detail['groupexam']['idseq']));
        $detail['currentday'] = AppController::getCurrentDate(true);

        return $detail;
    }

     private function checkEvalCopy($idGrpExam) {
         $colonne = "id as idgrade, studentExamLeaveUrl as url, createdAt as daterendu";
         $rps = $this->_select("grades", $colonne, "id_exam = '{$idGrpExam}'");
         
         if($rps){
             $rps = $this->fusiontableau($rps);
             if($rps AND $rps['url']){
                 $copy = explode('/', $rps['url']);
                 $rps['url'] = end($copy);
             }
         }        
         return $rps;
    }
    /*
     * Méthode permettant de recupérer les infos d'un document 
     * @param number $idGrpExam : identifiant du groupe 
     * @return array ['name', 'url', 'validated', 'version']
     */

    private function getEvalCorrection($idGrpExam) {
        $correction = [];
        $colsession = " endDate < '" .AppController::getCurrentDate(true). "' AND "
                . "id_group_exam = '{$idGrpExam}'";
       
        $wd = $this->_select('exams_sessions', '*', $colsession);
        
        if(isset($wd) AND count($wd) > 1){           
            $colonnes = "id_doc as iddoc, name, version, updatedAt as upload";
            $correction = $this->_select("documents", $colonnes, "id_group_exam = '{$idGrpExam}' AND id_documentType = 2");
       }
       return $correction;
    }

    private function getDates($idGrpExam) {
       
        return $this->_select("exams_sessions", "startDate, endDate", "id_group_exam = '{$idGrpExam}'");
    }

    private function getCampus($idCampus) {

        $colcpus = "name, phoneNumber, addressField, addressCity, addressPostalCode, addressCountry, addressExtra";
        $wherecpus = "id = '{$idCampus}'";

        return $this->_select("campuses", $colcpus, $wherecpus);
    }

    private function getSequence($idSquence) {

        return $this->_select("sequences", "name, year", "id = '{$idSquence}'");
    }

    /*
     * Méthode permettant de déterminer le detail d'une évaluation d'un étudiant
     * en fonction de son role et de son groupe
     * nous on ne veut que le role étudiant, comme on ne sait pas si le role est a la position '0' ou '1' 
     * on va parcourrir le tableau en recherchant la clé 'etudiant' ;
     * On fusionne ensuite le tableau $allexam pour avoir des données plus structurées 
     * @param array $userrole c'est le tableau des roles de l'utilisateur 
     * @param int $idgrp identifiant des groupe auxquels appartient l'étudiant
     * @return array $allexam tableau des evaluatons fuxionnées en fonction des états
     */

    public function allexamdetail(array $userrole, $iduser) {
        $allexam = array();
        foreach ($userrole as $value) {
            if ($value['slug'] == 'student') {
                $groups = $this->getStudentGroup($iduser);

                if (empty($groups)) {
                    return 'Student not found';
                }
                foreach ($groups as $value) {
                    $examplanned[] = $this->getStudentExamPlanned($value['id']);
                }
                foreach ($groups as $value) {
                    $examended[] = $this->getStudentExamEnded($value['id']);
                }
                foreach ($groups as $value) {
                    $examclosed[] = $this->getStudentExamClosed($value['id']);
                }
                foreach ($groups as $value) {
                    $examencours[] = $this->getStudentExamEncours($value['id']);
                }
                
                $allexam = $this->tfusionned(count($groups), $examplanned, $examended, $examclosed, $examencours);
            }
        }

        return $allexam;
    }

    public function getTotalEvalByStudent($id) {
         $cpt = [];
        $grp = $this->getStudentGroup($id);

        foreach ($grp as $value) {

            $tables = "groups_exams_dates as gexd, exams as exams, groups as groups";

            $col = "DISTINCT gexd.id as idgexd, gexd.isPlanned as planned, gexd.isStarted as started, "
                    . "gexd.isEnded as ended, gexd.isClosed as closed, "
                    . "groups.name as grpName, exams.name as examName, exams.uid as examUid, "
                    . "exams.description as description, exams.type as examType";

            $where = "(exams.id = gexd.id_exam AND groups.id = gexd.id_group) "
                    . "AND gexd.isPlanned = 1 AND groups.id = {$value['id']}";

            $cpt[] = $this->_select($tables, $col, $where);
        }
        
        return $this->fusiontableau($cpt);
    }

    public function getStudentGroup($idStudent) {

        $col = 'id_group as id';
        $where = "id_userStudent = '{$idStudent}'";
        $table = 'register';

        return $this->_select($table, $col, $where);
    }

    public function getStudentExamSansCopie($allgroup, $iduser) {
        $sanscopie = array();
        
        $sanscopie = $this->getExamSancopie($allgroup, $iduser);
       
        return $sanscopie;
    }
    
    private function getIdgroupsExam($groups){
        $gexd = array();
        
        foreach ($groups as $value) {
            $gexd[] = $this->_select('groups_exams_dates', 'DISTINCT id', "id_group = {$value['id']} AND (isEnded = 1 Or isClosed = 1)");
        }
        
        if(empty($gexd)){
            return $gexd;
        }
        return $this->fusiontableau($gexd);
    }
    private function getCopieForExamEnd($idgexd, $iduser){
        $sanscopie = array();
        $copie = array();
        foreach ($idgexd as $value) {
            $tables = "groups_exams_dates as gexd, grades as grades, exams_sessions as examses";
            $cols = "DISTINCT grades.id as idgrade";
            $where = "grades.id_exam = '" . $value['id'] . "' AND grades.id_userStudent = '" . $iduser . "' ";
            $copie = $this->_select($tables, $cols, $where);
            $copie = $this->fusiontableau($copie);
            if(empty($copie)){                              
                $tables = "groups_exams_dates as gexd, exams as exams, groups as groups,"
                        . " campuses as campus, modules as mods, exams_sessions as examses";

                $cols = "DISTINCT gexd.id as idgexd, mods.name as modulename, mods.code as modulecode, "
                        . "campus.name as campus, groups.name as grpName, "
                        . "exams.name as examName, exams.uid as examUid, "
                        . "exams.description as description, exams.type as examType";

                $whs = "(exams.id = gexd.id_exam AND groups.id = gexd.id_group) AND "
                        . "campus.id = groups.id_campus AND mods.id = gexd.id_module AND "
                        . "gexd.id = examses.id_group_exam "
                        . "AND (gexd.isEnded = 1 OR gexd.isClosed = 1) AND  gexd.id = {$value['id']} ";

                $sanscopie[] = $this->_select($tables, $cols, $whs);
            }
            
        }
        
        return $this->fusiontableau($sanscopie);
       
    }

    private function getExamSanCopie($groups, $iduser) {
        $sanscopie = array();
        
        $idgexd = $this->getIdgroupsExam($groups);
        
        if(empty($idgexd)){
            return $sanscopie;
        }
        
        return $this->getCopieForExamEnd($idgexd, $iduser);
       
    }

    private function getStudentExamEncours($idGroup) {
        return $this->getExamdetailEncoursOrPlanned($idGroup, true);
    }
    
    private function getStudentExamPlanned($idGroup) {
        return $this->getExamdetailEncoursOrPlanned($idGroup, false, true);
    }
   
    private function getStudentExamEnded($idGroup) {
        return  $this->getExamdetailEndedOrClosed($idGroup, true, false);       
    }
   
    private function getStudentExamClosed($idGroup) {        
        return $this->getExamdetailEndedOrClosed($idGroup, false, true);
    }
    /*
     * Méthode permettant de recupérer les évalutions terminée ou cloturées en fonction du groupe de l'étudiant
     * @param int $idGroup identifiant du groupe de l'étudiant
     * @return array() tableau des evaluations cloturées
     * * */
    public function getExamdetailEndedOrClosed($idgroup, $fin = false, $cloture = false){
        $rep = array();
        $table = "groups_exams_dates as gexd, exams_sessions as exses";
        $col = "DISTINCT gexd.id as idexam ,gexd.uid as uidexam";
        $where = "gexd.id = exses.id_group_exam AND gexd.id_group = {$idgroup} ";
  
        if($fin == true){
            $where .= " AND isEnded = 1";
        }
        if ($cloture == true) {
            $where .= " AND isClosed = 1";
        }
       
        $rps = $this->_select($table, $col, $where, 'exses.startDate ASC');
        
        foreach ($rps as $value) {
            $tables = "groups_exams_dates as gexd, exams as exams, groups as groups,"
                    . " campuses as campus, modules as mods, exams_sessions as examses";

            $cols = "DISTINCT gexd.id as idgexd,gexd.uid as uidgexd,gexd.isEnded as ended, gexd.isClosed as closed, mods.name as modulename, mods.code as modulecode, "
                    . "campus.name as campus, groups.name as grpName, "
                    //. "DATE_FORMAT(examses.endDate, '%Y-%m-%d') as enddate, "
                    . "exams.name as examName, exams.description as description, exams.type as examType";

            $whs = "(exams.id = gexd.id_exam AND groups.id = gexd.id_group) AND "
                    . "(campus.id = groups.id_campus AND mods.id = gexd.id_module) AND gexd.id = examses.id_group_exam "
                    . " AND  gexd.id = {$value['idexam']} ";
            
            $rep[] = $this->_select($tables, $cols, $whs);
        }
       
        return $rep;
    }

 /*
     * Méthode permettant de recupérer les évalutions encours ou planifiées en fonction du groupe de l'étudiant
     * @param int $idGroup identifiant du groupe de l'étudiant
     * @return array() tableau des evaluations
     * * */
    public function getExamdetailEncoursOrPlanned($idgroup, $encours = false, $planifie = false){
        $rep = array();
        $table = "groups_exams_dates as gexd, exams_sessions as exses";
        $col = "DISTINCT gexd.id";
        $where = "gexd.id = exses.id_group_exam AND gexd.id_group = {$idgroup} ";
       
        if($encours ==true){
            $where .= " AND isStarted = 1";
        }elseif($planifie == true){
            $where .= " AND isPlanned = 1 ";
        }
        
        $rps = $this->_select($table, $col, $where, 'exses.startDate ASC');
        
            for ($i = 0; $i < count($rps); $i++) {
            $tables = "groups_exams_dates as gexd, exams as exams, groups as groups,"
                    . " campuses as campus, modules as mods";

            $cols = "gexd.id as idgexd, mods.name as modulename, mods.code as modulecode, mods.objective as moduleobj, "
                    . "campus.name as campus, "
                    . "gexd.uid as uidgexdate, gexd.isPlanned as planned, gexd.isStarted as started, "
                    . "gexd.isEnded as ended, gexd.isClosed as closed, groups.name as grpName, "
                    . "exams.name as examName, exams.uid as examUid, "
                    . "exams.description as description, exams.type as examType";

            $wh = "(exams.id = gexd.id_exam AND groups.id = gexd.id_group) AND "
                    . "(campus.id = groups.id_campus AND mods.id = gexd.id_module) "
                    . " AND gexd.id = {$rps[$i]['id']} ";

            $rep[] = $this->_select($tables, $cols, $wh);
            $wheredate = "id_group_exam = {$rps[$i]['id']} ";
            $rep[$i]['session'] = $this->_select('exams_sessions as examses', 'examses.startDate as startsess, examses.endDate as endsess', $wheredate);
        }
        
        return $rep;
    }


    /*
     * Méthode permettant de fusionner des tableaux de données des groupes
     * @param array $groupe tableau des differents groupes auxquels appartient l'étudiant
     * @param array $planned tableau des evaluations planniefiées
     * @param array $ended tableau des évaluations terminées
     * @param array $clodes tableau des évaluations cloturées
     * @param array $started tableau des évaluation en-cours
     * @return array $tabexam tableau fusionné des évaluations
     */

    private function tfusionned($groupe, $planned, $ended, $closed, $started) {
        $tabexam = [];
        $examplanned =[];
        $examended = [];
        $examclosed = [];
        $examencours = [];
        
        if ($groupe == 1) {
            $tabexam['examplanned'] = $planned[0];
            $tabexam['examended'] = $ended[0];
            $tabexam['examclosed'] = $closed[0];
            $tabexam['examencours'] = $started[0];
        }else if($groupe >= 2){
            for($i = 2; $i < $groupe; $i++){
                $examplanned += array_merge($planned[$i - 2], $planned[$i - 1]);
            }
            for($i = 2; $i < $groupe; $i++){
                $examended += array_merge($ended[$i - 2], $ended[$i - 1]);
            }
            for($i = 2; $i < $groupe; $i++){                
                $examclosed += array_merge($closed[$i - 2], $closed[$i - 1]);
            }
            for($i = 2; $i < $groupe; $i++){                
                $examencours += array_merge($started[$i - 2], $started[$i - 1]);
            }
        }
            $tabexam['examplanned'] = $examplanned;
            $tabexam['examended'] = $examended;
            $tabexam['examclosed'] = $examclosed;
            $tabexam['examencours'] = $examencours;
            
        return $tabexam;
    }
    
     /*****
     * Méthode permettant de recuperer le tableau des evaluation terminées ou cloturées
      *  en fonction de l'id de l'etudiant
     * @param string $idStudent identifiant de l'étudiant
     * @return array() $rps tableau des évaluations terminées ou clôturées de l'étudiant
     * * */
    public function historiqueExam($idStudent) {
        $rps = array();
        $idGroup = $this->getStudentGroup($idStudent);
        
        foreach ($idGroup as $value) {
            $end[] = $this->getExamdetailEndedOrClosed($value['id'], true, false);
        }
        foreach ($idGroup as $value) {
             $closed[] = $this->getExamdetailEndedOrClosed($value['id'], false, true);
        }
        
        $rps['end'] = $this->fusiontableau($end);
        $rps['close'] = $this->fusiontableau($closed);
        
        return array_merge($rps['end'], $rps['close']);
         
    }

    /*****
     * Méthode permettant de fusionner le tableau des evaluation en fonction du groupe
     * @param array() $totaleval tableau de chaque groupe et ses evaluations auxquels appartient l'étudiant
     * @return array() $cpt d'entier des identifaints des classes de l'étudiant
     * * */
    private function fusiontableau($totaleval) {
        
        $cpt = [];
        if (count($totaleval) == 1) {
            $cpt = ($totaleval[0]);
        } else if (count($totaleval) >= 2) {
            for ($i = 2; $i < count($totaleval); $i++) {
                $cpt += array_merge($totaleval[$i - 2], $totaleval[$i - 1]);
            }
        }
        
        return $cpt;        
    }
    
}
