<?php
abstract class App_Model {

    protected $_data;
    protected $_tableInstance;

    public function __construct($options = null) {
        //init db fields those are missing from model definition.
        $this->_initFields();

        $this->setOptions($options);
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->_data;
    }

    /**
     * Convert camelcased string to underscored string
     * @param string $string
     * @return string
     */
    public function toUnderScore($string) {
        return preg_replace("/([A-Z])/e", "'_'.strtolower('\\1')", $string);
    }

    /**
     * Convert underscored string to camelCased string
     * @param string $string
     * @return string
     */
    public function toCamelCase($string) {
        return preg_replace("/\_(.)/e", "strtoupper('\\1')", $string);
    }

    public function setOptions($options) {
        if (is_array($options) || $options instanceof Zend_Db_Table_Row_Abstract) {
            foreach ($options as $option => $value) {
                $property = $this->toCamelCase($option);
                $this->$property = $value;
            }
        }
    }

    public function get($name) {
        return $this->__get($name);
    }

    public function __get($name) {
        $methodName = 'get' . $name;
        $field = $this->toUnderScore($name);

        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        } else if (array_key_exists($field, $this->_data)) {
            return $this->_data[$field];
        } else {
            throw new Exception($field . ' property not found in ' . get_called_class());
        }
    }

    public function set($name, $value) {
        $this->__set($name, $value);
    }

    public function __set($name, $value) {
        $methodName = 'set' . $name;
        $field = $this->toUnderScore($name);

        if (method_exists($this, $methodName)) {
            $this->$methodName($value);
        } else if (array_key_exists($field, $this->_data)) {
            $this->_data[$field] = $value;
        }
    }

    /**
     * @return Zend_Db_Table
     */
    public function getTable() {
        if (!($this->_tableInstance instanceof Zend_Db_Table)) {
            $this->setTable();
        }

        return $this->_tableInstance;
    }

    /**
     * @return Zend_Db_Table
     */
    public function setTable() {
        if (!($this->_tableInstance instanceof Zend_Db_Table)) {
            if (is_string($this->_tableClass)) {
                $className = $this->_tableClass;
                $this->_tableInstance = new $className();
            } else {
                throw new Exception('Table class must be set');
            }
        }
    }

    /**
     * @return Zend_Db_Table
     */
    public function getTableName() {
        return $table = $this->getTable()->getTableName();
    }

    /**
     * @return Zend_Db_Table
     */
    public function getTableCols() {
        $name = $this->getTableName();
        $sql = "SHOW COLUMNS FROM {$name}";
        $fields = App_Model::executeQuery($sql);
        $cols = array();
        foreach ($fields as $index => $field) {
            $cols[$field->Field] = $field->Default;
        }

        return $cols;
    }

    public function setDbTable() {
        $this->setTable();
    }

    //instance of Application_Model_DbTable_Users
    public function getDbTable() {
        return $this->getTable();
    }

    /**
     * @return array Array of where parts
     */
    protected function _getIdentityWhere() {
        $table = $this->getTable();
        $primaryKeys = $table->info(Zend_Db_Table_Abstract::PRIMARY);
        $tableName = $table->info(Zend_Db_Table_Abstract::NAME);

        $where = array();
        foreach ($primaryKeys as $primary) {
            $where["{$tableName}.{$primary}" . ' = ?'] = $this->_data[$primary];
        }
        return $where;
    }

    /**
     *
     * @return int
     */
    public function delete() {
        return $this->getTable()->delete($this->_getIdentityWhere());
    }

    public function update($data = null) {
        $table = $this->getTable();

        if (is_array($data)) {
            $options = $data;
        } else {
            $options = $this->_data;
        }

        $primaries = $table->info(Zend_Db_Table_Abstract::PRIMARY);

        foreach ($primaries as $primary) {
            unset($options[$primary]);
        }

        return $this->getTable()->update($options, $this->_getIdentityWhere());
    }

    public function find() {
        $table = $this->getTable();
        $primaries = $table->info(Zend_Db_Table_Abstract::PRIMARY);

        $where = $this->_getIdentityWhere();
        $row = $table->fetchRow($where);
        if ($row === null) {
            return false;
        } else {
            $this->setOptions($row);
            return true;
        }
    }

    public function fetch($ops = array()) {
        $table = $this->getTable();
        $select = $table->select();

        $options = array('order' => array(), 'where' => array(), 'columns' => array());
        $options = array_merge($options, $ops);

        if (!empty($options['columns'])) {
            $select->from($table, $options['columns']);
        }

        if (!empty($options['order'])) {
            foreach ($options['order'] as $cond) {
                $select->order($cond);
            }
        }

        if (!empty($options['where'])) {
            foreach ($options['where'] as $cond) {
                if (is_array($cond) && count($cond) == 2) {
                    $cond = $table->getAdapter()->quoteInto($cond[0], $cond[1]);
                }

                $select->where($cond);
            }
        }

        return $table->fetchAll($select);
    }

    public function save() {
        $table = $this->getTable();

        $primary = $table->insert($this->_data);
        $primaryKeys = $table->info(Zend_Db_Table_Abstract::PRIMARY);

        if (is_string($primary) && count($primaryKeys) == 1) {
            $this->setOptions(array($primaryKeys[1] => $primary));
        } else if (is_array($primary)) {
            $this->setOptions($primary);
        }

        return true;
    }

    public function initFromField($fieldName) {
        $table = $this->getTable();
        $select = $table->select();
        $select->where($fieldName . ' = ?', $this->_data[$fieldName]);

        $row = $table->fetchRow($select);
        if ($row === null) {
            return false;
        } else {
            $this->setOptions($row->toArray());
            return true;
        }
    }

    /**
     * @param array $fields camelcased fields
     */
    public function loadFromFields(array $fields) {
        $where = array();
        foreach ($fields as $field) {
            $where[$this->toUnderScore($field) . ' = ?'] = $this->$field;
        }

        $table = $this->getTable();
        $row = $table->fetchRow($where);
        if ($row) {
            $this->setOptions($row);
            return true;
        } else {
            return false;
        }
    }

    /**
     * executes generic SQL statement and returns all results as array
     *
     * @param string $sql
     * @param string $fetchMode
     * @return array
     * @author Kiran
     */
    public static function executeQuery($sql, $fetchMode = PDO::FETCH_OBJ) {
        $pdo = Zend_Db_Table::getDefaultAdapter();
        $conn = $pdo->getConnection();
        $stmt = $conn->query($sql);

        try {
            return $stmt->fetchAll($fetchMode);
        } catch (PDOException $e) {
            error_log($e->getMessage());

            echo $e->getMessage();
        }
    }

    private function _initFields() {
        $cols = $this->getTableCols();

        foreach ($cols as $name => $dbDefault) {
            if (!is_array($this->_data)) {
                $this->_data = array();
            }
            if (!array_key_exists($name, $this->_data)) {
                $this->_data[$name] = $dbDefault;
            }
        }
    }

    /**
     *
     * @param $name string name of variable
     */
    public function free() {
        unset($this);
    }

}