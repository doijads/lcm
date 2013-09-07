<?php

class CaseController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
         $this->view->headScript()->appendFile('/js/user.js');
    }

    public function indexAction(){        
       $request = $this->getRequest();
       $user    = new Application_Model_CaseMapper();
       
       //user(lawyer) registration form       
       $registerForm    = new Application_Form_Register( array( 'strFormType' => 'case' ) );
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
    }
    
    public function editlawyerAction(){          
        $request = $this->getRequest();
        $id = $request->getParams('id');
        $user    = new Application_Model_CaseMapper();
        $registerForm    = new Application_Form_Register( array( 'strFormType' => 'case' ) );
        $registerForm->submit->setLabel('Edit');
        if($request->isPost()){
            $formData =  $request->getPost();      
            if( $registerForm->isValid($request->getPost())) {
                
            }
        }
    }      
    
    public function createAction(){        
        $request = $this->getRequest();
        $form    = new Application_Form_Register();
        $this->view->registerForm = $form;        
    }
}