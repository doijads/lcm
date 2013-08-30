<?php

class ClientController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
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

