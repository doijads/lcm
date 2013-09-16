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
       
       $registerForm    			= new Application_Form_CaseRegister();
       $this->view->registerForm 	= $registerForm;                    
       $isLawyerCreated = false;
       
       if( $request->isPost() ){
            if( $registerForm->isValid($request->getPost() ) ) {
            	$data = $request->getPost();
            	$case = new Model_Cases();
            	$data['closed_by'] 		= NULL;
            	$data['closing_date']	= NULL;
                $case->save( $data );                
                $registerForm->reset(); 
                $isLawyerCreated = true;
                $this->view->isLawyerCreated = $isLawyerCreated ;
                $this->view->success = "Case added for Lawyer[" . $data['lawyer_id']. "] and Client[" . $data['client_id'] . "].";
            }                              
       }
    }
    
    public function editCaseAction(){          
        $request = $this->getRequest();
        $id = $request->getParams('id');
        $case    		= new Model_Cases();
        $registerForm   = new Application_Form_CaseRegister();
        $registerForm->submit->setLabel('Edit');
        
        if($request->isPost()){
            $formData =  $request->getPost();      
            if( $registerForm->isValid($request->getPost())) {
                
            }
        }
    }      
    
    public function createAction(){        
        $request = $this->getRequest();
        $form    = new Application_Form_CaseRegister();
        $this->view->registerForm = $form;      
    }
}