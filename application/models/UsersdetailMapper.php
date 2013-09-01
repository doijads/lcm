<?php

class Application_Model_UsersdetailMapper
{
    protected $_dbTable;
    
    public function setDbTable($dbTable)
    {
        //if $dbTable is passed as a string
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        //if $dbTable is not an instance of a class that extends Zend_Db_Table_Abstract
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
    
    //instance of Application_Model_DbTable_Users
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_UsersDetails');
        }
        return $this->_dbTable;
    }
    
    public function save($formData)
    {
        if( empty($formData) ){
            return false;
        }
        
        $data = array(
            'name'   => $formData['name'],
            'home_phone' => $formData['home_phone'],
            'work_phone' => $formData['work_phone'],
         
        );
// 
//        if (null === ($id = $guestbook->getId())) {
//            unset($data['id']);
//            $this->getDbTable()->insert($data);
//        } else {
//            $this->getDbTable()->update($data, array('id = ?' => $id));
//        }
    }
<<<<<<< HEAD
     
    //public function save($id);
    public function find($id){       
       //get the row
        $row  = $this->getDbTable()->find($id);
        return $row->toArray();                
    }
    
=======
 
    
    //public function save($id);
    //public function find($id);
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    //public function fetchAll();
}
?>
