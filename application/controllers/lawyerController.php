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
       $registerForm    = new Application_Form_Register(array('userRoleType' => 'lawyer'));
       $this->view->registerForm = $registerForm;                    
       $isLawyerCreated = false;
       if($request->isPost()){
            if( $registerForm->isValid($request->getPost())) {
                $user->save( $request->getPost() );                
                $registerForm->reset(); 
                $isLawyerCreated = true;
                $this->view->isLawyerCreated = $isLawyerCreated ;
            }                              
       }       
       //user(lawyer) registration form
       $searchForm    = new Application_Form_Search();
       $this->view->searchForm = $searchForm;              
    }
    
    public function editlawyerAction(){          
        $request = $this->getRequest();
        $id = $request->getParam('id');                                                         
        $user    = new Application_Model_UsersMapper();                
        $registerForm    = new Application_Form_Register(array('userRoleType' => 'lawyer'));        
        $getUserDetails = App_User::getUserById( $id );               
        $registerForm->populate($getUserDetails);
        
        $registerForm->submit->setLabel('Update');
        $isUpdated = false;
        $isLawyerUpdated = false;
        if($request->isPost()){
            $formData =  $request->getPost();      
            if( $registerForm->isValid($request->getPost())) {
                  $isUpdated = $user->update($formData,$id );              
                  if( $isUpdated ){
                      $this->_redirect('http://localhost/lawyer');
                      $isLawyerUpdated = true;
                      $this->view->isLawyerUpdated = $isLawyerCreated ;
                  }
                  
            }                                                           
        }
        
        $this->view->registerForm = $registerForm;
        
    }      
    
    public function createAction(){        
        $request = $this->getRequest();
        $form    = new Application_Form_Register();
        $this->view->registerForm = $form;        
    }

}

