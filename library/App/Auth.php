<?php

class App_Auth {

    const
            INVALID_USERNAME_PASSWORD = 0,
            PENDING = 1,
            ACTIVE = 2,
            APPROVED = 3,
            DECLINED = 4,
            SUSPENDED = 5,
            DELETED = 9,
            NO_USER_ACCESS_RULES = 100,
            ENCRIPTED_PASSWORD = 'ENCRIPTED-PASSWORD',
            READ_ONLY = 1,
            CHANGE_STATUS = 2,
            FULL_ACCESS = 3;

    public static function isLogged() {
        $auth = Zend_Auth::getInstance();
        return $auth->hasIdentity();
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

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);

        if ($result->isValid()) {
            $authData = $authAdapter->getResultRowObject();
            if (!$authData->id) {
                //clear cache
                if ($auth->hasIdentity()) {
                    $auth->clearIdentity();
                }
                return self::SUSPENDED;
            }

            $user = new Model_Users((array) $authData);

            self::setLogged($user);

            return self::ACTIVE;
        }

        return self::INVALID_USERNAME_PASSWORD;
    }

    public static function logout() {
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->clear();
    }

    public static function setLogged(Model_Users $user) {
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($user);
    }

    public static function getId() {
        $userId = null;
        $auth = Zend_Auth::getInstance();
        if (is_a($auth, 'Zend_Auth')) {
            $user = $auth->getIdentity();
            if (is_a($user, 'Model_Users')) {
                $userId = $user->id;
            }
        }
        
        return $userId;
    }

    /**
     * @return Model_User
     */
    public static function getUser() {
        $auth = Zend_Auth::getInstance();
        return $auth->getIdentity();
    }

}