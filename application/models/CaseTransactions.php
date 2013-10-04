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

    public static function getCaseTransactionTypes() {
        //return array( self::TRANSACTION_TYPE_RECEIVABLE => array( 'Amount ' )
    }
}