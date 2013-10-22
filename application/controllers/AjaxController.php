<?php

class AjaxController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function getUsersAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $request = $this->getRequest();
        $post = $request->getParams();
        
        $user = new Model_Users();
        //$getLawyer = new Application_Model_UsersMapper();       
        $result['success'] = false;
        //index string should be same as table column(
        if (!empty($post)) {
            $params = array(
                'name'  => $post['user_name'],
                'email' => $post['user_email']
            );
            
            $roleType = $post['user_type'];            
            $fetchRow = $user->getUsers( $params ,$roleType );
            $result['data'] = $fetchRow;
        }
                                                     
        if( $result['data'] ){
            $result['success'] = true;            
        }else {
            $result['success'] = false;            
        }
        
        echo json_encode($result);    
        exit(0);       
        //$this->asJSON();        
    }
    
    public function deleteUserAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $request = $this->getRequest();
        $lowyerId = $request->getParam('id');
        
        $result['success'] = false;
        
        if (!$request->isPost() || empty($lowyerId)) {
            echo json_encode($result);
            exit(0);
        }

        //delete lowyer.
        $user = new Model_Users();
        $user->id =  $lowyerId;
        //$result['success'] =$user->deleteUser();        
        $result['success'] = true;        
        echo json_encode($result);
        exit(0);
    }
    
    public function displayClientModalAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $clientId = $this->getRequest()->getParam('id');
         
        //$this->view->action( 'build-client-view', 'client', 'default', $data),
        $user = new Model_Users();        
        
        if(isset( $clientId )) {        
            $userDetails = $user->getUsersById( $clientId );           
        }
        
        $data = array(
            'users' => $userDetails            
            );
                 
        $result = array(
            'data'    => $this->view->partial('_partials/display-client-details.phtml', array('data' => $data)), 
            'success' => true            
        );
        
        echo json_encode($result);
        exit(0);
    }
    
    public function displayLawyerModalAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $lawyerId = $this->getRequest()->getParam('id');
         
        //$this->view->action( 'build-client-view', 'client', 'default', $data),
        $user = new Model_Users();        
        
        if(isset( $lawyerId )) {        
            $userDetails = $user->getUsersById( $lawyerId );           
        }
        
        $data = array(
            'users' => $userDetails            
            );
                 
        $result = array(
            'data'    => $this->view->partial('_partials/display-lawyer-details.phtml', array('data' => $data)), 
            'success' => true            
        );
        
        echo json_encode($result);
        exit(0);
    }
    
    public function displayCaseModalAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $request  = $this->getRequest();
        $caseId   = $request->getParam('id');

        $case = new Model_Cases();        
        
        if( isset( $caseId ) ) {        
            $getCaseDetails = $case->getCaseDetailsById( (int) $caseId );
        }
          
        if( empty( $getCaseDetails ) ) {
            $this->view->error = "Case not found.";
            return;
        }else {
            foreach( $getCaseDetails as $arrCaseDetail ) {
                $case          = array(
                                        'date_of_allotment' => $arrCaseDetail['date_of_allotment'],
                                        'due_date'          => $arrCaseDetail['due_date'],
                                        'closed_by'         => $arrCaseDetail['closed_by'],
                                        'closing_date'      => $arrCaseDetail['closing_date'] );

                $caseHistory[] = array( 
                                        'hearing_date'      => $arrCaseDetail['hearing_date'],
                                        'next_hearing_date' => $arrCaseDetail['next_hearing_date'],
                                        'judge_name'        => $arrCaseDetail['judge_name'],
                                        'content'           => $arrCaseDetail['content'] );
 
                $caseBudget[] = array( 
                                        'transaction_type_id'  => $arrCaseDetail['transaction_type_id'],
                                        'amount'               => $arrCaseDetail['amount'],
                                        'submission_date'      => $arrCaseDetail['submission_date'],
                                        'submitted_by'         => $arrCaseDetail['submitted_by'],
                                        'approved_by'          => $arrCaseDetail['approved_by'],
                                        'transaction_details'  => $arrCaseDetail['transaction_details'] ); 

                $caseDocument[] = array( 
                                        'name'         => $arrCaseDetail['case_doc_name'],
                                        'path'         => $arrCaseDetail['case_doc_path'],
                                        'details'      => $arrCaseDetail['case_doc_details'],
                                        'uploaded_by'  => $arrCaseDetail['case_doc_uploaded_by'],
                                        'uploaded_on'  => $arrCaseDetail['case_doc_uploaded_on'] );
                $arrCaseDetails = array( 
                                         'case'         => $case,
                                         'history'      => $caseHistory,
                                         'budget'       => $caseBudget,
                                         'documents'    => $caseDocument );
            }
        }
        
        $user     = new Model_Users();
        $fetchRow = $user->fetchUsersByUserTypes( array( USER_LAWYER, USER_CLIENT, USER_ADMIN, USER_ADMINISTRATOR ) );
        
        $arrUserRekeyedByUserType = array();
        
        if( true == is_array( $fetchRow ) ) {
            foreach( $fetchRow as $arrUser ) {
                $arrUserRekeyedByUserType[$arrUser['user_type']][$arrUser['id']] = $arrUser['name'];
            }
        }       

        $result = array(
            'data'    => $this->view->partial('_partials/display-case-detail.phtml', array('data' => $arrCaseDetails, 'users' => $arrUserRekeyedByUserType ) ), 
            'success' => true            
        );
                    
        echo json_encode($result);
        exit(0);
    }
}