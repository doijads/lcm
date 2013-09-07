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
        $this->createdBy = App_User::getUserId();

        return parent::save();
    }

    public function update($data = null) {
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = md5($data['password']);
        }
        $this->setOptions($data);

        return parent::update();
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

        //echo $sql ; exit;    
        $userData = $this->getDbTable()->fetchAll($sql);

        return $userData->toArray();
    }

    public function deleteUser($id) {
        $userDetails = new Model_UserDetails($id);
        $userDetails->delete();
        return parent::delete();
    }

}

