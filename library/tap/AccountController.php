<?php

require(LIBRARY_PATH . '/Stripe/lib/Stripe.php');

class AccountController extends Zend_Controller_Action {

    public function init() {
        parent::init();
    }

    public function adminFundAccountAction() {
        $request = $this->getRequest();

        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }

        // we don't need a layout for this action
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $result = array( 'success' => false );

        // restrict to admin, configs/acl.ini does this too, it might be
        // overkill but adding funds is a pretty big part of the
        // system
        if (App_User::isAdmin()) {
            $request = $this->getRequest();

            if (isset($request->user_id) && isset($request->amount)) {
                // fetch user to verify the id passed is a real account
                $user = new Model_User();
                $user->id = $request->user_id;
                $user->find();

                $balance = (float) $user->balance;
                $amount = (float) $request->amount;
                $transactionTypeId = 9;
                $action = 'added';
                if ($request->getParam('subtract') == true) {
                    $amount = -$amount;
                    $transactionTypeId = 10;
                    $action = 'withdrawn';
                }
                
                // user appears to be valid
                if (is_numeric($user->id)) {
                    $tran = new Model_Transaction();
                    $tran->userId = $user->id;
                    $tran->transactionTypeId = $transactionTypeId; // admin funding
                    $tran->statusId = 3; // approved
                    $tran->amount = $amount;
                    $tran->addedById = App_User::getUserId();
                    $tran->note = $request->note;

                    // save transaction
                    if ($tran->save()) {
                        // update user balance
                        $user->balance = $amount + $balance;
                        if ($user->low_budget_notified == '1' && $user->balance >= 50) {
                            $user->low_budget_notified = 0;
                        }
                        $user->update();
                        $result['success'] = true;
                        $result['balance'] = $user->balance;
                        
                        App_User::sendFundUpdateEmail($user, $tran, App_User::getLoggedUser()->id, $action);
                    }
                }
            }
        }
        
        echo json_encode($result);
        exit(0);
    }

    public function infoAction() {        
        $request = $this->getRequest();
       
        $session = new Zend_Session_Namespace('access');
        $noAccess = null;
        if(isset($session->noAccess)){
            $noAccess = $session->noAccess;
        }
        if (!$noAccess && (isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'], '/publisher/dashboard/id') !== FALSE || strpos($_SERVER['HTTP_REFERER'], '/advertiser/dashboard/id') !== FALSE || strpos($_SERVER['HTTP_REFERER'], '/account/summary/id') !== FALSE) && !isset($request->id))) {
            $iId = array_pop(explode('/', $_SERVER['HTTP_REFERER']));
            $this->_redirect('/account/info/id/' . $iId);
        } elseif (!$noAccess && (isset($_SESSION['view_user']['viewUserId']) && $_SESSION['view_user']['viewUserId'] != '' && !isset($request->id))) {
            $session = new Zend_Session_Namespace('view_user');
            if(isset($session->viewUserId)){
                $viewUserId = $session->viewUserId;
                unset($session->viewUserId);
            }
            unset($session);
            $this->_redirect('/account/info/id/' . $viewUserId);
        }
        
        if(isset($session->noAccess)){
            unset($session->noAccess);
        }
        unset($session);
        
        $user = new Model_User; 
        $isAdminUser = App_User::isAdmin();              
        $isLoggedUser = 1;
        if (isset($request->id)) {
            if (!App_User::allow($request->id, false, false, 'account-info')) {
                 //App_User::accessDenied();
                 $this->_redirect('/');
            }
            $user->id = $request->id;
            $isLoggedUser = 0;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }

        $user->find();
        $apiTokenVal = $user->apiToken;
        //in case of wrong user id.
        if (!$user->id) {
            $user = App_User::getLoggedUser()->id;
            $user->find();
        }
        $accountRoleTypeIds = $user->roleTypeIds;    
        
        //build account info form
        $accountInfoForm = new Default_Form_EditProfile(array('profileID' => $user->id, 'role_type_ids' => $user->roleTypeIds, 'partnerId' => $user->partner_id));
        $accountInfoForm->setStateParams($request);
        Model_UserAccessRule::filterForm($accountInfoForm, $user->id);
        
        //build change password form
        $changePasswordForm = new Default_Form_ChangePassword();
        Model_UserAccessRule::filterForm($changePasswordForm, $user->id);
        
        //build email prereference form
        $emailPreferencesForm = new Default_Form_EmailPreferences(); 
        Model_UserAccessRule::filterForm($emailPreferencesForm, $user->id);
        
        //build admin details form                             
        if ($isAdminUser) {            
            $adminDetailsForm = new Default_Form_AdminDetails(array('profileID' => $user->id,'user_role' => $user->roleTypeIds,'publisher_type_id'=>$user->publisher_type_id,'user_partner_id'=>$user->partner_id ));
            Model_UserAccessRule::filterForm($adminDetailsForm, $user->id);
        }

        //build payment detaisl form
        $paymentDetailsForm = new Default_Form_PaymentDetails(array('profileID' => $user->id));
        Model_UserAccessRule::filterForm($paymentDetailsForm, $user->id);
        $paymentDetailsForm->balance->setAttrib('data-id', $user->id);
        $paymentDetailsForm->addDisplayGroup(array('paypalLogin'), 'paypal');
        $paymentDetailsForm->addDisplayGroup(array('paymentDetail'), 'paymentgroup');
        $paymentDetailsForm->addDisplayGroup(array('beneficiaryName', 'bankName', 'bankAddress', 'iban', 'ach'), 'pay');
        $paymentDetailsForm->addDisplayGroup(array('account_name', 'dd_bank_name', 'routing_number', 'account_number'), 'direct_deposit');
        $paymentDetailsForm->addDisplayGroup(array('paymentTo', 'bankStreetAddress', 'bankCity', 'bankState', 'bankZip'), 'check');

        if ($isAdminUser) {
            $paymentDetailsForm->addDisplayGroup(array('credit_terms', 'balance', 'country', 'type', 'businessName', 'usTin'), 'main');
        } else {
            $paymentDetailsForm->addDisplayGroup(array('balance', 'country', 'type', 'businessName', 'usTin'), 'main');
        }
        $userData = array('user_id' => $user->id);
        $paymentInfoTax = new Model_PaymentInfoTax($userData);
        $paymentInfoWire = new Model_PaymentInfoWire($userData);
        $paymentInfoPayPal = new Model_PaymentInfoPaypal($userData);
        $paymentInfoCheck = new Model_PaymentInfoCheck($userData);
        $paymentInfoDirectDeposit = new Model_PaymentInfoDirectDeposit($userData);

        //do check for forms submitted.
        $accountInfoFormSubmitted = (bool) $request->getParam('submit_register_form', false);
        $changePasswordFormSubmitted = (bool) $request->getParam('changePassword', false);
        $emailPrefFormSubmitted = (bool) $request->getParam('save_email_preferences', false);
        $adminDetailsFormSubmitted = (bool) $request->getParam('save_admin_details', false);
        $paymentFormSubmitted = (bool) $request->getParam('submit_payment_details', false);
      
        $notifyAddressTax = false;
        $notifyW8W9 = false;   
        $isUsTinRequired = false;

        if ($request->isPost()) {
            //process payment form.

            $paymentMethod = $request->getParam('paymentDetail', false);
            if ($paymentFormSubmitted && $paymentMethod == 1) {
                $paymentDetailsForm->removeWireElementValidators();
                $paymentDetailsForm->removeCheckElementValidators();
                $paymentDetailsForm->removeDirectDepositElementValidators();
            } else if ($paymentFormSubmitted && $paymentMethod == 2) {
                $paymentDetailsForm->removeWireElementValidators();
                $paymentDetailsForm->removePayPalElementValidators();
                $paymentDetailsForm->removeDirectDepositElementValidators();
            } else if ($paymentFormSubmitted && $paymentMethod == 3) {
                $paymentDetailsForm->removeWireElementValidators();
                $paymentDetailsForm->removePayPalElementValidators();
                $paymentDetailsForm->removeCheckElementValidators();
            } else {
                $paymentDetailsForm->removePayPalElementValidators();
                $paymentDetailsForm->removeCheckElementValidators();
                $paymentDetailsForm->removeDirectDepositElementValidators();
            }

            if ($paymentFormSubmitted && $paymentDetailsForm->isValid($request->getPost())) {
                //send notification to admin if publisher update/save payment info                                                                          
                if (in_array('1', $user->roleTypeIds) || in_array('3', $user->roleTypeIds)) {
                    $this->setNotification($user->id);
                }
                $this->paymentForm($user->id);
                //we are not going to process the balance, it is disabled
                $paymentDetailsForm->balance->setValue(number_format($user->balance, 2));
                
                //check if W8/W9 form uploaded
                $irsUploaded = $request->getParam('hid_irs');
                if ($paymentFormSubmitted && !empty($irsUploaded)) {
                    App_User::sendW8W9Form($user, $irsUploaded);
                    $user->w8w9_email_sent = 1;
                    $user->update();
                }
                
                //reload the page.
                $this->_reloadAccountInfo($user->id);
            }

            //process account info form
            if ($accountInfoFormSubmitted) {

                //valid roles.
                $formData = $request->getPost();
                
                //validate form
                $roleTypeIds = array();
                if (isset($formData['role_type_ids'])) {
                    $roleTypeIds = $formData['role_type_ids'];
                }
                $isEmailValidForHigherAuthority = App_User::allowHigherAccess(null, $formData['email'], $roleTypeIds);

                //set the email custom error message
                $isAccountInfoFormDataValid = true;
                if (!$isEmailValidForHigherAuthority && $formData['email']) {
                    $accountInfoForm->email->setErrors(array('invalidRoleTypeIds' => "Email address is not valid to becomes higher authority user."));
                    $accountInfoForm->populate($formData);
                } else {
                    $isAccountInfoFormDataValid = $accountInfoForm->isValid($formData);
                }
                                
                if ($isEmailValidForHigherAuthority && $isAccountInfoFormDataValid) {
                    $data = $accountInfoForm->getValues();
                    $accountInfoForm->api_token->setValue($apiTokenVal);
                    
                    $roleChanged = "";
                    $advertiser = false;
                    $publisher = false;
                    
                    //check previous role
                    foreach ($accountRoleTypeIds as $userRoleTypeId) {
                        if (in_array($userRoleTypeId, array(1, 3))) {
                            $publisher = true;                           
                        }
                        if (in_array($userRoleTypeId, array(2, 4))) {
                            $advertiser = true;                            
                        }
                    }
                   
                    //get current role
                   if (!($advertiser == "true" && $publisher == "true")) {
                        foreach ($data['role_type_ids'] as $roleTypeId) {
                            if (in_array($roleTypeId, array(1, 3)) && $advertiser == true) {                                
                                $roleChanged = 1;
                            }
                            if (in_array($roleTypeId, array(2, 4)) && $publisher == true) {                              
                                $roleChanged = 2;
                            }
                        }
                    }
                    
                    $data['api_token'] = $apiTokenVal;
                    if(isset($data['last_login_date'])){
                        unset($data['last_login_date']);
                    }
                    $user->setOptions($data);                   
                    $user->update(null, true);
                    
                    $accountRoleTypeIds = $user->roleTypeIds;
                    
                    if($roleChanged) {
                        Model_UserEmailPreferences::setDefaultPreferences($user, $roleChanged);
                    }

                    //Set default advertiser type on role change.
                    //mark advertiser default as 'Self-Service'
                    if (in_array(2, $user->roleTypeIds) || in_array(4, $user->roleTypeIds) || in_array(5, $user->roleTypeIds) || in_array(6, $user->roleTypeIds)) {
                        Model_UserAdvertiserType::setDefaultAdvertiserType($user->id, 2);
                    }

                    //Delete advertiser type on role change from advertiser to other.
                    if ( !(in_array(2, $user->roleTypeIds) || in_array(4, $user->roleTypeIds)) && !(in_array(5, $user->roleTypeIds) || in_array(6, $user->roleTypeIds))) {
                        //mark advertiser default as 'Self-Service'
                        if (!in_array(2, $user->roleTypeIds) && !in_array(4, $user->roleTypeIds)) {
                            Model_UserAdvertiserType::deleteAdvertiserType($user->id);
                        }
                    }
                    
                    //reload the page.
                    $this->_reloadAccountInfo($user->id);
                } else {
                    $accountRoleTypeIds = $accountInfoForm->getValue('role_type_ids');
                }
            }

            //process password form
            if ($changePasswordFormSubmitted && $changePasswordForm->isValid($request->getPost())) {
                $id = $user->id;
                $model = new Model_User(array('id' => $id));
                $model->find();
                $model->password = md5($changePasswordForm->getValue('password'));
                $model->update();
            }

            //process email preferences form.
            if ($emailPrefFormSubmitted && $emailPreferencesForm->isValid($request->getPost())) {
                $userEmailPref = $emailPreferencesForm->getValues();
                $prefValues = array();
                foreach (array('advertiser_email_preferences', 'publisher_email_preferences') as $prefKey) {
                    if (!empty($userEmailPref[$prefKey])) {
                        $prefValues = array_merge($prefValues, $userEmailPref[$prefKey]);
                    }
                }
                $prefValues = array_unique($prefValues);
                Model_UserEmailPreferences::updatePreferences($user->id, $prefValues);
            }

            //process admin details form
            if ($adminDetailsFormSubmitted && $adminDetailsForm->isValid($request->getPost())) {
                $adminDetailValues = $adminDetailsForm->getValues();

                //first check for current assigned managers.
                $managers = array();
                $userManagers = new Model_UserAccountManager();
                $userManagers = $userManagers->fetch(array('where' => array("user_id = {$user->id}")));
                if (count($userManagers)) {
                    foreach ($userManagers->toArray() as $manager) {
                        $managers[$manager['role_type_id']] = $manager;
                    }
                }
                //update advertiser account manager id.
                if (isset($adminDetailValues['advertiser_account_manager_id'])) {
                    //update only if changed.
                    if ( array_key_exists( 4, $managers ) ) {
                        $manager = $managers[4];
                        if ( $adminDetailValues['advertiser_account_manager_id'] != $manager['manager_id'] ) {
                            Model_UserAccountManager::updateUserAccountManager($user->id, 4, $adminDetailValues['advertiser_account_manager_id']);
                            App_User::sendManagerDetailsToUser( $user->id, false, true, false );
                            //App_User::sendUserDetailToManager( $user,$adminDetailValues['advertiser_account_manager_id'] );    
                        }
                    } else {
                        Model_UserAccountManager::updateUserAccountManager($user->id, 4, $adminDetailValues['advertiser_account_manager_id']);
                        App_User::sendManagerDetailsToUser( $user->id, false, true );
                        //App_User::sendUserDetailToManager( $user,$adminDetailValues['advertiser_account_manager_id'] );    
                    }
                }
                //update publisher account manager id.
                if (isset($adminDetailValues['publisher_account_manager_id'])) {
                    //update only if changed.
                    if ( array_key_exists( 3, $managers ) ) {
                        $manager = $managers[3];
                        if ( $adminDetailValues['publisher_account_manager_id'] != $manager['manager_id'] ) {
                            Model_UserAccountManager::updateUserAccountManager($user->id, 3, $adminDetailValues['publisher_account_manager_id']);
                            App_User::sendManagerDetailsToUser( $user->id, true );
                            //App_User::sendUserDetailToManager( $user,$adminDetailValues['publisher_account_manager_id'] );    
                        }
                    } else {
                        Model_UserAccountManager::updateUserAccountManager($user->id, 3, $adminDetailValues['publisher_account_manager_id']);
                        App_User::sendManagerDetailsToUser( $user->id, true );
                        //App_User::sendUserDetailToManager( $user,$adminDetailValues['publisher_account_manager_id'] );    
                    }
                }

                //update advertiser type.
                if (isset($adminDetailValues['advertiser_type_id'])) {
                    Model_UserAdvertiserType::updateAdvertiserType($user->id, $adminDetailValues['advertiser_type_id']);
                }
                if ( !empty($adminDetailValues['publisher_type_id']) ) {
                    $id = $user->id;
                    $modelUserDirect = new Model_User(array('id' => $id));
                    $modelUserDirect->find();
                    $modelUserDirect->publisher_type_id = trim($adminDetailValues['publisher_type_id']);
                    $modelUserDirect->update();
                }
                /*if (isset($adminDetailValues['publisher_type_id'])) {
                    /*$id = $user->id;
                    $modelUserPublisherType = new Model_UserPublisherType(array('id' => $id));
                    $modelUserPublisherType->find();
                    $modelUserPublisherType->publisher_type_id = trim($adminDetailValues['publisher_type_id']);
                    $modelUserPublisherType->update();
                    Model_UserPublisherType::updatePublisherType($user->id, $adminDetailValues['publisher_type_id']);
                }
                */
                //update Revenue share
                if( isset($adminDetailValues['revenue_share_id']) ){
                    $id = $user->id;
                    $revshare = trim($adminDetailValues['revenue_share_id']);
                    $modelUserRevenue = new Model_User(array('id' => $id));
                    $modelUserRevenue->find();
                    $modelUserRevenue->revshare = $revshare;                                                   
                    $modelUserRevenue->update(); 
                    
                    //update sites also
                    $site = new Model_Site();
                    $site->massUpdateRevshare($user->id, $revshare);
                }                
                
                //update payment terms                
                if(isset($adminDetailValues['payment_term_id'])) {                                                           
                    $id = $user->id;
                    $model = new Model_User(array('id' => $id));
                    $model->find();
                    $model->payment_term_id = $adminDetailValues['payment_term_id'];                                                            
                    $model->update();
                }  
                
                //partner inventory
                if (array_key_exists('include_partner_ids', $adminDetailValues)) {
                    $submittedPartners = array();
                    if (is_array($adminDetailValues['include_partner_ids'])) {
                        $submittedPartners = $adminDetailValues['include_partner_ids'];
                    }
                    $allPartners = Tapit_PseudoConstant::partners();
                    $excPartner = new Model_PublisherExcludePartner();
                    $excPartner->userId = $user->id;
                    $excPartner->removePartnerInArray($submittedPartners);
                    $isPublisher = false;
                    if (in_array(1, $user->roleTypeIds) || in_array(3, $user->roleTypeIds) ||
                            in_array(5, $user->roleTypeIds) || in_array(6, $user->roleTypeIds)) {
                        $isPublisher = true;
                    }
                    if ($isPublisher && (count($submittedPartners) != count($allPartners))) {
                        $excludePartners = array();
                        foreach ($allPartners as $id => $partner) {
                            if (in_array($id, $submittedPartners)) {
                                continue;
                            }
                            $excludePartners[$id] = $id;
                        }
                        if (!empty($excludePartners)) {
                            $excPartner->addPartners($excludePartners);
                        }
                    }
                }
                
            }
            
        } 

        //set defaults values.
        $populateValues = $user->toArray();
                         
        
        //set default account info form
        if (!$accountInfoFormSubmitted) {

            if (!$populateValues['country_id']) {
                $populateValues['country_id'] = 229;
            }

            if( $isAdminUser ) {
                $dateRegistered = date("m/d/y", strtotime($populateValues['created']));
                $populateValues['date_register'] = $dateRegistered;
                $lastLogin = date("m/d/y", strtotime($populateValues['last_login_date']));
                $populateValues['last_login_date'] = $lastLogin;
            }
            
            $accountInfoForm->populate($populateValues);
        }

        //set default change password form 
        if (!$changePasswordFormSubmitted) {
            $changePasswordForm->populate($populateValues);
        }

        //set default email preferences form
        if (!$emailPrefFormSubmitted) {
            $userEmailPreferences = new Model_UserEmailPreferences(false);
            $userEmailPreferences = $userEmailPreferences->fetch(array('where' => array("user_id = {$user->id}")));
            $emailPrefValues = array();
            if (count($userEmailPreferences)) {
                foreach ($userEmailPreferences as $pref) {
                    $emailPrefValues['advertiser_email_preferences'][$pref['email_preferences_id']] = $pref['email_preferences_id'];
                    $emailPrefValues['publisher_email_preferences'][$pref['email_preferences_id']] = $pref['email_preferences_id'];
                }
            }

            $emailPreferencesForm->populate($emailPrefValues);
        }

        //set default admin detials form.
        if ($isAdminUser && !$adminDetailsFormSubmitted) {                                   
            $adminDetailValues = array();
            $buildAdvertiserType = false;
            $buildPublisherType  = false;
            $buildPaymentTerm = false;
            $userRoleTypeIds = $user->role_type_ids;
            foreach ($userRoleTypeIds as $userRoleTypeId) {
                if (in_array($userRoleTypeId, array(2, 4, 5, 6))) {
                    $buildAdvertiserType = true;
                    //break;
                }
                if (in_array($userRoleTypeId, array(1, 3, 5, 6))) {
                    $buildPaymentTerm = true;
                }
            }
            if ($buildAdvertiserType) {
                $userAdvType = new Model_UserAdvertiserType(false);
                $userAdvType = $userAdvType->fetch(array('where' => array("user_id = {$user->id}")));
                if (count($userAdvType)) {
                    $adminDetailValues['advertiser_type_id'] = $userAdvType->current()->advertiser_type_id;
                }
            }
            foreach ($userRoleTypeIds as $userRoleTypeId) {
                if (in_array($userRoleTypeId, array(1, 3, 5, 6))) {
                    $buildPublisherType = true;
                    break;
                }
            }
           
            if ($buildPublisherType) {
                    $adminDetailValues['publisher_type_id'] = $user->publisher_type_id;
            }
            //get the user managers.
            $userAccManagers = Model_UserAccountManager::getUserAccountManagers($user->id);
            foreach ($userAccManagers as $manager) {
                if ($manager['role_type_id'] == 3) {
                    $adminDetailValues['publisher_account_manager_id'] = $manager['manager_id'];
                }
                if ($manager['role_type_id'] == 4) {
                    $adminDetailValues['advertiser_account_manager_id'] = $manager['manager_id'];
                }
            }
                                   
            
            if($buildPaymentTerm || $buildAdvertiserType){
                $adminDetailValues['payment_term_id'] = $user->payment_term_id;
            }                      
                        
            //set default value for revenue share and populate
            $revenueShareValue = $user->revshare;
            if( empty($revenueShareValue)){ 
                //set default if no value present
                $revenueShareValue = '0.50';
            }           
            $adminDetailValues['revenue_share_id'] = $revenueShareValue;
                        
            //partner inventory
            $allowPartners = array();
            $allPartners = Tapit_PseudoConstant::partners();
            $excludedPartners = Model_PublisherExcludePartner::getPartners($user->id);
            foreach ($allPartners as $id => $partner) {
                if (array_key_exists($id, $excludedPartners)) {
                    continue;
                }
                $allowPartners[$id] = $id;
            }
            $adminDetailValues['include_partner_ids'] = $allowPartners;
            
            $adminDetailsForm->populate($adminDetailValues);                                 
        }
                
        //set default payment details form.
        if (!$paymentFormSubmitted) {
            //format the balance to double digit
            $populateValues['balance'] = number_format($populateValues['balance'], 2);
            $paymentDetailsForm->populate($populateValues);

            if ($paymentInfoTax->initFromField('user_id')) {
                $populateValues += $paymentInfoTax->toArray();
                $populateValues['country'] = $paymentInfoTax->countryId;
                $populateValues['businessName'] = $paymentInfoTax->businessName;
                $populateValues['usTin'] = $paymentInfoTax->usTin;
                $userModel = new Model_User(array('id' => $user->id));
                $userModel->find();
                $populateValues['paymentDetail'] = $userModel->paymentMethod;
                $paymentDetailsForm->populate($populateValues);
            } 

            if ($user->payment_method == '1' && $paymentInfoPayPal->initFromField('user_id')) {
                $populateValues += $paymentInfoPayPal->toArray();
                $populateValues['paypalLogin'] = $paymentInfoPayPal->login;
                $paymentDetailsForm->populate($populateValues);
            }

            if ($user->payment_method == '0' && $paymentInfoWire->initFromField('user_id')) {
                $populateValues += $paymentInfoWire->toArray();
                $populateValues['beneficiaryName'] = $paymentInfoWire->beneficiaryName;
                $populateValues['bankName'] = $paymentInfoWire->bankName;
                $populateValues['bankAddress'] = $paymentInfoWire->bankAddress;
                $paymentDetailsForm->populate($populateValues);
            }

            if ($user->payment_method == '2' && $paymentInfoCheck->initFromField('user_id')) {
                $populateValues += $paymentInfoCheck->toArray();
                $populateValues['paymentTo'] = $paymentInfoCheck->paymentTo;
                $populateValues['bankStreetAddress'] = $paymentInfoCheck->bankStreetAddress;
                $populateValues['bankCity'] = $paymentInfoCheck->bankCity;
                $populateValues['bankState'] = $paymentInfoCheck->bankState;
                $populateValues['bankZip'] = $paymentInfoCheck->bankZip;
                $paymentDetailsForm->populate($populateValues);
            }
            
            if ($user->payment_method == '3' && $paymentInfoDirectDeposit->initFromField('user_id')) {
                $populateValues += $paymentInfoDirectDeposit->toArray();
                $populateValues['account_name'] = $paymentInfoDirectDeposit->account_name;
                $populateValues['dd_bank_name'] = $paymentInfoDirectDeposit->bank_name;
                $populateValues['account_number'] = $paymentInfoDirectDeposit->account_number;
                $populateValues['routing_number'] = $paymentInfoDirectDeposit->routing_number;
                $paymentDetailsForm->populate($populateValues);
            }
        }
        
        //check if IRS W8/W9 form upload is allowed (roles as publisher, publisher account manager or admin)
        $allowIRSUpload = false;
        if (!empty($accountRoleTypeIds)) {
            foreach ($accountRoleTypeIds as $userRoleTypeId) {
                if ($userRoleTypeId == 6 || $userRoleTypeId == 5 || $userRoleTypeId == 3 || $userRoleTypeId == 1) {
                    if (App_User::fullAccess() || App_User::changeStatus() || ($user->id == App_User::getMainUserId())) {
                        $allowIRSUpload = true;
                    }
                }
            }
        }
        
        $irsImport = null;
        $isW8orW9 = 'W8';
        $cntryFlag = false; 
        if ($allowIRSUpload) {
            //If country under Financial Details is blank, check for country from User info
            $cntry = $paymentDetailsForm->getValue('country');
            if ($cntry == '') {
                if ($user->country_id == '229') {
                    $cntryFlag = true;
                }
            }
            if (($cntry == '229') || ($cntryFlag == true)) { // if user is from US
                $paymentDetailsForm->irs_import->setLabel('Upload W9 Form:');
                $isW8orW9 = 'W9';
            }
            $paymentDetailsForm->addDisplayGroup(array('irs_import'), 'irs');
            $irsImport = $paymentDetailsForm->getDisplayGroup('irs');
        }
        
        $isPublisher = false;
        if (!empty($accountRoleTypeIds)) {
            foreach ($accountRoleTypeIds as $userRoleTypeId) {
                if (in_array($userRoleTypeId, array(1, 3, 5, 6))) {
                    $isPublisher = true;
                }
            }
        }
       
        if ($isPublisher) {
            //check for w8-w9 notification
            $publisherEarnings = Tapit_PublisherStats::getPublisherEarnings($user->id, $user->partner_id);
            if ($publisherEarnings > 1 && !$user->w8w9_email_sent) {
                $notifyW8W9 = true;
            }

            //check for address notification
            if ($paymentInfoTax->initFromField('user_id')) {
                if ($paymentInfoTax->countryId) {
                    if ($paymentInfoTax->countryId == '229' && !$paymentInfoTax->usTin) {
                        $isUsTinRequired = true;
                        $notifyAddressTax = true;
                    }
                } else {
                    $notifyAddressTax = true;
                }
            } else {
                $notifyAddressTax = true;
            }
        }
        
        $profileImage = "0";       
        if (file_exists(PUBLIC_PATH . '/profiles/'.$user->id.'/'.$user->id.'.png')) {
            $profileImage = "1";            
        }
        
        $userAccManagers = Model_UserAccountManager::getUserAccountManagers($user->id);
        $this->view->accManager = $userAccManagers;
        
        $this->view->irsImport = $irsImport;
        $this->view->allowISRUpload = $allowIRSUpload;
        
        $partnerId = ($user->partnerId) ? $user->partnerId : 1;
        $this->view->partnerId = $partnerId;
        
        //login history.
        $loginDetails = array();
        if ($isAdminUser) {
            $loginHistory = new Model_LoginHistory();
            $loginDetails = $loginHistory->fetAllLastLogin($user->id);
        }
        $this->view->loginDetails = $loginDetails;
        
        $this->view->paypal = $paymentDetailsForm->getDisplayGroup('paypal');
        $this->view->main = $paymentDetailsForm->getDisplayGroup('main');
        $this->view->paymentgroup = $paymentDetailsForm->getDisplayGroup('paymentgroup');
        $this->view->pay = $paymentDetailsForm->getDisplayGroup('pay');
        $this->view->check = $paymentDetailsForm->getDisplayGroup('check');
        $this->view->directDeposit = $paymentDetailsForm->getDisplayGroup('direct_deposit');

        $this->view->changePasswordForm = $changePasswordForm;
        $this->view->accountInfoForm = $accountInfoForm;
        $this->view->isAdminUser = $isAdminUser;
        $this->view->emailPreferencesForm = $emailPreferencesForm;
        $this->view->paymentDetailsForm = $paymentDetailsForm;
        $this->view->userRoleTypeIds = json_encode($accountRoleTypeIds);
        $this->view->id = $user->id;
        $this->view->w8w9_email_sent = $user->w8w9_email_sent;
        
        //session getting swap
        $token = App_User::getLoggedUser()->getToken();
        if (App_User::isChildUser()) {
            $mainUser = new Model_User();
            $mainUser->id = App_User::getMainUserId();
            $mainUser->find();
            $token = $mainUser->getToken();
        }
        
        $this->view->networkmediation_enabled = $user->networkmediation_enabled;
        if ($isAdminUser) {
            $this->view->adminDetailsForm = $adminDetailsForm;
        }
        
        $mainUserId = App_User::getMainUserId();
        $accessRules = Model_UserAccessRule::getRules($mainUserId);
        
        //array used in javascript
        $pageInfo = array(
            'user_id' => $user->id,
            'main_user_id' => $mainUserId,
            "access_rules" => json_encode($accessRules),
            'user_status' => $user->status_id,
            'is_logged_user' => $isLoggedUser,
            'user_token' => $token,
            'profile_image' => $profileImage,
            'notify_address_tax' => $notifyAddressTax,
            'is_UsTin_required' => $isUsTinRequired,
            'notify_W8W9' => $notifyW8W9,
            'w8orw9' => $isW8orW9);
        $this->view->pageInfo = $pageInfo;
    }

    private function _reloadAccountInfo($userId = null) {
        $request = $this->getRequest();
        //redirect to reload the forms with updated values
        if (isset($request->id) && $userId) {
            $this->_redirect('/account/info/id/' . $userId);
        } else {
            $this->_redirect('/account/info');
        }
    }
    
    
    /*
     * Create new user account, admin only
     */

    public function createAction() {
        //only admin can create a account
        if (!App_User::isAdmin()) {
            $this->_redirect('/');
        }
        
        $request = $this->getRequest();
        $accountForm = new Default_Form_UserAccount();
        Model_UserAccessRule::filterForm($accountForm);
        if (!App_User::fullAccess() && !App_User::changeStatus()) {
            $this->_redirect('/');
        }
        $accountFormSubmitted = (bool) $request->getParam('submit_create_user_form', false);

        $userId = null;
        $isUserCreated = false;
        
        if ($request->isPost()) {
            $formData = $request->getPost();
            $formData['status_id'] = 1;
            
            $isAccountInfoFormDataValid = true;
            //validate form
            $roleTypeIds = array();
            if (isset($formData['role_type_ids'])) {
                $roleTypeIds = $formData['role_type_ids'];
            }
            $isEmailValidForHigherAuthority = App_User::allowHigherAccess(null, $formData['email'], $roleTypeIds);

            //set the email custom error message
            if (!$isEmailValidForHigherAuthority && $formData['email']) {
                $accountForm->email->setErrors(array('invalidRoleTypeIds' => "Email address is not valid to becomes higher authority user."));
                $accountForm->populate($formData);
            } else {
                $isAccountInfoFormDataValid = $accountForm->isValid($formData);
            }

            if ( $isEmailValidForHigherAuthority && $isAccountInfoFormDataValid && $accountFormSubmitted ) {
                $user = new Model_User();
                $user->setOptions($formData);
                $user->password = md5($accountForm->getValue('password'));
                $user->apiToken = md5(serialize($formData));
                $user->completed = 1;
                $user->statusId = 2;
                $user->save();
                $userId = $user->id;
                $isUserCreated = true;                               
                
                //make default mailing all mailing options.
                Model_UserEmailPreferences::setDefaultMailingPreferences($user);

                //mark advertiser default as 'Self-Service'
                if (in_array(2, $user->roleTypeIds) || in_array(4, $user->roleTypeIds)) {
                    Model_UserAdvertiserType::updateAdvertiserType($user->id, 2);
                }
                $accountForm->populate(array('role_type_ids' => array(), 'email' => null));
            }
        }

        $this->view->userId = $userId;
        $this->view->accountForm = $accountForm;
        $this->view->isUserCreated = $isUserCreated;
    }
    
    
    public function multiUserAction() {
        //only admin can create a account
        if (!App_User::isAdmin()) {
            $this->_redirect('/');
        }

        $request = $this->getRequest();
        $userId = App_User::getUserId();
        if (isset($request->id)) {
            $userId = $request->id;
            if (!App_User::allow($request->id)) {
                //App_User::accessDenied();
                $this->_redirect('/');
            }
        }
        $user = new Model_User();
        $user->id = $userId;
        
        //no user found
        //logged in user is child
        //processign user is child
        if (!$user->find() || App_User::isChildUser() || Model_User::getMainAccountId($userId)) {
            $this->_redirect('/');
        }
        
        //do check for valid parent user
        $isValidParentUser = App_User::allowChildUser($userId);
        
        $userAccessRules = new Model_UserAccessRule();
        $userForm = new Default_Form_MultiUser( array('user_id' => $user->id, 'user_email' => $user->email, 'role_type_ids' => $user->roleTypeIds, 'partner_id' => $user->partnerId ) );
        $accountFormSubmitted = (bool) $request->getParam('submit_create_multi_user_form', false);                       

        $childUserId = null;
        $isEmailSend = $isUserCreated = false;
        if ($accountFormSubmitted && $request->isPost()) {
            $formData = $request->getPost();
            
            //validate form 
            $isAccountInfoFormDataValid = true;
            $isEmailValidForHigherAuthority = App_User::allowHigherAccess(null, $formData['email'], $user->roleTypeIds);

            //set the email custom error message
            if (!$isEmailValidForHigherAuthority && $formData['email']) {
                $userForm->email->setErrors(array('invalidRoleTypeIds' => "Email address is not valid to becomes higher authority user."));
            } else {
                $isAccountInfoFormDataValid = $userForm->isValid($formData);
            }
            
            if ($isEmailValidForHigherAuthority &&  $isAccountInfoFormDataValid ) {
                $data['email'] = $formData['email'];
                $data['completed'] = 0;
                $data['status_id'] = 1;
                $data['account_id'] = $userId;
                $data['partner_id'] = $formData['partner_id'];;
                $data['password'] = $this->createRandomPassword();
                $data['role_type_ids'] =  $user->roleTypeIds;
                $data['api_token'] = md5(serialize($data));
                
                $childUser = new Model_User($data);
                $childUser->password = md5($data['password']);
                
                if ($childUser->save()) {
                    $childUserId = $childUser->id;
                    $isUserCreated = true;
                    //send welcome email
                    $isEmailSend = App_User::sendMultiuserEmail($data);

                    if (!empty($formData['rule_type_ids'])) {
                        foreach ($formData['rule_type_ids'] as $ruleId) {
                            $userAccessRules = new Model_UserAccessRule();
                            $userAccessRules->userId = $childUserId;
                            $userAccessRules->accessRuleId = $ruleId;
                            $userAccessRules->save();
                        }
                    }
                }
                
                //clear form element
                $userForm->populate(array('email' => '', 'rule_type_ids' => array(), 'role_type_ids' => $user->roleTypeIds ));
            } else {
                $formData['role_type_ids'] = $user->roleTypeIds;
                $userForm->populate($formData);
            }
        }
        
        $pageInfo = array( 'user_id' => $user->id );          
        
        $this->view->pageInfo      = $pageInfo;                
        $this->view->userId        = $userId;
        $this->view->userForm      = $userForm;
        $this->view->isUserCreated = $isUserCreated;
        $this->view->isSend        = $isEmailSend;
        $this->view->childUserId   = $childUserId;
        $this->view->isValidParentUser =  $isValidParentUser; 
    }
     
    
     
    function createRandomPassword() 
    {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;

        while ($i <= 7) 
        {
            $num  = rand() % 33;
            $tmp  = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }

        return $pass;
    }
    
    /*
     * @display referral information
     */

    public function referralInfoAction() {
        $this->_helper->layout->disableLayout();
        $request = $this->getRequest();

        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            //exit(0);
        }

        $user = new Model_User;
        $isAdminUser = App_User::isAdmin();
        
        if (isset($request->referral_id) && App_User::allow($request->referral_id)) {
            $user->id = $request->referral_id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }

        //pickup referral user ids.
        $referrelIds = Model_User::getReferralUserIds($user->id, null, 'created desc');
        $referralEarnings = Tapit_Stats::getUsersTotalEarning($referrelIds, Tapit_Stats::LIFETIME, null, null, true);
        $referralDetails = Model_User::getUserInfo($referrelIds);

        foreach ($referralDetails as $userId => &$info) {
            $earnings = 0;
            if (array_key_exists($userId, $referralEarnings)) {
                $earnings = $referralEarnings[$userId];
            }
            $info['earnings'] = ($earnings) * Tapit_Stats::REFERRAL_EARNINGS_PERCENTAGE;;
        }
        
        $this->view->referralDetails = $referralDetails;
        $this->view->isAdminUser = $isAdminUser;
        $this->view->userId = $user->id;
    }


      /**
       * Process Stripe Credit Card Payment
       *
       * @return void
       * @author Craig Hoover
       */
      public function stripeProcessAction()
      {      
          $config = new Zend_Config_Ini(APPLICATION_PATH .'/configs/application.ini','production');
      
          $this->_helper->viewRenderer->setNoRender(true);
          $this->_helper->layout()->disableLayout();
        
          $request = $this->getRequest();
          $user = new Model_User;   
          $userId = App_User::getLoggedUser()->id;
      
          if($request->isPost())
          {
              Stripe::setApiKey($config->tapit->stripe->private_api_token);
      
              $error = '';      
              $token = $request->getPost('stripeToken');
              $amount = $request->getPost('stripeAmount');
              
              if((float) $amount < 50)
              {
                 $error = 'Minimum deposit is $50.00 USD';
              }
      
              try 
              {
                  if (empty($token))
                  {
                     $error = "The Stripe Token was not generated correctly";
                  }
                      
                  if (empty($amount))
                  {
                     $error = "The amount is invalid"; 
                  }
                  
                  if(empty($error))
                  {                      
                  
                     $user->id = $userId;
                     $user->find();                  
                  
                     $charge = Stripe_Charge::create(array(
                        'amount'   => (float) $amount * 100, // amount needs to be in cents
                        'currency' => "usd",
                        'card'     => $token,
                        'description' => 'Payment for User '.$user->id.' by '.$user->email
                     ));
                     
      
                     $transaction = new Model_Transaction();
                     $transaction->setOptions(array( 
                        "user_id"             => $user->id,
                        "transaction_type_id" => 3,
                        "added_by_id"         => $user->id,
                        "status_id"           => 3,
                        "amount"              => $amount,
                        "note"                => "Stripe {$charge->id}"
                     ));
                     $transaction->save(); 
                     
                     // set new balance with payment
                     $balance = (float) $user->balance + (float) $amount;   	            
                     
                     // update user balance notification        
                     if ($amount >= 50 && $user->low_budget_notified == '1') 
                     {
                        $user->low_budget_notified = 0;
                     }
                     
                     $user->balance = $balance;
                     
                     $isUserBalanceUpdated = $user->update();
                     
                     @$this->sendConfirmationEmail($user, $amount);

                  }
              }
              catch (Exception $e) 
              {
                  $error = $e->getMessage();
              }
          }
          
          if(!empty($error))
          {
            $data = array('error' => $error);
          }
          else
          {
            $data = array('success' => true, 'balance' => number_format($user->balance,2));
          }
          
          $this->_helper->json($data);
           
      }

    
    public function paymentAction() {
        $request = $this->getRequest();
        $user = new Model_User;

        if (isset($request->id) && App_User::allow($request->id)) {
            $user->id = $request->id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }
        $user->find();

        $fundAccount = new Default_Form_FundAccount();
        $this->view->fundAccount = $fundAccount;
    }

    public function networkMediationStatusAction() {
        $request = $this->getRequest();

        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $id = $request->getPost("id");
        $isenable = $request->getPost("networkmediation_enabled");

        //Network Mediation
        if (isset($isenable)) {
            $model = new Model_User(array('id' => $id));
            $model->find();
            $model->networkmediation_enabled = $isenable;
            $model->update();
        }
    }

    public function networkMediationAction() {
        
        $request = $this->getRequest();
        $appId = $request->getParam('app_id', 1);
        $action = $request->getParam('action');
        $sourcePage = $request->getParam('source_page', 'network-mediation');
        $partner = App_Partner::singleton();
        if ($partner->partner['id'] != '1' && $sourcePage==="network-mediation") {
            //App_User::accessDenied(false);
            $this->_redirect('/');
        }
       
        $networkMediationForm = new Default_Form_NetworkMediation(array('appId' => $appId, "source_page" => $sourcePage));
        $this->view->networkMediationForm = $networkMediationForm;

        
        $cntIndex = 0;
        $countryList = array();
        $countries = new Model_Country_List(false);
        $countries->setOrderStr('name ASC');
        $countries->initDefault();
        foreach ($countries as $country) {
            $countryList[$cntIndex]["name"] = $country->name;
            $countryList[$cntIndex]["id"] = $country->id;
            $cntIndex++;
        }
        $this->view->countryList = $countryList;
        
        $platformDevicesList = array();
        $platforms = new Model_Platform_List(false);
        $platforms->initDefault();
        foreach ($platforms->toArray() as $platform) {
            $platformDevicesList[] = array(
                'name' => $platform['name'],
                'id' => $platform['id']
            );
        }
        $this->view->platformDevicesList = $platformDevicesList;
        
        $carriersList = array();
        $carriers = new Model_Carrier_List(false);
        $carriers->initDefault();
        foreach ($carriers->toArray() as $carrier) {
            $carriersList[] = array(
                'name' => $carrier['name'],
                'id' => $carrier['id'],
                'country_id' => $carrier['country_id']
            );
        }
        $this->view->carriersList = $carriersList;
        $json = new Zend_Json();
        $this->view->carriersListJson = $json->encode($carriersList);
        
        //assigned to view
        $this->view->appId = $appId;
        $this->view->source_page = $sourcePage;
        $this->view->pageTitle = "Network Mediation";
        
        $this->view->adnetworks = Tapit_Adnetworks::getAdnetworks($appId, $sourcePage);        
        $this->view->adnetworksEnableCount = Tapit_Adnetworks::getTotalEnableAdnetworks($appId);
        
        $mainUserId = App_User::getMainUserId();
        $accessRules = Model_UserAccessRule::getRules($mainUserId);
        $pageInfo = array(
            'main_user_id' => $mainUserId,
            'logged_in_user_id' => App_User::getUserId(),
            "access_rules" => json_encode($accessRules));
        $this->view->mediationPageInfo = json_encode($pageInfo);
    }

    public function confirmAction() {
        $request = $this->getRequest();
        $user = new Model_User;

        if (isset($request->id) && App_User::allow($request->id)) {
            $user->id = $request->id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }
        $user->find();

        if ($request->isPost()) {
            // use generic adapter
            $paypal = new Paypal_GenericAdapter();
            
            $amount = @$_REQUEST['L_AMT0'];
            
            if((float) $amount < 50.00 && false == App_User::isAdmin())
            {
               $amount = 50.00;
            }            

            // start transaction request
            if (!isset($_REQUEST['txn_id']) && !isset($_REQUEST['txn_type'])) {
                $protocol = $_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http';
                $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];
                
                //fetch partner configured paypal email
                $paypalEmail = 'paypal@tapit.com';
                $partnerLabel = 'Tapit';
                $partnerDetail = App_Partner::singleton()->partner;
                if (!empty($partnerDetail)) {
                    $paypalEmail = $partnerDetail['paypal_email'];
                    $partnerLabel = $partnerDetail['label'];
                }

                // set our base params
                $params = array(
                    'business' => $paypalEmail,
                    'item_name' => $partnerLabel.' Campaign Budget',
                    'amount' => $amount,
                    'currency_code' => 'USD',
                    'return' => "{$baseUrl}/account/summary?paymentMade=true",
                    'cancel_return' => "{$baseUrl}/account/summary?paymentMade=false",
                    'notify_url' => "{$baseUrl}/index/ipn",
                    'no_note' => 1,
                    'lc' => 'US',
                    'first_name' => $user->fname,
                    'last_name' => $user->lname,
                    'payer_email' => $user->email,
                    'item_number' => date('YmdH') . '-' . $user->id
                );

                // generates a URL and redirect for transaction
                $paypal->processRequest($params);
            }
        } else {
            header('Location:/account/summary');
            exit(0);
        }
    }

    // !!!! crh: I don't think we need this anymore, we shall see
    public function processedAction() {
        /*

          $request = $this->getRequest();
          $user = new Model_User;

          if(App_User::getRole() == 'admin' && isset($request->id)) {
          $user->id = $request->id;
          }
          else {
          $user->id = App_User::getLoggedUser()->id;
          }
          $user->find();

          ini_set('session.bug_compat_42',0);
          ini_set('session.bug_compat_warn',0);

          $paypal = new Paypal_NVPAdapter();

          $token =urlencode( $_SESSION['token']);
          $paymentAmount =urlencode ($_SESSION['TotalAmount']);
          $paymentType = urlencode($_SESSION['paymentType']);
          $currCodeType = urlencode($_SESSION['currCodeType']);
          $payerID = urlencode($_SESSION['payer_id']);
          $serverName = urlencode($_SERVER['SERVER_NAME']);

          $nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName ;

          $resArray = $paypal->hash_call("DoExpressCheckoutPayment",$nvpstr);
          $ack = strtoupper($resArray["ACK"]);

          if( $ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING' )
          {
          $_SESSION['reshash'] = $resArray;
          $location = "/account/error";
          header( "Location: $location" );
          }
          else {
          // Insert the transaction
          //
          $transaction = new Model_Transaction();
          $transaction->setOptions( array( "user_id" => $user->id,
          "transaction_type_id" => 3,
          "status_id" => 3,
          "amount" => $paymentAmount ) );
          $transaction->save();

          // Update the user balance
          //
          $user->setOptions( array( "balance" => ($user->balance + $paymentAmount) ) );
          $user->update();

          // Redirect to Summary
          //
          $location = "/account/summary";
          header( "Location: $location" );
          }
         */
    }

    public function cancelAction() {
        $request = $this->getRequest();
        $user = new Model_User;

        if (isset($request->id) && App_User::allow($request->id)) {
            $user->id = $request->id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }
        $user->find();
    }

    public function errorAction() {
        $request = $this->getRequest();
        $user = new Model_User;

        if (App_User::isAdmin() && isset($request->id)) {
            $user->id = $request->id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }
        $user->find();
    }

    public function summaryAction() 
    {
    
        $config = new Zend_Config_Ini(APPLICATION_PATH .'/configs/application.ini','production');
    
        $request = $this->getRequest();
        
        // add Stripe Javascript dependencies
        $this->view->headScript()->prependScript("Stripe.setPublishableKey('".$config->tapit->stripe->public_api_token."');");
        $this->view->headScript()->prependFile("https://js.stripe.com/v2/", $type='text/javascript');
        
        if (isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'], '/publisher/dashboard/id') !== FALSE || strpos($_SERVER['HTTP_REFERER'], '/advertiser/dashboard/id') !== FALSE || strpos($_SERVER['HTTP_REFERER'], '/account/info/id') !== FALSE) && !isset($request->id)) {
            $iId = array_pop(explode('/', $_SERVER['HTTP_REFERER']));
            $this->_redirect('/account/summary/id/' . $iId);
        } elseif (isset($_SESSION['view_user']['viewUserId']) && $_SESSION['view_user']['viewUserId'] != '' && !isset($request->id)) {
            $session = new Zend_Session_Namespace('view_user');
            if(isset($session->viewUserId)){
                $viewUserId = $session->viewUserId;
                unset($session->viewUserId);
            }
            $this->_redirect('/account/summary/id/' . $viewUserId);
        }
        
        $user = new Model_User;
        if (isset($request->id)) {
            if (!App_User::allow($request->id, false, false, 'account-summary')) {
                 //App_User::accessDenied();
                 $this->_redirect('/');
            }
            $user->id = $request->id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }

        // check if returning from PayPal
        $payment = $request->paymentMade;

        if (!empty($payment)) {
            if ($payment == 'true') {
                $_SESSION['fund_message'] = true;
            } else {
                $_SESSION['cancel_message'] = true;
            }
        }

        $user->find();

        $role = '';
        if (in_array(6, $user->roleTypeIds)) {
            $role = 'super-admin';
        } else if (in_array(5, $user->roleTypeIds)) {
            $role = 'admin';
        } else if ((in_array(1, $user->roleTypeIds) && in_array(2, $user->roleTypeIds)) ||
                (in_array(3, $user->roleTypeIds) && in_array(4, $user->roleTypeIds))) {
            $role = 'publisher_advertiser';
        } else if (in_array(1, $user->roleTypeIds) || in_array(3, $user->roleTypeIds)) {
            //publisher or publisher manager
            $role = 'publisher';
        } else if (in_array(2, $user->roleTypeIds) || in_array(4, $user->roleTypeIds)) {
            //advertiser or advertiser manager
            $role = 'advertiser';
        }

        $this->view->role = $role;

        $addfund = new Default_Form_AddFund(array('user_role' => $role));
        Model_UserAccessRule::filterForm($addfund);

        $this->view->addfund = $addfund;

        $transactions = new Model_Transaction_List(true);
        $transactions->page = (int) $request->getParam('page');
        $transactions->setOrderStr('created DESC');
        $transactions->loadForUser($user);

        $transactionTypes = new Model_TransactionType_List(false);
        $transactionTypes->initByPrimaryArray('id', $transactions->getPropertyArray('transactionTypeId'));
        $statusTypes = new Model_Status_List(false);
        
        //fetch partner info
        $partnerDetail = App_Partner::singleton()->partner;

        $partnerName = null;
        if (!empty($partnerDetail)) {
            $partnerId = $partnerDetail['id'];
            $partnerName = $partnerDetail['label'];
        }
        $this->view->partnerName = $partnerName;

        //$transaction = new Model_Transaction();
        //$this->view->pendingEarnings = $transaction->pendingEarnings($user->id);
        $pendingEarnings = $user->monthly_earnings;
        
        $totClicks = null;
        $startTimeslice = null;
        $endTimeslice = null;
        
        $fundRequest = '';
        $requestEarnings = 0;

        if (strpos(App_User::getRole(), 'publisher') !== FALSE || strpos(App_User::getRole(), 'admin') !== FALSE) {
            if ($role != 'advertiser') {
                $paymentTerms = 60;
                if ($user->payment_term_id) {
                    $objPaymentTerms = new Model_PaymentTerm();
                    $objPaymentTerms->id = $user->payment_term_id;
                    $objPaymentTerms->find();
                    $paymentTerms = $objPaymentTerms->value;
                }

                $objUserFundRequest = new Model_UserFundRequest();
                $endTimeslice = $objUserFundRequest->findLastFundRequest($user->id);
                if (!$endTimeslice)
                    $endTimeslice = 0;
               
                $this->view->totalEarnings = Tapit_PublisherStats::getUserPendingEarnings($user->id, $endTimeslice, $partnerId);
                $earnings = Tapit_PublisherStats::getUserFundtoRequest($user->id, $endTimeslice, $paymentTerms, $partnerId);
                $cntEarnings = count($earnings);

                $requestEarnings = 0;
                //$totClicks = 0;
                $startTimeslice = '';
                $endTimeslice = '';

                if ($cntEarnings > 0) {
                    $k = 0;
                    foreach ($earnings as $row) {
                        $requestEarnings += $row['earnings'];
                        //$totClicks += $row->clicks;
                        if ($k == 0) {
                            $startTimeslice = $row['timeslice'];
                            if ($cntEarnings == 1) {
                                $endTimeslice = $row['timeslice'];
                            }
                        } elseif ($k == ($cntEarnings - 1)) {
                            $endTimeslice = $row['timeslice'];
                        }
                        $k++;
                    }
                }
                if ($requestEarnings >= 100)
                    $fundrequest = 1;
                
                $pendingEarnings = $requestEarnings;
            }
        }        
        
        $this->view->pendingEarnings = $pendingEarnings;
        $this->view->totClicks = $totClicks;
        $this->view->startTimeslice = $startTimeslice;
        $this->view->endTimeslice = $endTimeslice;

        $this->view->user = $user;
        $this->view->transactions = $transactions;
        $this->view->transactionTypes = $transactionTypes;
        $this->view->statusTypes = $statusTypes;
        
        $mainUserId = App_User::getMainUserId();
        $accessRules = Model_UserAccessRule::getRules($mainUserId);
        $pageInfo = array(
            'main_user_id' => $mainUserId,
            "access_rules" => json_encode($accessRules),
            'fund_request' => $fundRequest);
        $this->view->summaryPageInfo = $pageInfo;
    }

    protected function paymentForm($accountId = null) {
        $request = $this->getRequest();
        $PaymentDetails = new Default_Form_PaymentDetails();

        //get the account id from info action
        $userId = $accountId;
        if (!$userId) {
            $userId = App_User::getUserId();
        }

        $userData = array('user_id' => $userId);
        $paymentInfoTax = new Model_PaymentInfoTax($userData);
        $paymentInfoWire = new Model_PaymentInfoWire($userData);
        $paymentInfoPayPal = new Model_PaymentInfoPaypal($userData);
        $paymentInfoCheck = new Model_PaymentInfoCheck($userData);
        $paymentInfoDirectDeposit = new Model_PaymentInfoDirectDeposit($userData);
        $buttonPress = (bool) $request->getParam('submit_payment_details', false);

        if ($request->isPost()) {

            $paymentMethod = $request->getParam('paymentDetail', false);
            if ($buttonPress && $paymentMethod == 1) {
                $PaymentDetails->removeWireElementValidators();
                $PaymentDetails->removeCheckElementValidators();
                $PaymentDetails->removeDirectDepositElementValidators();
            } else if ($buttonPress && $paymentMethod == 2) {
                $PaymentDetails->removeWireElementValidators();
                $PaymentDetails->removePayPalElementValidators();
                $PaymentDetails->removeDirectDepositElementValidators();
            } else if ($buttonPress && $paymentMethod == 3) {
                $PaymentDetails->removeWireElementValidators();
                $PaymentDetails->removePayPalElementValidators();
                $PaymentDetails->removeCheckElementValidators();
            } else {
                $PaymentDetails->removePayPalElementValidators();
                $PaymentDetails->removeCheckElementValidators();
                $PaymentDetails->removeDirectDepositElementValidators();
            }

            if ($buttonPress && $PaymentDetails->isValid($request->getPost())) {
                $userModel = new Model_User(array('id' => $userId));
                $userModel->find();
                $userModel->paymentMethod = $paymentMethod;
                $userModel->update();

                if ($paymentInfoTax->initFromField('user_id')) {
                    $paymentInfoTax->setOptions($PaymentDetails->getValues());
                    $paymentInfoTax->countryId = $PaymentDetails->getValue('country');
                    $paymentInfoTax->update();
                } else {
                    $paymentInfoTax->setOptions($PaymentDetails->getValues());
                    $paymentInfoTax->countryId = $PaymentDetails->getValue('country');
                    $paymentInfoTax->save();
                }
               
                if ($paymentMethod == 1) {
                    //function to clean the old details
                    $this->updatePaymentDetails( $userId, $paymentMethod );
                    
                    if ($paymentInfoPayPal->initFromField('user_id')) {                        
                        $paymentInfoPayPal->setOptions($PaymentDetails->getValues());
                        $paymentInfoPayPal->login = $PaymentDetails->getValue('paypalLogin');
                        $paymentInfoPayPal->update();                       
                    } else {
                        $paymentInfoPayPal->setOptions($PaymentDetails->getValues());
                        $paymentInfoPayPal->login = $PaymentDetails->getValue('paypalLogin');
                        $paymentInfoPayPal->save();
                    }
                } else if ($paymentMethod == 2) {
                    //function to clean the old details
                    $this->updatePaymentDetails( $userId, $paymentMethod );
                    
                    if ($paymentInfoCheck->initFromField('user_id')) {
                        $paymentInfoCheck->setOptions($PaymentDetails->getValues());
                        $paymentInfoCheck->update();                        
                        
                    } else {
                        $paymentInfoCheck->setOptions($PaymentDetails->getValues());
                        $paymentInfoCheck->save();
                    }
                } else if ($paymentMethod == 3) {
                    //function to clean the old details
                    $this->updatePaymentDetails( $userId, $paymentMethod );
                        
                    $ddBankName = $PaymentDetails->getValue('dd_bank_name');
                    if ($paymentInfoDirectDeposit->initFromField('user_id')) {
                        $paymentInfoDirectDeposit->setOptions($PaymentDetails->getValues());
                        $paymentInfoDirectDeposit->bank_name = $ddBankName;
                        $paymentInfoDirectDeposit->update();
                        
                    } else {
                        $paymentInfoDirectDeposit->setOptions($PaymentDetails->getValues());
                        $paymentInfoDirectDeposit->bank_name = $ddBankName;
                        $paymentInfoDirectDeposit->save();
                    }
                } else {
                    
                    //function to clean the old details
                    $this->updatePaymentDetails( $userId, $paymentMethod );
                        
                    if ($paymentInfoWire->initFromField('user_id')) {
                        $paymentInfoWire->setOptions($PaymentDetails->getValues());
                        $paymentInfoWire->update();
                        //function to clean the old details
                        $this->updatePaymentDetails( $userId, $paymentMethod );
                    } else {
                        $paymentInfoWire->setOptions($PaymentDetails->getValues());
                        $paymentInfoWire->save();
                    }
                }
            }
        }
    }

    public function updatePaymentDetails($uid,$paymentMethod){
        
        $userModel = new Model_User();
        if(empty( $uid ) ){
           return; 
        }   
        $userData = array('user_id' => $uid,'payment_method'=>$paymentMethod);        
        $paymentInfoCheck  = new Model_PaymentInfoCheck($userData);
        $paymentInfoWire   = new Model_PaymentInfoWire($userData);
        $paymentInfoPayPal = new Model_PaymentInfoPaypal($userData);
        $paymentInfoDirectDeposit = new Model_PaymentInfoDirectDeposit($userData);
        
        //for Payment type - Paypal
        if( $paymentMethod == 1 ){
            $paymentInfoCheck->deleteRecord( $userData );
            $paymentInfoWire->deleteRecord( $userData );
            $paymentInfoDirectDeposit->deleteRecord( $userData );
            
        }//for Payment type - Check
        else if( $paymentMethod == 2 ){
            $paymentInfoPayPal->deleteRecord( $userData );
            $paymentInfoWire->deleteRecord( $userData );
            $paymentInfoDirectDeposit->deleteRecord( $userData ); 
            
        }//for Payment type - Deposite
        else if( $paymentMethod == 3 ){
            $paymentInfoCheck->deleteRecord( $userData );
            $paymentInfoWire->deleteRecord( $userData );
            $paymentInfoPayPal->deleteRecord( $userData );
            
        }//for Payment type - Wire
        else{
            $paymentInfoCheck->deleteRecord( $userData );
            $paymentInfoDirectDeposit->deleteRecord( $userData );
            $paymentInfoPayPal->deleteRecord( $userData );             
        }                         
        
    }
    
    protected function setNotification($userId) {

        $request = $this->getRequest();
        $PaymentDetails = new Default_Form_PaymentDetails();
        $buttonPress = (bool) $request->getParam('submit_payment_details', false);
        $userData = array('user_id' => $userId);
        $paymentInfoTax = new Model_PaymentInfoTax($userData);
        $paymentInfoWire = new Model_PaymentInfoWire($userData);
        $paymentInfoPayPal = new Model_PaymentInfoPaypal($userData);
        $paymentInfoCheck = new Model_PaymentInfoCheck($userData);
        $paymentInfoDirectDeposit = new Model_PaymentInfoDirectDeposit($userData);
        $populateValues = array();
        $partnerDetail = App_Partner::singleton()->partner;
        $partnerId = 1;
        if($partnerDetail['id']){      
            $partnerId = $partnerDetail['id'];
            $partnerName        = $partnerDetail['label'];
            $partnerSupportUrl  = $partnerDetail['support_url'];
            $partnerAccountUrl  = $partnerDetail['account_url'];
        }
        unset($partnerDetail);     
        
        $bgcolor = '#2C709E';
        $fontcolor = '#FFFFFF';
        if($partnerId == '3'){
            $bgcolor = '#fbd130';
            $fontcolor = '#666666';
        }
        
        if ($paymentInfoTax->initFromField('user_id')) {

            $populateValues['country'] = $paymentInfoTax->countryId;
            $populateValues['businessName'] = $paymentInfoTax->businessName;
            $populateValues['usTin'] = $paymentInfoTax->usTin;
            $userModel = new Model_User(array('id' => $userId));
            $userModel->find();
            $populateValues['paymentDetail'] = $userModel->paymentMethod;
        }


        if ($paymentInfoPayPal->initFromField('user_id')) {
            $one = $paymentInfoPayPal->toArray();
            $populateValues['paypalLogin'] = $paymentInfoPayPal->login;
        }
        if ($paymentInfoWire->initFromField('user_id')) {
            $populateValues['beneficiaryName'] = $paymentInfoWire->beneficiaryName;
            $populateValues['bankName'] = $paymentInfoWire->bankName;
            $populateValues['bankAddress'] = $paymentInfoWire->bankAddress;
            $populateValues['iban'] = $paymentInfoWire->iban;
            $populateValues['ach'] = $paymentInfoWire->ach;
        }
        if ($paymentInfoCheck->initFromField('user_id')) {
            $populateValues['paymentTo'] = $paymentInfoCheck->paymentTo;
            $populateValues['bankStreetAddress'] = $paymentInfoCheck->bankStreetAddress;
            $populateValues['bankCity'] = $paymentInfoCheck->bankCity;
            $populateValues['bankState'] = $paymentInfoCheck->bankState;
            $populateValues['bankZip'] = $paymentInfoCheck->bankZip;
        }
        if ($paymentInfoDirectDeposit->initFromField('user_id')) {
            $populateValues['account_name'] = $paymentInfoDirectDeposit->account_name;
            $populateValues['dd_bank_name'] = $paymentInfoDirectDeposit->bank_name;
            $populateValues['account_number'] = $paymentInfoDirectDeposit->account_number;
            $populateValues['routing_number'] = $paymentInfoDirectDeposit->routing_number;
        }


        if ($request->isPost()) {

            if ($buttonPress) {
                $formData = $request->getPost();
                $diff = array_diff($populateValues, $formData);
                $userEmail = App_User::getLoggedUser()->email;
                $userFname = App_User::getLoggedUser()->fname;
                $protocol = $_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http';
                $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];
                $accountInfoUrl = $baseUrl . '/account/info/id/' . $userId;

                if (!empty($diff)) {
                    //send email notification                    

                    $recipientsEmail = $partnerAccountUrl;
                    $subject = $userEmail . ' has  Updated their Payment Infomation!';

                    $headers = "From: ".$partnerName."! Account<".$partnerAccountUrl.">\r\n";
                    $headers .= "MIME-Version: 1.0\n";
                    $headers .= "Content-Type: text/html; charset=utf-8\n";
                    $headers .= "Content-Transfer-Encoding: base64\r\n\r\n";

                    $message = "";
                    $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
                    $message .= "<p style=\"border-radius:10px; text-transform: uppercase; font-size: 15px; font-family: 'Droid Sans', sans-serif; line-height: 20px; padding: 5px 10px; background-color:".$bgcolor."; color:".$fontcolor.";\"><b>PUBLISHER UPDATES PAYMENT INFORMATION!</b></p>";
                    $message .= "<p>Hey guys, just wanted to let you know that ,</p>";
                    $message .= "<p><b>" . $userFname . "</b></p>";
                    $message .= "<p>has updated their payment details. You can view the changes here:</p>";
                    $message .= "<p>" . $accountInfoUrl . "</p>";
                    $message .= "<br />";
                    $message .= "Thanks,<br />";
                    $message .= "Team ".$partnerName."!&trade;<br />";
                    $message .= "</body></html>";
                    $message = rtrim($message);
                    App_Email::send($recipientsEmail, $subject, $message, $headers);
                }
            }
        }
    }

    public function generateInvoiceAction() {
        $this->_helper->layout()->disableLayout();
        $request = $this->getRequest();
        $trasactionId = $request->getParam('transId');
        $monthSelected = $request->getParam('month');
        $user = new Model_User;

        if (isset($request->id) && App_User::allow($request->id)) {
            $user->id = $request->id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }


        $user->find();
        $transactions = new Model_Transaction_List(true);

        //load transaction for single transaction Id 
        if ($trasactionId) {
            $transactions->loadByTrnsId($user, $trasactionId);
            $this->view->generateSigleInvoice = $transactions;
        }
        //load transaction for specific month 
        else if ($monthSelected) {
            $transactions->loadTrnsByMonth($user, $monthSelected);
            $this->view->generateMonthlyInvoice = $transactions;
        }
        
        
        $partnerId = $user->partner_id;
        if(!$partnerId){
            $partnerDetail = Tapit_PseudoConstant::partners(null, null, 'ads.tapit.com');
            $partnerId =  key($partnerDetail);  
        }   
        
        $invoiceLogoTop = '/images/white_label/1/images/doc-logo-small.png';
        $invoiceLogoBottom = '/images/white_label/1/images/doc-logo-bottom.png';
        
        if (is_file('../public/images/white_label/'.$partnerId.'/images/doc-logo-small.png')) {
            $invoiceLogoTop = '/images/white_label/'.$partnerId.'/images/doc-logo-small.png';
        }
        
        if (is_file('../public/images/white_label/'.$partnerId.'/images/doc-logo-bottom.png')) {
            $invoiceLogoBottom = '/images/white_label/'.$partnerId.'/images/doc-logo-bottom.png';
        }
        
        $this->view->invoiceLogoTop = $invoiceLogoTop;
        $this->view->invoiceLogoBottom = $invoiceLogoBottom;
        
        $transactionTypes = new Model_TransactionType_List(false);
        $transactionTypes->initByPrimaryArray('id', $transactions->getPropertyArray('transactionTypeId'));
        $statusTypes = new Model_Status_List(false);
        $this->view->userDetails = $user;
        
        //get the partner details.
        $partnerId = ($user->partnerId)?$user->partnerId:1;
        $this->view->partnerAddress = Model_Partner::getPartnerDisplayAddress($partnerId);

        $this->view->transactionTypes = $transactionTypes;
        $this->view->statusTypes = $statusTypes;
    }

    function getpdfAction() {
        require(LIBRARY_PATH . '/fpdf.php');
        $request = $this->getRequest();
        $trasactionId = $request->getParam('transId');
        $userId = $request->getParam('id');
        $trnsDate = $request->getParam('date');
        $monthSelected = date("m", strtotime($trnsDate));

        $user = new Model_User;
        if (isset($request->id) && App_User::allow($request->id)) {
            $user->id = $request->id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }
        $user->find();
        $transactions = new Model_Transaction_List(true);

        //load transaction for single transaction Id 
        if ($trasactionId) {
            $transactions->loadByTrnsId($user, $trasactionId);
            $this->view->generateSigleInvoice = $transactions;
        }
        //load transaction for specific month 
        else if ($monthSelected) {
            $transactions->loadTrnsByMonth($user, $monthSelected);
            $this->view->generateMonthlyInvoice = $transactions;
        }

              
        $partnerId = $user->partner_id;
        if(!$partnerId){
            $partnerDetail = Tapit_PseudoConstant::partners(null, null, 'ads.tapit.com');
            $partnerId =  key($partnerDetail);  
        }
       
        $invoiceLogoTop = '/images/white_label/1/images/doc-logo-small.png';
        $invoiceLogoBottom = '/images/white_label/1/images/doc-logo-bottom.png';
        if (is_file('../public/images/white_label/'.$partnerId.'/images/doc-logo-small.png')) {
            $invoiceLogoTop = '/images/white_label/'.$partnerId.'/images/doc-logo-small.png';
        }
        if (is_file('../public/images/white_label/'.$partnerId.'/images/doc-logo-bottom.png')) {
            $invoiceLogoBottom = '/images/white_label/'.$partnerId.'/images/doc-logo-bottom.png';
        }
        $this->view->invoiceLogoTop = $invoiceLogoTop;
        $this->view->invoiceLogoBottom = $invoiceLogoBottom;
        
        $this->view->partnerAddress = Model_Partner::getPartnerDisplayAddress($partnerId);
        $transactionTypes = new Model_TransactionType_List(false);
        $transactionTypes->initByPrimaryArray('id', $transactions->getPropertyArray('transactionTypeId'));
        $statusTypes = new Model_Status_List(false);
        $this->view->userDetails = $user;
        $this->view->transactionTypes = $transactionTypes;
        $this->view->statusTypes = $statusTypes;

        //created an object of fpdf						
        $pdf = new FPDF();
        $this->view->pdf = $pdf;
    }

    public function getAllCountriesAction(){
        $this->_helper->layout()->disableLayout();
        $json = new Zend_Json();
       	$countries = new Model_Country_List(false);        
        $countries->initDefault(); 
        foreach( $countries->toArray() as $country )
        {   
            $allCountry[] = array( 
                                   'name'  => $country['name'],
                                   'id'    => $country['id'] 
                                );
            
        }        
        echo $json->encode( $allCountry );
        exit(0);  
                
    }
    
    
    public function getAllPlatformsAction(){
        $this->_helper->layout()->disableLayout();
        $json = new Zend_Json();
       	$platforms = new Model_Platform_List(false);        
        $platforms->initDefault(); 
        foreach( $platforms->toArray() as $platform )
        {   
            $allPlatform[] = array( 
                                   'name'  => $platform['name'],
                                   'id'    => $platform['id'] 
                                );
            
        }            
        echo $json->encode( $allPlatform );
        exit(0);  
                
    }
    public function getAllCarriersAction(){
        $this->_helper->layout()->disableLayout();
        $json = new Zend_Json();
       	$carriers = new Model_Carrier_List(false);        
        $carriers->initDefault(); 
        foreach( $carriers->toArray() as $carrier )
        {   
            $allCarrier[] = array( 
                                   'name'  => $carrier['name'],
                                   'id'    => $carrier['id'] 
                                );
            
        }        
        echo $json->encode( $allCarrier );
        exit(0);  
                
    }
    
    public function fundRequestAction() {
        $request = $this->getRequest();

        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }

        $uid = $request->getParam('id');

        if (isset($uid) && App_User::allow($uid)) {
            $user_id = $uid;
        } else {
            $user_id = App_User::getUserId();
        }

        $clicks = $request->getParam('tot_clicks');
        $fund = $request->getParam('tot_funds');
        $startTimeslice = $request->getParam('start_timeslice');
        $endTimeslice = $request->getParam('end_timeslice');

        $objUserFundReq = new Model_UserFundRequest();
        if ($objUserFundReq->saveFundRequest($user_id, $fund, $startTimeslice, $endTimeslice)) {
            $success = 1;
        } else {
            $success = 0;
        }

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        echo $success;
        exit;
    }

    /*
     * @display account manager information
     */
    public function managerInfoAction() {

        $this->_helper->layout->disableLayout();
        $request = $this->getRequest();

        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            exit(0);
        }
        
        $user = new Model_User();
        if (isset($request->user_id) && App_User::allow($request->user_id)) {
            $user->id = $request->user_id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }
        $mngrDetails = Model_User::getAccountManagerInfo($user->id);
        $user_roles = Model_UserRole::getUserRoles($user->id);

        $advAccountManager = array();
        $pubAccountManager = array();

        foreach ($mngrDetails as $roleTypeId => $info) {
            if ($roleTypeId == "4"
                    && (in_array(2, $user_roles)
                    || in_array(4, $user_roles)
                    || in_array(5, $user_roles)
                    || in_array(6, $user_roles))
            ) {
                $advManagerId = $info['id'];
                $advManagerRoles = Model_UserRole::getUserRoles($advManagerId);

                if (in_array(4, $advManagerRoles)) {
                    $advAccountManager[$roleTypeId] = $info;
                }
            }
            if ($roleTypeId == "3"
                    && (in_array(1, $user_roles)
                    || in_array(3, $user_roles)
                    || in_array(5, $user_roles)
                    || in_array(6, $user_roles)
                    )
            ) {

                $publisherManagerId = $info['id'];
                $publisherManagerRoles = Model_UserRole::getUserRoles($publisherManagerId);

                if (in_array(3, $publisherManagerRoles)) {
                    $pubAccountManager[$roleTypeId] = $info;
                }
            }
        }
       
        $this->view->advMngrDetails = $advAccountManager;
        $this->view->pubMngrDetails = $pubAccountManager;
    }
    
    public function addPromoAction() {
        $this->_helper->layout->disableLayout();
        $request = $this->getRequest();
        
        $userId = $request->getParam('id');
        
        //build add promo code form
        $addPromoCode = new Default_Form_AddPromo(array('user_id' => $userId ));
        
        $this->view->addPromoCode = $addPromoCode;  
        $this->view->userId = $userId;
    }
    
    
    private function sendConfirmationEmail(Model_User $user, $amount)
    {
         // generate email                     
         $partnerName = null;
         $partnerDetail = Tapit_PseudoConstant::partners(null, null, $_SERVER["HTTP_HOST"]); 
         $partnerId = 1;
         
         foreach ($partnerDetail as $id => $detail) 
         {
             $partnerId          = $id;
             $partnerName        = $detail['label'];
             $partnerUrl         = $detail['url'];
             $partnerPhone       = $detail['phone'];
             $partnerSupportUrl  = $detail['support_url'];
             $partnerAccountUrl  = $detail['account_url'];
         }
         
         unset($partnerDetail);
   
         if (!$partnerName) 
         {
             $partnerDetail = Tapit_PseudoConstant::partners(null, null, 'ads.tapit.com');
             
             foreach ($partnerDetail as $id => $detail) 
             {
                 $partnerId          = $id;
                 $partnerName        = $detail['label'];
                 $partnerUrl         = $detail['url'];
                 $partnerPhone       = $detail['phone'];
                 $partnerSupportUrl  = $detail['support_url'];
                 $partnerAccountUrl  = $detail['account_url'];
             }
             unset($partnerDetail);
         }
           
         $bgcolor = '#2C709E';
         $fontcolor = '#FFFFFF';
         
         if ($partnerId == '3') 
         {
             $bgcolor = '#fbd130';
             $fontcolor = '#666666';
         }
         
         // send confirm email to user
         $subject = 'Payment Confirmation';

         $headers = "From: ".$partnerName."! Support<".$partnerSupportUrl.">\r\n";
         $headers .= "MIME-Version: 1.0\n";
         $headers .= "Content-Type: text/html; charset=utf-8\n";
         $headers .= "Content-Transfer-Encoding: base64\r\n\r\n";

         $message = "";
         $message .= "<html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type content='text/html; charset=utf-8' /></head><body style=\"font-family: 'Droid Sans', sans-serif;\">";
         $message .= "<p style=\"border-radius:10px; text-transform: uppercase; font-size: 15px; font-family: 'Droid Sans', sans-serif; line-height: 20px; padding: 5px 10px; background-color:".$bgcolor."; color:".$fontcolor.";\"><b>YOUR PAYMENT HAS BEEN RECEIVED</b></p>";                            
         $message .= "<p>".$user->fname.", your payment has been received!</p>";       
         $message .= '<p>We\'ve received payment of $'.number_format($amount,2).' and have applied it to your account!</p>'; 
         $message .= "<p>If you have any questions, please contact your Account Manager or <a href=\"mailto:".$partnerSupportUrl."\">support</a> for assistance.</p>";
         $message .= "<p>Thanks for choosing ".$partnerName."!</p>";
         $message .= "<br>";
         $message .= "Sincerely,<br />";
         $message .= "Team ".$partnerName."!&trade;<br />";
         $message .= "P: ".$partnerPhone."<br />";
         $message .= "E: ".$partnerSupportUrl;
         $message .= "</body></html>";
         $message = rtrim($message);

         $fromEmail = $partnerSupportUrl;
         $fromName  = $partnerName."! Support";
         
         @App_Email::send($user->email, $subject, $message, $headers, $fromEmail, $fromName);    
    
    
    }

}

