<?php

class Application_Model_CaseMapper
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
            $this->setDbTable('Application_Model_DbTable_Cases');
        }
        return $this->_dbTable;
    }
    
    public function save($formData)
    {
        $userData = array();
        
        if( empty($formData) ){
            return false;
        }
        
        $userData = array(
      		'lawyer_id'			=> $formData['lawyer_id'],
        	'client_id' 		=> $formData['client_id'],
        	'date_of_allotment' => $formData['date_of_allotment'],
        	'due_date' 			=> $formData['due_date'],
        	'closed_by' 		=> $formData['closed_by'],
        	'closing_date' 		=> $formData['closing_date']
        );
       
        $userRow = $this->getDbTable()->insert($userData);
    }
 
    public function getCases( $params ){
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
    
    public function deleteCase( $uid ){         
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
}
?>
