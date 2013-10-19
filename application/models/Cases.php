<?php

class Model_Cases extends App_Model {
	 
    const CASE_REGISTER = 1;
    const CASE_HISTORY  = 2;
    const CASE_DOCUMENT = 3;
    const CASE_BUDGET   = 4;
   
    protected $_tableClass = 'Model_DbTable_Cases';
    
   public function save( $formData ) {
        if( empty( $formData ) ){
            return false;
        }
        unset( $formData['submit'] );
        return $this->getDbTable()->insert($formData);
    }
 
    public function update( $data = null ) {
        if( is_array( $data ) ) {
            $this->setOptions( $data );  
        }
         
        return parent::update( $data );
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
    
    public function getCaseDetailsByClientId( $clientId ){        
        $whereClause = "c.client_id = {$clientId}";
        
        $caseDocFields = array(
                'cd.id as case_doc_id', 
                'cd.name as document_name'
            );
        
        $caseHistoryFields = array(
                'ch.id as case_history_id', 
                'ch.hearing_date as hearing_date',
                'ch.next_hearing_date as next_hearing_date',
                'ch.judge_name as judge_name'
            );
        
        $caseTransactionFields = array(
                'ct.id as case_transaction_id',
                'ct.amount as amount',
                'ct.transaction_type_id as trasaction_type_id',
                'ct.transaction_details as trasaction_details'
            );        
        
        $strSql = $this->getDbTable()->select()
                      ->setIntegrityCheck(false)
                      ->from(array('c' => 'cases'), array('c.*'))
                      ->joinLeft(array('cd' => 'case_documents'), 'cd.case_id = c.id', $caseDocFields)
                      ->joinLeft(array('ch' => 'case_history'), 'ch.case_id = c.id', $caseHistoryFields)
                      ->joinLeft(array('ct' => 'case_transactions'), 'ct.case_id = c.id', $caseTransactionFields)
                      ->where($whereClause);
                
        return $this->getDbTable()->fetchAll($strSql)->toArray();
    }
    
    public function getCaseDetailsByLawyerId( $lawyerId ){
        $whereClause = "c.lawyer_id = {$lawyerId}";
         $caseDocFields = array(
                'cd.id as case_doc_id', 
                'cd.name as document_name'
            );
                
        $caseHistoryFields = array(
                'ch.id as case_history_id', 
                'ch.hearing_date as hearing_date',
                'ch.next_hearing_date as next_hearing_date',
                'ch.judge_name as judge_name'
            );
    
        $caseTransactionFields = array(
                'ct.id as case_transaction_id',
                'ct.amount as amount',
                'ct.transaction_type_id as trasaction_type_id',
                'ct.transaction_details as trasaction_details'
            );        
        
        $strSql = $this->getDbTable()->select()
                      ->setIntegrityCheck(false)
                      ->from(array('c' => 'cases'), array('c.*'))
                      ->joinLeft(array('cd' => 'case_documents'), 'cd.case_id = c.id', $caseDocFields)
                      ->joinLeft(array('ch' => 'case_history'), 'ch.case_id = c.id', $caseHistoryFields)
                      ->joinLeft(array('ct' => 'case_transactions'), 'ct.case_id = c.id', $caseTransactionFields)
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
  
    public function getCaseDetailsById( $caseId ){
        $whereClause = "c.id = {$caseId}";
         $caseDocFields = array(
                'cd.id as case_doc_id', 
                'cd.name as document_name'
            );
        
        $caseHistoryFields = array(
                'ch.id as case_history_id', 
                'ch.hearing_date as hearing_date',
                'ch.next_hearing_date as next_hearing_date',
                'ch.judge_name as judge_name'
            );
        
        $caseTransactionFields = array(
                'ct.id as case_transaction_id',
                'ct.amount as amount',
                'ct.transaction_type_id as trasaction_type_id',
                'ct.transaction_details as trasaction_details'
            );        
        
        $strSql = $this->getDbTable()->select()
                      ->setIntegrityCheck(false)
                      ->from(array('c' => 'cases'), array('c.*'))
                      ->joinLeft(array('cd' => 'case_documents'), 'cd.case_id = c.id', $caseDocFields)
                      ->joinLeft(array('ch' => 'case_history'), 'ch.case_id = c.id', $caseHistoryFields)
                      ->joinLeft(array('ct' => 'case_transactions'), 'ct.case_id = c.id', $caseTransactionFields)
                      ->where($whereClause);
        
        return $this->getDbTable()->fetchAll($strSql)->toArray();         
        
    }
    
     public function find( $id ) {
        //get the row
        $row  = $this->getDbTable()->find($id);
        return $row->toArray();        
    }
}
?>
