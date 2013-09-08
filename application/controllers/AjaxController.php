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
        
        $user = new Model_Users();
        //$getLawyer = new Application_Model_UsersMapper();       
        $result['success'] = false;
        //index string should be same as table column(
        if (!empty($post)) {
            $params = array(
                'name'  => $post['user_name'],
                'email' => $post['user_email']
            );
            $fetchRow = $user->getUsers( $params );
            $result['data'] = $fetchRow;
        }
                                                     
        if( $result['data'] ){
            $result['success'] = true;
            echo json_encode($result);
        }
        exit(0);       
        //$this->asJSON();        
    }
   
    
    public function deletelawyersAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $request = $this->getRequest();
        $post = $request->getParams();
                
        $user = new Model_Users();
        $user->id =  $post['id'];
        
        $result['success'] = false;
        if( empty( $post['id'] )){            
            return;
        }     
         
        if( $user->deleteUser() ){
            $result['success'] = true ;
            echo json_encode($result);
        }
        exit(0);
        
    }

    public function contactAction(){
        
        
    }
            
    

}

