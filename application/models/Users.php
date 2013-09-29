<?php

class Model_Users extends App_Model {

    protected $_tableClass = 'Model_DbTable_Users';

    /**
     * Table data Columns
     * 
     * Gross hack by Kiran
     * Columns can be defined here to suppress the db column initialize
     */
    protected $_data = array('user_type' => 1);

    public function save() {

        //defined here those we can't afford to be null.
        $this->createdOn = date('Y-m-d');
        $this->createdBy = App_User::get('id');

        return parent::save();
    }

    public function update($data = null) {
        //this allow to update object w/ 
        //1. $user = new Model_User($data);
        //$user->update();
        //
        //2. $user = new Model_User();
        //$user->update($data);
        
        if (is_array($data)) {
            $this->setOptions($data);  
        }
         
        return parent::update($data);
    }

    public function getUsers($params , $roleType =  null) {
        //$userMapperObj = new Application_Model_UsersdetailMapper();           
        $conditions = array();
        if (!empty($params)) {
            foreach ($params as $property => $value) {
                if (!empty($value)) {
                    $conditions[] = "{$property} LIKE '%{$value}%'";
                }
            }
        }
        $whereClause = (!empty($conditions)) ? implode(" OR ", $conditions) : null;
                       
        if($roleType){
           $whereClause = $whereClause." AND user_type = {$roleType}" ;
        }
                
        $sql = $this->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('u' => 'users'), array('*'))
                ->join(array('ud' => 'user_details'), 'ud.user_id = u.id')
                ->where($whereClause)
                ->group('u.id');
                  
        $userData = $this->getDbTable()->fetchAll($sql);
        return $userData->toArray();
    }

    
    public function getUsersById( $id ){
        if( empty($id) ){
            return;
        }
        
        $whereClause = "u.id = {$id}";        
        $sql = $this->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('u' => 'users'), array('*'))
                ->join(array('ud' => 'user_details'), 'ud.user_id = u.id' )
                ->where($whereClause);
                
                 
        $userData = $this->getDbTable()->fetchAll($sql);
 
        return $userData[0]->toArray();
    }
    
    public function getUsersByProperty($params) {
        
       $conditions = array();
        if (!empty($params)) {
            foreach ($params as $property => $value) {
                if (!empty($value)) {
                    $conditions[] = "{$property} = '{$value}'";
                }
            }
        }
        $whereClause = (!empty($conditions)) ? implode(" OR ", $conditions) : null;
        
        $sql = $this->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('u' => 'users'), array('*'))
                ->join(array('ud' => 'user_details'), 'ud.user_id = u.id' )
                ->where($whereClause);
                
                 
        $userData = $this->getDbTable()->fetchAll($sql);
 
        return $userData[0]->toArray();
    }
    
    public function fetchUsersByUserTypes( $arrIntUserTypes = array() ) {
    	
    	if( true == empty( $arrIntUserTypes ) ) {
    		return false;
    	}

    	$whereClause = ' user_type IN (' . implode( ',' , $arrIntUserTypes ) . ')';
    
    	$sql = $this->getDbTable()->select()
						    	  ->setIntegrityCheck(false)
						    	  ->from(array('u' => 'users'), array('*'))
						    	  ->join(array('ud' => 'user_details'), 'ud.user_id = u.id' )
						    	  ->where($whereClause);
    
    	 return $this->getDbTable()->fetchAll($sql)->toArray();
    }
    
    public function deleteUser() {                
        $userDetails = new Model_UserDetails();
        $userDetails->user_id = $this->id;
        $userDetails->delete(); 
        
        return parent::delete();        
    }
}