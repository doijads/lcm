<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction(){
       $request = $this->getRequest();
        
       $loginForm = null;
       $user      = null;
       if (!App_Auth::isLogged()) {
           $loginForm = new Application_Form_Login();
       }  else { 
            $user = App_Auth::getUser()    ;
            $this->view->loggedInUser   = $user->name;
            $this->view->loggedIdUserId = App_User::get('id');
       }                                                                          
       $this->view->loginForm = $loginForm;                       
    }
    
    
    public function registerAction(){        
        $request = $this->getRequest();
        $form    = new Application_Form_Register();
        $this->view->registerForm = $form;        
    }
    public function contactAction(){
        
        
    }

}

