<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction(){        
       $request = $this->getRequest();
       $form    = new Application_Form_Login();
       $this->view->loginForm = $form;                       
    }
    public function registerAction(){        
        $request = $this->getRequest();
        $form    = new Application_Form_Register();
        $this->view->registerForm = $form;        
    }
    public function contactAction(){
        
        
    }
            
    

}

