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
 * @version    $Id: QueueMessage.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: QueueMessage.php 23775 2011-03-01 17:25:24Z ralph $
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
 * @property string $MessageId         Message ID
 * @property string $InsertionTime     Insertion time
 * @property string $ExpirationTime    Expiration time
 * @property string $PopReceipt  	   Receipt verification for deleting the message from queue.
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property string $MessageId         Message ID
 * @property string $InsertionTime     Insertion time
 * @property string $ExpirationTime    Expiration time
 * @property string $PopReceipt         Receipt verification for deleting the message from queue.
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @property string $TimeNextVisible   Next time the message is visible in the queue
 * @property int    $DequeueCount      Number of times the message has been dequeued. This value is incremented each time the message is subsequently dequeued.
 * @property string $MessageText       Message text
 */
class Zend_Service_WindowsAzure_Storage_QueueMessage
<<<<<<< HEAD
	extends Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
{
    /**
     * Constructor
     * 
     * @param string $messageId         Message ID
     * @param string $insertionTime     Insertion time
     * @param string $expirationTime    Expiration time
     * @param string $popReceipt  	    Receipt verification for deleting the message from queue.
=======
    extends Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
{
    /**
     * Constructor
     *
     * @param string $messageId         Message ID
     * @param string $insertionTime     Insertion time
     * @param string $expirationTime    Expiration time
     * @param string $popReceipt          Receipt verification for deleting the message from queue.
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string $timeNextVisible   Next time the message is visible in the queue
     * @param int    $dequeueCount      Number of times the message has been dequeued. This value is incremented each time the message is subsequently dequeued.
     * @param string $messageText       Message text
     */
<<<<<<< HEAD
    public function __construct($messageId, $insertionTime, $expirationTime, $popReceipt, $timeNextVisible, $dequeueCount, $messageText) 
=======
    public function __construct($messageId, $insertionTime, $expirationTime, $popReceipt, $timeNextVisible, $dequeueCount, $messageText)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    {
        $this->_data = array(
            'messageid'       => $messageId,
            'insertiontime'   => $insertionTime,
            'expirationtime'  => $expirationTime,
            'popreceipt'      => $popReceipt,
            'timenextvisible' => $timeNextVisible,
<<<<<<< HEAD
        	'dequeuecount'    => $dequeueCount,
=======
            'dequeuecount'    => $dequeueCount,
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            'messagetext'     => $messageText
        );
    }
}
