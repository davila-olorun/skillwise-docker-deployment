<?php

class UsersDal extends AppModel {

    public $tableName = 'users';

    public function __construct() {
        parent::__construct();
    }

    public function saveUsers(Users $user) {
        $response = array();

        $dataArray = ApiController::convertObjectIntoArray($user);

        $reqData = $this->resolveRequestParams($dataArray);

        if (!$this->isUserExist($reqData['data'][':uid'])) {
            $response['rps'] = $this->_insert($this->tableName, $reqData['props'], $reqData['bind'], $reqData['data']);
        } else {
            $rps = $this->getUserIdByUid($reqData['data'][':uid']);
            $response['rps'] = $rps[0]['id'];
        }

        return $response;
    }

    public function updateUsers(Users $object, $userId = null) {
        $response = array();

        $dataArray = ApiController::convertObjectIntoArray($object);

        $reqData = $this->resolveRequestParams($dataArray, 'update');
        
        $where = "";
        
        if($userId === null){
           $where = 'uid = "' . $reqData['data'][':uid'] . '" '; 
        }else{
           $where = 'id = "' . $userId . '" ';
        }
        
        $response['rps'] = $this->_update($this->tableName, $reqData['bind'], $where, $reqData['data']);

        return $response;
    }

    public function getUserIdByUid($uid) {
        $rps = $this->_select($this->tableName, 'id', "uid = '" . $uid . "' ");
        return $rps;
    }

    public function getUserIdByUsername($username) {
        $rps = $this->_select($this->tableName, 'id', "username = '" . $username . "'  OR schoolEmail = '" . $username . "' ");
        return $rps;
    }

    private function isUserExist($uid) {
        $rps = $this->_select($this->tableName, 'id', "uid = '" . $uid . "' ");

        if (is_array($rps) && !empty($rps)) {
            return true;
        } else {
            return false;
        }
    }

    private function getExamByModuleId($modid, $grpid) {

        $tables = "groups_exams_dates as gexd, exams as exams, groups as groups, campuses as campus, modules as mods";

        $col = "gexd.startDate as startDate, gexd.endDate as endDate, gexd.isPlanned as planned, "
                . "gexd.isStarted as started, gexd.isEnded as ended, gexd.isClosed as closed, "
                . "groups.name as grpName, exams.name as examName, exams.uid as examUid, "
                . "exams.description as description, exams.type as examType, campus.name as campus ";

        $where = "(exams.id = gexd.id_exam AND groups.id = gexd.id_group AND campus.id = groups.id_campus)"
                . " AND groups.id = {$grpid} AND mods.id = {$modid}";

        return $this->_select($tables, $col, $where);
    }

    private function getTeacherModule($idteach) {
        $tablesTeach = "groups_exams_dates as gexd, exams as exam, modules as modul, "
                . "users as users, groups as groupe";
        $colsTeach = "gexd.startDate as startDate, gexd.endDate as endDate, gexd.isPlanned as planned, "
                . "gexd.isStarted as started, gexd.isEnded as ended, gexd.isClosed as closed, "
                . "modul.name as modulename, "
                . "modul.description as moduledescrip, modul.code as modulecdoe, "
                . "modul.objective as moduleobj, exam.name as examname, exam.description as examdescrip, "
                . "exam.type as examtype, groupe.name as groupname";
        $whereTeach = "(modul.id = gexd.id_module AND exam.id = gexd.id_exam AND users.id = gexd.id_userTeacher) "
                . "AND groupe.id = gexd.id_group AND users.id = '" . $idteach . "'";

        return $this->_select($tablesTeach, $colsTeach, $whereTeach);
    }

    /*
     * Methode permettant de recupérer le role de l'individu
     * @param sting $iduser identifiant de l'individu connecté
     * @return array tableau des differents role que pocede l'individu 
     */

    public function getUserRole($iduser) {

        $tables = "user_roles as userrole,  roles  as roles, $this->tableName as users ";
        $col = "roles.slug";
        $where = "userrole.id_role = roles.id AND "
                . "userrole.id_user = users.id AND "
                . "users.id = '{$iduser}'";
        return $this->_select($tables, $col, $where);
    }

    /*
     * Methode permettant de recupérer les information de l'individu avec son role
     * @param sting $uid unique id d'un étudiant connecté
     * @return array $user tableau de toutes les infos de l'étudiant et et son role
     */

    public function getStudentByUid($uid) {
        $user = array();
        $cols = "id, uid, firstName, lastName, username, schoolEmail as proemail, personalEmail as email, "
                . "phoneMobile as mobile, DATE_FORMAT(birthDate, '%Y-%m-%d') as birthDate, "
                . "birthDepartment as birthDepart, birthCity, birthCountry, "
                . "nationality, addressField, addressCity, addressPostalCode, addressCountry, addressExtra";
        $where = "uid = '" . $uid . "'";
        $infos = $this->_select($this->tableName, $cols, $where);

        if (empty($infos)) {
            return 'Student not found';
        }
        $user['infos'] = $infos[0];

        $user['role'] = $this->getUserRole($user['infos']['id']);

        if (empty($user['role'])) {
            return 'Student not found';
        }

        return $user;
    }

    public function authentication(Users $user) {
        $rps = $this->checkAccountByEmailAndPassword($user);

        if (empty($rps)) {
            return "User not found";
        }

        if (!$this->verifyPassword($user->getPassword(), $rps[0]['password'])) {
            return "Password do not match";
        }

        $userRoles = $this->getUserRolesByUserId($rps[0]['id']);

        if (empty($userRoles)) {
            return "User has not roles";
        }

        $loggedUser = $rps[0];

        foreach ($userRoles as $value) {
            $loggedUser['roles'][] = $value['slug'];
        }

        return $loggedUser;
    }

    private function checkAccountByEmailAndPassword(Users $user) {
        $select = "usr.id, usr.uid, usr.firstName, usr.lastName, usr.password";
        $table = $this->tableName . " as usr, roles as rol, user_roles as urol";
        $where = "usr.id = urol.id_user AND rol.id = urol.id_role AND usr.accountStatus = 1 AND  "
                . "(schoolEmail = '" . $user->getSchoolEmail() . "' OR username = '" . $user->getSchoolEmail() . "')  ";

        return $this->_select($table, $select, $where, null, 1);
    }

    private function verifyPassword($password, $hashPassword) {
        return password_verify($password, $hashPassword);
    }

    private function getUserRolesByUserId($idUser) {
        $userRoles = new UserRolesDal();
        return $userRoles->getUserRolesByUserId($idUser);
    }

    public function uploadUser(Users $user) {

        if ($this->emailExist($user->getSchoolEmail(), $user->getUid())) {
            return $response['rps'] = "Erreur : l'email est déjà utilisé";
        }
        
        return $this->updateUsers($user);
    }

    private function emailExist($email, $uid) {
        $condition = "personalEmail = '" . $email . "' AND uid !='" . $uid . "'";
        $rps = $this->_select('users', 'id', $condition);

        if (is_array($rps) && !empty($rps)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function checkUserCurrentPassword($password, $user){
        $user = $this->_select($this->tableName, 'password', 'uid = "' . $user->getUid() . '" ');
        
        if (!empty($user) && $this->verifyPassword($password, $user[0]['password'])) {
            return true;
        }
        return false;
    }

    public function upPass(Users $user) {
        return $this->updateUsers($user);
    }

}
