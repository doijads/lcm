<?php

<<<<<<< HEAD

=======
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
class Application_Model_UsersMapper
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
            $this->setDbTable('Application_Model_DbTable_Users');
        }
        return $this->_dbTable;
    }
    
    public function save($formData)
    {
        $userData = array();
        $userDetailsData = array();
        
        if( empty($formData) ){
            return false;
        }
        
        $userData = array(
            'name'   => $formData['name'],
            'user_type'  => USER_LAWYER, //constant specified in configs/constant.ini file
            'home_phone' => $formData['home_phone'],
            'work_phone' => $formData['work_phone'],
            'mobile_number' => $formData['mobile_number'],
            'email' => $formData['email'],
            'fax_number' => $formData['fax_number'],
            'street_line'   => $formData['street_line'],
            'city'   => $formData['city'],
            'state' => $formData['state'],
            'country' => $formData['country'],
            'postal_code'   => $formData['postal_code'],            
            'created_on' => date('Y-m-d H:i:s'),
            'created_by' => USER_ADMIN          
        );
       
        $userRow = $this->getDbTable()->insert($userData);
        $uid = array('id' => $userRow);
         
        $userDetailsData = array(
              'user_id' => $uid['id'],
              'pan_card_number'  => $formData['pan_card_number'],
<<<<<<< HEAD
              'IFSC_code'  => $formData['IFSC_code'],
=======
              'IFSC_code '  => $formData['ifsc_code'],
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
              'bank_account_number'  => $formData['bank_account_number'],
              'service_tax_number'  => $formData['service_tax_number']           
        );
                
        $userMapperObj = new Application_Model_UsersdetailMapper();
<<<<<<< HEAD
        $userMapperObj->getDbTable()->insert($userDetailsData); 
      
=======
        $userMapperObj->getDbTable()->insert($userDetailsData);               
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }
 
    public function getUsers( $params ){
        //$userMapperObj = new Application_Model_UsersdetailMapper();           
        $conditions = array();
      
        
        if( !empty($params) ){
           foreach( $params as $property=>$value ){ 
                    if(!empty($value)){
                        $conditions[] =  "{$property} LIKE '%{$value}%'" ;               
                    }
           }           
        }        
                
        $whereClause = (!empty($conditions)) ? implode(" OR ", $conditions) : null;        
                  
        $sql = $this->getDbTable()->select()
                       ->setIntegrityCheck(false)
                       ->from(array('u'=>'users'),array('*'))                                     
                       ->join(array('ud'=>'user_details'),'ud.user_id = u.id')
                       ->where($whereClause)                                      
                       ->group('u.id');   
        
        //echo $sql ; exit;    
        $userData = $this->getDbTable()->fetchAll($sql);
                     
        //$result = $model->getTable()->delete('creative_id = ' . $this->id);
        return $userData->toArray();
    }
<<<<<<< HEAD
    
    public function deleteUsers( $uid ){         
        $userDetailsObj = new Application_Model_UsersdetailMapper();
        $userDetailsObj->getDbTable()->delete('user_id = '. $uid );
        $userData = $this->getDbTable()->delete('id = '. $uid );
        
        if( $userData ){
            return true;
        }
    }
  
    
     public function find($id){
        //get the row
        $row  = $this->getDbTable()->find($id);
        return $row->toArray();        
        
    }
=======
  
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
}
?>
