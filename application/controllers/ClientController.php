<?php

class ClientController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

     public function indexAction(){        
       $request = $this->getRequest();       
       //user(lawyer) registration form       
       $registerForm    = new Application_Form_Register(array( 'userRoleType' => 'client'));
       $this->view->registerForm = $registerForm;                                         
       if($request->isPost()){
            if( $registerForm->isValid($request->getPost())) {
                $data = $request->getPost();
                $user = new Model_Users($data);  
                //set user role as client (id = 3)
                $user->user_type = USER_CLIENT ;
                $user->save();
                if ($user->id) {
                    $data['user_id'] = $user->id;
                    $userDetails = new Model_UserDetails($data);
                    $userDetails->save();               
                    $recipient = array('email' => $data['email'],
                                       'password' => $data['password']
                                        );
                    
                    /*Uncomment this when set up the mail server */
                    //App_Email::sendEmailToClient($recipient);                    
                }               
                $registerForm->reset(); 
                $this->view->success = "New Client has been added";                
            }                              
       }         
       
       //lawyer list
       $client = new Model_Users();      
       $clientList = $client->fetchUsersByUserTypes( array(USER_CLIENT) );             
       if( !empty($clientList) ){           
           $this->view->clientList = $clientList ;
       }
       
       //user(lawyer) registration form
       $searchForm    = new Application_Form_Search();
       //Redirect message from edit action..and display on this action
       $messages = $this->_helper->FlashMessenger->getMessages('editclient');
       print_r($messages);
       if(is_array($messages) && !empty($messages)){
           $this->view->success = $messages[0] ;
       }
       $this->view->searchForm = $searchForm;
       
       //$viewDetails = $this->_viewClient();
       //$this->view->viewDetails = $viewDetails;
    } 
    
     public function editClientAction(){             
        $baseUrl = $this->view->baseUrl();
        $frontUrl = $baseUrl.'/client' ;                
               
        $request = $this->getRequest();
        $id = $request->getParam('id');                                                         
        //$user    = new Application_Model_UsersMapper(); 
        
        $user = new Model_Users();
        
        $registerForm    = new Application_Form_Register(array( 'userRoleType' => 'client'));
        
        $registerForm->getElement('email')->clearValidators(); 
                       
        //$getUserDetails = App_User::getUserById( $id );                                   
        $getUserDetails = $user->getUsersById( $id );
        print_r($getUserDetails);                          
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
                  $user->update($formData);
                  
                  //update user details
                  $formData['user_id'] = $id;
                  $userDetails = new Model_UserDetails();
                  $userDetails->update($formData);
                  
                  $isUpdated = true;
                  if( $isUpdated ){              
                      $this->_helper->FlashMessenger->addMessage("Client has been updated successfully", 'editclient');
                      $this->_redirect($frontUrl);                      
                  }
                  
            }                                                           
        }
        
        $this->view->registerForm = $registerForm;
      }      


    public function createAction(){        
        $request = $this->getRequest();
        //$form    = new Application_Form_Register();
        //$this->view->registerForm = $form;        
    }

}

