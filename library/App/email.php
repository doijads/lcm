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
            if( mail($recipient, $subject, $message,     $fromName) ){
                $isSend = true;                             
            }else{
                $isSend = false;
            }            
            
     
            
            return $isSend; 
        }
        
    }
    public static function sendEmailToLawyer($recipient){
        $subject = 'Welcome Lawyer';
        $headers = "From: LCM>\r\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: text/html; charset=utf-8\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n\r\n";
        
        $fromEmail = "admin@lcm.com";
        $fromName  = "Admin";
                
        $message = "Welcome..";
        $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
        $message .= "<p>You have been registered with our system.</p><n/>";
        $message .= "<p>You can login to system with following credentails.</p><n/>";
        $message .= "<p><b>UserName : </b>".$recipient['email']."</p><n/>";
        $message .= "<p><b>Password : </b>".$recipient['password']."</p><n/>";
        $message .= "</body></html>";         
        $message = rtrim($message);
                
        self::send($recipient['email'],$subject, $message, $headers, $fromEmail, $fromName);        
    }
    
    public static function sendEmailToClient($recipient){        
       $subject = 'Welcome Client';
        $headers = "From: LCM>\r\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: text/html; charset=utf-8\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n\r\n";
        
        $fromEmail = "admin@lcm.com";
        $fromName  = "Admin";
                
        $message = "Welcome..";
        $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
        $message .= "<p>You have been registered with our system.</p><n/>";
        $message .= "<p>You can login to system with following credentails.</p><n/>";
        $message .= "<p><b>UserName : </b>".$recipient['email']."</p><n/>";
        $message .= "<p><b>Password : </b>".$recipient['password']."</p><n/>";
        $message .= "</body></html>";         
        $message = rtrim($message);
                
        self::send($recipient['email'],$subject, $message, $headers, $fromEmail, $fromName);        
    }
    
    public static function sendEmailToAdmin($recipient){
        
        self::send($recipient,$subject, $message, $headers, $fromEmail, $fromName);
    }
                 
}