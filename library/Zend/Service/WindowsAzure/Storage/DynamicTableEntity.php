<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DynamicTableEntity.php 24593 2012-01-05 20:35:02Z matthew $
 */

=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DynamicTableEntity.php 23775 2011-03-01 17:25:24Z ralph $
 */


/**
 * @see Zend_Service_WindowsAzure_Exception
 */
require_once 'Zend/Service/WindowsAzure/Exception.php';

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
/**
 * @see Zend_Service_WindowsAzure_Storage_TableEntity
 */
require_once 'Zend/Service/WindowsAzure/Storage/TableEntity.php';

<<<<<<< HEAD
=======

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_DynamicTableEntity extends Zend_Service_WindowsAzure_Storage_TableEntity
{   
    /**
     * Dynamic properties
     * 
     * @var array
     */
    protected $_dynamicProperties = array();
    
    /**
     * Magic overload for setting properties
     * 
     * @param string $name     Name of the property
     * @param string $value    Value to set
     */
    public function __set($name, $value) {      
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_DynamicTableEntity extends Zend_Service_WindowsAzure_Storage_TableEntity
{
    /**
     * Dynamic properties
     *
     * @var array
     */
    protected $_dynamicProperties = array();

    /**
     * Magic overload for setting properties
     *
     * @param string $name     Name of the property
     * @param string $value    Value to set
     */
    public function __set($name, $value) {
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        $this->setAzureProperty($name, $value, null);
    }

    /**
     * Magic overload for getting properties
<<<<<<< HEAD
     * 
=======
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string $name     Name of the property
     */
    public function __get($name) {
        return $this->getAzureProperty($name);
    }
<<<<<<< HEAD
    
    /**
     * Set an Azure property
     * 
=======

    /**
     * Set an Azure property
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string $name Property name
     * @param mixed $value Property value
     * @param string $type Property type (Edm.xxxx)
     * @return Zend_Service_WindowsAzure_Storage_DynamicTableEntity
     */
    public function setAzureProperty($name, $value = '', $type = null)
    {
        if (strtolower($name) == 'partitionkey') {
            $this->setPartitionKey($value);
        } else if (strtolower($name) == 'rowkey') {
            $this->setRowKey($value);
        } else if (strtolower($name) == 'etag') {
            $this->setEtag($value);
        } else {
            if (!array_key_exists(strtolower($name), $this->_dynamicProperties)) {
                // Determine type?
<<<<<<< HEAD
                if (is_null($type)) {
=======
                if ($type === null) {
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
                    $type = 'Edm.String';
                    if (is_int($value)) {
                        $type = 'Edm.Int32';
                    } else if (is_float($value)) {
                        $type = 'Edm.Double';
                    } else if (is_bool($value)) {
                        $type = 'Edm.Boolean';
<<<<<<< HEAD
                    } else if ($value instanceof DateTime || $this->_convertToDateTime($value) !== false) {
                        if (!$value instanceof DateTime) {
                            $value = $this->_convertToDateTime($value);
                        }
                        $type = 'Edm.DateTime';
                    }
                }
                
                // Set dynamic property
                $this->_dynamicProperties[strtolower($name)] = (object)array(
                        'Name'  => $name,
                    	'Type'  => $type,
                    	'Value' => $value,
                    );
            }
            
            // Set type?
            if (!is_null($type)) {
            	$this->_dynamicProperties[strtolower($name)]->Type = $type;
            	
            	// Try to convert the type
            	if ($type == 'Edm.Int32' || $type == 'Edm.Int64') {
            		$value = intval($value);
            	} else if ($type == 'Edm.Double') {
            		$value = floatval($value);
            	} else if ($type == 'Edm.Boolean') {
            		if (!is_bool($value)) {
            			$value = strtolower($value) == 'true';
            		}
            	} else if ($type == 'Edm.DateTime') {
            		if (!$value instanceof DateTime) {
                    	$value = $this->_convertToDateTime($value);
                    }
            	}
            }
       
    		// Set value
=======
                    }
                }

                // Set dynamic property
                $this->_dynamicProperties[strtolower($name)] = (object)array(
                        'Name'  => $name,
                        'Type'  => $type,
                        'Value' => $value,
                    );
            }

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            $this->_dynamicProperties[strtolower($name)]->Value = $value;
        }
        return $this;
    }
<<<<<<< HEAD
    
    /**
     * Set an Azure property type
     * 
=======

    /**
     * Set an Azure property type
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string $name Property name
     * @param string $type Property type (Edm.xxxx)
     * @return Zend_Service_WindowsAzure_Storage_DynamicTableEntity
     */
    public function setAzurePropertyType($name, $type = 'Edm.String')
    {
        if (!array_key_exists(strtolower($name), $this->_dynamicProperties)) {
<<<<<<< HEAD
            $this->setAzureProperty($name, '', $type);            
        } else {
            $this->_dynamicProperties[strtolower($name)]->Type = $type;   
        }
        return $this;
    }
    
    /**
     * Get an Azure property
     * 
=======
            $this->setAzureProperty($name, '', $type);
        } else {
            $this->_dynamicProperties[strtolower($name)]->Type = $type;
        }
        return $this;
    }

    /**
     * Get an Azure property
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string $name Property name
     * @param mixed $value Property value
     * @param string $type Property type (Edm.xxxx)
     * @return Zend_Service_WindowsAzure_Storage_DynamicTableEntity
     */
    public function getAzureProperty($name)
    {
        if (strtolower($name) == 'partitionkey') {
            return $this->getPartitionKey();
        }
        if (strtolower($name) == 'rowkey') {
            return $this->getRowKey();
        }
        if (strtolower($name) == 'etag') {
            return $this->getEtag();
        }

        if (!array_key_exists(strtolower($name), $this->_dynamicProperties)) {
<<<<<<< HEAD
            $this->setAzureProperty($name);            
=======
            $this->setAzureProperty($name);
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        }

        return $this->_dynamicProperties[strtolower($name)]->Value;
    }
<<<<<<< HEAD
    
    /**
     * Get an Azure property type
     * 
=======

    /**
     * Get an Azure property type
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string $name Property name
     * @return string Property type (Edm.xxxx)
     */
    public function getAzurePropertyType($name)
    {
        if (!array_key_exists(strtolower($name), $this->_dynamicProperties)) {
<<<<<<< HEAD
            $this->setAzureProperty($name, '', $type);            
        }
        
        return $this->_dynamicProperties[strtolower($name)]->Type;
    }
    
    /**
     * Get Azure values
     * 
=======
            $this->setAzureProperty($name, '', $type);
        }

        return $this->_dynamicProperties[strtolower($name)]->Type;
    }

    /**
     * Get Azure values
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @return array
     */
    public function getAzureValues()
    {
        return array_merge(array_values($this->_dynamicProperties), parent::getAzureValues());
    }
<<<<<<< HEAD
    
    /**
     * Set Azure values
     * 
=======

    /**
     * Set Azure values
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param array $values
     * @param boolean $throwOnError Throw Zend_Service_WindowsAzure_Exception when a property is not specified in $values?
     * @throws Zend_Service_WindowsAzure_Exception
     */
    public function setAzureValues($values = array(), $throwOnError = false)
    {
        // Set parent values
        parent::setAzureValues($values, false);
<<<<<<< HEAD
        
        // Set current values
        foreach ($values as $key => $value) 
=======

        // Set current values
        foreach ($values as $key => $value)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        {
            $this->$key = $value;
        }
    }
}
