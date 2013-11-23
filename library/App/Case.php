<?php

class App_Case {

    const
            INVALID_USERNAME_PASSWORD = 0,
            PENDING = 1,
            ACTIVE = 2,
            APPROVED = 3,
            DECLINED = 4,
            SUSPENDED = 5,
            DELETED = 9,
            NO_USER_ACCESS_RULES = 100,
            ENCRIPTED_PASSWORD = 'ENCRIPTED-PASSWORD',
            READ_ONLY = 1,
            CHANGE_STATUS = 2,
            FULL_ACCESS = 3;

 
   static function getCaseName( $id ){
//       if( empty($id)){
//        return $name = '';
//       }
//       
//       $case = new Model_Cases();
//       $case->id = $id;
//       $case->find();
//       return $case->name;               
   }
   
   static function getCaseStatus( $id ){
       if( empty($id)){
        return $name = '';
       }
       $statuses = new Model_Status();
       $statuses->id = $id;
       $statuses->find();
       return $statuses->name;       
   }
    
}