<?php

class Model_Status extends App_Model {
	
    protected $_tableClass = 'Model_DbTable_Status';
        
    public function getCases( $statusId ) {
       if( empty($statusId) ){
           return;
       }        
       $whereClause = ("id = {$statusId}");             
       $strSql = $this->getDbTable()->select()
                      ->setIntegrityCheck(false)
                      ->from(array('st' => 'status'), array('*'))
                      ->where($whereClause);
        return $this->getDbTable()->fetchAll($strSql)->toArray();
    }
    
}
?>
