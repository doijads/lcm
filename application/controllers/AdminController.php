<?php

class AdminController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
         $this->view->headScript()->appendFile('/js/user.js');
    }

    public function indexAction(){                 
//       $request = $this->getRequest();
//       
//       //user(lawyer) registration form       
//       $registerForm    = new Application_Form_Register(array( 'userRoleType' => 'admin' ) );
//       $this->view->registerForm = $registerForm;                    
//       //$isLawyerCreated = false;                    
//       if($request->isPost()){
//            if( $registerForm->isValid($request->getPost())) {
//                $data = $request->getPost();
//                $user = new Model_Users($data); 
//                //set user role as 2 for lawyer
//                $user->user_type = USER_LAWYER ;
//                if( isset($data['password']) ){
//                    $user->password  = md5($data['password']);
//                }
//                                                
//                $user->save();
//                if ($user->id) {
//                    $data['user_id'] = $user->id;
//                    $userDetails = new Model_UserDetails($data);                                                           
//                    $userDetails->save();               
//                    $recipient = array('email' => $data['email'],
//                                       'password' => $data['password']
//                                        );
//                    
//                    /*Uncomment this when set up the mail server */
//                    //App_Email::sendEmailToLawyer($recipient);                    
//                }
//                
//                $registerForm->reset(); 
//                $this->view->success = "New Lawyer has been added";                
//            }                              
//       }  
//       //lawyer list
//       $lawyer = new Model_Users();      
//       $lawyerList = $lawyer->fetchUsersByUserTypes( array(USER_LAWYER) );             
//       if( !empty($lawyerList) ){           
//           $this->view->lawyerList = $lawyerList ;
//       }
//              
//       //user(lawyer) registration form
//       $searchForm    = new Application_Form_Search();
//       //Redirect message from edit action..and display on this action
//       $messages = $this->_helper->FlashMessenger->getMessages('editlawyer');
//       if(is_array($messages) && !empty($messages)){
//           $this->view->success = $messages[0] ;
//       }
//       $this->view->searchForm = $searchForm;              
    }
    
    public function editAdminAction(){             
        $baseUrl = $this->view->baseUrl();
        $frontUrl = $baseUrl.'/admin' ;                
               
        $request = $this->getRequest();
        $id = $request->getParam('id');                                                         
        //$user    = new Application_Model_UsersMapper(); 
        
        $user = new Model_Users();
        
        $registerForm    = new Application_Form_Register(array( 'userRoleType' => 'admin'));        
        $registerForm->getElement('email')->clearValidators();                        
        //$getUserDetails = App_User::getUserById( $id );                                       
        $getUserDetails = $user->getUsersById( $id );
        
        if( !empty($getUserDetails) ){                                    
            $registerForm->populate($getUserDetails);
        }
        $registerForm->submit->setLabel('Update');
        $isUpdated = false;        
        if($request->isPost()){
            $formData =  $request->getPost();                            
            if( $registerForm->isValid($request->getPost())) {                                                                 
                  //update user
                  $formData['id'] = $id;                                                     
                  $formData['password'] = md5($formData['password']);
                  $user->update($formData);
                  
                  //update user details
                  $formData['user_id'] = $id;
                  $userDetails = new Model_UserDetails();                                                                  
                  $userDetails->update($formData);   
                  
                  $isUpdated = true;
                  
                  if( $isUpdated ){              
                      $this->_helper->FlashMessenger->addMessage("Lawyer has been updated successfully", 'editlawyer');
                      $this->_redirect($frontUrl);                      
                  }
                  
            }                                                           
        }
        
        $this->view->registerForm = $registerForm;
      }      
    
  
}