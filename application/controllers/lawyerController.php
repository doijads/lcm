<?php

class lawyerController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
         $this->view->headScript()->appendFile('/js/user.js');
    }

    public function indexAction(){                 
       $request = $this->getRequest();
       $user    = new Application_Model_UsersMapper();                          
       //user(lawyer) registration form       
       $registerForm    = new Application_Form_Register(array( 'strFormType' => 'lawyer', 'userRoleType' => 'lawyer'));
       $this->view->registerForm = $registerForm;                    
       $isLawyerCreated = false;                    
       if($request->isPost()){
            if( $registerForm->isValid($request->getPost())) {
                $user->save( $request->getPost() );                
                $registerForm->reset(); 
                $this->view->success = "New Lawyer has been created";                
            }                              
       }       
       //user(lawyer) registration form
       $searchForm    = new Application_Form_Search();
       //Redirect message from edit action..and display on this action
       $messages = $this->_helper->FlashMessenger->getMessages('editlawyer');
       if(is_array($messages) && !empty($messages)){
           $this->view->success = $messages[0] ;
       }
       $this->view->searchForm = $searchForm;              
    }
    
    public function editlawyerAction(){      
        
        $baseUrl = $this->view->baseUrl();
        $frontUrl = 'http://'.$baseUrl.'/lawyer' ;                
                
        $request = $this->getRequest();
<<<<<<< HEAD
        $id = $request->getParams('id');
        $user    = new Application_Model_UsersMapper();
        $registerForm    = new Application_Form_Register(array( 'strFormType' => 'lawyer', 'userRoleType' => 'lawyer'));
        $registerForm->submit->setLabel('Edit');
        if($request->isPost()){
            $formData =  $request->getPost();      
            if( $registerForm->isValid($request->getPost())) {
                
                
            }
        }
=======
        $id = $request->getParam('id');                                                         
        $user    = new Application_Model_UsersMapper();                
        $registerForm    = new Application_Form_Register(array('userRoleType' => 'lawyer'));              
        $getUserDetails = App_User::getUserById( $id );               
        if( !empty($getUserDetails) ){
            $registerForm->populate($getUserDetails);
        }
        $registerForm->submit->setLabel('Update');
        $isUpdated = false;
        $isLawyerUpdated = false;
        if($request->isPost()){
            $formData =  $request->getPost();      
            if( $registerForm->isValid($request->getPost())) {
                  $isUpdated = $user->update($formData,$id );                                
                  if( $isUpdated ){              
                      $this->_helper->FlashMessenger->addMessage("Lawyer has been updated successfully", 'editlawyer');
                      $this->_redirect($frontUrl);                      
                  }
                  
            }                                                           
        }
        
        $this->view->registerForm = $registerForm;
        
>>>>>>> 1c17792a3e55c283199b221e0de3d7c427fd825b
    }      
    
    public function createAction(){        
        $request = $this->getRequest();
        $form    = new Application_Form_Register();
        $this->view->registerForm = $form;        
    }

}

