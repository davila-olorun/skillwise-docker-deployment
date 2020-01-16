<?php

class DataUpdaterClass extends AppModel {

    private $campuses;
    private $classrooms;
    private $sequences;
    private $groups;
    private $modules;
    private $affect;
    private $exams;
    private $examDates;
    private $sessionDates;
    private $users;
    private $userRoles;
    private $register;
    private $teach;
    private $campusTeam;

    public function __construct() {
        parent::__construct();
    }

    public function saveCampuses(array $campuses) {
        $datas = null;
        $data = null;

        foreach ($campuses as $value) {
            //J'initialise mon objet campus
            $this->campuses = new Campuses();

            //Je passe chaque valeur à son setter
            $this->campuses->setUid($value['id']);
            $this->campuses->setName($value['name']);
            $this->campuses->setPhoneNumber($value['phoneNumber']);
            $this->campuses->setAddressField($value['addressField1']);
            $this->campuses->setAddressField($value['addressField2']);
            $this->campuses->setAddressCity($value['addressCity']);
            $this->campuses->setAddressPostalCode($value['addressPostalCode']);
            $this->campuses->setAddressCountry($value['addressCountry']);
            $this->campuses->setAddressExtra($value['addressExtra1']);
            $this->campuses->setAddressExtra($value['addressExtra2']);
            $this->campuses->setCreatedAt(ApiController::getCurrentDate(true));
            $this->campuses->setUpdatedAt(ApiController::getCurrentDate(true));

            $data = (new CampusesDal())->saveCampuses($this->campuses);
            $this->campuses = null;
            
            if(!empty($value['director']) && intval($data['rps']) > 0){
                $user = $this->saveUsers(array($value['director']), ApiController::$rolesSlug['director']);
                
                if(intval($user[0]['rps']) > 0){
                    //J'initialise mon objet campus team
                    $this->campusTeam = new CampusesTeams();

                    $this->campusTeam->setIdCampus($data['rps']);
                    $this->campusTeam->setIdUser($user[0]['rps']);
                    $this->campusTeam->setIsDirector(1);

                    $data['director'] = (new CampusesTeamsDal())->saveTeam($this->campusTeam);
                }
            }
            
            $datas[] = $data;
        }

        return $datas;
    }

    public function saveCampusesClassrooms(array $campusesData) {
        $datas = null;

        foreach ($campusesData as $value) {
            //Je recupère l'id du campus
            $id = $value['id'];

            //Je verifie si le campus a des classes

            if (is_array($value['classrooms']) && !empty($value['classrooms'])) {
                $data = null;
                //Je parcours les classes du campus pour les insérer
                foreach ($value['classrooms'] as $val) {
                    //J'initialise mon objet classrooms
                    $this->classrooms = new Classrooms();

                    //Je passe chaque valeur à son setter
                    $this->classrooms->setUid($val['id']);
                    $this->classrooms->setName($val['name']);
                    $this->classrooms->setCapacity($val['capacity']);
                    $this->classrooms->setCreatedAt(ApiController::getCurrentDate(true));
                    $this->classrooms->setUpdatedAt(ApiController::getCurrentDate(true));
                    $this->classrooms->setIdCampus($id);

                    $data[] = (new ClassroomsDal())->saveCampusesClassrooms($this->classrooms);
                    $this->classrooms = null;
                }
                $datas[$id] = $data;
            }
        }

        return $datas;
    }

    public function saveSequences(array $sequences) {
        $datas = null;

        foreach ($sequences as $value) {
            //J'initialise mon objet sequence
            $this->sequences = new Sequences();

            //Je passe chaque valeur à son setter
            $this->sequences->setUid($value['id']);
            $this->sequences->setName($value['name']);
            $this->sequences->setYear($value['year']);
            $this->sequences->setCreatedAt(ApiController::getCurrentDate(true));
            $this->sequences->setUpdatedAt(ApiController::getCurrentDate(true));

            $datas[] = (new SequencesDal())->saveSequences($this->sequences);
            $this->sequences = null;
        }

        return $datas;
    }

    public function saveGroups(array $groupsData) {
        $datas = null;

        foreach ($groupsData as $value) {

            //Je verifie si le groupe est bien lié a une sequence et un campus

            if (!empty($value['sequence']) && !empty($value['campus'])) {

                //J'initialise mon objet groups
                $this->groups = new Groups();

                //Je passe chaque valeur à son setter
                $this->groups->setUid($value['id']);
                $this->groups->setName($value['name']);
                $this->groups->setStartDate(ApiController::formatGeniusDate($value['startDate']));
                $this->groups->setEndDate(ApiController::formatGeniusDate($value['endDate']));
                $this->groups->setMaxHours($value['maxHours']);
                $this->groups->setClosed(($value['closed'] === false) ? 0 : 1);
                $this->groups->setCreatedAt(ApiController::getCurrentDate(true));
                $this->groups->setUpdatedAt(ApiController::getCurrentDate(true));
                $this->groups->setIdCampus($value['campus']['id']);
                $this->groups->setIdSequence($value['sequence']['id']);

                $datas[] = (new GroupsDal())->saveGroups($this->groups);
                $this->groups = null;
            }
        }

        return $datas;
    }

    public function saveModules(array $modules) {
        $datas = null;

        foreach ($modules as $value) {

            if (is_array($value['sequences']) && !empty($value['sequences'])) {
                //J'initialise mon objet module
                $this->modules = new Modules();

                //Je passe chaque valeur à son setter
                $this->modules->setUid($value['id']);
                $this->modules->setName($value['name']);
                $this->modules->setCode($value['code']);
                $this->modules->setObjective($value['objective']);
                $this->modules->setDescription($value['description']);
                $this->modules->setShortDescription($value['shortDescription']);
                $this->modules->setEctsCredits($value['ectsCredits']);
                $this->modules->setLength($value['length']);
                $this->modules->setCreatedAt(ApiController::getCurrentDate(true));
                $this->modules->setUpdatedAt(ApiController::getCurrentDate(true));

                $datas[] = (new ModulesDal())->saveModules($this->modules);
                $this->modules = null;
            }
        }

        return $datas;
    }

    public function affectModulesToSequences(array $modules) {
        $datas = null;

        foreach ($modules as $value) {

            if (is_array($value['sequences']) && !empty($value['sequences'])) {

                $data = null;
                foreach ($value['sequences'] as $val) {
                    //J'initialise mon objet Affect
                    $this->affect = new Affect();

                    //Je passe l'id du module à son setter
                    $this->affect->setIdModule($value['id']);

                    //Je passe l'id de la sequence à son setter
                    $this->affect->setIdSequence($val['id']);

                    $data[$value['id']][] = (new AffectDal())->saveModulesAndSequences($this->affect);
                    $this->affect = null;
                }
                $datas[] = $data;
            }
        }

        return $datas;
    }

    public function saveExams(array $exams) {
        $datas = null;

        foreach ($exams as $Ekey => $value) {

            if (!empty($value['dates']) && !empty($value['module']) && !empty($value['group'])) {

                $data = null;
                $idExam = null;
                $idGroupExamDate = null;

                foreach ($value['dates'] as $key => $val) {

                    if (ApiController::formatGeniusDate($val['startDate']) >= ApiController::getCurrentDate()) {
                        //J'initialise mon objet exams
                        $this->exams = new Exams();

                        //Je passe chaque valeur à son setter
                        $this->exams->setUid($val['exam']['id']);
                        $this->exams->setName($val['exam']['name']);
                        $this->exams->setDescription($val['exam']['description']);
                        $this->exams->setType((!empty($val['exam']['type'])) ? $val['exam']['type'] : null);
                        $this->exams->setCreatedAt(ApiController::getCurrentDate(true));
                        $this->exams->setUpdatedAt(ApiController::getCurrentDate(true));


                        //Je recupère l'id de l'exam inseré dans la bd
                        $idExam = (new ExamsDal())->saveExams($this->exams);

                        if (intval($idExam['rps']) > 0) {
                            $this->examDates = new GroupsExamsDates();

                            $this->examDates->setUid(uniqid()); //$val['id']
                            //$this->examDates->setStartDate(ApiController::formatGeniusDate($val['startDate']));
                            //$this->examDates->setEndDate(ApiController::formatGeniusDate($val['endDate']));
                            $this->examDates->setCreatedAt(ApiController::getCurrentDate(true));
                            $this->examDates->setUpdatedAt(ApiController::getCurrentDate(true));
                            $this->examDates->setIdExam($idExam['rps']);
                            $this->examDates->setIdGroup($value['group']['id']);
                            $this->examDates->setIdModule($value['module']['id']);
                            $this->examDates->setIdClassroom($val['classroom']['id']);
                            $this->examDates->setIdUserTeacher($val['teacher']['id']);

                            $idGroupExamDate = ($idGroupExamDate == null) ? (new GroupsExamsDatesDal())->saveDates($this->examDates, $val['id']) : $idGroupExamDate;

                            if (intval($idGroupExamDate['rps']) > 0) {
                                $this->sessionDates = new ExamsSessions();

                                $this->sessionDates->setUid($val['id']);
                                $this->sessionDates->setStartDate(ApiController::formatGeniusDate($val['startDate']));
                                $this->sessionDates->setEndDate(ApiController::formatGeniusDate($val['endDate']));
                                $this->sessionDates->setCreatedAt(ApiController::getCurrentDate(true));
                                $this->sessionDates->setUpdatedAt(ApiController::getCurrentDate(true));
                                $this->sessionDates->setIdGroupExam($idGroupExamDate['rps']);

                                $data[$Ekey][] = (new ExamsSessionsDal())->saveSessions($this->sessionDates);
                            } else {
                                $data[$Ekey][] = $idGroupExamDate; //je recupère l'erreur 
                            }

                            //$data[$Ekey][] = (new GroupsExamsDatesDal())->saveDates($val['id']);
                        } else {
                            $data[$Ekey][] = $idExam; //je recupère l'erreur
                        }
                    } else {
                        $data[$Ekey][] = 'date passée';
                    }
                    $this->examDates = null;
                    $this->exams = null;
                }

                $datas[] = $data;
            }
        }

        return $datas;
    }

    public function saveTeachers(array $users) {
        return $this->saveUsers($users, ApiController::$rolesSlug['teacher']);
    }

    public function saveStudents(array $users) {
        return $this->saveUsers($users, ApiController::$rolesSlug['student']);
    }

    public function saveStudentsRegistration(array $users) {
        $datas = null;

        foreach ($users as $Ekey => $value) {

            if (!empty($value['sGroups'])) {

                $data = null;
                $idUser = null;

                //J'initialise mon objet usersDal
                $this->users = new UsersDal();

                //Je recupère l'id du user dans la bd
                $idUser = $this->users->getUserIdByUid($value['id']);

                foreach ($value['sGroups'] as $key => $val) {

                    if (!empty($idUser) && intval($idUser[0]['id']) > 0) {
                        //J'initialise mon objet register
                        $this->register = new Register();

                        $this->register->setIdUserStudent($idUser[0]['id']);
                        $this->register->setIdGroup($val['id']);

                        $data[] = (new RegisterDal())->saveUserStudentRegistration($this->register);
                    } else {
                        $data[] = 'User not found';
                    }
                    $this->register = null;
                }

                $this->users = null;
                $datas[] = $data;
            }
        }

        return $datas;
    }

    public function saveTeacherModules(array $users) {
        $datas = null;

        foreach ($users as $Ekey => $value) {

            if (!empty($value['tModules'])) {

                $data = null;
                $idUser = null;

                //J'initialise mon objet usersDal
                $this->users = new UsersDal();

                //Je recupère l'id du user dans la bd
                $idUser = $this->users->getUserIdByUid($value['id']);

                foreach ($value['tModules'] as $key => $val) {

                    if (!empty($idUser) && intval($idUser[0]['id']) > 0) {
                        //J'initialise mon objet teach
                        $this->teach = new Teach();

                        $this->teach->setIdUserTeacher($idUser[0]['id']);
                        $this->teach->setIdModule($val['id']);

                        $data[] = (new TeachDal())->saveTeacherModules($this->teach);
                    } else {
                        $data[] = 'User not found';
                    }
                    $this->teach = null;
                }

                $this->users = null;
                $datas[] = $data;
            }
        }

        return $datas;
    }

    public function saveUsersRoles(array $users) {
        $datas = null;

        foreach ($users as $value) {
            $data = null;
            $idUser = null;
            $newUserData = null;
            $insertedUserId = null;
            
            if (empty($value['profile'])) {
                $datas[] = "User profile not found";
                $this->users = null;
                continue;
            }
            
            //J'initialise mon objet usersDal
            $this->users = new UsersDal();

            //Je recupère l'id du user dans la bd
            $idUser = $this->users->getUserIdByUid($value['profile']['id']);

            if (empty($idUser)) {
                $newUserData = array($value['profile']);
                $insertedUserId = $this->saveUsers($newUserData);
                
                if (empty($insertedUserId[0]['rps']) || intval($insertedUserId[0]['rps']) < 1) {
                    $datas[] = "User not found";
                    $this->users = null;
                    continue;
                }else{
                    $idUser = null;
                    $idUser[]['id'] = $insertedUserId[0]['rps'];
                }
            }
                                
            $rolesLog = array();
            if (!empty($value['roles'])) {
                
                foreach ($value['roles'] as $key => $rolValue) {
                    $this->userRoles = new UserRoles();

                    $this->userRoles->setIdUser($idUser[0]['id']);
                    //$this->userRoles->setIdRole(null);
                    $this->userRoles->setActive(1);

                    $roleSlug = (isset(ApiController::$rolesSlug[$rolValue])) ? ApiController::$rolesSlug[$rolValue] : null;
                    $data = ($roleSlug !== null) ? (new UserRolesDal())->saveUserRoles($this->userRoles, $roleSlug) : null;

                    $this->userRoles = null;
                    $rolesLog[] = $data;
                    $data = null;
                }
            }
            
            $campusLog = array();
            
            if (!empty($value['campuses'])) {
                
                foreach ($value['campuses'] as $key => $val) {
                    $this->campuses = new CampusesDal();

                    $idCampus = $this->campuses->getCampusIdByUid($val['id']);

                    if (!empty($idCampus) && intval($idCampus[0]['id']) > 0) {
                        //J'initialise mon objet campus team
                        $this->campusTeam = new CampusesTeams();

                        $this->campusTeam->setIdCampus($idCampus[0]['id']);
                        $this->campusTeam->setIdUser($idUser[0]['id']);

                        $data = (new CampusesTeamsDal())->saveTeam($this->campusTeam);
                    } else {
                        $data = 'Campus not found';
                    }
                    $this->campusTeam = null;
                    $campusLog[] = $data;
                    $data = null;
                }
            }
            $this->users = null;
            $datas[] = array('user' => $idUser[0]['id'], 'roles' => $rolesLog, 'campuses' => $campusLog);
        }

        return $datas;
    }

    private function saveUsers(array $users, $roleSlug = null) {
        $datas = null;

        foreach ($users as $value) {

            //J'initialise mon objet user
            $this->users = new Users();

            //Je passe chaque valeur à son setter
            $this->users->setUid($value['id']);
            $this->users->setFirstName($value['firstName']);
            $this->users->setLastName($value['lastName']);

            /* if (!empty($value['user']['username']) && empty($value['o365username'])) {
              $this->users->setUsername($value['user']['username']);
              $this->users->setSchoolEmail($value['user']['username']);
              } else if (empty($value['user']['username']) && !empty($value['o365username'])) {
              $this->users->setUsername($value['o365username']);
              $this->users->setSchoolEmail($value['o365username']);
              } else {
              $this->users->setUsername($value['user']['username']);
              $this->users->setSchoolEmail($value['o365username']);
              } */
            $this->users->setUsername($value['user']['username']);
            $this->users->setSchoolEmail($value['o365username']);
            $this->users->setPersonalEmail($value['personalEmail']);
            $this->users->setPhoneMobile(ApiController::removeSpecificChar($value['phoneMobile'], " ", ""));

            $this->users->setBirthDate(ApiController::formatGeniusDate($value['birthDate']));
            $this->users->setBirthDepartment($value['birthDepartement']);
            $this->users->setBirthCity($value['birthCity']);
            $this->users->setBirthCountry($value['birthCountry']);

            $this->users->setNationality($value['nationality']);

            $this->users->setAddressField($value['addressField1']);
            $this->users->setAddressField($value['addressField2']);
            $this->users->setAddressCity($value['addressCity']);
            $this->users->setAddressPostalCode($value['addressPostalCode']);
            $this->users->setAddressCountry($value['addressCountry']);
            $this->users->setAddressExtra($value['addressExtra1']);
            $this->users->setAddressExtra($value['addressExtra2']);

            $this->users->setCreatedAt(ApiController::getCurrentDate(true));
            $this->users->setUpdatedAt(ApiController::getCurrentDate(true));

            $result = (new UsersDal())->saveUsers($this->users);

            $data = $result;
            if (intval($result['rps']) > 0 && $roleSlug !== null) {
                $this->userRoles = new UserRoles();

                $this->userRoles->setIdUser($result['rps']);
                //$this->userRoles->setIdRole(null);
                $this->userRoles->setActive(1);

                $data['user'] = $result['rps'];
                $data['role'] = (new UserRolesDal())->saveUserRoles($this->userRoles, $roleSlug);
            }
            $datas[] = $data;
            $this->users = null;
        }

        return $datas;
    }

}
