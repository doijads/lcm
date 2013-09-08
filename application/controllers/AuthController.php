<?php

class AuthController extends Zend_Controller_Action {

    public function init() {
        parent::init();
    }

    public function loginAction() {
        
        $request = $this->getRequest();
        
        $email = $request->getParam('email', '');
        $password = $request->getParam('password', '');

        if ($email == '' || $password == '') {
            $this->_helper->flashMessenger->addMessage('Missing username or password');
            $this->_redirect('/');
        }

        $isLoggedIn = App_Auth::login($email, $password);

        if ($isLoggedIn == App_User::ACTIVE) {
            $url = '/';
            $this->_redirect($url);
        } else if (in_array($isLoggedIn, array(App_User::SUSPENDED, App_User::DELETED, App_User::NO_USER_ACCESS_RULES))) {
            // set temporary message
            $this->_helper->flashMessenger->addMessage('You are not authorized user to logged in.');
            $this->_redirect('/');
        } else {
            // set temporary message
            $this->_helper->flashMessenger->addMessage('Username or password does not match');
            $this->_redirect('/');
        }
    }

    public function logoutAction() {
        App_Auth::logout();
        $this->_redirect('/');
    }

}