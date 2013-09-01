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
 * @version    $Id: BlobContainer.php 24593 2012-01-05 20:35:02Z matthew $
 */

=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: BlobContainer.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Service_WindowsAzure_Exception
 */
require_once 'Zend/Service/WindowsAzure/Exception.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
 */
require_once 'Zend/Service/WindowsAzure/Storage/StorageEntityAbstract.php';
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * 
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @property string $Name          Name of the container
 * @property string $Etag          Etag of the container
 * @property string $LastModified  Last modified date of the container
 * @property array  $Metadata      Key/value pairs of meta data
 */
class Zend_Service_WindowsAzure_Storage_BlobContainer
{
    /**
     * Data
<<<<<<< HEAD
     * 
     * @var array
     */
    protected $_data = null;
    
    /**
     * Constructor
     * 
=======
     *
     * @var array
     */
    protected $_data = null;

    /**
     * Constructor
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string $name          Name
     * @param string $etag          Etag
     * @param string $lastModified  Last modified date
     * @param array  $metadata      Key/value pairs of meta data
     */
<<<<<<< HEAD
    public function __construct($name, $etag, $lastModified, $metadata = array()) 
=======
    public function __construct($name, $etag, $lastModified, $metadata = array())
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    {
        $this->_data = array(
            'name'         => $name,
            'etag'         => $etag,
            'lastmodified' => $lastModified,
            'metadata'     => $metadata
        );
    }
<<<<<<< HEAD
    
    /**
     * Magic overload for setting properties
     * 
=======

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

        throw new Exception("Unknown property: " . $name);
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

        throw new Exception("Unknown property: " . $name);
    }
}
