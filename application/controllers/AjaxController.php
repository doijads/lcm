<?php

class AjaxController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function getUsersAction(){    
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
   
    
    public function deleteUserAction(){
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
    
    public function displayClientModalAction(){
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
            'data' => $this->view->partial('_partials/display-client-details.phtml', array('data' => $data)), 
            'success' => true            
        );
        
        echo json_encode($result);
        exit(0);
    }
    
    public function displayLawyerModalAction(){
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
            'data' => $this->view->partial('_partials/display-lawyer-details.phtml', array('data' => $data)), 
            'success' => true            
        );
        
        echo json_encode($result);
        exit(0);
    }
    
    
}