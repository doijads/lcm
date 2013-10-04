<?php

class Model_CaseHistory extends App_Model {

	protected $_tableClass = 'Model_DbTable_CaseHistory';
    
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
}