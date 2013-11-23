<?php

class Model_CaseTransactions extends App_Model {

    //Transaction used from Lawyer
    const TRANSACTION_TYPE_RECEIVABLE = 1;

    //Transaction used from Client
    const TRANSACTION_TYPE_PAYABLE = 2;

	protected $_tableClass = 'Model_DbTable_CaseTransactions';
    
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

    public function getCaseTransactionById( $id ){
        if( empty($id) ){
            return;
        }      
        $whereClause = "ct.case_id = {$id}";        
        $sql = $this->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('ct' => 'case_transactions'), array('*'))                
                ->where($whereClause);
                
        $userData = $this->getDbTable()->fetchAll($sql);
 
        return $userData->toArray();
    }
    
    public function getCaseTransactionExpensesById( $id ){
        if( empty($id) ){
            return;
        }      
        $whereClause = "ct.case_id = {$id} AND transaction_type_id = 1";        
        $sql = $this->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('ct' => 'case_transactions'), array('*'))                
                ->where($whereClause);
                
        $userData = $this->getDbTable()->fetchAll($sql);
 
        return $userData->toArray();
    }
    
    public function getCaseTransactionPaymentsById( $id ){
        if( empty($id) ){
            return;
        }      
        $whereClause = "ct.case_id = {$id} AND transaction_type_id = 2";        
        $sql = $this->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('ct' => 'case_transactions'), array('*'))                
                ->where($whereClause);
                
        $userData = $this->getDbTable()->fetchAll($sql);
 
        return $userData->toArray();
    }
    
    public static function getCaseTransactionTypes() {
        //return array( self::TRANSACTION_TYPE_RECEIVABLE => array( 'Amount ' )
    }
}