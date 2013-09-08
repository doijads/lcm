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
        $data['id'] = 1;
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            //$data['password'] = md5($data['password']);
        }
        
        $this->setOptions($data);
        
        $isUpdated = parent::update($data);
        
        return $isUpdated;
    }

    public function getUsers($params) {
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
        $sql = $this->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('u' => 'users'), array('*'))
                ->join(array('ud' => 'user_details'), 'ud.user_id = u.id')
                ->where($whereClause)
                ->group('u.id');
          
        $userData = $this->getDbTable()->fetchAll($sql);

        return $userData->toArray();
    }

    
    public function getUsersById($params) {
        //$userMapperObj = new Application_Model_UsersdetailMapper();           
        
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

    public function deleteUser() {                
        $userDetails = new Model_UserDetails();
        $userDetails->user_id = $this->id;
        $userDetails->delete(); 
        
        return parent::delete();        
    }

}

