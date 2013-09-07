<?php

class App_User
{
    
    const
        INVALID_USERNAME_PASSWORD = 0,
        PENDING   = 1,
        ACTIVE    = 2,
        APPROVED  = 3,
        DECLINED  = 4,
        SUSPENDED = 5,
        DELETED   = 9,
        NO_USER_ACCESS_RULES = 100,
        ENCRIPTED_PASSWORD = 'ENCRIPTED-PASSWORD',
        DIFFERENT_PARTNER_USER = 'DIFFERENT_PARTNER_USER',
        READ_ONLY = 1,
        CHANGE_STATUS = 2,
        FULL_ACCESS = 3;    
        
   
    public static function getRole()
    {
        $auth = Zend_Auth::getInstance();
        $role = 'guest';
        
        if ($auth->hasIdentity()) {

            $user = self::getLoggedUser();
           
            //some time we get the
            //email address as identity.
            $userRoles = array();
            if ( is_a( $user, 'Model_User' ) ) {
                $userRoles = $user->roleTypeIds;
            }

            if ( in_array(6, $userRoles) ) {
                $role = 'super-admin';
            } else if ( in_array(5, $userRoles) ) {
                $role = 'admin';
            } else if ( (in_array(1, $userRoles) && in_array(2, $userRoles)) || 
                        (in_array(1, $userRoles) && in_array(4, $userRoles)) ||
                        (in_array(2, $userRoles) && in_array(3, $userRoles)) ||
                        (in_array(3, $userRoles) && in_array(4, $userRoles)) ) {
                $role = 'publisher_advertiser';
            } else if (in_array(1, $userRoles)||in_array(3, $userRoles)) {
                //publisher or publisher manager
                $role = 'publisher';
            } else if (in_array(2, $userRoles)||in_array(4, $userRoles)) {
                //advertiser or advertiser manager
                $role = 'advertiser';
            }
            
            /*
              if($user->roleTypeId == 1 || $user->roleTypeId == 2) {
              return 'publisher';
              } else if ($user->roleTypeId == 3) {
              return 'advertiser';
              } else if($user->roleTypeId == 4) {
              return 'publisher_advertiser';
              } else if($user->roleTypeId == 5) {
              return 'admin';
              } else {
              return 'guest';
              }
             */
        }
        
        return $role;
    }
    
    public static function getUserHomePage() {
        $url = null;
        if (App_User::isLogged()) {
            $role = App_User::getRole();
            switch ($role) {
                case 'publisher':
                    $url = '/publisher/dashboard';
                    break;

                case 'advertiser':
                    $url = '/advertiser/dashboard';
                    break;

                case 'publisher_advertiser':
                    //we need to redirect to respective dashboards.
                    if (App_User::isAdvertiser()) {
                        $url = '/advertiser/dashboard';
                    } else if (App_User::isPublisher()) {
                        $url = '/publisher/dashboard';
                    } else if ( App_User::isPublisherManager()) {
                        $url = '/advertiser/dashboard';
                    } else {
                        $url = '/publisher/dashboard';
                    }
                    break;
                
                case 'admin':
                    $url = '/admin/network/dashboard';
                    $partner = App_Partner::singleton();
                    if ($partner->partner['name'] == 'tapit') {
                         $url = '/advertiser/dashboard';
                    }
                    
                    break;

                case 'super-admin':
                    $url = '/admin/network/dashboard';
                    break;
            }
        }
        return $url;
    }
    
    /**
     *
     * @param string $email
     * @param password $password
     */
    public static function login($email, $password, $treatPassword = true) {
        if (empty($email) || empty($password)) {
            return self::INVALID_USERNAME_PASSWORD;
        }

        $authAdapter = new Zend_Auth_Adapter_DbTable();

        $authAdapter->setTableName('users');
        $authAdapter->setCredential($password);
        //some time we do pass md5 password.
        if ($treatPassword) {
            $authAdapter->setCredentialTreatment('MD5(?)');
        }
        $authAdapter->setCredentialColumn('password');
        $authAdapter->setIdentity($email);
        $authAdapter->setIdentityColumn('email');
        
        //make sure to cosider default as tapit domain
        $partnerDetail = Tapit_PseudoConstant::partners(null, null, $_SERVER["HTTP_HOST"]);
        if (empty($partnerDetail)) {
            $partnerDetail = Tapit_PseudoConstant::partners(null, null, 'ads.tapit.com');
        }
        $partnerId = key($partnerDetail);
        $authAdapter->getDbSelect()->where('partner_id = ?', $partnerId);
        
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);
        
        if ($result->isValid()) {
            $authData = $authAdapter->getResultRowObject();

            //do check for suspended users.
            if ($authData->status_id == self::SUSPENDED) {
                //clear cache
                if ($auth->hasIdentity()) {
                    $auth->clearIdentity();
                }
                return self::SUSPENDED;
            } else if ($authData->status_id == self::DELETED) {
                //clear cache
                if ($auth->hasIdentity()) {
                    $auth->clearIdentity();
                }
                return self::DELETED;
            }

            $user = new Model_User((array) $authData);

            //empty access rule child
            if ($user->accountId && !Model_UserAccessRule::getRuleCnt($user->id)) {
                //clear cache
                if ($auth->hasIdentity()) {
                    $auth->clearIdentity();
                }
                return self::NO_USER_ACCESS_RULES;
            }

            //do check for user of same partner domain
            $partnerDetail = Tapit_PseudoConstant::partners(null, null, $_SERVER["HTTP_HOST"]);
            if(count($partnerDetail) > 0){
                if($user->partner_id != key($partnerDetail)){
                    if ($auth->hasIdentity()) {
                        $auth->clearIdentity();
                    }
                    return self::DIFFERENT_PARTNER_USER;
                }
            }
            self::setLogged($user);
            
            return self::ACTIVE;
        }

        return self::INVALID_USERNAME_PASSWORD;
    }

    public static function logout()
    {
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->clear();
    }


    public static function getEmail() {
        $auth = Zend_Auth::getInstance();

        $email = null;
        if ($auth->hasIdentity()) {
            $user = self::getLoggedUser();
            if (is_a($user, 'Model_User')) {
                $email = $user->email;
            }
        }

        return $email;
    }

    public static function isLogged()
    {
        $auth = Zend_Auth::getInstance();
        return $auth->hasIdentity();
    }

    public static function register(array $data, $createUser = false, $setUserLoggedIn = true, $throughGoogle = false) {

         // create API token
         if(!isset($data['api_token']) || (isset($data['api_token']) && empty($data['api_token']))) 
         {
            $data['api_token'] = md5(serialize($data));
         }
         
        $user = new Model_User($data);

        // only create if told to do so
        // google account creation needs this 

        if ($createUser) {
            $user->statusId = 2;
            $user->save();

            //save the account managers.
            if ($user->find()) {
                //get the user managers.                         
                $userAccManagers = Model_UserAccountManager::getUserAccountManagers($user->id);
                if (empty($userAccManagers) && strpos($user->email, '@tapit.com') === false) {
                    //get the account managers.
                    $userRoleTypeIds = $user->roleTypeIds;                                                              
                    if (in_array('1', $userRoleTypeIds)) {
                        unset($userRoleTypeIds[array_search('1', $userRoleTypeIds)]);
                    }
                    if (!empty($userRoleTypeIds)) {
                        $partnerDetail = Tapit_PseudoConstant::partners(null, null, $_SERVER["HTTP_HOST"]);
                        $partnerId = (count($partnerDetail) > 0) ? key($partnerDetail) : 1;
                        
                        $accManagerIds = $user->accountManagerIds($userRoleTypeIds, $partnerId);
                        //$accManagerIds = $user->accountManagerIds($userRoleTypeIds);
                        foreach ($accManagerIds as $roleId => $managerId) {
                            $accManager = new Model_UserAccountManager();
                            $accManager->user_id = $user->id;
                            $accManager->manager_id = $managerId;
                            $accManager->role_type_id = $roleId;
                            $accManager->save();
                        }
                    }
                }
                
                //make default mailing all mailing options.
                if ($throughGoogle) {
                     Model_UserEmailPreferences::setDefaultMailingPreferences($user);
                }
            }
            
        }

        $_SESSION['welcome'] = true;
        
        // send welcome email to user
        if (!$throughGoogle) {
            self::sendWelcomeEmailToUser($data);
        }
        

        //App_Email::send($data['email'], $subject, $message, $headers);

        $url = BASE_URL.'/salesforce.php';
        
        $fields = array();
        $userFlds = array(
            'lname' => 'lname',
            'fname' => 'fname',
            'company' => 'company',
            'email' => 'email',
            'phone' => 'phone');
        foreach ($userFlds as $key => $fld) {
            $fVal = null;
            if (isset($data[$fld])) {
                $fVal = urlencode($data[$fld]);
            }
            $fields[$key] = $fVal;
        }
        
        $query = http_build_query($fields);
        
        //there are issues w/ SforceBaseClient.php
        //$ch = curl_init();
        //curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POST, count($fields));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        //$result = curl_exec($ch);
        //curl_close($ch);

        if ($setUserLoggedIn) {
            self::setLogged($user);
        }
        
        //send account managers mail except publishers
        $userRoleTypeIds = $user->role_type_ids;                                        
        unset( $userRoleTypeIds[array_search( '1', $userRoleTypeIds )] ); 
        
        if ( !$throughGoogle && $createUser && $setUserLoggedIn && !empty($userRoleTypeIds) ) {
            //send accoutn manager mail to google registered user.
            self::sendManagerDetailsToUser( $userRoleTypeIds );
        }
                        
    }

    public static function setLogged(Model_User $user)
    {
        $updateLastLoginDate = true;
        $readDbConfig = Zend_Registry::get('read')->getConfig();
        $defaultDbConfig = Zend_Db_Table::getDefaultAdapter()->getConfig();
        if ( $readDbConfig['host'] == $defaultDbConfig['host']) {
            $updateLastLoginDate = false;
        }
        
        //set last login date.           
        if ( $user->id && $user->find() && $updateLastLoginDate ) {
            $lastLoginDate = date('YmdHis');
            $user->last_login_date = $lastLoginDate;
            //don't use $user->update();
            //avoid object chaining to update single field.
            $sql = "UPDATE LOW_PRIORITY users set last_login_date = {$lastLoginDate} WHERE id =" .$user->id;
            $table = $user->getTable();
            $table->getAdapter()->query($sql);
        }
        
        //logged main account user.
        $mainUser = $user;
        if ($user->accountId) {
            $mainAccountUser = new Model_User();
            $mainAccountUser->id = $user->accountId;
            if ($mainAccountUser->find()) {
                $mainUser = $mainAccountUser;
                unset($mainAccountUser);
            }
            $mainUser->mainUserId = $user->id;
        }
        
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($mainUser);
    }

    public static function getUserId() 
    {
        $userId = null;
        $auth = Zend_Auth::getInstance();
        if (is_a($auth, 'Zend_Auth')) {
            $user = $auth->getIdentity();
            if (is_a($user, 'Model_User')) {
                $userId = $user->id;
            }
        }
        return $userId;
    }
    
    public static function getMainUserId() {
        $mainUserId = 0;
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            if (is_object($auth->getIdentity())) {
                $mainUserId = $auth->getIdentity()->mainUserId;
                if (!$mainUserId) {
                    $mainUserId = $auth->getIdentity()->id;
                }
            }
        }

        return $mainUserId;
    }

    /**
     * @return Model_User
     */
    public static function getLoggedUser()
    {
        $auth = Zend_Auth::getInstance();
        return $auth->getIdentity();
    }
	
    /**
     * @return bool
     */
    public static function isAdmin() {
        return in_array(self::getRole(), array('admin', 'super-admin'));
    }

    /**
     * @return bool
     */
    public static function isSuperAdmin() {
        return self::getRole() == 'super-admin';
    }
    
    /**
     * @return bool
     */
    public static function isAdvertiserManager() {

        $isManager = false;
        $user = self::getLoggedUser();

        //some time we get the
        //email address as identity.
        $userRoles = array();
        if (is_a($user, 'Model_User')) {
            $userRoles = $user->roleTypeIds;

            if (in_array(4, $userRoles)) {
                $isManager = true;
            }
        }

        return $isManager;
    }

    /**
     * @return bool
     */
    public static function isPublisherManager() {

        $isManager = false;
        $user = self::getLoggedUser();

        //some time we get the
        //email address as identity.
        $userRoles = array();
        if (is_a($user, 'Model_User')) {
            $userRoles = $user->roleTypeIds;

            if (in_array(3, $userRoles)) {
                $isManager = true;
            }
        }

        return $isManager;
    }

    /**
     * @return bool
     */
    public static function isPublisherAdvertiserManager() {

        $isManager = false;
        $user = self::getLoggedUser();

        //some time we get the
        //email address as identity.
        $userRoles = array();
        if (is_a($user, 'Model_User')) {
            $userRoles = $user->roleTypeIds;

            if (in_array(3, $userRoles) && in_array(4, $userRoles)) {
                $isManager = true;
            }
        }

        return $isManager;
    }

    /**
     * @return bool
     */
    public static function isAdvertiser() {

        $isAdvertiser = false;
        $user = self::getLoggedUser();

        //some time we get the
        //email address as identity.
        $userRoles = array();
        if (is_a($user, 'Model_User')) {
            $userRoles = $user->roleTypeIds;

            if (in_array(2, $userRoles)) {
                $isAdvertiser = true;
            }
        }

        return $isAdvertiser;
    }

    /**
     * @return bool
     */
    public static function isPublisher() {

        $isPublisher = false;
        $user = self::getLoggedUser();

        //some time we get the
        //email address as identity.
        $userRoles = array();
        if (is_a($user, 'Model_User')) {
            $userRoles = $user->roleTypeIds;

            if (in_array(1, $userRoles)) {
                $isPublisher = true;
            }
        }

        return $isPublisher;
    }

    /**
     * @return bool
     */
    public static function isChildUser() {
        return (self::getUserId() != self::getMainUserId());
    }
    
    /**
     * @return bool
     */
    public static function fullAccess() {

        $allow = false;
        $userId = self::getMainUserId();
        $accessRules = Model_UserAccessRule::getRules($userId);
        if (in_array(self::FULL_ACCESS, $accessRules)) {
            $allow = true;
        }

        return $allow;
    }
    
    

    /**
     * @return bool
     */
    public static function changeStatus() {

        $allow = false;
        $userId = self::getMainUserId();
        $accessRules = Model_UserAccessRule::getRules($userId);
        if (in_array(self::FULL_ACCESS, $accessRules) ||
                in_array(self::CHANGE_STATUS, $accessRules)) {
            $allow = true;
        }

        return $allow;
    }

    /**
     * @return bool
     */
    public static function readOnly() {

        $allow = false;
        $userId = self::getMainUserId();
        $accessRules = Model_UserAccessRule::getRules($userId);
        if (in_array(self::FULL_ACCESS, $accessRules) ||
                in_array(self::CHANGE_STATUS, $accessRules) ||
                in_array(self::READ_ONLY, $accessRules)) {
            $allow = true;
        }
        
        return $allow;
    }
    
    /**
     * @return bool
     */
    public static function allowSiteSearch() {
        $auth = Zend_Auth::getInstance();
        $allow = false;
        if ($auth->hasIdentity()) {
            $user = self::getLoggedUser();
            $userRoles = $user->roleTypeIds;
            if ( in_array( 3, $userRoles ) || in_array( 4, $userRoles ) ||
                 in_array( 5, $userRoles ) || in_array( 6, $userRoles ) ) {
               $allow = true; 
            }
        }
        
        return $allow;
    }

    /**
     * @return bool
     */
    public static function allow($userId, $publisherDashboardPage = false, $advertiserDashboardPage = false, $pageName = null) {
        $allow = false;
        if (empty($userId)) {
            return $allow;
        }
        
        $userRoleTypeIds = Model_UserRole::getUserRoles($userId);
        
        $loggedInUserRoleTypeIds = array();
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $loggedInUser = self::getLoggedUser();
            $loggedInUserRoleTypeIds = $loggedInUser->roleTypeIds;
        }

        if (empty($loggedInUserRoleTypeIds) || !is_array($loggedInUserRoleTypeIds)) {
            return $allow;
        }

        $samePartner = false;
        $loggedInUserPartner = App_Partner::singleton();
        $userPartner = Model_Partner::getUserPartner($userId);
        if (isset($userPartner['id']) &&  ($userPartner['id'] == $loggedInUserPartner->partner['id'])) {
            $samePartner = true;
        }
        
        if (empty($userRoleTypeIds) || !is_array($userRoleTypeIds)) {
            //id aprtner's admin, do check for same partner
            if (in_array(5, $loggedInUserRoleTypeIds)) {
                if ($samePartner) {
                    $allow = true;
                }
            }
            if (in_array(6, $loggedInUserRoleTypeIds)) {
                $allow = true;
            }

            return $allow;
        }
        
        if ( $loggedInUser->id == $userId ) {
            return true;
        }
        

        $accountAccessRoles = 
        array(
            1 => array(3, 5, 6),
            2 => array(4, 5, 6),
            3 => array(5, 6),
            4 => array(5, 6),
            5 => array(5, 6),
            6 => array(6));

        $dashboardAccessRoles = 
        array(
            1 => array(3, 5, 6),
            2 => array(4, 5, 6),
            3 => array(3, 5, 6),
            4 => array(4, 5, 6),
            5 => array(5, 6),
            6 => array(5, 6));

      
        
        //lets allow publisher manager to access admin's publisher page
        if ($publisherDashboardPage) {
            $dashboardAccessRoles[5][] = 3;
            $dashboardAccessRoles[6][] = 3;
        }

        //lets allow advertiser manager to access admin's advertiser page
        if ($advertiserDashboardPage) {
            $dashboardAccessRoles[5][] = 4;
            $dashboardAccessRoles[6][] = 4;
        }

        $accessRoles = $accountAccessRoles;
        $accountInfoPage = true;
        if ($publisherDashboardPage || $advertiserDashboardPage) {
            $accessRoles = $dashboardAccessRoles;
            $accountInfoPage = false;
        }
        
        //do check for supper admin user.
        $isSuperAdmin = App_User::isSuperAdmin();
        
        foreach ($userRoleTypeIds as $roleId) {
            //don't consider advertiser roles for publisher pages.
            if ($publisherDashboardPage && in_array($roleId, array(2, 4))) {
                continue;
            }
            //don't consider publisher roles for advertiser pages.
            if ($advertiserDashboardPage && in_array($roleId, array(1, 3))) {
                continue;
            }

            if (array_key_exists($roleId, $accessRoles)) {

                //consider all user roles across access roles. 
                if ($publisherDashboardPage || $advertiserDashboardPage) {
                    $allow = false;
                }

                //account info page don't consider both advertiser and pulisher roles.
                //pushlisher manager should able to access advertiser and publisher user
                //so don't reset allow for both advertiser and publisher user.
                if ($accountInfoPage && !in_array($roleId, array(1, 2))) {
                    $allow = false;
                }

                foreach ($accessRoles[$roleId] as $reqRole) {
                    if (in_array($reqRole, $loggedInUserRoleTypeIds)) {
                        if ($samePartner) {
                            $allow = true;
                            break;
                        } else if ($isSuperAdmin) {
                            $allow = true;
                            break;
                        }
                    }
                }
            }
        }
        
        //allow to update own page
        if (!$allow && !$publisherDashboardPage && !$advertiserDashboardPage) {
            if ($pageName && !in_array($pageName, array('account-summary', 'referral-dashboard'))) {
                if ($userId == App_User::getMainUserId()) {
                    $allow = true;
                }
            }
        }
       
        return $allow;
    }

    /**
     *
     * @param string $email
     * @param password $password
     */
    public static function sendEmail($userData) { 
        $partnerDetail = Tapit_PseudoConstant::partners(null, null, $_SERVER["HTTP_HOST"]);
        foreach ($partnerDetail as $id => $detail){
            $partnerId = $id;
            $partnerName = $detail['label'];
            $partnerSupportUrl  = $detail['support_url'];
        }        
        
        if(empty($partnerDetail)){            
            $partnerDetail = Tapit_PseudoConstant::partners(null, null, 'ads.tapit.com');
            foreach ($partnerDetail as $id => $detail){
                $partnerId = $id;
                $partnerName = $detail['label'];
                $partnerSupportUrl  = $detail['support_url'];
            }
        }   
        
        $bgcolor = '#2C709E';
        $fontcolor = '#FFFFFF';
        if($partnerId == '3'){
            $bgcolor = '#fbd130';
            $fontcolor = '#666666';
        }
        
        $subject = 'Welcome to '.$partnerName.'!';
        $message = '';
        $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
        $message .= "<p style=\"border-radius:10px; text-transform: uppercase; font-size: 15px; font-family: 'Droid Sans', sans-serif; line-height: 20px; padding: 5px 10px; background-color:".$bgcolor."; color:".$fontcolor.";\"><b>Welcome to ".$partnerName."!</b></p>";
        $message .= 'Hi ' . $userData['fname'] . ',';
        $message .= '<br/><br/>';
        $message .= 'Thanks for registering with '.$partnerName.'!.';
        $message .= '<br/>';
        $message .= 'Please click on the link below to verify and activate your account:  ';
        $message .= $userData['uniqueUrl'] . ' to verify.';
        $message .= "</body></html>";
        $fromEmail = $partnerSupportUrl;
        $fromName  = $partnerName.' Support Team';
                
        $response  = App_Email::send($userData['email'], $subject, $message, null, $fromEmail, $fromName);

        if ($response || true) {
            $status = 'success';
            return $status;
        } else {
            $status = 'fail';
            return $status;
        }
    }
        
        
    public static function sendMultiuserEmail($userData) {       
        $partnerDetail = Tapit_PseudoConstant::partners(null, null, $_SERVER["HTTP_HOST"]);
        foreach ($partnerDetail as $id => $detail){
            $partnerId = $id;
            $partnerName        = $detail['label'];            
            $partnerSupportUrl  = $detail['support_url'];
        }
        
        if(empty($partnerDetail)){            
            $partnerDetail = Tapit_PseudoConstant::partners(null, null, 'ads.tapit.com');
            foreach ($partnerDetail as $id => $detail){
                $partnerId = $id;
                $partnerName        = $detail['label'];                
                $partnerSupportUrl  = $detail['support_url'];
            }
        }         
        
        $partnerUrl = 'http://'.$_SERVER["HTTP_HOST"];
        
        $subject = 'Welcome to '.$partnerName.'!';
        $message = '';
        $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
        $message .= "<p style=\"border-radius:10px; text-transform: uppercase; font-size: 15px; font-family: 'Droid Sans', sans-serif; line-height: 20px; padding: 5px 10px; background-color:#2C709E; color:#FFFFFF;\"><b>Welcome to ".$partnerName."!</b></p>";
        $message .= 'Hello,';
        $message .= '<br/><br/>';
        $message .= 'You have been registered with '.$partnerName.'!.';
        $message .= '<br/>';
        $message .= 'You can login to site <a target="_blank" href="'.$partnerUrl.'">('.$partnerUrl.')</a> with following credential';
        $message .= '<br/><br/>';
        $message .= 'User id: '.$userData['email'];
        $message .= '<br/>';
        $message .= 'Password: '.$userData['password'];
        $message .= '<br/><br/>';       
        $message .= "</body></html>";
        
        
        $fromEmail = $partnerSupportUrl;
        $fromName  = $partnerName.' Support Team';               
        $response  = App_Email::send($userData['email'], $subject, $message, null, $fromEmail, $fromName);
        if ($response || true) {
            $status = 'success';
            return $status;
        } else {
            $status = 'fail';
            return $status;
        }
    }  
    
    public static function updateLoggedIn(Model_User $user) {
        //logged main account user.
        $mainUser = $user;
        if ($user->accountId) {
            $mainAccountUser = new Model_User();
            $mainAccountUser->id = $user->accountId;
            if ($mainAccountUser->find()) {
                $mainUser = $mainAccountUser;
                unset($mainAccountUser);
            }
            $mainUser->mainUserId = $user->id;
        }
        
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->clear();
        $auth->getStorage()->write($mainUser);
    }

    public function sendManagerDetailsToUser($userId, $skipAdvertiser = false, $skipPublisher = false, $timeOffset = true ) 
    {        
                        
        if (empty($userId)) {
            return;
        }
        
        $user = new Model_User();
        $user->id = $userId;
        if (!$user->find()) {
            return;
        }
                
        $partnerDetail = App_Partner::singleton()->partner;
        if ($partnerDetail['id']) {
            $partnerName = $partnerDetail['label'];
            $partnerSupportUrl = $partnerDetail['support_url'];
            $partnerId = $partnerDetail['id'];
            $partnerSDKUrl = $partnerDetail['sdk_link'];
            $partnerSupportLink = $partnerDetail['support_link'];
        }
        unset($partnerDetail);    
        
        //fetch the account manager ids.
        $accManagerIds = array();
        $userAccManagers = Model_UserAccountManager::getUserAccountManagers($user->id);
        foreach ($userAccManagers as $manager) {
            if ($manager['role_type_id'] == 3 && !$skipPublisher ) {
                $accManagerIds[3] = $manager['manager_id'];
            }
            if ($manager['role_type_id'] == 4 && !$skipAdvertiser ) {
                $accManagerIds[4] = $manager['manager_id'];
            }
        }

        $mailedManagerIds = array();
        $roleTypeIds = $user->roleTypeIds;
                               
        foreach ($accManagerIds as $roleId => $managerId) {
            //send two mails w/ different contents.
            if (in_array($managerId, $mailedManagerIds)) {
                //continue;
            }

            $accManager = new Model_User();
            $accManager->id = $managerId;
            if (!$accManager->find()) {
                continue;
            }
            
            //messenger
            $msgTypeId = $accManager->social_app_type_id;
            if ( !$msgTypeId ) {
                $msgTypeId = 1;
            }
            $messengerList = Model_User::socialAppTypesList();
            $msgType = $messengerList[$msgTypeId];

            $fromEmail = $accManager->email;
            $fromName = ucfirst($accManager->fname) . ' ' . ucfirst($accManager->lname);
            $headers = array( );
            $toEmail = $user->email;
            
            $message = null;

            $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
            
            $selectArray = array(0, 1, 2);
            $selectedEmail = array_rand($selectArray);
      
            if ($roleId == '3') {
                if ($selectedEmail == 0) {
                    $subject = 'Your ' . $partnerName . '! Account Manager';

                    $message .= "<p>Hi there " . ucfirst($user->fname) . ",</p>";
                    $message .= "<p>I just noticed your registration on " . $partnerName . "!</p>";
                    $message .= "<p>My name is <strong>" . ucfirst($accManager->fname) . ' ' . ucfirst($accManager->lname) . "</strong> and I wanted to let you know that I will be your dedicated account manager.</p>";
                    $message .= "<p>I will help with planning and making sure you get the best eCPMs on your mobile inventory. Can you give me an idea of how much volume you have and which countries you are seeing the most traffic from?</p>";
                    $message .= "<p>The next step is to install our SDK. It only takes a few minutes and you can find the SDK's and instructions here:<a href='".$partnerSDKUrl."' target='_blank'>".$partnerSDKUrl."</a></p>";
                    $message .= "<p>By installing our SDK you get to:</p>";
                    $message .= "<ol>";
                    $message .= "<li>Earn money by showing Ads on your Apps</li>";
                    $message .= "<li>Use Mediation to control which other ad networks show ads on your app</li>";
                    $message .= "<li>Earn higher eCPM's by showing unique ad units such as AdPrompts, Video Ads and Interstitials</li>";
                    $message .= "<li>Sell your inventory in our RTB auction</li>";
                    $message .= "</ol>";
                    $message .= "<p>Please contact me anytime if you have any questions or regarding anything about your account.</p>";
                    $message .= "<p>{$msgType}: {$accManager->social_app_username}</p>";
                    $message .= "<p>Thanks again, " . ucfirst($user->fname) . " I'm looking forward to working with you.</p>";
                } elseif ($selectedEmail == 1) {
                    $subject = $partnerName . ' Account Manager';

                    $message .= "<p>Hey " . ucfirst($user->fname) . ",</p>";
                    $message .= "<p>I saw you registered with us, so I wanted to reach out and let you know that I will be your Account Manager.</p>";
                    $message .= "<p>My name is <strong>" . ucfirst($accManager->fname) . ' ' . ucfirst($accManager->lname) . "</strong> and I'd be happy to connect with you on Skype, Email or Phone so that we can ensure you're earning the highest eCPM's possible with us.</p>";
                    $message .= "<p>My info is:</p>";
                    $message .= "<p>{$msgType}: {$accManager->social_app_username}</p>";
                    $message .= "<p>I'd like to learn more about the Countries you're seeing the most volume in as well as other sites or apps you may have.</p>";
                    $message .= "<p>The next step is to install our SDK. It only takes a few minutes and you can find the SDK's and instructions here:<a href='" . $partnerSDKUrl . "' target='_blank'>" . $partnerSDKUrl . "</a></p>";
                    $message .= "<p>By installing our SDK you get to:</p>";
                    $message .= "<ol>";
                    $message .= "<li>Earn money by showing Ads on your Apps</li>";
                    $message .= "<li>Use Mediation to control which other ad networks show ads on your app</li>";
                    $message .= "<li>Earn higher eCPM's by showing unique ad units such as AdPrompts, Video Ads and Interstitials</li>";
                    $message .= "<li>Sell your inventory in our RTB auction</li>";
                    $message .= "</ol>";
                    $message .= "<p>Add me on chat and we'll connect. Looking forward to it.</p>";
                    $message .= "<p>Thanks, <br />" . ucfirst($accManager->fname) . "</p>";
                } else {
                    $subject = $partnerName . ' Account Manager Intro..';

                    $message .= "<p>Hi " . ucfirst($user->fname) . "!</p>";
                    $message .= "<p>I'm <strong>" . ucfirst($accManager->fname) . ' ' . ucfirst($accManager->lname) . "</strong> and I'll be your dedicated Account Manager at {$partnerName}. I wanted to connect with you and find out more about your volume, countries, platforms, etc so I can help you make the most money possible at {$partnerName}</p>";
                    $message .= "<p>When's a good time to connect?</p>";
                    $message .= "<p>Here's my contact info:</p>";
                    $message .= "<p>{$msgType}: {$accManager->social_app_username}</p>";
                    $message .= "<p>The next step is to install our SDK. It only takes a few minutes and you can find the SDK's and instructions here:<a href='" . $partnerSDKUrl . "' target='_blank'>" . $partnerSDKUrl . "</a></p>";
                    $message .= "<p>By installing our SDK you get to:</p>";
                    $message .= "<ol>";
                    $message .= "<li>Earn money by showing Ads on your Apps</li>";
                    $message .= "<li>Use Mediation to control which other ad networks show ads on your app</li>";
                    $message .= "<li>Earn higher eCPM's by showing unique ad units such as AdPrompts, Video Ads and Interstitials</li>";
                    $message .= "<li>Sell your inventory in our RTB auction</li>";
                    $message .= "</ol>";
                    $message .= "<p>Add me or email me and we'll connect. I'm looking forward to working with you, " . ucfirst($user->fname) . ". </p>";
                    $message .= "<p>Thanks, <br />" . ucfirst($accManager->fname) . ' ' . ucfirst($accManager->lname) . "</p>";
                }
                $message .= "</body></html>";

                $response = App_Email::send($toEmail, $subject, $message, $headers, $fromEmail, $fromName);
            } else {
                if ($selectedEmail == 0) {
                    $subject = 'Your ' . $partnerName . ' Account Manager';

                    $message .= "<p>Hi there " . ucfirst($user->fname) . ",</p>";
                    $message .= "<p>I just saw that you signed up with " . $partnerName . "!</p>";
                    $message .= "<p>My name is <strong>" . ucfirst($accManager->fname) . ' ' . ucfirst($accManager->lname) . "</strong> and I wanted to let you know that I will be your dedicated account manager.</p>";
                    $message .= "<p>I will help with scaling your campaign and answering any questions like volume in specific countries, cpc's and anything else. Can you give me an idea of how much volume you're looking for and in which countries?</p>";
                    $message .= "<p>Kindly add me on chat to connect.</p>";
                    $message .= "<p>{$msgType}: {$accManager->social_app_username}</p>";
                    $message .= "<p>Please contact me anytime if you have any questions or regarding anything about your account.";
                    if ($partnerId == '1') {
                        $message .= " I have also put together a FAQ on how to use the TapIt system that can be located here: http://tapit.com/feature/tapit-self-serve-faq";
                    }
                    $message .= "</p><p>Thanks again, " . ucfirst($user->fname) . " and looking forward to working with you.</p>";
                } elseif ($selectedEmail == 1) {
                    $subject = 'Your ' . $partnerName . ' Account Manager!';

                    $message .= "<p>Hey " . ucfirst($user->fname) . ",<br />Just wanted to introduce myself as I will be your {$partnerName} Account Manager.</p>";
                    $message .= "<p>My name is <strong>" . ucfirst($accManager->fname) . ' ' . ucfirst($accManager->lname) . "</strong> and I'm here to help scale your campaigns, answer any questions, and anything else that you might need. </p>";
                    $message .= "<p>Add me on Skype and let me know how I can help. Here's my contact info:</p>";
                    $message .= "<p>{$msgType}: {$accManager->social_app_username}</p>";
                    $message .= "<p>Please contact me anytime if you have any questions or regarding anything about your account. ";
                    $message .= "You can also browse ".$partnerSupportLink." to find lots of helpful guides and walkthroughs.";
                    if ($partnerId == '1') {
                        $message .= " I have also put together a FAQ on how to use the TapIt system that can be located here: http://tapit.com/feature/tapit-self-serve-faq";
                    }
                    $message .= "</p><p>Thanks again, " . ucfirst($user->fname) . " and looking forward to working with you.</p>";
                    $message .= "<p>" . ucfirst($accManager->fname) . "</p>";
                } else {
                    $subject = 'Your ' . $partnerName . ' Account Manager...';

                    $message .= "<p>Hello " . ucfirst($user->fname) . ",<br />";
                    $message .= "I'll be your {$partnerName} Account Manager, so I just wanted to reach out and introduce myself.</p>";
                    $message .= "<p>I'm <strong>" . ucfirst($accManager->fname) . ' ' . ucfirst($accManager->lname) . "</strong> and you can reach out to me anytime with any questions or help that you need. I can help you scale your campaigns, get faster approvals, find big pockets of inventory, etc... </p>";
                    $message .= "<p>Add me on Skype and let me know how I can help. Here's my contact info: </p>";
                    $message .= "<p>{$msgType}: {$accManager->social_app_username}</p>";
                    $message .= "<p>Please contact me anytime if you have any questions or regarding anything about your account. ";
                    $message .= "You can also browse ".$partnerSupportLink." to find lots of helpful guides and walkthroughs.";
                    if ($partnerId == '1') {
                        $message .= " I have also put together a FAQ on how to use the TapIt system that can be located here: http://tapit.com/feature/tapit-self-serve-faq";
                    }
                    $message .= "</p><p>Thanks again, " . ucfirst($user->fname) . " and looking forward to working with you.</p>";
                    $message .= "<p>" . ucfirst($accManager->fname) . ' ' . ucfirst($accManager->lname) . "</p>";
                }
                $message .= "</body></html>";

                if ($timeOffset) {
                    $addMins = 7;
                    $response = App_Email::send($toEmail, $subject, $message, $headers, $fromEmail, $fromName, null, null, $addMins);
                } else {
                    $response = App_Email::send($toEmail, $subject, $message, $headers, $fromEmail, $fromName);
                }
            }
            $mailedManagerIds[$managerId] = $managerId;
        }
    }
    
    public function sendWelcomeEmailToUser($data)
    {            
        $partnerName = null;        
        $partnerDetail = Tapit_PseudoConstant::partners(null, null, $_SERVER["HTTP_HOST"]);
        $partnerId = key($partnerDetail);
        unset($partnerDetail);
        
        if(!$partnerId){            
            $partnerDetail = Tapit_PseudoConstant::partners(null, null, 'ads.tapit.com');
            $partnerId = key($partnerDetail);
            unset($partnerDetail);
        }    
        $partnerObj = new Model_Partner();
        $partnerObj->id = $partnerId;
        $partnerObj->find();
        
        if($partnerId){   
            $partnerId           = $partnerObj->id;
            $partnerName         = $partnerObj->label;
            $partnerUrl          = $partnerObj->url;
            $partnerPhone        = $partnerObj->phone;
            $partnerSupportUrl   = $partnerObj->support_url;
            $partnerAdvHelpLink  = $partnerObj->adv_help_link;
            $partnerPubHelpLink  = $partnerObj->pub_help_link;
            $partnerAdvSettLink  = $partnerObj->adv_setting_link;
            $partnerPubSettLink  = $partnerObj->pub_setting_link;
            $partnerAdvFaqLink   = $partnerObj->adv_faq_link;
            $partnerPubFaqLink   = $partnerObj->pub_faq_link;
            $partnerCovTrackLink = $partnerObj->conv_tracking_link;            
        }  
        
        $bgcolor = '#2C709E';
        $fontcolor = '#FFFFFF';
        if($partnerId == '3'){
            $bgcolor = '#fbd130';
            $fontcolor = '#666666';
        }
        
        
        $subject = 'Welcome to '.$partnerName.'!';
        $headers = "From: ".$partnerSupportUrl." \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n\r\n";

        $message = "";
        $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
        $message .= "<p style=\"border-radius:10px; text-transform: uppercase; font-size: 15px; font-family: 'Droid Sans', sans-serif; line-height: 20px; padding: 5px 10px; background-color:".$bgcolor."; color:".$fontcolor.";\"><b>WELCOME TO ".$partnerName."!</b></p>";
        
        $roleTypeIds = array();
        if (isset($data['role_type_ids']) && is_array($data['role_type_ids'])) {
            $roleTypeIds = $data['role_type_ids'];
        }
        
        if (in_array(2, $roleTypeIds) && !in_array(1, $roleTypeIds)) {
            $message .= "<p style=\"font-size: 20px;\">" . $data['fname'] . ' ' . $data['lname'] . ", you are sooo cool!</p>";
            $message .= '<p style=\"font-size: 20px;\">Thank you for signing up as an advertiser with <font color="#2C709E">'.$partnerName.'!</font> </p>';
            $message .= "<p>Now, lets get this show on the road!</p>";
            $message .= '<p>Lets get you launching campaigns on the <font color="#2C709E">'.$partnerName.'!</font> Mobile Ad Platform!';
            $message .= '<ul style="list-style: decimal;">';
            $message .= '<li>Log-in to your account: '.$partnerUrl.'</li>';
            $message .= '<li>Click on the Create a Campaign button</li>';
            $message .= '<li>Update your Creatives and define your Targeting</li>';
            $message .= '<li>Fund your account via Paypal</li>';
            $message .= '<li>After your Campaign and Ads are approved, they\'ll start serving!</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Here are some good resources to help you out';            
            $message .= '<ul style="list-style: decimal;">';
            $message .= '<li>Advertiser Support Portal: '.$partnerAdvHelpLink.'</li>';
            $message .= '<li>Advertiser FAQ\'s: '.$partnerAdvFaqLink.'</li>';
            $message .= '<li>Setting Up a Campaign: '.$partnerAdvSettLink.'</li>';
            $message .= '<li>Conversion Tracking with '.$partnerName.'!: '.$partnerCovTrackLink.'</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Your account information:';
            $message .= '<ul style="list-style: none;">';
            $message .= '<li>Username: <b>' . $data['email'] . '</b></li>';
            $message .= '<li>Name: <b>' . $data['fname'] . ' ' . $data['lname'] . '</b></li>';
            $message .= '<li>Company: <b>' . $data['company'] . '</b></li>';
            $message .= '<li>Address: <b>' . $data['address1'] . '</b></li>';
            $message .= '<li>Phone: <b>' . $data['phone'] . '</b></li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Thank you for choosing <font color="#2C709E">'.$partnerName.'!&trade;</font>. We look forward to providing you with the most effective mobile advertising solutions and building a successful relationship!</p>';
        } elseif (in_array(1, $roleTypeIds) && in_array(2, $roleTypeIds)) {
            $message .= "<p style=\"font-size: 20px;\">" . $data['fname'] . ' ' . $data['lname'] . ", you are sooo cool!</p>";
            $message .= '<p style=\"font-size: 20px;\">Thank you for signing up with <font color="#2C709E">'.$partnerName.'!</font> </p>';
            $message .= "<p>Now, lets get this show on the road!</p>";
            $message .= '<p>Lets get you launching campaigns on the <font color="#2C709E">'.$partnerName.'!</font> Mobile Ad Platform!';
            $message .= '<ul style="list-style: decimal;">';
            $message .= '<li>Log-in to your account: '.$partnerUrl.'</li>';
            $message .= '<li>Click on the Create a Campaign button</li>';
            $message .= '<li>Update your Creatives and define your Targeting</li>';
            $message .= '<li>Fund your account via Paypal</li>';
            $message .= '<li>After your Campaign and Ads are approved, they\'ll start serving!</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Here are some good resources to help you out';
            $message .= '<ul style="list-style: decimal;">';           
            $message .= '<li>Advertiser Support Portal: '.$partnerAdvHelpLink.'</li>';
            $message .= '<li>Advertiser FAQ\'s: '.$partnerAdvFaqLink.'</li>';
            $message .= '<li>Setting Up a Campaign: '.$partnerAdvSettLink.'</li>';
            $message .= '<li>Conversion Tracking with '.$partnerName.'!: '.$partnerCovTrackLink.'</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Lets get you earning money and displaying <font color="#2C709E">'.$partnerName.'!&trade;</font> ads!:';
            $message .= '<ul style="list-style: decimal;">';
            $message .= '<li>Log-in to your account: '.$partnerUrl.'</li>';
            $message .= '<li>Add site(s) or app(s) to your account.</li>';
            $message .= '<li>Get code. We provide you with the sample code needed to display ads.  You simply integrate it into your site/app.</li>';
            $message .= '<li>Site approval. In 1-2 days you will receive an email confirming if your site has been approved.  Once approved, your site will be ready to display ads!</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>How to make the most money from your mobile ad inventory with <font color="#2C709E">'.$partnerName.'!&trade;</font>';
            $message .= '<ul style="list-style: decimal;">';
            $message .= '<li>Standard Banners</li>';
            $message .= '<li>Rich Media</li>';
            $message .= '<li>Video</li>';
            $message .= '<li>Offer Walls</li>';
            $message .= '<li>Full Page Interstitials</li>';
            $message .= '<li>AdPrompts&trade;</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Here are some helpful resources to help you get started:';
            $message .= '<ul style="list-style: decimal;">';
            $message .= '<li>Publisher Help Portal: '.$partnerPubHelpLink.'</li>';
            $message .= '<li>Setting up your Sites & Zones: '.$partnerPubSettLink.'</li>';
            $message .= '<li>FAQ\'s: '.$partnerPubFaqLink.'</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Your account information:';
            $message .= '<ul style="list-style: none;">';
            $message .= '<li>Username: <b>' . $data['email'] . '</b></li>';
            $message .= '<li>Name: <b>' . $data['fname'] . ' ' . $data['lname'] . '</b></li>';
            $message .= '<li>Company: <b>' . $data['company'] . '</b></li>';
            $message .= '<li>Address: <b>' . $data['address1'] . '</b></li>';
            $message .= '<li>Phone: <b>' . $data['phone'] . '</b></li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Thank you for choosing <font color="#2C709E">'.$partnerName.'!&trade;</font>. We look forward to providing you with the most effective mobile advertising solutions and building a successful relationship!</p>';
        } else {
            $message .= "<p style=\"font-size: 20px;\">" . $data['fname'] . ' ' . $data['lname'] . ", you are awesome!</p>";
            $message .= '<p style=\"font-size: 20px;\">Thank you for signing up as a publisher with <font color="#2C709E">'.$partnerName.'!</font> </p>';
            $message .= "<p>Now, lets get this show on the road!</p>";
            $message .= '<p>Lets get you earning money and displaying <font color="#2C709E">'.$partnerName.'!&trade;</font> ads!:';
            $message .= '<ul style="list-style: decimal;">';
            $message .= '<li>Log-in to your account: '.$partnerUrl.'</li>';
            $message .= '<li>Add site(s) or app(s) to your account.</li>';
            $message .= '<li>Get code. We provide you with the sample code needed to display ads.  You simply integrate it into your site/app.</li>';
            $message .= '<li>Site approval. In 1-2 days you will receive an email confirming if your site has been approved.  Once approved, your site will be ready to display ads!</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>How to make the most money from your mobile ad inventory with <font color="#2C709E">'.$partnerName.'!&trade;</font>';
            $message .= '<ul style="list-style: decimal;">';
            $message .= '<li>Standard Banners</li>';
            $message .= '<li>Rich Media</li>';
            $message .= '<li>Video</li>';
            $message .= '<li>Offer Walls</li>';
            $message .= '<li>Full Page Interstitials</li>';
            $message .= '<li>AdPrompts&trade;</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Here are some helpful resources to help you get started:';
            $message .= '<ul style="list-style: decimal;">';
            $message .= '<li>Publisher Help Portal: '.$partnerPubHelpLink.'</li>';
            $message .= '<li>Setting up your Sites & Zones: '.$partnerPubSettLink.'</li>';
            $message .= '<li>FAQ\'s: '.$partnerPubFaqLink.'</li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Your account information:';
            $message .= '<ul style="list-style: none;">';
            $message .= '<li>Username: <b>' . $data['email'] . '</b></li>';
            $message .= '<li>Name: <b>' . $data['fname'] . ' ' . $data['lname'] . '</b></li>';
            $message .= '<li>Company: <b>' . @$data['company'] . '</b></li>';
            $message .= '<li>Address: <b>' . @$data['address1'] . '</b></li>';
            $message .= '<li>Phone: <b>' . @$data['phone'] . '</b></li>';
            $message .= '</ul>';
            $message .= "</p>";
            $message .= '<p>Thank you for choosing <font color="#2C709E">'.$partnerName.'!&trade;</font>. We look forward to providing you with the most effective mobile publishing solutions, and building a successful relationship.</p>';
        }
        $message .= "<p>In the mean time, if you have any questions, feel free to write to us at ".$partnerSupportUrl.", or call ".$partnerPhone."</p>";
        $message .= "<br />";
        $message .= "Sincerely,<br />";
        $message .= "Team ".$partnerName."!&trade;<br />";
        $message .= "P: ".$partnerPhone."<br />";
        $message .= "E: ".$partnerSupportUrl;
        $message .= "</body></html>";
        $message = rtrim($message);

        $fromEmail = $partnerSupportUrl ;
        $fromName = $partnerName.' Support Team';

        $response = App_Email::send($data['email'], $subject, $message, null, $fromEmail, $fromName);
    }
    
    public function sendUserDetailToManager(&$user, $accManager){
                        
        $manager = new Model_User();
        $manager->id = $accManager;
        $manager->find();
        
        $partnerDetail = App_Partner::singleton()->partner;
        if($partnerDetail['id']){            
            $partnerName        = $partnerDetail['label'];
            $partnerUrl         = $partnerDetail['url'];
            $partnerSupportUrl  = $partnerDetail['support_url'];
        }
        unset($partnerDetail);    
        
        $subject = $user->email.' has just Registered with '.$partnerName.'!';
        $fromEmail = $partnerSupportUrl;
        $fromName  = $partnerName.' Support Team';
        
        $message = null;
        $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
        $message .= "<p>Hey {$manager->fname},</p>";
        $message .= "<p>{$user->fname} {$user->lname} just registered with ".$partnerName."! with the following information:</p>";
        $message .= "<p>";
        $message .= "Name: {$user->fname} {$user->lname}<br />";
        $message .= "Email: {$user->email}<br />";
        $message .= "Company: {$user->company}<br />";
        
        $country = new Model_Country();
        $country->id = $user->country_id;
        $country->find();
        $message .= "Country: {$country->name}<br />";
        
        $msgTypeId = $user->social_app_type_id;
        if ( !$msgTypeId ) {
            $msgTypeId = 1;
        }
                        
        $messengerList = Model_User::socialAppTypesList();
                       
        $msgType = $messengerList[$msgTypeId];
        
        $message .= "Messenger service: {$msgType}<br />";
        $message .= "Username: {$user->social_app_username}<br />";
        $message .= "They signed up from a location in: {$user->registered_country}<br />";
        $message .= "</p>";
        $message .= "<p>Please reach out immediately and get them going!</p>";
        $message .= "<p>Love,<br />".$partnerName."!</p>";                
        
        $response = App_Email::send($manager->email, $subject, $message, null, $fromEmail, $fromName);        
    }
    
    public function sendFundUpdateEmail(&$user, &$trans, $admin, $action){
        
        $adminDetail = new Model_User();
        $adminDetail->id = $admin;
        $adminDetail->find();
        
        $partnerAccountEmail = 'accounting@tapit.com';
        
        $partnerDetail = App_Partner::singleton()->partner;
        if($partnerDetail['id']){            
            $partnerAccountEmail  = $partnerDetail['account_url'];
        }
        unset($partnerDetail);    
        
        if($action == 'added'){
            $actionText = 'added to';
            $actionSubject = 'added to';
        }else{
            $actionText = 'removed from';
            $actionSubject = 'withdrawn from';
        }
     
        $subject = 'Alert: User '.$user->id.' had funds '.$actionSubject.' their account.';
        $fromEmail = $adminDetail->email;
        $fromName  = $adminDetail->fname.' '.$adminDetail->lname;
                      
        $message = null;
        $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
        $message .= "<p>Hey there,</p>";
        $message .= "<p>At ".date("h:i a")." today, $".abs($trans->amount)." was ".$actionText.":</p>";
        $message .= '<p>Account ID: <a href="'.BASE_URL.'/account/info/id/'.$user->id.'">'.$user->id."</a><br />";
        $message .= "<p>Account Name: ".$user->fname.' '.$user->lname."<br />";
        $message .= "<p>Email Address: ".$user->email."</p>";
        $message .= "<p>With the note:".$trans->note."</p>";
        $message .= "<p>Thanks</p>";                
       
        $response = App_Email::send($partnerAccountEmail, $subject, $message, null, $fromEmail, $fromName);        
    }    
    
    public function sendW8W9Form(&$user, $fileName) {         
        $partnerDetail = App_Partner::singleton()->partner;
        if($partnerDetail['id']){     
            $partnerAccountEmail  = $partnerDetail['account_url'];
        }
        
        $fileDir = PUBLIC_PATH . '/media/w8_w9/' . $user->id . '/';
        $filePath = $fileDir . $fileName;
        
        $formType = 'W8';
        if($user->country_id == 229) {
            $formType = 'W9';
        }

        $subject = $formType.' Form for User Id - '.$user->id.'!';
        $fromEmail = $user->email;
        $fromName  = $user->fname.' '.$user->lname;
        
        $message = null;
        $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
        $message .= "<p>Hey,</p>";
        $message .= "<p>I have attached the ".$formType." Form required. Kindly let me know in case of any concerns.</p>";
        $message .= "<p>My User Information:</p>";
        $message .= "Name: {$user->fname} {$user->lname}<br />";
        $message .= "Id: {$user->id}<br />";
        $message .= "Email: {$user->email}<br />";
        $message .= "Company: {$user->company}<br />";
        
        $country = new Model_Country();
        $country->id = $user->country_id;
        $country->find();
        $message .= "Country: {$country->name}<br />";        
        $message .= "</p>";  
        $message .= "<p>Thanks,<br />".$fromName."</p>";  
                
        $response = App_Email::send($partnerAccountEmail, $subject, $message, null, $fromEmail, $fromName, $filePath, $fileName);  
       
        $files = glob($fileDir . '*', GLOB_MARK);
        foreach ($files as $file) {
            unlink($file);
        }
        if (is_dir($fileDir)) {
            rmdir($fileDir);
        }
        
        return $response;
    }
    
    public static function accessDenied($isModal = false) {
        if ($isModal) {
            $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
            $view->headScript()->appendFile('/js/access.js');
            echo "<script type=\"text/javascript\">accessDenied.isPermissionDenied = true;</script>";
        } else {
            $r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $r->gotoUrl('/admin/users/access-denied')->redirectAndExit();
        }
    }
    
    public static function allowHigherAccess($userId = null, $email = null, $roleTypeIds = array()) {
        $allow = false;
        if (empty($userId) && empty($email)) {
            return $allow;
        }
        
        if ($userId) {
            $user = new Model_User();
            $user->id = $userId;
            if (!$user->find()) {
                return $allow;
            }
            if (!$email) {
                $email = $user->email;
            }
        }
        
        //do check for higher roles, other wise allow user
        if (is_array($roleTypeIds) && !empty($roleTypeIds)) {
            $isHigherAuthority = false;
            foreach ($roleTypeIds as $roleId) {
                if (in_array($roleId, array(3, 4, 5, 6))) {
                    $isHigherAuthority = true;
                    break;
                }
            }
            if (!$isHigherAuthority) {
                return true;
            }
        }
        
        $emailParts = explode('@', $email);
        $userEmailDomain = array_pop($emailParts);
        if (strpos($userEmailDomain, '.') !== false ) {
            $emailParts = explode('.', $userEmailDomain);
            $userEmailDomain = current($emailParts).'.';
        }
        
        $partners = Tapit_PseudoConstant::partners();
        foreach ($partners as $partner) {
            if (strpos($partner['url'], $userEmailDomain) !== false) {
                $allow = true;
                break;
            }
        }
        
        //gross hack for cybage team.
        if (!$allow && in_array($userEmailDomain, array('tapit.', 'cybage.', 'coxds.', 'phunware.', 'snakkmedia.'))) {
            $allow = true;
        }
        
        return $allow;
    }
    
    public static function searchCacheKey() {
        $cacheKey = 'user_specific';
        if (App_User::isAdvertiserManager()) {
            $cacheKey = 'partner_specific_advertisers';
        }
        if (App_User::isPublisherManager()) {
            $cacheKey = 'partner_specific_publishers';
        }
        if (App_User::isPublisherAdvertiserManager()) {
            $cacheKey = 'partner_specific_advertisers_and_publishers';
        }
        if (App_User::isAdmin()) {
            $cacheKey = 'partner_specific_all';
        }
        if (App_User::isSuperAdmin()) {
            $cacheKey = 'all';
        }
        
        return $cacheKey;
    }
    
   
    public function completeUserRegistration($data, $user_id) {
        $update = true;
        $requiredFields = array('role_type_id', 'fname', 'lname', 'email', 'company', 'title', 'password', 'confirm_password', 'zip', 'phone', 'i_gree');
        foreach ($requiredFields as $fld) {
            if (empty($data [$fld])) {
                $update = false;
                break;
            }
        }
        if ($data ['password'] != $data ['confirm_password']) {
            $update = false;
        }

        if (!$update) {
            return;
        }

        $user = new Model_User ();
        $user->id = $user_id;
        if (!$user->find()) {
            return;
        }

        //send welcome email for google registrations
        $justCompleted = false;
        if (!$user->completed) {
            $justCompleted = true;
        }
        
        //check for main user, if its a child user dont send mails
        $mainAccountId = $user->accountId;
        
        $data ['role_type_ids'] = explode('|', $data ['role_type_id']);
        if (!is_array($data ['role_type_ids'])) {
            if (!empty($data ['role_type_ids'])) {
                $data ['role_type_ids'] = array($data ['role_type_ids']);
            } else {
                $data ['role_type_ids'] = array();
            }
        }
        $data ['completed'] = true;

        //don't update roles for child account
        if ($mainAccountId) {
            foreach ($data as $fld => $val) {
                if ($fld == 'role_type_id' || $fld == 'role_type_ids') {
                    unset($data[$fld]);
                }
            }
        }
        
        $user->update($data, true);

        //reset account managers those were already set
        if ($justCompleted && !$mainAccountId) {
            //send welcome email
            App_User::sendWelcomeEmailToUser($user->toArray());

            if (in_array(2, $data['role_type_ids'])) {
                $userAccManagers = Model_UserAccountManager::getUserAccountManagers($user->id);
                foreach ($userAccManagers as $roleId => $manager) {
                    $userAccManager = new Model_UserAccountManager ();
                    $userAccManager->id = $manager ['id'];
                    $userAccManager->delete();
                }
            }

            //send manager email
            if (strpos($user->email, '@tapit.com') === false) {
                //get the account managers.
                $accManagerIds = $user->accountManagerIds($user->roleTypeIds, $user->partnerId);
                foreach ($accManagerIds as $roleId => $managerId) {
                    //send account managers mail except publishers
                    if ($roleId != 3) {
                        $accManager = new Model_UserAccountManager ();
                        $accManager->user_id = $user->id;
                        $accManager->manager_id = $managerId;
                        $accManager->role_type_id = $roleId;
                        $accManager->save();
                    }
                }
                App_User::sendManagerDetailsToUser($user->id);
            }

            //mark advertiser default as 'Self-Service'
            if (in_array(2, $user->roleTypeIds)) {
                Model_UserAdvertiserType::updateAdvertiserType($user->id, 2);

                //make default mailing all mailing options.
                Model_UserEmailPreferences::setDefaultMailingPreferences($user);
            }
        }
    }
    
    public static function isValidAjaxCall($request, $operation) {
        $valid = false;
        if (empty($request) || empty($operation)) {
            return $valid;
        }

        $userId = $request->getParam('user_id', '');
        $mainUserId = $request->getParam('main_user_id', '');
        $loggedInUserId = $request->getParam('logged_in_user_id', '');
        //$secreteKey = $request->getParam('secrete_key', '');

        if (empty($mainUserId) || empty($loggedInUserId) || empty($operation)) {
            return $valid;
        }

        $operations = array(
            'advertiser-update-campaign-status' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'advertiser-rename-campaign' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'ajax-update-campaign-daily-budget' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'advertiser-update-bid' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'advertiser-copy-campaign' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'advertiser-update-creative-status' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'advertiser-rename-creative' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'advertiser-copy-creatives' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'ajax-update-creatives-weight' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'publisher-dashboard-update-site-status' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'ajax-add-app-unit' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'ajax-update-app' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'publisher-save-mediation' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'publisher-house-ad-status' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'publisher-house-ad' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
            'ajax-update-app-unit' => array(self::FULL_ACCESS, self::CHANGE_STATUS),
        );

        if (!array_key_exists($operation, $operations)) {
            return $valid;
        }

        //get the main user roles.
        $mainUserAccessRules = Model_UserAccessRule::getRules($mainUserId);

        foreach ($operations[$operation] as $per) {
            if (in_array($per, $mainUserAccessRules)) {
                $valid = true;
                break;
            }
        }

        return $valid;
    }
    
    public static function allowChildUser($userId = null) {
        $allow = false;
        if ($userId) {
            $userRoles = Model_UserRole::getUserRoles($userId);
            if (!in_array(3, $userRoles) && !in_array(4, $userRoles) && !in_array(5, $userRoles) && !in_array(6, $userRoles)) {
                $allow = true;
            }
        }

        return $allow;
    }
    
    
    public static function makeStackMobUser($request = null) {
        $isStackMobUser = 0;
        if (empty($request)) {
            return $stackMobUser;
        }
               
        if ($request->getParam('s')) {
            $urlParts = explode('=', base64_decode($request->getParam('s')));
            
            if ( in_array('is_stackmob', $urlParts ) && in_array(1, $urlParts ) ) { 
                $isStackMobUser = 1;
            }
                        
        }

        return $isStackMobUser;
    }
}