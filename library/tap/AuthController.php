<?php

class AuthController extends Zend_Controller_Action
{

    public function init()
    {
        parent::init();
    }
    
    /*
     * Handles SSO integration with Phunware Maas
     * A request should be formatted as such:
     *
     * https://ads.tapit.com/auth/sso/{base64_encoded_request}
     *
     * The {base64_encoded_request) is a combination of:
     *
     * - User Signature Token
     * - User API Token
     * - GMT date YmdH
     *
     * A hash will be generated using ripeemd160 and the entire request
     * will be concatenated:
     *
     * {User API Token}:{Hash}
     *
     * Then that result is base64_encoded
     */    
    
    public function ssoAction()
    {
      // disable layout
      $this->_helper->layout()->disableLayout(); 
      $this->_helper->viewRenderer->setNoRender(true);
    
      $request = $this->getRequest();
      $request->getParams();
      
      /** 
      
      For Test Request Generation
      ----------------------------
      
      $this->loginUrl = 'http://choover.tapit.com/auth/sso';
      
      $tapitSignatureToken = '3040ef33cddcadd73469b964a46f32e9123';
      $tapitApiToken = '3040ef33cddcadd73469b964a46f32e9';      
      
      $comp = $tapitSignatureToken . $tapitApiToken . gmdate('YmdH');
      $hash = hash('ripemd160', $comp);
       
      $query = base64_encode(implode('', array($tapitApiToken.':'.$hash)));
      
      $url = $this->loginUrl. '/' . $query;
      
      echo $url;exit;
      
      */
      
      // get last segment which should be auth request
      $authRequest = base64_decode(array_pop(explode('/', $request->getRequestUri())));
      
      if(strpos($authRequest, ':') > -1)
      {
         list($apiToken, $requestHash) = explode(':', $authRequest);      
      
         $user_model = new Model_User();
         $user = $user_model->findUserByApiToken($apiToken);
         
         if($user)
         {          
            // re-generate hash and compare
            $comp = $user->signatureToken . $user->apiToken . gmdate('YmdH');
            $hash = hash('ripemd160', $comp);     
            
            // validation successful
            if($hash === $requestHash)
            {              
               // clear any pre-existing authorizations
               $auth = Zend_Auth::getInstance();
               $auth->getStorage()->clear();
                
               // authorize               
               App_User::setLogged($user);            
              
            } 
            else 
            {
              throw new Zend_Controller_Dispatcher_Exception('SSO request malformed.  Cannot authenticate.'); 
            }   
         } 
         else
         {
            throw new Zend_Controller_Dispatcher_Exception('SSO user not found.  Cannot authenticate.'); 
         }

      
      } else
      {
         throw new Zend_Controller_Dispatcher_Exception('SSO request malformed or expired. Cannot authenticate'); 
      }
    
    }

    public function loginAction()
    {
         
        $request = $this->getRequest();
        $addLoginHistory = true;
        $readDbConfig   = Zend_Registry::get('read')->getConfig();
        $defaultDbConfig = Zend_Db_Table::getDefaultAdapter()->getConfig();
        
        $email = $request->getParam('email', '');
        $password = $request->getParam('password', '');

        if ($email == '' || $password == '') {
            $this->_helper->flashMessenger->addMessage('Missing username or password');
            $this->_redirect('/');
        }

        $isLoggedIn = App_User::login($email, $password);
       
        if ($isLoggedIn == App_User::ACTIVE) {
            //create user login history.
            if ($readDbConfig['host'] != $defaultDbConfig['host']) {
                Model_LoginHistory::create();
            }
            
            $role = App_User::getRole();
            if ($role) {
                $url = App_User::getUserHomePage();
                if (!$url) {
                   $url = '/';
                }
                $this->_redirect($url);
            }
        } else if ( in_array($isLoggedIn, array(App_User::SUSPENDED, App_User::DELETED, App_User::NO_USER_ACCESS_RULES) ) ) {
            // set temporary message
            $this->_helper->flashMessenger->addMessage('You are not authorized user to logged in.');
            $this->_redirect('/');
        } else {
            // set temporary message
            $this->_helper->flashMessenger->addMessage('Username or password does not match');
            $this->_redirect('/');
        }
    }

    public function googleAction() {
        $profile = $this->_getProfileAction();

        $consumer = new Ak33m_OpenId_Consumer();

        $id = 'https://www.google.com/accounts/o8/id';

        $protocol = $_SERVER['SERVER_PORT'] == '80' ? 'http' : 'https';

        $returnTo = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/auth/google-verify';

        $request = $this->getRequest();
        $ref = $request->getParam('ref');
        if (!empty($ref)) {
            $returnTo .= "?ref={$ref}";
        }

        $stackMob = $request->getParam('s');
        if (!empty($stackMob)) {
            $returnTo .= "?s={$stackMob}";
        }
        
        $r = $consumer->login($id, $returnTo, null, $profile);
    }

    public function googleVerifyAction() {
        $profile = $this->_getProfileAction();
        $addLoginHistory = true;
        $readDbConfig = Zend_Registry::get('read')->getConfig();
        $defaultDbConfig = Zend_Db_Table::getDefaultAdapter()->getConfig();

        if ($readDbConfig['host'] == $defaultDbConfig['host']) {
            $addLoginHistory = false;
        }

        $consumer = new Ak33m_OpenId_Consumer();

        if ($consumer->verify($this->getRequest()->getParams(), $id, $profile)) {
            $data = $profile->getProperties();
            $email = $data['email'];

            $partnerDetail = Tapit_PseudoConstant::partners(null, null, $_SERVER["HTTP_HOST"]);
            if (empty($partnerDetail)) {
                $partnerDetail = Tapit_PseudoConstant::partners(null, null, 'ads.tapit.com');
            }
            $partnerId = key($partnerDetail);

            $user = new Model_User();
            $user = $user->findUser($email, true, $partnerId);

            if ($user->id) {
                //do check for suspended users.
                if ($user->status_id == App_User::SUSPENDED) {
                    $this->_helper->flashMessenger->addMessage('You are not authorized user to logged in.');
                    $this->_redirect('/');
                } else if ($user->status_id == App_User::DELETED) {
                    $this->_helper->flashMessenger->addMessage('You are not authorized user to logged in.');
                    $this->_redirect('/');
                }
                //empty access rule child
                if ($user->accountId && !Model_UserAccessRule::getRuleCnt($user->id)) {
                    $this->_helper->flashMessenger->addMessage('You are not authorized user to logged in.');
                    $this->_redirect('/');
                }
                
                App_User::setLogged($user);

                $role = App_User::getRole();
                //create user login history
                if ($addLoginHistory) {
                    Model_LoginHistory::create();
                }

                switch ($role) {
                    case 'publisher': $this->_redirect('/publisher/dashboard');
                        break;
                    case 'advertiser': $this->_redirect('/advertiser/dashboard');
                        break;
                    case 'publisher_advertiser': $this->_redirect('/advertiser/dashboard');
                        break;
                    case 'admin': $this->_redirect('/advertiser/dashboard');
                        break;
                    case 'super-admin': $this->_redirect('/admin/network/dashboard');
                        break;

                    default:
                        break;
                }
            } else {

                $password = md5(microtime() . rand(1, 9999999));

                $userData = array(
                    'role_type_id' => 1,
                    'partner_id' => $partnerId,
                    'country_id' => 0,
                    'social_app_type_id' => 1,
                    'password' => $password,
                    'confirm_password' => $password,
                    'completed' => false
                );

                $userFields = array(
                    'email' => 'email',
                    'fname' => 'firstName',
                    'lname' => 'lastName');
                foreach ($userFields as $key => $fld) {
                    $fldVal = null;
                    if (isset($data[$fld])) {
                        $fldVal = $data[$fld];
                    }
                    $userData[$key] = $fldVal;
                }

                $referrerId = Model_User::getReferrerIDFromUrl();
                if ($referrerId) {
                    $userData['referrer_id'] = $referrerId;
                }

                //check for stackmob user
                $userData['is_stackmob'] = App_User::makeStackMobUser($this->getRequest());                
                                
                $form = new Default_Form_Register(array('partnerId' => $partnerId));
                $form->removeElement('captcha');

                //remove some of required validator for regsteration with google
                $elementCompany = $form->getElement('company');
                $elementCompany->setRequired(false);

                $elementTitle = $form->getElement('title');
                $elementTitle->setRequired(false);

                $elementPhone = $form->getElement('phone');
                $elementPhone->setRequired(false);

                $elementZip = $form->getElement('zip');
                $elementZip->setRequired(false);

                $elementSocialAppUsername = $form->getElement('social_app_username');
                $elementSocialAppUsername->setRequired(false);
                                
                if( $userData['is_stackmob'] ){
                  $stackMob = $form->getElement('is_stackmob');  
                  $stackMob->setValue( $userData['is_stackmob'] );
                }                
                
                if ($form->isValid($userData)) {
                    App_User::register($form->getGoogleValues(), true, true, true);
                }
            }
        }

        $this->_redirect('/');
    }


    public function logoutAction()
    {
        App_User::logout();
        $session = new Zend_Session_Namespace('fundMessage');
        unset($session->messageshow);
        $this->_redirect('/');
    }


    /**
     * @return Openid_Extension_AttributeExchange
     */
    protected function _getProfileAction()
    {
        $profile = new Openid_Extension_AttributeExchange(array(
            'firstName' => true,
            'fullName' => true,
            'email'    => true,
            'lastName' => true),null,1.1
        );

        return $profile;
    }
    
 


}