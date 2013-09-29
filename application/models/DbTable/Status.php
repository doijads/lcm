<?php

class Model_DbTable_Status extends App_DbTable {

    /** Table name */
    protected $_name = 'status';

    /**
     * Primary Key
     * Gross hack by Kiran
     * 
     * defined here to suppress db table primary key
     */
    //protected $_primary = 'id';

    /**
     * Table Columns
     * 
     * Gross hack by Kiran
     * Columns can be defined here to suppress the db column fetch
     */
    //protected $_cols = array( 'id', 'name');
}

