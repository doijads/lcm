<?php

class App_User {

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

    /**
     * @var object
     * @static
     */
    static private $_singleton = null;

    /**
     *
     */
    private function __construct() {
        $user = new Model_Users();
        if (App_Auth::isLogged() && App_Auth::getId()) {
            $user->id = App_Auth::getId();
            $user->find();
        }

        $this->user = $user->toArray();
    }

    /**
     * singleton function used to manage this object
     *
     * @return object
     * @static
     */
    static function &singleton() {
        if (self::$_singleton === null) {
            self::$_singleton = new App_User();
        }
        return self::$_singleton;
    }

    /**
     * get value from singleton object
     *
     * @return value
     * @static
     */
    static function get($key) {
        if (self::$_singleton === null) {
            self::$_singleton = new App_User();
        }
        
        $value = null;
        if (isset(self::$_singleton->user[$key])) {
            $value = self::$_singleton->user[$key];
        }

        return $value;
    }

    /**
     * set value to singleton object
     *
     * @return value
     * @static
     */
    static function set($key, $value) {
        if (self::$_singleton === null) {
            self::$_singleton = new App_User();
        }
        self::$_singleton->user[$key] = $value;
    }

   static function getUserName( $id ){
       if( empty($id)){
        return $name = '';
       }
       
       $user = new Model_Users();
       $user->id = $id;
       $user->find();
       return $user->name;               
   }
   
   static function getStatus( $id ){
       if( empty($id)){
        return $name = '';
       }
       $statuses = new Model_Status();
       $statuses->id = $id;
       $statuses->find();
       return $statuses->name;       
   }
    
}