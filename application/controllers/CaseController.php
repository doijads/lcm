<?php

class CaseController extends Zend_Controller_Action {
    
    public function init() {

        ini_alter('date.timezone','Asia/Calcutta');

        $case = new Model_Cases();
        $refUser = ( App_User::get('user_type') == USER_LAWYER ) ? 'lawyer_id' : 'client_id';
        $param = ( true == in_array( App_User::get('user_type'), array( USER_LAWYER, USER_CLIENT ) ) ) ? array( $refUser => App_User::get('id') ) : array();
        $this->view->dataList = $case->getCases( $param );

        $user = new Model_Users();
        $fetchRow = $user->fetchUsersByUserTypes( array( USER_LAWYER, USER_CLIENT, USER_ADMIN, USER_ADMINISTRATOR ) );
        
        $arrUserRekeyedByUserType = array();
        
        if( true == is_array( $fetchRow ) ) {
            foreach( $fetchRow as $arrUser ) {
                $arrUserRekeyedByUserType[$arrUser['user_type']][$arrUser['id']] = $arrUser['name'];
            }
            $this->view->userList = $arrUserRekeyedByUserType;
        }
    }

    public function indexAction() {    

       $request = $this->getRequest();
       
       $registerForm    			= new Application_Form_CaseRegister();
       $this->view->registerForm 	= $registerForm;                    
       $isLawyerCreated = false;
       
       if( $request->isPost() && $registerForm->isValid( $request->getPost() ) ) {
        	$data = $request->getPost();
        	$case = new Model_Cases();

            if( false == is_null( $data['closing_date'] ) ) {
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
    
    public function editCaseAction() {

        $request = $this->getRequest();
        $id = $request->getParam( 'id' );

        $case    		= new Model_Cases();
        $registerForm   = new Application_Form_CaseRegister();

        $getCaseDetails = $case->getCases( array( 'id' => (int) $id ) );

        if( !empty($getCaseDetails) && 1 == count( $getCaseDetails ) ){
            $getCaseDetails = array_pop( $getCaseDetails );
            $registerForm->populate($getCaseDetails);
        }else{
            $this->view->error = "Case not found.";   
        }
        
        $registerForm->submit->setLabel('Update');
        $this->view->registerForm  = $registerForm; 
        
        if($request->isPost()){
            $formData =  $request->getPost();      
            if( $registerForm->isValid($request->getPost())) {
                
            }
        }
    }      
    
    public function createAction() {   

        $request = $this->getRequest();
        $form    = new Application_Form_CaseRegister();
        $this->view->registerForm = $form;      
    }
}