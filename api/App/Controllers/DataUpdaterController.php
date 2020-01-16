<?php

class DataUpdaterController extends ApiController implements AppInterface {

    private $currentYear = null;
    private $checkYear = true;
    private $DataUpdater = null;

    public function __construct() {
        parent::__construct();
        $this->currentYear = self::getCurrentDateInfos('year');
        //$this->loadModel('DataUpdater');
        $this->DataUpdater = new DataUpdaterClass();
    }

    public function view() {
        parent::view();
    }

    public function setCampuses() {
        $query = <<<'GRAPHQL'
                    query {
                        campuses{
                            id
                            name
                            phoneNumber
                            addressField1
                            addressField2
                            addressCity
                            addressPostalCode
                            addressCountry
                            addressExtra1
                            addressExtra2
                            director{
                              id
                              firstName
                              lastName
                              user { username }
                              o365username
                              personalEmail
                              phoneMobile
                              birthDate
                              birthDepartement
                              birthCity
                              birthCountry
                              nationality
                              addressField1
                              addressField2
                              addressCity
                              addressPostalCode
                              addressCountry
                              addressExtra1
                              addressExtra2
                            }
                        }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $campusesDataFromGenius = $this->graphqlQuery($query);

        $rps = $this->DataUpdater->saveCampuses($campusesDataFromGenius['data']['campuses']);

        $this->response($rps);
    }

    public function setCampusesClassrooms() {
        $query = <<<'GRAPHQL'
                    query {
                        campuses{
                            id
                            classrooms{
                               id
                               name
                               capacity
                            }
                        }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $campusesDataFromGenius = $this->graphqlQuery($query);

        $rps = $this->DataUpdater->saveCampusesClassrooms($campusesDataFromGenius['data']['campuses']);

        $this->response($rps);
    }

    public function setSequences() {
        $query = <<<'GRAPHQL'
                    query GetSequences($year: Int!){
                        sequences(where: {year : $year}){
                           id
                           name
                           year
                        }
                    }
GRAPHQL;
        $query2 = <<<'GRAPHQL'
                    query GetSequences{
                        sequences{
                           id
                           name
                           year
                        }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $sequencesDataFromGenius = ($this->checkYear) ? $this->graphqlQuery($query, array('year' => $this->currentYear)) : $this->graphqlQuery($query2);

        $rps = $this->DataUpdater->saveSequences($sequencesDataFromGenius['data']['sequences']);

        $this->response($rps);
    }

    public function setGroups() {
        $query = <<<'GRAPHQL'
                    query GetGroups($year: Int!){
                        groups{
                            id
                            name
                            startDate
                            endDate
                            maxHours
                            closed
                            campus{
                              id
                              name
                            }
                            sequence(where: {year : $year}){
                              id
                              name
                            }
                        }
                    }
GRAPHQL;
        $query2 = <<<'GRAPHQL'
                    query GetGroups{
                        groups{
                            id
                            name
                            startDate
                            endDate
                            maxHours
                            closed
                            campus{
                              id
                              name
                            }
                            sequence{
                              id
                              name
                            }
                        }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $groupsDataFromGenius = ($this->checkYear) ? $this->graphqlQuery($query, array('year' => $this->currentYear)) : $this->graphqlQuery($query2);

        $rps = $this->DataUpdater->saveGroups($groupsDataFromGenius['data']['groups']);

        $this->response($rps);
    }

    public function setModules() {
        $query = <<<'GRAPHQL'
                    query GetModules($year: Int!){
                        modules{
                            id
                            name
                            code
                            objective
                            shortDescription
                            description
                            ectsCredits
                            length
                            sequences(where: {year : $year}){
                              id
                              name
                              year
                            }
                        }
                    }
GRAPHQL;
        $query2 = <<<'GRAPHQL'
                    query GetModules{
                        modules{
                            id
                            name
                            code
                            objective
                            shortDescription
                            description
                            ectsCredits
                            length
                            sequences{
                              id
                              name
                              year
                            }
                        }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $modulesDataFromGenius = ($this->checkYear) ? $this->graphqlQuery($query, array('year' => $this->currentYear)) : $this->graphqlQuery($query2);

        $rps = $this->DataUpdater->saveModules($modulesDataFromGenius['data']['modules']);

        $this->response($rps);
    }

    public function affectModulesToSequences() {
        $query = <<<'GRAPHQL'
                    query GetSequencesModules($year: Int!){
                        modules{
                            id
                            name
                            sequences(where: {year : $year}){
                              id
                              name
                            }
                        }
                    }
GRAPHQL;
        $query2 = <<<'GRAPHQL'
                    query GetSequencesModules{
                        modules{
                            id
                            name
                            sequences{
                              id
                              name
                            }
                        }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $modulesDataFromGenius = ($this->checkYear) ? $this->graphqlQuery($query, array('year' => $this->currentYear)) : $this->graphqlQuery($query2);

        $rps = $this->DataUpdater->affectModulesToSequences($modulesDataFromGenius['data']['modules']);

        $this->response($rps);
    }
    
    public function setUsers() {
        $query = <<<'GRAPHQL'
                    query{
                        users{
                            id
                            username
                            roles
                            profile{
                                id
                                firstName
                                lastName
                                user { username }
                                o365username
                                personalEmail
                                phoneMobile
                                birthDate
                                birthDepartement
                                birthCity
                                birthCountry
                                nationality
                                addressField1
                                addressField2
                                addressCity
                                addressPostalCode
                                addressCountry
                                addressExtra1
                                addressExtra2
                            }
                            campuses{
                              id 
                              name
                            }
                            defaultView
                        }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $usersDataFromGenius = $this->graphqlQuery($query);

        $rps = $this->DataUpdater->saveUsersRoles($usersDataFromGenius['data']['users']);

        $this->response($rps);
    }
    
    public function setTeachers() {
        $query = <<<'GRAPHQL'
                    query{
                        profiles(
                            where: { status_some: { 
                              role: "teacher"
                              status: "active"
                                }
                            }
                          ) {
                            id
                            firstName
                            lastName
                            user { username }
                            o365username
                            personalEmail
                            phoneMobile
                            birthDate
                            birthDepartement
                            birthCity
                            birthCountry
                            nationality
                            addressField1
                            addressField2
                            addressCity
                            addressPostalCode
                            addressCountry
                            addressExtra1
                            addressExtra2
                          }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $usersDataFromGenius = $this->graphqlQuery($query);

        $rps = $this->DataUpdater->saveTeachers($usersDataFromGenius['data']['profiles']);

        $this->response($rps);
    }
    
    public function setExams() {
        $query = <<<'GRAPHQL'
                    query{
                        lessons {
                            module{
                              id
                              name
                            }
                            group{
                              id
                              name
                            }
                            dates(where: {
                              exam: { id_not: null}
                            }) {
                              id
                              startDate
                              endDate
                              exam {
                                id
                                name
                                description
                                length
                                deadLineLength
                                type
                                reset
                              }
                              classroom{
                                id
                                name
                              }
                              teacher{
                                id 
                                firstName
                                lastName
                              }
                            }
                        }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $examsDataFromGenius = $this->graphqlQuery($query);

        $rps = $this->DataUpdater->saveExams($examsDataFromGenius['data']['lessons']);

        $this->response($rps);
    }

    public function setStudents() {
        $query = <<<'GRAPHQL'
                    query{
                        profiles(
                            where: { status_some: { 
                              role: "student"
                              status: "active"
                                }
                            }
                          ) {
                            id
                            firstName
                            lastName
                            user { username }
                            o365username
                            personalEmail
                            phoneMobile
                            birthDate
                            birthDepartement
                            birthCity
                            birthCountry
                            nationality
                            addressField1
                            addressField2
                            addressCity
                            addressPostalCode
                            addressCountry
                            addressExtra1
                            addressExtra2
                          }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $usersDataFromGenius = $this->graphqlQuery($query);

        $rps = $this->DataUpdater->saveStudents($usersDataFromGenius['data']['profiles']);

        $this->response($rps);
    }
    
    public function setStudentsRegistration() {
        $query = <<<'GRAPHQL'
                    query{
                        profiles(
                            where: { status_some: { 
                              role: "student"
                              status: "active"
                                }
                            }
                          ) {
                            id
                            firstName
                            lastName
                            user { username }
                            sGroups {
                               id
                               name
                            }
                          }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $usersDataFromGenius = $this->graphqlQuery($query);

        $rps = $this->DataUpdater->saveStudentsRegistration($usersDataFromGenius['data']['profiles']);

        $this->response($rps);
    }
    
    public function setTeacherModules() {
        $query = <<<'GRAPHQL'
                    query{
                        profiles(
                            where: { status_some: { 
                              role: "teacher"
                              status: "active"
                                }
                            }
                          ) {
                            id
                            firstName
                            lastName
                            user { username }
                            tModules {
                               id
                               name
                            }
                          }
                    }
GRAPHQL;

        //récupération des données depuis l'api graphql de genius
        $usersDataFromGenius = $this->graphqlQuery($query);

        $rps = $this->DataUpdater->saveTeacherModules($usersDataFromGenius['data']['profiles']);

        $this->response($rps);
    }
    
}
