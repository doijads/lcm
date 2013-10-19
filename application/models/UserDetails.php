<?php

class Model_UserDetails extends App_Model {

    protected $_tableClass = 'Model_DbTable_UserDetails';

    /**
     * Table data Columns
     * 
     * Gross hack by Kiran
     * Columns can be defined here to suppress the db column initialize
     */
    //protected $_data = array( 'id' => null, 'name' => null );
    
    public function update($data = null) {
        //this allow to update object w/ 
        //1. $user = new Model_UserDetails($data);
        //$user->update();
        //
        //2. $user = new Model_User();
        //$user->update($data);
        
        if (is_array($data)) {
            $this->setOptions($data);  
        }         
        return parent::update($data);
    }
    
}
