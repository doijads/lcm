<?php

class Model_CaseDocuments extends App_Model {

    //const CASE_DOCUMENT_PATH = date('Y') . date('m') . '/';
	protected $_tableClass = 'Model_DbTable_CaseDocuments';
    
    public function save( $formData ) {
        if( empty( $formData ) ){
            return false;
        }
        unset( $formData['submit'], $formData['MAX_FILE_SIZE'] );
        return $this->getDbTable()->insert($formData);
    }
 
    public function update( $data = null ) {
        if( is_array( $data ) ) {
            $this->setOptions( $data );  
        }
        unset( $formData['submit'], $formData['MAX_FILE_SIZE'] );
        return parent::update( $data );
    }

    public static function getCaseDocumentTypes() {
        //return array( self::TRANSACTION_TYPE_RECEIVABLE => array( 'Amount ' )
    }
    
    public function getCaseDocumentsList( $caseId ){
         if( !$caseId ){
             return;
         }
         $whereClause = "cd.case_id = {$caseId}";
         $sql = $this->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('cd' => 'case_documents'), array('*'))                
                ->where($whereClause);
                       
        $caseDocs = $this->getDbTable()->fetchAll($sql);        
        
        return $caseDocs->toArray();
        
    }
    
}