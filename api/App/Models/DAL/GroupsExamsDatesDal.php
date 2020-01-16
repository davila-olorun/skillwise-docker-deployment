<?php

class GroupsExamsDatesDal extends AppModel {

    private $tableName = 'groups_exams_dates';
    
    public function __construct() {
        parent::__construct();
    }
    public function countEvalCorrections($evalId) {
        $table = $this->tableName . " as gexd, documents as doc, document_types as doct";
        $select = 'gexd.id as nbre';
        $where = "gexd.id = doc.id_group_exam AND doc.id_documentType = doct.id AND "
                . "doct.slug = 'corrige' AND doc.id_group_exam = '" . $evalId . "' ";
        
        $rps = $this->_select($table, $select, $where);
        
        return count($rps);
    }
    /**
     * Permet d'enregistrer les evals venant de genius dans la BD SkillWise
     * 
     * @return array contenant une clée rps avec l'id de l'élément inséré ou un message d'erreur
     */
    public function saveDates(GroupsExamsDates $groupExamDate,$sessionDateUid) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($groupExamDate);

        if (!empty($dataArray['id_group'])) {
            $group = new GroupsDal();
            $groupData = $group->getGroupIdByUid($dataArray['id_group']);
            $dataArray['id_group'] = (is_array($groupData) && !empty($groupData)) ? $groupData[0]['id'] : null;
        }

        if (!empty($dataArray['id_module'])) {
            $module = new ModulesDal();
            $moduleData = $module->getModuleIdByUid($dataArray['id_module']);
            $dataArray['id_module'] = (is_array($moduleData) && !empty($moduleData)) ? $moduleData[0]['id'] : null;
        }

        if (!empty($dataArray['id_userTeacher'])) {
            $users = new UsersDal();
            $usersData = $users->getUserIdByUid($dataArray['id_userTeacher']);
            $dataArray['id_userTeacher'] = (is_array($usersData) && !empty($usersData)) ? $usersData[0]['id'] : null;
        }

        if (!empty($dataArray['id_classroom'])) {
            $class = new ClassroomsDal();
            $classData = $class->getClassroomIdByUid($dataArray['id_classroom']);
            $dataArray['id_classroom'] = (is_array($classData) && !empty($classData)) ? $classData[0]['id'] : null;
        }
        
        $reqData = $this->resolveRequestParams($dataArray);

        if (!$this->isDateExist($sessionDateUid)) {
            $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        } else {
            $rps = $this->getGroupExamIdBySessionUid($sessionDateUid);
            $response['rps'] = $rps[0]['id'];
        }

        return $response;
    }
    public function getEvalDataById($evalId) {
        return $this->_select($this->tableName, "*", "id = '".$evalId."' ");
    }
    protected function setExamState(GroupsExamsDates $groupExamDate, $idGroupExam){
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($groupExamDate);
        
        $reqData = $this->resolveRequestParams($dataArray, 'update');
        
        if($reqData['data'][':isPlanned'] > 1){
            $reqData['data'][':isPlanned'] = 0;
        }
        
        if($reqData['data'][':isStarted'] > 1){
            $reqData['data'][':isStarted'] = 0;
        }
        
        if($reqData['data'][':isEnded'] > 1){
            $reqData['data'][':isEnded'] = 0;
        }
        
        if($reqData['data'][':isClosed'] > 1){
            $reqData['data'][':isClosed'] = 0;
        }
        
        $whereCondition = "id = '".$idGroupExam."' ";
        
        $response['rps'] = $this->_update($this->tableName, $reqData['bind'], $whereCondition, $reqData['data']);
        
        return $response;
    }
    /**
     * Permet de récupérer les cinq prochaines evals avec tous les details
     * 
     * @param string $userUid correspond au uid de l'utilisateur (Professeur) pour lequel on souhaite récupérer les evals
     * @return array contenant les evaluations
     */
    public function getNextEvals($userUid = null) {
        return $this->getEvaluationsWithDetails($userUid,null, false, true);
    }
    /**
     * Permet de récupérer les cinq dernières evals avec tous les details
     * 
     * @param string $userUid correspond au uid de l'utilisateur (Professeur) pour lequel on souhaite récupérer les evals
     * @return array contenant les evaluations
     */
    public function getLastEvals($userUid = null) {
        return $this->getEvaluationsWithDetails($userUid,null, true);
    }
    public function getEvalDetails($evalUid) {
        $details = $this->getEvalBasicDetails($evalUid);

        if (empty($details)) {
            return array();
        }

        $evalId = $details[0]['evalId'];
        $groupId = $details[0]['groupId'];

        //je recupère les sessions de l'évaluation

        $dates = $this->getExamDates($evalId);

        $details[0]['sessions'] = (!empty($dates)) ? $dates : NULL;

        //Vérification si c'est une evaluation à risque
        $isRiskyEval = $this->isRiskyEval($evalId);

        $details[0]['riskyEval'] = (empty($isRiskyEval)) ? false : true;

        //Récupération des sujets de l'évaluation
        $evalSubjects = $this->getEvalDocuments($evalId, 'sujet');
        $validSubjects = $this->evalHasSubject($evalId, false);

        $details[0]['subjects'] = (!empty($evalSubjects['doc'])) ? $evalSubjects['doc'] : NULL;
        $details[0]['hasValidatedSubject'] = (!empty($validSubjects)) ? true : false;

        //Récupération des corrections de l'évaluation
        $evalCorrections = $this->getEvalDocuments($evalId, 'corrige'); 

        $details[0]['corrections'] = (!empty($evalCorrections['doc']) AND $evalCorrections['canView'] === true) ? $evalCorrections['doc'] : NULL;
        $details[0]['nbreCorrections'] = (!empty($evalCorrections['doc'])) ? count($evalCorrections['doc']) : 0;
        $details[0]['canViewCorrections'] = $evalCorrections['canView'];

        //Récupération des stats des copies rendues et non rendues
        $getCopiesStats = $this->copiesStats($evalId, $groupId);

        $details[0]['copiesRendues'] = $getCopiesStats['rendues'];
        $details[0]['copiesNonRendues'] = $getCopiesStats['nonRendues'];
        $details[0]['students'] = $getCopiesStats['students'];
        
        //récupération des copies rendues
        $getCopiesRendues = $this->getLeavesRendering($evalId, $groupId);
        $details[0]['leaves'] = (!empty($getCopiesRendues)) ? $getCopiesRendues : NULL;

        //Récupérons le prof
        $teacher = $this->getExamTeacher($details[0]['teacherId']);

        $details[0]['teacher'] = (!empty($teacher)) ? $teacher[0] : NULL;


        return array('details' => $details[0]);
    }
    public function getEvalBasicDetails($evalUid) {
        //Je récupère les details basic de l'évaluation

        $table = $this->tableName . " as gexd, exams as exm, modules as modu, groups as gps, sequences as seq, campuses as camp";
        $fieldToSelect = "gexd.id as evalId, gexd.uid as evalUid, gexd.isPlanned as planned, gexd.isStarted as started, gexd.isEnded as ended, gexd.isClosed as closed, gexd.id_userTeacher as teacherId, "
                . "modu.uid as moduleUid, modu.code as moduleCode, modu.name as moduleName, "
                . "gps.id as groupId, gps.uid as groupUid, gps.name as groupName, "
                . "seq.uid as sequenceUid, seq.name as sequenceName, "
                . "camp.uid as campusUid, camp.name as campusName";
        $whereCondition = "gexd.id_exam = exm.id AND gexd.id_module = modu.id AND gexd.id_group = gps.id AND "
                . "gps.id_sequence = seq.id AND gps.id_campus = camp.id AND gexd.uid = '" . $evalUid . "' ";

        return $this->_select($table, $fieldToSelect, $whereCondition);
    }
    /**
     * permet de récupérer les evals avec tous les details
     * 
     * @param string $userUid correspond au uid de l'utilisateur (Professeur) pour lequel on souhaite récupérer les évals
     * @param int $page contient le numero de la page pour laquelle vous souhaitez recupérer les évals
     * @param boolean $onlyLastEval true pour récupérer les dernières évals      
     * @param boolean $onlyNextEval true pour récupérer les prochaines évals 
     * @return array contenant les evaluations
     */
    public function getEvaluationsWithDetails($userUid = null, $page = null, $onlyLastEval = false, $onlyNextEval = false) {
        //Je recherche le nombre d'element à ignorer dans la requete select
        //$offset = ($page * ApiController::$NUMBER_OF_EVALS_PER_PAGE) - ApiController::$NUMBER_OF_EVALS_PER_PAGE;
        //$limit = ApiController::$NUMBER_OF_EVALS_PER_PAGE . " OFFSET " . $offset;
        $limit = null;
        //Je récupère les evals de la BD

        $table = $this->tableName . " as gexd, exams as exm, modules as modu, groups as gps, sequences as seq, campuses as camp";
        $fieldToSelect = "DISTINCT gexd.id as evalId, gexd.uid as evalUid, gexd.isPlanned as planned, gexd.isStarted as started, gexd.isEnded as ended, gexd.isClosed as closed, gexd.id_userTeacher as teacherId, "
                . "modu.uid as moduleUid, modu.code as moduleCode, modu.name as moduleName, "
                . "gps.uid as groupUid, gps.name as groupName, "
                . "seq.uid as sequenceUid, seq.name as sequenceName, "
                . "camp.uid as campusUid, camp.name as campusName ";
        $whereCondition = "gexd.id_exam = exm.id AND gexd.id_module = modu.id AND gexd.id_group = gps.id AND "
                . "gps.id_sequence = seq.id AND gps.id_campus = camp.id ";
        
        //si le paramètre userUid n'est pas null, on renforce la requête pour récupérer uniquement les évals de l'utilisateur (le professeur)
        if($userUid !== null){
            $table .= " , users as usr ";
            $whereCondition .= " AND gexd.id_userTeacher = usr.id AND usr.uid = '".$userUid."' ";
        }
        
        $countEval = $this->_select($table, "COUNT(gexd.id) as nbre", $whereCondition);

        //rajoutons une condition pour pouvoir filtrer les données
        $table .= ", exams_sessions as examsess";
        $whereCondition .= " AND gexd.id = examsess.id_group_exam ";

        $allEvals = array();

        if (!$onlyLastEval && !$onlyNextEval) {
            //Je recupère toutes les evaluations
            $allEvals = $this->_select($table, $fieldToSelect, $whereCondition, null, $limit);
        } else if($onlyLastEval) {
            //Je recupère juste les 5 dernières
            $limit = 5;
            $whereCondition .= " AND examsess.startDate <= '" . ApiController::getCurrentDate(true) . "' ";

            $allEvals = $this->_select($table, $fieldToSelect, $whereCondition, null, $limit);
        }else if($onlyNextEval) {
            //Je recupère juste les 5 dernières
            $limit = 5;
            $whereCondition .= " AND examsess.startDate >= '" . ApiController::getCurrentDate(true) . "' ";

            $allEvals = $this->_select($table, $fieldToSelect, $whereCondition, null, $limit);
        }

        if (empty($allEvals)) {
            return $allEvals;
        }

        //Je parcours les evals pour récupérer plus de détails
        $result = array();

        foreach ($allEvals as $key => $value) {

            $evalId = $value['evalId'];

            //je recupère les sessions de l'évaluation

            $dates = $this->getExamDates($evalId);

            $allEvals[$key]['sessions'] = (!empty($dates)) ? $dates : NULL;

            //Vérification si c'est une evaluation à risque
            $isRiskyEval = $this->isRiskyEval($evalId);

            $allEvals[$key]['riskyEval'] = (empty($isRiskyEval)) ? false : true;

            //Vérifions que l'évaluation possède un sujet
            $evalHasSubject = $this->evalHasSubject($evalId, false);

            $allEvals[$key]['hasValidatedSubject'] = (!empty($evalHasSubject)) ? true : false;

            //Récupérons le prof
            $teacher = $this->getExamTeacher($value['teacherId']);

            $allEvals[$key]['teacher'] = (!empty($teacher)) ? $teacher[0] : NULL;

            $result[] = array(
                'id' => $evalId,
                'uid' => $value['evalUid'],
                'code' => $value['moduleCode'],
                'module' => $value['moduleName'],
                'groupe' => $value['groupName'],
                'sequence' => $value['sequenceName'],
                'campus' => $value['campusName'],
                'dateDebut' => (!empty($dates)) ? $dates[0]['startDate'] : NULL,
                'dateFin' => (!empty($dates)) ? $dates[count($dates) - 1]['endDate'] : NULL,
                'planned' => intval($value['planned']),
                'started' => intval($value['started']),
                'ended' => intval($value['ended']),
                'closed' => intval($value['closed']),
                'riskyEval' => $allEvals[$key]['riskyEval'],
                'hasValidatedSubject' => $allEvals[$key]['hasValidatedSubject'],
                'teacher' => $allEvals[$key]['teacher']
            );
        }
        $totalEvals = ($countEval[0]['nbre'] === NULL) ? 0 : intval($countEval[0]['nbre']);
        //return array('evals' => array(), 'total' => 0, 'nbrePages' => 0);
        return array('evals' => $result, 'total' => $totalEvals, 'nbrePages' => ceil($totalEvals / ApiController::$NUMBER_OF_EVALS_PER_PAGE));
    }
    private function getExamTeacher($userId) {
        return $this->_select('users as usr', "usr.uid, usr.firstName, usr.lastName", "usr.id = '" . $userId . "' ");
    }
    /**
     * Permet de récupérer les statistiques des evaluations
     * 
     * @param string $userUid correspond au uid de l'utilisateur (Professeur) pour lequel on souhaite avoir les stats
     * @return array contenant les statistiques sur les evaluations
     */
    public function getAllEvaluationStats($userUid = null) {
        $rps = array();

        $allEvals = $this->getAllEvals($userUid);
        
        $evalPlanifiee = $this->getAllPlanEvalStats($userUid);

        $evalPlanAvecSujet = $this->getAllPlanEvalWithSubjectStats($userUid);

        $evalEnCoursAvecSujet = $this->getAllStartEvalWithSubjectStats($userUid);

        $evalTerminee = $this->getAllEndedEvalStats($userUid);

        $evalCloturee = $this->getAllClosedEvalStats($userUid);

        //Evaluation total planifiée 
        $rps['evalTotal'] = (!empty($allEvals)) ? $allEvals[0]['nbre'] : 0;
        
        //Evaluation total planifiée 
        $rps['evalPlanifiee'] = (!empty($evalPlanifiee)) ? $evalPlanifiee[0]['nbre'] : 0;

        //Evaluation planifiée avec sujet
        $rps['evalPlanAvecSujet'] = (!empty($evalPlanAvecSujet)) ? $evalPlanAvecSujet[0]['nbre'] : 0;

        //Evaluation planifiée sans sujet
        $rps['evalPlanSansSujet'] = $rps['evalPlanifiee'] - $rps['evalPlanAvecSujet'];

        //Evaluation planifiée sans sujet avec risque
        $rps['evalPlanSansSujetAvecRisque'] = $this->getAllRiskyEvalStats($userUid);

        //Evaluation en cours sujet fourni
        $rps['evalEnCoursAvecSujet'] = (!empty($evalEnCoursAvecSujet)) ? $evalEnCoursAvecSujet[0]['nbre'] : 0;

        //Evaluation total terminée 
        $rps['evalTerminee'] = (!empty($evalTerminee)) ? $evalTerminee[0]['nbre'] : 0;

        //Evaluation total clôturée 
        $rps['evalCloturee'] = (!empty($evalCloturee)) ? $evalCloturee[0]['nbre'] : 0;

        return $rps;
    }
    /**
     * Permet de récupérer les statistiques des copies des evaluations terminées
     * 
     * @param string $userUid correspond au uid de l'utilisateur (Professeur) pour lequel on souhaite avoir les stats des copies
     * @return array contenant les stats des copies d'examen, les evals avec le plus de copies en retard et les evals à risque
     */
    public function getEvalsCopiesStats($userUid = null) {
        //J'initialise le nombre de copies

        $copies = array(
            'rendues' => 0,
            'nonRendues' => 0
        );

        //Je recupère les évaluations terminées
        $endedEvals = $this->getAllEndedEvalsInfos($userUid);

        if (empty($endedEvals)) {
            return array('totalCopies' => $copies, 'evalsTerm' => array());
        }

        foreach ($endedEvals as $key => $val) {

            $getCopiesStats = $this->copiesStats($val['id'], $val['id_group']);

            //Je complète les stats
            $copies['nonRendues'] += ($getCopiesStats['nonRendues'] !== null) ? $getCopiesStats['nonRendues'] : 0;
            $copies['rendues'] += ($getCopiesStats['rendues'] !== null) ? $getCopiesStats['rendues'] : 0;

            //Je rajoute les stats sur l'élément en cours
            $endedEvals[$key]['students'] = ($getCopiesStats['students'] !== null) ? $getCopiesStats['students'] : 0;
            
            $endedEvals[$key]['copiesRendues'] = ($getCopiesStats['rendues'] !== null) ? $getCopiesStats['rendues'] : 0;
            
            $endedEvals[$key]['copiesNonRendues'] = ($getCopiesStats['nonRendues'] !== null) ? $getCopiesStats['nonRendues'] : 0;            
        }

        return array('totalCopies' => $copies, 'evalsTerm' => $endedEvals);
    }
    private function copiesStats($idGroupExam, $idGroup) {
        $copies = array(
            'rendues' => 0,
            'nonRendues' => 0,
            'students' => 0
        );
        //Je recherche le nombre d'étudiant du group
        $getGroupSize = (new GroupsDal())->getGroupSize($idGroup);

        $groupsSize = ($getGroupSize[0]['nbre'] !== null) ? $getGroupSize[0]['nbre'] : 0;

        if ($groupsSize == 0) {
            return $copies;
        }

        $copies['students'] = $groupsSize;

        //Je recherche le nombre de copies rendues
        $getEvalLeavesRendering = $this->getExamLeavesRendering($idGroupExam, $idGroup);

        $leavesRendering = ($getEvalLeavesRendering[0]['nbre'] !== null) ? $getEvalLeavesRendering[0]['nbre'] : 0;

        //Si aucune copie n'a été rendue
        if ($leavesRendering == 0) {
            $copies['nonRendues'] = $groupsSize;
            $copies['rendues'] = $leavesRendering;
        } else {
            //si des copies ont été rendues
            $copies['rendues'] = $leavesRendering;
            $leavesNotRendering = $groupsSize - $leavesRendering;
            $copies['nonRendues'] = $leavesNotRendering;
        }

        return $copies;
    }
    /**
     * Permet de récupérer les copies rendues d'un examen
     *  
     * @param int $id_exam contient l'identifiant de l'examen (évaluation)
     * @return array contenant une clé nbre avec le nombre de copies rendues
     */
    public function getLeavesRendering($id_exam, $idGroup) {
        
        $students = (new GroupsDal())->getGroupStudents($idGroup);
        
        if(empty($students)){
            return $students;
        }
        
        foreach ($students as $key => $value) {
            $leave = (new GradesDal())->getStudentLeave($id_exam, $value['userId']);
            
            if(empty($leave)){
                $students[$key]['hasLeave'] = false;
                $students[$key]['data'] = null;
            }else{
                $students[$key]['hasLeave'] = true;
                unset($leave[0]['leaveUrl']);
                $students[$key]['data'] = $leave[0];
            }
        }
        return $students;
    }
    /**
     * Permet de compter le nombre de copies rendues
     *  
     * @param int $id_exam contient l'identifiant de l'examen (évaluation)
     * @param int $idGroup contient l'identifiant du group
     * @return array contenant une clé nbre avec le nombre de copies rendues
     */
    public function getExamLeavesRendering($id_exam, $idGroup) {
        return $this->_select($this->tableName . " as gexd, users as usr, grades as grd", "COUNT(grd.id) as nbre",
                        "usr.id = grd.id_userStudent AND gexd.id = grd.id_exam AND "
                . "gexd.id_group = '".$idGroup."' AND grd.id_exam = '" . $id_exam . "' "
                . "AND ( grd.studentExamLeaveUrl != '' OR grd.studentExamLeaveUrl != NULL )");
    }
    /**
     * Permet de récupérer les détails des évaluations avec risque de retard
     * 
     * @param string $userUid correspond au uid de l'utilisateur (Professeur) pour lequel on souhaite avoir les evals
     * @return array contenant les détails des évaluations planifiées avec risque de retard
     */
    public function getAllRiskyEvalDetails($userUid = null) {
        //Je récupére les evals à risque
        $evalPlan = $this->getAllRiskyEvals($userUid);

        if (empty($evalPlan)) {
            return array();
        }

        $result = array();

        foreach ($evalPlan as $value) {

            //Je vérifie si le sujet existe
            $checkSubject = $this->evalHasSubject($value['id']);

            if (is_array($checkSubject) AND ! empty($checkSubject)) {
                continue;
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }
    /**
     * Permet de récupérer le nombre d'évaluations planifiée avec risque de retard
     * 
     * @param string $userUid correspond au uid de l'utilisateur (Professeur) pour lequel on souhaite avoir les stats
     * @return int le nombre des évaluations planifiées avec risque de retard
     */
    private function getAllRiskyEvalStats($userUid = null) {
        //Je récupére les evals à risque
        $evalPlan = $this->getAllRiskyEvals($userUid);

        if (empty($evalPlan)) {
            return 0;
        }

        $i = 0;
        foreach ($evalPlan as $value) {

            //Je vérifie si le sujet existe
            $checkSubject = $this->evalHasSubject($value['id']);

            if (is_array($checkSubject) AND ! empty($checkSubject)) {
                continue;
            } else {
                $i++;
            }
        }
        return $i;
    }
    /**
     * Permet de récupérer les documents d'une evaluation (sujet, corrige etc) en fonction du type de document et de l'id de l'évaluation
     * 
     * @param int $idGroupExam contient l'id de l'évaluation
     * @param string $docTypeSlug contient le slug du type de document qu'on souhaite récupérer
     * @return array contenant la liste des documents ou un tableau vide
     */
    public function getEvalDocuments($idGroupExam, $docTypeSlug) {
        $table = $this->tableName . " as gexd, documents as doc, document_types as doct";
        $select = 'gexd.id as evalId, doc.id_doc as docId, doc.name as docName, '
                . 'doc.version as docVersion, doc.validated as docValidated, doc.createdAt as docCreatedDate, doc.updatedAt as docUpdatedDate';
        $where = "gexd.id = doc.id_group_exam AND doc.id_documentType = doct.id AND "
                . "doct.slug = '" . $docTypeSlug . "' AND doc.id_group_exam = '" . $idGroupExam . "' ";
        
        //Permet de savoir si je dois afficher les corrections ou non
        $canView = true;
        
        if($docTypeSlug === 'corrige'){
            
            $getSessions = $this->getExamDates($idGroupExam);
            
            //Je recherche les dates de debut et de fin
            $size = count($getSessions);
            
            $dateDebut = null;
            $dateFin = null;
            
            if($size == 1){
                $dateDebut = $getSessions[0]['startDate'];
                $dateFin = $getSessions[0]['endDate'];
            }else if($size > 1){
                $dateDebut = $getSessions[0]['startDate'];
                $dateFin = $getSessions[$size - 1]['endDate'];
            }
            //Je vérifie que la date de fin de l'examen est bel et bien passée
            if($dateFin !== null){
                $todayIs = new DateTime(ApiController::getCurrentDate(true));
                
                $dateFinIs = new DateTime($dateFin);
                
                if($todayIs < $dateFinIs){
                    $canView = false;
                }
            }
            
            $where .= "AND (gexd.isEnded = 1 OR gexd.isClosed = 1) ";
        }
        
        return array('doc' => $this->_select($table, $select, $where), 'canView' => $canView);
    }
    /**
     * Permet de vérifier si le sujet validé d'une évaluation existe
     * 
     * @param int $idGroupExam contient l'id de l'évaluation
     * @param bool $onlyPlannedEval préciser si vous voulez vérifier uniquement les eval planifiées ou n'importe quelle eval
     * @return array contenant l'id de l'évaluation 
     */
    public function evalHasSubject($idGroupExam, $onlyPlannedEval = true) {
        $table = $this->tableName . " as gexd, documents as doc, document_types as doct";
        $where = "gexd.id = doc.id_group_exam AND doc.id_documentType = doct.id AND "
                . "doc.validated = 1 AND doct.slug = 'sujet' AND doc.id_group_exam = '" . $idGroupExam . "' ";

        if ($onlyPlannedEval) {
            $where .= "AND gexd.isPlanned = 1";
        }

        return $this->_select($table, 'gexd.id', $where);
    }
    /**
     * Permet de récupérer les dates (sessions) d'une évaluation
     * 
     * @param int $idGroupExam contient l'id de l'évaluation
     * @return array contenant les dates (les sessions)
     */
    protected function getExamDates($idGroupExam) {
        return $this->_select($this->tableName . " as gexd, exams_sessions as examsess, exams as exa",
                        "examsess.startDate, examsess.endDate",
                        "gexd.id = examsess.id_group_exam AND gexd.id_exam = exa.id AND examsess.id_group_exam = '" . $idGroupExam . "' ", "examsess.startDate ASC");
    }
    /**
     * Permet de vérifier si l'évaluation est une evaluation à risque
     * 
     * @param int $idGroupExam contient l'id de l'évaluation
     * @return array contenant l'id de l'évaluation s'il s'agit d'une eval à risque
     */
    private function isRiskyEval($idGroupExam) {
        return $this->_select($this->tableName . " as gexd, exams_sessions as examsess, exams as exa",
                        "DISTINCT gexd.id",
                        "gexd.id = examsess.id_group_exam AND gexd.id_exam = exa.id AND gexd.isPlanned = 1 AND examsess.id_group_exam = '" . $idGroupExam . "' AND "
                        . "DATEDIFF(examsess.startDate, '" . ApiController::getCurrentDate(true) . "' ) >= 0 AND "
                        . "DATEDIFF(examsess.startDate, '" . ApiController::getCurrentDate(true) . "' ) <= '" . ApiController::$EVAL_RISKY_DAYS_DELAY . "' ");
    }
    /**
     * Permet de récupérer la liste des évaluations à risque (avec ou sans sujet)
     * 
     * @param string $userUid correspond au uid de l'utilisateur (Professeur) pour lequel on souhaite avoir les stats
     * @return array contenant les évaluations
     */
    private function getAllRiskyEvals($userUid = null) {
        //Je vérifie que la différence entre date de debut de l'évaluation et la date du jour est <= 14 jours
        
        $table = $this->tableName." as gexd, exams_sessions as examsess, exams as exa, modules as modu, groups as gps";
        $where = "gexd.id = examsess.id_group_exam AND gexd.id_exam = exa.id AND gexd.id_group = gps.id AND modu.id = gexd.id_module AND gexd.isPlanned = 1 AND "
                . "DATEDIFF(examsess.startDate, '" . ApiController::getCurrentDate(true) . "' ) >= 0 AND "
                . "DATEDIFF(examsess.startDate, '" . ApiController::getCurrentDate(true) . "' ) <= '" . ApiController::$EVAL_RISKY_DAYS_DELAY . "' ";
        if($userUid !== null){
            $table .= " , users as usr";
            $where .= " AND gexd.id_userTeacher = usr.id AND usr.uid = '".$userUid."' ";
        }
        
        return $this->_select($table,
                        "DISTINCT gexd.id, gexd.uid, modu.name as moduleName, exa.name as examName, gps.name as groupName, DATEDIFF(examsess.startDate, '" . ApiController::getCurrentDate(true) . "' ) as days",
                        $where, null);
    }
    /**
     * Permet de récupérer le nombre d'évaluations en cours avec sujet
     * 
     * @param string $userUid correspond au uid de l'utilisateur (professeur) pour lequel on souhaite avoir les stats
     * @return array contenant le nombre des évaluations en cours avec sujet
     */
    private function getAllStartEvalWithSubjectStats($userUid = null) {
        $table = $this->tableName." as gexd, documents as doc, document_types as doct";
        $where = "gexd.id = doc.id_group_exam AND doc.id_documentType = doct.id AND gexd.isStarted = 1 AND doc.validated = 1 AND doct.slug = 'sujet' ";
        if($userUid !== null){
            $table .= " , users as usr";
            $where .= " AND gexd.id_userTeacher = usr.id AND usr.uid = '".$userUid."' ";
        }
        $rps = $this->_select($table,"DISTINCT gexd.id as nbre",$where);
        $rps[0]['nbre'] = count($rps);
        return $rps;
    }
    /**
     * Permet de récupérer le nombre d'évaluations planifiées avec sujet
     * 
     * @param string $userUid correspond au uid de l'utilisateur (professeur) pour lequel on souhaite avoir les stats
     * @return array contenant le nombre des évaluations planifiées avec sujet
     */
    private function getAllPlanEvalWithSubjectStats($userUid = null) {
        $table = $this->tableName." as gexd, documents as doc, document_types as doct";
        $where = "gexd.id = doc.id_group_exam AND doc.id_documentType = doct.id AND gexd.isPlanned = 1 AND doc.validated = 1 AND doct.slug = 'sujet' ";
        if($userUid !== null){
            $table .= " , users as usr";
            $where .= " AND gexd.id_userTeacher = usr.id AND usr.uid = '".$userUid."' ";
        }
        $rps = $this->_select($table,"DISTINCT gexd.id as nbre",$where);
        $rps[0]['nbre'] = count($rps);
        return $rps;
    }
    /**
     * Permet de récupérer les infos des évaluations terminées
     * 
     * @param string $userUid correspond au uid de l'utilisateur (Professeur) pour lequel on souhaite avoir les stats des copies
     * @return array contenant les ids des évaluations terminées
     */
    private function getAllEndedEvalsInfos($userUid = null) {
        $table = $this->tableName . " as gexd, modules as modu, groups as gps";
        $champs = "gexd.id, gexd.uid, modu.name as moduleName, gps.name as groupName, isPlanned, isStarted, isEnded, isClosed, "
                . "gexd.id_exam, gexd.id_module, gexd.id_group, gexd.id_userTeacher, gexd.id_classroom";
        $where = "gexd.id_module = modu.id AND gexd.id_group = gps.id AND gexd.isEnded = 1 ";
        if($userUid !== null){
            $table .= " , users as usr";
            $where .= " AND gexd.id_userTeacher = usr.id AND usr.uid = '".$userUid."' ";
        }
        return $this->_select($table, $champs,$where);
    }
    /**
     * Permet de récupérer le nombre d'évaluations clôturées
     * 
     * @param string $userUid correspond au uid de l'utilisateur (professeur) pour lequel on souhaite avoir les stats
     * @return array contenant le nombre des évaluations clôturées
     */
    private function getAllClosedEvalStats($userUid = null) {
        $table = $this->tableName." as gexd";
        $where = "gexd.isClosed = 1 ";
        if($userUid !== null){
            $table .= " , users as usr";
            $where .= " AND gexd.id_userTeacher = usr.id AND usr.uid = '".$userUid."' ";
        }
        $rps = $this->_select($table,"DISTINCT gexd.id as nbre",$where);
        $rps[0]['nbre'] = count($rps);
        return $rps;
    }
    /**
     * Permet de récupérer le nombre d'évaluations terminées
     * 
     * @param string $userUid correspond au uid de l'utilisateur (professeur) pour lequel on souhaite avoir les stats
     * @return array contenant le nombre des évaluations terminées
     */
    private function getAllEndedEvalStats($userUid = null) {
        $table = $this->tableName." as gexd";
        $where = "gexd.isEnded = 1 ";
        if($userUid !== null){
            $table .= " , users as usr";
            $where .= " AND gexd.id_userTeacher = usr.id AND usr.uid = '".$userUid."' ";
        }
        $rps = $this->_select($table,"DISTINCT gexd.id as nbre",$where);
        $rps[0]['nbre'] = count($rps);
        return $rps;
    }
    /**
     * Permet de récupérer le nombre d'évaluations planifiées
     * 
     * @param string $userUid correspond au uid de l'utilisateur (professeur) pour lequel on souhaite avoir les stats
     * @return array contenant le nombre des évaluations planifiées
     */
    private function getAllPlanEvalStats($userUid = null) {
        $table = $this->tableName." as gexd";
        $where = "gexd.isPlanned = 1 ";
        if($userUid !== null){
            $table .= " , users as usr";
            $where .= " AND gexd.id_userTeacher = usr.id AND usr.uid = '".$userUid."' ";
        }
        $rps = $this->_select($table,"DISTINCT gexd.id as nbre",$where);
        $rps[0]['nbre'] = count($rps);
        return $rps;
    }
    /**
     * Permet de récupérer le nombre d'évaluations planifiées
     * 
     * @param string $userUid correspond au uid de l'utilisateur (professeur) pour lequel on souhaite avoir les stats
     * @return array contenant le nombre des évaluations planifiées
     */
    private function getAllEvals($userUid = null) {
        $table = $this->tableName." as gexd";
        $where = "1 = 1 ";
        if($userUid !== null){
            $table .= " , users as usr";
            $where .= " AND gexd.id_userTeacher = usr.id AND usr.uid = '".$userUid."' ";
        }
        $rps = $this->_select($table,"DISTINCT gexd.id as nbre",$where);
        $rps[0]['nbre'] = count($rps);
        return $rps;
    }
    /**
     * Permet de récupérer l'id d'une évaluation à partir du uid de l'une de ses sessions
     * 
     * @param string $sessionDateUid contient le uid de l'une des sessions de l'évaluation
     * @return array contenant l'id de l'évaluation ou un tableau vide s'il ne trouve rien
     */
    private function getGroupExamIdBySessionUid($sessionDateUid) {
        $table = $this->tableName . " as gexd, exams_sessions as examsess";
        $where = "gexd.id = examsess.id_group_exam AND examsess.uid = '" . $sessionDateUid . "' ";

        return $this->_select($table, 'gexd.id', $where);
    }
    /**
     * Permet de savoir si une évaluation existe déjà dans la base de données
     * 
     * @param string $uid contient le uid de l'une des sessions de l'évaluation
     * @return boolean
     */
    private function isDateExist($uid) {
        $table = $this->tableName . " as gexd, exams_sessions as examsess";
        $where = "gexd.id = examsess.id_group_exam AND ";

        $rps = $this->_select($table, 'gexd.id', $where . "examsess.uid = '" . $uid . "' ");

        if (is_array($rps) && !empty($rps)) {
            return true;
        } else {
            return false;
        }
    }
}
