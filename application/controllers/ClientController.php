<?php

class ClientController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

     public function indexAction(){        
       $request = $this->getRequest();
       $user    = new Application_Model_UsersMapper();
       
       //user(lawyer) registration form       
       $registerForm    = new Application_Form_Register(array('userRoleType' => 'client'));
       $this->view->registerForm = $registerForm;                    
       $isClientCreated = false;
       if($request->isPost()){
            if( $registerForm->isValid($request->getPost())) {
                $user->save( $request->getPost() );                
                $registerForm->reset(); 
                $isClientCreated = true;
                $this->view->isClientCreated = $isClientCreated ;
            }                              
       }       
       //user(lawyer) registration form
       $searchForm    = new Application_Form_Search();
       $this->view->searchForm = $searchForm;              
    } 

    
    public function indexAction(){        
       $request = $this->getRequest();
       $form    = new Application_Form_Register();
       $this->view->registerForm = 'this is tet';                       
    }

    public function createAction(){        
        $request = $this->getRequest();
        //$form    = new Application_Form_Register();
        //$this->view->registerForm = $form;        
    }

}

