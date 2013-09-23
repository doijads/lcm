<?php

class CaseController extends Zend_Controller_Action
{
    
    public function init()
    {
        ini_alter('date.timezone','Asia/Calcutta');
        /* Initialize action controller here */
        $this->view->headScript()->appendFile('/js/user.js');
        $user = new Model_Cases();
        $refUser = ( App_User::get('user_type') == USER_LAWYER ) ? 'lawyer_id' : 'client_id';
        $param = ( true == in_array( App_User::get('user_type'), array( USER_LAWYER, USER_CLIENT ) ) ) ? array( $refUser => App_User::get('id') ) : array();
        $this->view->dataList = $user->getCases( $param );

        $user = new Model_Users();
        $fetchRow = $user->fetchUsersByUserTypes( array( USER_LAWYER, USER_CLIENT ) );
        
        $arrUserRekeyedByUserType = array();
        
        if( true == is_array( $fetchRow ) ) {
            foreach( $fetchRow as $arrUser ) {
                $arrUserRekeyedByUserType[$arrUser['user_type']][$arrUser['id']] = $arrUser['name'];
            }
            $this->view->userList = $arrUserRekeyedByUserType;
        }
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
                if( false == isset( $data['closing_date'] ) ) {
                    $data['closed_by']      = App_User::get('id');;
                }else {
                    $data['closed_by']      = NULL;
                    $data['closing_date']   = NULL;    
                }
            	
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