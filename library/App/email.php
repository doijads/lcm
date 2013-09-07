<?php

class App_Email
{
  
    public static function send( $recipient, $subject, $message, $headers, $fromEmail='admin@lcm.com', $fromName = 'Admin'){
                
//        echo "Result--->";
//        echo "<pre>";
//        print_r($recipient);
//        echo "</br>";
//        print_r($subject);
//        echo "</br>";
//        print_r($message);
//        echo "</br>";
//        print_r($headers);
//        echo "</br>";
//        print_r($fromEmail);
//        echo "</br>";
//        print_r($fromName);
//        
//        exit;
        $isSend = false;
        if( !empty($recipient) ){
//            if( mail($recipient, $subject, $message,     $fromName) ){
//                $isSend = true;                             
//            }else{
//                $isSend = false;
//            }            
//            
     
            
            return $isSend; 
        }
        
    }
    public static function sendEmailToLawyer($recipient){
        $subject = 'Welcome Subject';
        $headers = "From: LCM>\r\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: text/html; charset=utf-8\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n\r\n";
        
        $fromEmail = "admin@lcm.com";
        $fromName  = "Admin";
                
        $message = "";
        $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
        $message .= "<p>You have been registered with our system.</p>";
        $message .= "</body></html>";         
        $message = rtrim($message);
                
        self::send($recipient,$subject, $message, $headers, $fromEmail, $fromName);        
    }
    
    public static function sendEmailToClient($recipient){
        
        self::send($recipient,$subject, $message, $headers, $fromEmail, $fromName);
    }
    
    public static function sendEmailToAdmin($recipient){
        
        self::send($recipient,$subject, $message, $headers, $fromEmail, $fromName);
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