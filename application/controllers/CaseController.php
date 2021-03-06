<?php

class CaseController extends Zend_Controller_Action {

    public function init() { }
    
    public function indexAction() {    

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

        $request = $this->getRequest();
       
        $registerForm    			= new Application_Form_CaseRegister( array( 'formType' => Model_Cases::CASE_REGISTER ) );
        $this->view->registerForm 	= $registerForm;                    
        $isLawyerCreated = false;
       
        if( $request->isPost() && $registerForm->isValid( $request->getPost() ) ) {
        	$data = $request->getPost();
        	$case = new Model_Cases();

            if( 0 <> strlen( $data['closing_date'] ) ) {
                $data['closed_by']      = App_User::get('id');;
            }else {
                $data['closed_by']      = NULL;
                $data['closing_date']   = NULL;    
            }
        	
            $case->save( $data );                
            $registerForm->reset(); 
            $this->view->success = "Case added for Lawyer[" . $data['lawyer_id']. "] and Client[" . $data['client_id'] . "].";
            $this->_redirect( $this->view->baseUrl() . '/case/' );
        }
    }
    
    public function editAction() {

        $request = $this->getRequest();

        $registerForm = $this->loadCase( $id = $request->getParam( 'id' ) );
        
        $registerForm->submit->setLabel('Update');
        $this->view->registerForm  = $registerForm; 
        
        if( $request->isPost() && $registerForm->isValid( $request->getPost() ) ) {
            $data = $request->getPost();
            $data['id'] = $id;
            $case = new Model_Cases();

            if( 0 <> strlen( $data['closing_date'] ) ) {
                $data['closed_by']      = App_User::get('id');
            }else {
                $data['closed_by']      = NULL;
                $data['closing_date']   = NULL;    
            }
            
            $case->update( $data );                
            $registerForm->reset(); 
            $this->view->success = "Case updated successfully.";
            $this->_redirect( $this->view->baseUrl() . '/case/' );
        }
    }      
    
    public function historyAction() {
        $request = $this->getRequest();
        $registerForm = $this->loadCase( $case_id = $request->getParam( 'case_id' ) );

        $this->view->registerForm  = $registerForm; 
        
        if( $request->isPost() && $registerForm->isValid( $request->getPost() ) ) {
            $data = $request->getPost();
            $data['case_id'] = $case_id ;
            $caseHistory = new Model_CaseHistory();

            $caseHistory->save( $data );                
            $registerForm->reset(); 
            $this->view->success = "Case detail added successfully for case id[" . $case_id. "]";
        }
    }
    
    public function documentAction() {
        $request = $this->getRequest();
        $registerForm = $this->loadCase( $case_id = $request->getParam( 'case_id' ) );
        $this->view->registerForm  = $registerForm;
                                             
        $caseDocuments = new Model_CaseDocuments();
        $caseDir = "/documents/".$case_id ;
                        
        if( $request->isPost() && $registerForm->isValid( $request->getPost() ) ) {
            $data = $request->getPost();                                    
            $data['case_id'] = $case_id ;
        
            if ($_FILES['document']['error'] > 0) {                
                $this->view->error = "Some thing is Wrong".$_FILES["document"]["error"]; 
                $validFile = false;
            } else {
                $validFile = true;
                $data['path'] = $_FILES['document']['name'];
                $data['uploaded_by'] = App_User::get('id');
                $data['uploaded_on'] = date('Y-m-d');
            }
                       
            $isUpload = false;
            if( $validFile  ){
                $dir        = PUBLIC_PATH."/documents/".$case_id ; 
                $docs       = $dir."/".$_FILES["document"]["name"];
                if( !file_exists( $docs ) ){
                    if( !is_dir( $dir )){
                        mkdir( $dir , 0777 );    
                    }
                                                                                                                                                                                                                                                                                                                                                                          
                    if( move_uploaded_file($_FILES["document"]["tmp_name"], $docs ) ){
                            $isUpload = true;                            
                    }else{
                            $isUpload = false;                            
                    }                    
                                                                              
                } else {                     
                    $this->view->error = $_FILES["document"]["name"]." file Already Exists" ;
                }                                            
            }
                                                    
            if( $isUpload ){
                $caseDocuments->save( $data );
                $this->view->success = "Case document uploaded successfully for case id[" . $case_id. "]";
            }
            $registerForm->reset(); 
        }               
        $docListing = $caseDocuments->getCaseDocumentsList( $case_id );       
        $this->view->caseListing = $docListing ;    
        $this->view->caseDir     = $caseDir ;    
    }

    public function budgetAction() {
        $request = $this->getRequest();
        $registerForm = $this->loadCase( $case_id = $request->getParam( 'case_id' ) );
       
        $cases  = new Model_Cases();
        $getCaseDetails  = $cases->getCases( array( 'id' => (int) $case_id) );
                        
        $this->view->caseDetails   = $getCaseDetails;
        $this->view->registerForm  = $registerForm; 
            
        if( $request->isPost() || $registerForm->isValid( $request->getPost() ) ) {
            $post = $request->getPost();
            $data = array();
                        
            $data['transaction_type_id'] = $post['transaction_type_id'];
            $data['submission_date']    = $post['submission_date'];
            $data['fees_type_id']       = $post['fees_type_id'];            
            $data['hours_spent']        = $post['hours_spent'];            
            $data['amount']             = $post['amount'];
            $data['transaction_details']= $post['transaction_details'];
            
            $data['case_id']            = $case_id ;
            $data['submitted_by']       = App_User::get('id');           
            //$data['transaction_type_id']   = ( App_User::get('user_type') <> USER_CLIENT ) ? Model_CaseTransactions::TRANSACTION_TYPE_RECEIVABLE : Model_CaseTransactions::TRANSACTION_TYPE_PAYABLE;
            $caseTransaction = new Model_CaseTransactions();
                            
            $caseTransaction->save( $data );                
            $registerForm->reset(); 
            $this->view->success = "Case expense added successfully for case id[" . $case_id . "]";
        }
    }

    private function loadCase( $id ) {

        switch( $this->getParam( 'action' )) {
            case 'edit':
                $intFormType = Model_Cases::CASE_REGISTER;
                break;
            case 'history':
                $intFormType = Model_Cases::CASE_HISTORY;
                break;
            case 'document':
                $intFormType = Model_Cases::CASE_DOCUMENT;
                break;
            case 'budget':
                $intFormType = Model_Cases::CASE_BUDGET;
                break;    
        }

       $case           = new Model_Cases();
       $registerForm   = new Application_Form_CaseRegister( array( 'formType' => $intFormType )  );

       $getCaseDetails = $case->getCases( array( 'id' => (int) $id ) );

       if( !empty($getCaseDetails) && 1 == count( $getCaseDetails ) ) {
            $getCaseDetails = array_pop( $getCaseDetails );
            foreach( $getCaseDetails as $strKey => &$mixValue) {
                if( true == is_integer( stripos( $strKey, 'date' ) ) ) { 
                    $mixValue = ( 0 < strtotime( $mixValue ) ) ? date( 'Y-m-d', strtotime( $mixValue ) ) : NULL;
                }
            }
            $registerForm->populate( $getCaseDetails );
        }else{
            $registerForm = NULL;
            $this->view->error = "Case not found.";
        } 

        return $registerForm;
    }
}