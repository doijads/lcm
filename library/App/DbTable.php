<?php
abstract class App_DbTable extends Zend_Db_Table_Abstract {

    /** Columns */
    protected $_cols;

    /** Primary Key */
    protected $_primary;

    public function __construct() {
        if (empty($this->_cols)) {
            $this->_initColumnNames();
        }
        parent::__construct();
    }

    private function _initColumnNames() {
        $sql = "SHOW COLUMNS FROM {$this->_name}";
        $fields = App_Model::executeQuery($sql);
        foreach ($fields as $index => $field) {
            $this->_cols[] = $field->Field;
        }
    }

    public function getTableName() {
        return $this->_name;
    }

    public function geTableColumnNames() {
        return $this->_cols;
    }

}
