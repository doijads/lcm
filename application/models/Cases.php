<?php

class Model_Cases extends App_Model {
	
    protected $_tableClass = 'Model_DbTable_Cases';
    
    public function save( $formData ) {
        if( empty( $formData ) ){
            return false;
        }
        
        $caseData = array( 'lawyer_id'			=> $formData['lawyer_id'],
				           'client_id' 			=> $formData['client_id'],
				           'date_of_allotment' 	=> $formData['date_of_allotment'],
				           'due_date' 			=> $formData['due_date'],
				           'closed_by' 			=> $formData['closed_by'],
				           'closing_date' 		=> $formData['closing_date'] );
       
        $userRow = $this->getDbTable()->insert($caseData);
    }
 
    public function getCases( $params ){
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
        
        $caseData = $this->getDbTable()->fetchAll($sql);
        return $caseData->toArray();
    }
    
    public function deleteCase( $uid ){         
        $userDetailsObj = new Application_Model_UsersdetailMapper();
        $userDetailsObj->getDbTable()->delete('user_id = '. $uid );
        $caseData = $this->getDbTable()->delete('id = '. $uid );
        
        if( $caseData ){
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
