<?php


class GradesDal extends AppModel{
    private $tableName = 'grades';
    
    public function __construct() {
        parent::__construct();
    }
    public function saveGrade(Grades $grade) {
        $response = array();
        
        $dataArray = ApiController::convertObjectIntoArray($grade);
        
        $reqData = $this->resolveRequestParams($dataArray);
        
        if(!$this->isExist($reqData['data'][':id_userStudent'], $reqData['data'][':id_exam'])){
            $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        }else{
            $response['rps'] = "Existe";
        }
        
        return $response;
    }
    public function getStudentLeave($idExam,$idUserStudent) {
        $select = "grd.id as gradeId, grd.uid as gradeUid, grd.grade as grade, grd.comment as comment, "
                . "grd.absent as absent, grd.cheat as cheat, grd.studentExamLeaveUrl as leaveUrl, "
                . "grd.createdAt as leaveCreatedDate, grd.updatedAt as leaveUpdatedDate ";
        
        return $this->_select($this->tableName . " as grd, users as usr, groups_exams_dates as gexd", $select,
                        "usr.id = grd.id_userStudent AND gexd.id = grd.id_exam AND grd.id_userStudent = '" . $idUserStudent . "' AND "
                . "grd.id_exam = '" . $idExam . "' "
                . "AND ( grd.studentExamLeaveUrl != '' OR grd.studentExamLeaveUrl IS NOT NULL )");
    }
    public function getStudentLeaveForDownload($gradeId, $examUid, $userStudentUid = null){
        $select = "grd.id as gradeId, grd.uid as gradeUid, grd.grade as grade, grd.comment as comment, "
                . "grd.absent as absent, grd.cheat as cheat, grd.studentExamLeaveUrl as leaveUrl, "
                . "grd.createdAt as leaveCreatedDate, grd.updatedAt as leaveUpdatedDate ";
        $where = "usr.id = grd.id_userStudent AND gexd.id = grd.id_exam AND grd.id = '" . $gradeId . "' AND "
                . "gexd.uid = '" . $examUid . "' "
                . "AND ( grd.studentExamLeaveUrl != '' OR grd.studentExamLeaveUrl IS NOT NULL ) ";
        
        /*if($userStudentUid != null){
            $where .= " AND usr.uid = '".$userStudentUid."' ";
        }*/
        
        return $this->_select($this->tableName . " as grd, users as usr, groups_exams_dates as gexd", $select,$where);
    }

    public function isExist($idUser,$idExam){
        $rps = $this->_select($this->tableName, 'id', "id_exam = '" . $idExam . "' AND id_userStudent = '".$idUser."' ");

        if (is_array($rps) && !empty($rps)) {
            return true;
        } else {
            return false;
        }
    }
}
