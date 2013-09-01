<?php

class AjaxController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function getlawyersAction(){    
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $request = $this->getRequest();
        $post = $request->getParams();
        $getLawyer = new Application_Model_UsersMapper();       
        $result['success'] = false;
        //index string should be same as table column(
        if (!empty($post)) {
            $params = array(
                'name'  => $post['user_name'],
                'email' => $post['user_email']
            );
            $result['data'] = $getLawyer->getUsers( $params );
        }
                       
        if( $result['data'] ){
            $result['success'] = true;
            echo json_encode($result);
        }
        exit(0);       
        //$this->asJSON();        
    }
    
    public function editlawyersAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $request = $this->getRequest();
        $post = $request->getParams();
        $getLawyer = new Application_Model_UsersMapper();  
        $getLawyerDetails = new Application_Model_UsersdetailMapper();  
        $result['success'] = false;
        if( !empty($post['id'])){
            $result['success'] = true;  
            $lawyer       = $getLawyer->find( $post['id'] );
            $lawyerDetail =  $getLawyerDetails->find( $post['id'] );
            //merge the data from user and user details table
            $allDetails[]   = $lawyer[0] + $lawyerDetail[0];
            $result['data']  = $allDetails ;
        }                                
        //$registerForm    = new Application_Form_Register(array('userRoleType' => 'lawyer'));
        //$registerForm->populate($userAll);               
        echo json_encode($result);                         
        exit(0);        
    }
    
    public function deletelawyersAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $request = $this->getRequest();
        $post = $request->getParams();
                
        $userMapperObj = new Application_Model_UsersMapper();       
        $result['success'] = false;
        if( empty( $post['id'] )){            
            return;
        }     
        if( $userMapperObj->deleteUsers( $post['id'] ) ){
            $result['success'] = true ;
            echo json_encode($result);
        }
        exit(0);
        
    }

    public function contactAction(){
        
        
    }
            
    

}

