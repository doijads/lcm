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
 * @subpackage Diagnostics
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
<<<<<<< HEAD
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Diagnostics
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @see Zend_Service_WindowsAzure_Diagnostics_Exception
 */
require_once 'Zend/Service/WindowsAzure/Diagnostics/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Diagnostics
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_WindowsAzure_Diagnostics_ConfigurationObjectBaseAbstract
{
    /**
     * Data
<<<<<<< HEAD
     * 
     * @var array
     */
    protected $_data = null;
    
    /**
     * Magic overload for setting properties
     * 
=======
     *
     * @var array
     */
    protected $_data = null;

    /**
     * Magic overload for setting properties
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string $name     Name of the property
     * @param string $value    Value to set
     */
    public function __set($name, $value) {
        if (array_key_exists(strtolower($name), $this->_data)) {
            $this->_data[strtolower($name)] = $value;
            return;
        }
<<<<<<< HEAD
	require_once 'Zend/Service/WindowsAzure/Diagnostics/Exception.php';
=======

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        throw new Zend_Service_WindowsAzure_Diagnostics_Exception("Unknown property: " . $name);
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
        if (array_key_exists(strtolower($name), $this->_data)) {
            return $this->_data[strtolower($name)];
        }
<<<<<<< HEAD
	require_once 'Zend/Service/WindowsAzure/Diagnostics/Exception.php';
=======

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        throw new Zend_Service_WindowsAzure_Diagnostics_Exception("Unknown property: " . $name);
    }
}