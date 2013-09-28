<?php

class Model_Cases extends App_Model {
	
    protected $_tableClass = 'Model_DbTable_Cases';
    
    public function save( $formData ) {
        if( empty( $formData ) ){
            return false;
        }
        unset( $formData['submit'] );
        $userRow = $this->getDbTable()->insert($formData);
    }
 
    public function getCases( $params ) {
        $conditions = array();
      
        if( !empty($params) ){
           foreach( $params as $property=>$value ){ 
              if(!empty($value)){
                if( !is_numeric($value)) {
                  $conditions[] =  "{$property} LIKE '%{$value}%'" ;     
                } else {
                  $conditions[] =  "{$property} = {$value}" ;
                }
              }
           }           
        }        
                
       $whereClause = (!empty($conditions)) ? implode(" AND ", $conditions) : '1=1';        
                  
       $strSql = $this->getDbTable()->select()
                      ->setIntegrityCheck(false)
                      ->from(array('c' => 'cases'), array('*'))
                      ->where($whereClause);
        return $this->getDbTable()->fetchAll($strSql)->toArray();
    }
    
    public function deleteCase( $uid ) {         
        $userDetailsObj = new Application_Model_UsersdetailMapper();
        $userDetailsObj->getDbTable()->delete('user_id = '. $uid );
        $caseData = $this->getDbTable()->delete('id = '. $uid );
        
        if( $caseData ){
            return true;
        }
    }
  
     public function find( $id ) {
        //get the row
        $row  = $this->getDbTable()->find($id);
        return $row->toArray();        
    }
}
?>
