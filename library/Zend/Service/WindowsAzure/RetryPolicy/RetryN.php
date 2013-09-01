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
 * @subpackage RetryPolicy
<<<<<<< HEAD
 * @version    $Id: RetryN.php 24593 2012-01-05 20:35:02Z matthew $
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @version    $Id: RetryN.php 23775 2011-03-01 17:25:24Z ralph $
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract
 */
require_once 'Zend/Service/WindowsAzure/RetryPolicy/RetryPolicyAbstract.php';

/**
<<<<<<< HEAD
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage RetryPolicy
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @see Zend_Service_WindowsAzure_RetryPolicy_Exception
 */
require_once 'Zend/Service/WindowsAzure/RetryPolicy/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage RetryPolicy
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_RetryPolicy_RetryN extends Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract
{
    /**
     * Number of retries
<<<<<<< HEAD
     * 
     * @var int
     */
    protected $_retryCount = 1;
    
    /**
     * Interval between retries (in milliseconds)
     * 
     * @var int
     */
    protected $_retryInterval = 0;
    
    /**
     * Constructor
     * 
=======
     *
     * @var int
     */
    protected $_retryCount = 1;

    /**
     * Interval between retries (in milliseconds)
     *
     * @var int
     */
    protected $_retryInterval = 0;

    /**
     * Constructor
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param int $count                    Number of retries
     * @param int $intervalBetweenRetries   Interval between retries (in milliseconds)
     */
    public function __construct($count = 1, $intervalBetweenRetries = 0)
    {
        $this->_retryCount = $count;
        $this->_retryInterval = $intervalBetweenRetries;
    }
<<<<<<< HEAD
    
    /**
     * Execute function under retry policy
     * 
=======

    /**
     * Execute function under retry policy
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param string|array $function       Function to execute
     * @param array        $parameters     Parameters for function call
     * @return mixed
     */
    public function execute($function, $parameters = array())
    {
        $returnValue = null;
<<<<<<< HEAD
        
=======

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        for ($retriesLeft = $this->_retryCount; $retriesLeft >= 0; --$retriesLeft) {
            try {
                $returnValue = call_user_func_array($function, $parameters);
                return $returnValue;
            } catch (Exception $ex) {
                if ($retriesLeft == 1) {
<<<<<<< HEAD
                    require_once 'Zend/Service/WindowsAzure/RetryPolicy/Exception.php';
                    throw new Zend_Service_WindowsAzure_RetryPolicy_Exception("Exceeded retry count of " . $this->_retryCount . ". " . $ex->getMessage());
                }
                    
=======
                    throw new Zend_Service_WindowsAzure_RetryPolicy_Exception("Exceeded retry count of " . $this->_retryCount . ". " . $ex->getMessage());
                }

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
                usleep($this->_retryInterval * 1000);
            }
        }
    }
}