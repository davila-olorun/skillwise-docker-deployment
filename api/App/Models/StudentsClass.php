<?php

class StudentsClass extends UsersDal{
    
    private $users;
    private $userRoles;
    private $groups;
    private $exams;
    private $examDates;
    private $documents;
    public function __construct() {
        parent::__construct();
    }    
    public function getUserData($uid){      
        $user = array();
        $infos = $this->getStudentByUid($uid);  

        // les différents groupes auxquels appartient l'étudiant
        $this->groups = new GroupsDal;
        
        $user['groupe'] = $this->groups->getStudentClass($infos['infos']['id']);
        
        if(empty($user['groupe'])){
            return $user;
        }
        $this->exams = new ExamsDal();

        //total des évaluations de l'étudiant
        $user['texam'] = $this->getEvalsAndCopiesStats($uid);
         
        //details des évaluations en fonction des états
        $user['texamdetail'] = $this->exams->allexamdetail($infos['role'], $infos['infos']['id']);
              
         //details des évaluations sans copie rendues
        $user['evalsanscopie'] = $this->exams->getStudentExamSansCopie($user['groupe'], $infos['infos']['id']);
        
        return $user;
    }
    
    public function getDetailExam($uid){
        $this->exams = new ExamsDal();
        $rps = $this->exams->getStudentExamDetail($uid);
        return $rps;
    }
    public function getAllExamTerminateClose($uid){
        $user = array();
        $infos = $this->getStudentByUid($uid);  
       
          // les différents groupes auxquels appartient l'étudiant
        $this->groups = new GroupsDal;
        
        $user['groupe'] = $this->groups->getStudentClass($infos['infos']['id']);
        
        
         $this->exams = new ExamsDal();
         
        $user['hist'] = $this->exams->historiqueExam($infos['infos']['id']);
        
        return $user;
    }

    public function getDoc($uidexam){
        $this->exams = new ExamsDal();
        $doc = $this->exams->getDocument($uidexam);
    }
    public function getEvalsAndCopiesStats($studentUid) {
        
        //J'initialise les resultats attendus
        $result = array(
            'tPlanEvals' => 0,
            'tPlanEvalsSf' => 0,
            'tCopiesRendues' => 0,
            'tCopiesNonRendues' => 0
        );
        
        //Je recherche les groupes de l'étudiant
        $groups = $this->_select('groups as gps, register as regi, users as usr', 
                    "gps.id as id, usr.id as userId", 
                    "gps.id = regi.id_group AND regi.id_userStudent = usr.id AND usr.uid = '".$studentUid."'");
        
        if(empty($groups)){
            return $result;
        }

        foreach ($groups as $key => $value) {
            $idGroup = $value['id'];
            $idUser = $value['userId'];
            
            //Total des evaluations planifiées
            $tPlanEvals = $this->_select('groups_exams_dates', "COUNT(id) as nbre", "isPlanned = 1 AND id_group = '".$idGroup."' ");
            
            $result['tPlanEvals'] += (!empty($tPlanEvals[0]['nbre'])) ? $tPlanEvals[0]['nbre'] : 0;
            
            //Total des evaluations planifiées avec sujet
            $tPlanEvalsSf = $this->_select("groups_exams_dates as gexd, documents as doc, document_types as doct", 
                    "COUNT(gexd.id) as nbre", 
                    "gexd.id = doc.id_group_exam AND doc.id_documentType = doct.id AND gexd.isPlanned = 1 "
                    . "AND doc.validated = 1 AND doct.slug = 'sujet' AND gexd.id_group = '".$idGroup."' ");
            
            $result['tPlanEvalsSf'] += (!empty($tPlanEvalsSf[0]['nbre'])) ? $tPlanEvalsSf[0]['nbre'] : 0;
            
            //total des copies rendues
            $tCopiesRendues = $this->_select("groups_exams_dates as gexd, users as usr, grades as grd", "COUNT(grd.id) as nbre",
                        "usr.id = grd.id_userStudent AND gexd.id = grd.id_exam AND gexd.isEnded = 1 AND grd.id_userStudent = '" . $idUser . "' AND gexd.id_Group = '".$idGroup."' "
                    . "AND ( grd.studentExamLeaveUrl != '' OR grd.studentExamLeaveUrl != NULL )");
            
            //total des exams terminées
            $tEndedExam =  $this->_select("groups_exams_dates as gexd", "COUNT(gexd.id) as nbre", 
                    "gexd.isEnded = 1 AND gexd.id_group = '".$idGroup."' ");
            
            $copiesRendues = (!empty($tCopiesRendues[0]['nbre'])) ? $tCopiesRendues[0]['nbre'] : 0;
            
            $endedExam = (!empty($tEndedExam[0]['nbre'])) ? $tEndedExam[0]['nbre'] : 0;
            
            if($endedExam == 0){
                continue;
            }
            
            $copiesNonRendues = 0;
            
            if($copiesRendues == 0){
                $copiesNonRendues = $endedExam;
            }else{
                $copiesNonRendues = $endedExam - $copiesRendues;
            }
            
            $result['tCopiesRendues'] += $copiesRendues;
            $result['tCopiesNonRendues'] += $copiesNonRendues;
        }
        
        return $result;
    }
    public function uploadCopies(array $data) {
        $grade = new Grades();
        $grade->setIdExam($data['idEval']);
        $grade->setUid(uniqid());
        $grade->setStudentExamLeaveUrl($data['url']);
        $grade->setIdUserStudent($data['idUser']);
        $grade->setCreatedAt($data['date']);
        $grade->setUpdatedAt($data['date']);
        
        return (new GradesDal())->saveGrade($grade);
    }
    public function userProfil($token){
        return $this->getStudentByUid($token);       
    }
}
