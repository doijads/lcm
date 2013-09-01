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
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
<<<<<<< HEAD
=======
 * @see Zend_Service_WindowsAzure_Exception
 */
require_once 'Zend/Service/WindowsAzure/Exception.php';

/**
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @see Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
 */
require_once 'Zend/Service/WindowsAzure/Storage/StorageEntityAbstract.php';

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
 * @property string  $Container       Container name
 * @property string  $Name            Name
 * @property string  $LeaseId         Lease id
 * @property string  $LeaseTime       Time remaining in the lease period, in seconds. This header is returned only for a successful request to break the lease. It provides an approximation as to when the lease period will expire.
 */
class Zend_Service_WindowsAzure_Storage_LeaseInstance
<<<<<<< HEAD
	extends Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
{
    /**
     * Constructor
     * 
=======
    extends Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
{
    /**
     * Constructor
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string  $containerName   Container name
     * @param string  $name            Name
     * @param string  $leaseId         Lease id
     * @param string  $leaseTime       Time remaining in the lease period, in seconds. This header is returned only for a successful request to break the lease. It provides an approximation as to when the lease period will expire.
     */
<<<<<<< HEAD
    public function __construct($containerName, $name, $leaseId, $leaseTime) 
    {	        
        $this->_data = array(
            'container'        => $containerName,
            'name'             => $name,
        	'leaseid'          => $leaseId,
=======
    public function __construct($containerName, $name, $leaseId, $leaseTime)
    {	
        $this->_data = array(
            'container'        => $containerName,
            'name'             => $name,
            'leaseid'          => $leaseId,
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            'leasetime'        => $leaseTime
        );
    }
}
