<?php

class App_User
{
    
    
    public static function isLogged()
    {                
        $auth = Zend_Auth::getInstance();
        return $auth->hasIdentity();
    }
    
    
    public static function getUserById( $id ){
        $getLawyer = new Application_Model_UsersMapper();          
        $getLawyerDetails = new Application_Model_UsersdetailMapper();                          
        $allDetails = array();        
        if( $id ){            
            $lawyer       = $getLawyer->find( $id );            
            $lawyerDetail =  $getLawyerDetails->find( $id );                        
            //merge the data from user and user details table
            $allDetails   = $lawyer[0] + $lawyerDetail[0];                    
            return $allDetails ;
        }                                              
        
    }
            
    
//    public static function getRole()
//    {
//        $auth = Zend_Auth::getInstance();
//        $role = 'guest';
//        
//        if ($auth->hasIdentity()) {
//
//            $user = self::getLoggedUser();
//           
//            //some time we get the
//            //email address as identity.
//            $userRoles = array();
//            if ( is_a( $user, 'Model_User' ) ) {
//                $userRoles = $user->roleTypeIds;
//            }
//
//            if ( in_array(6, $userRoles) ) {
//                $role = 'super-admin';
//            } else if ( in_array(5, $userRoles) ) {
//                $role = 'admin';
//            } else if ( (in_array(1, $userRoles) && in_array(2, $userRoles)) || 
//                        (in_array(1, $userRoles) && in_array(4, $userRoles)) ||
//                        (in_array(2, $userRoles) && in_array(3, $userRoles)) ||
//                        (in_array(3, $userRoles) && in_array(4, $userRoles)) ) {
//                $role = 'publisher_advertiser';
//            } else if (in_array(1, $userRoles)||in_array(3, $userRoles)) {
//                //publisher or publisher manager
//                $role = 'publisher';
//            } else if (in_array(2, $userRoles)||in_array(4, $userRoles)) {
//                //advertiser or advertiser manager
//                $role = 'advertiser';
//            }
//            
//            /*
//              if($user->roleTypeId == 1 || $user->roleTypeId == 2) {
//              return 'publisher';
//              } else if ($user->roleTypeId == 3) {
//              return 'advertiser';
//              } else if($user->roleTypeId == 4) {
//              return 'publisher_advertiser';
//              } else if($user->roleTypeId == 5) {
//              return 'admin';
//              } else {
//              return 'guest';
//              }
//             */
//        }
//        
//        return $role;
//    }
//     
//    public static function isLogged()
//    {
//        $auth = Zend_Auth::getInstance();
//        return $auth->hasIdentity();
//    }
//
//    public static function getUserId() 
//    {
//        $userId = null;
//        $auth = Zend_Auth::getInstance();
//        if (is_a($auth, 'Zend_Auth')) {
//            $user = $auth->getIdentity();
//            if (is_a($user, 'Model_User')) {
//                $userId = $user->id;
//            }
//        }
//        return $userId;
//    }
//    
//    /**
//     * @return Model_User
//     */
//    public static function getLoggedUser()
//    {
//        $auth = Zend_Auth::getInstance();
//        return $auth->getIdentity();
//    }
//	
//    /**
//     * @return bool
//     */
//    public static function isAdmin() {
//        return in_array(self::getRole(), array('admin', 'super-admin'));
//    }
//  
}