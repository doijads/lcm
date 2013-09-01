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
 * @version    $Id: QueueInstance.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: QueueInstance.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
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
 * @property string  $Name                     Name of the queue
 * @property array   $Metadata                 Key/value pairs of meta data
 * @property integer $ApproximateMessageCount  The approximate number of messages in the queue
 */
class Zend_Service_WindowsAzure_Storage_QueueInstance
<<<<<<< HEAD
	extends Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
{
    /**
     * Constructor
     * 
     * @param string $name          Name
     * @param array  $metadata      Key/value pairs of meta data
     */
    public function __construct($name, $metadata = array()) 
=======
    extends Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
{
    /**
     * Constructor
     *
     * @param string $name          Name
     * @param array  $metadata      Key/value pairs of meta data
     */
    public function __construct($name, $metadata = array())
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    {
        $this->_data = array(
            'name'         => $name,
            'metadata'     => $metadata,
            'approximatemessagecount' => 0
        );
    }
}
