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
 * @package    Zend_XmlRpc
 * @subpackage Value
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DateTime.php 24593 2012-01-05 20:35:02Z matthew $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DateTime.php 23775 2011-03-01 17:25:24Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */


/**
 * Zend_XmlRpc_Value_Scalar
 */
require_once 'Zend/XmlRpc/Value/Scalar.php';


/**
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Value
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Value_DateTime extends Zend_XmlRpc_Value_Scalar
{
    /**
     * PHP compatible format string for XML/RPC datetime values
     *
     * @var string
     */
    protected $_phpFormatString = 'Ymd\\TH:i:s';

    /**
     * ISO compatible format string for XML/RPC datetime values
     *
     * @var string
     */
    protected $_isoFormatString = 'yyyyMMddTHH:mm:ss';

    /**
     * Set the value of a dateTime.iso8601 native type
     *
     * The value is in iso8601 format, minus any timezone information or dashes
     *
     * @param mixed $value Integer of the unix timestamp or any string that can be parsed
     *                     to a unix timestamp using the PHP strtotime() function
     */
    public function __construct($value)
    {
        $this->_type = self::XMLRPC_TYPE_DATETIME;

        if ($value instanceof Zend_Date) {
            $this->_value = $value->toString($this->_isoFormatString);
        } elseif ($value instanceof DateTime) {
            $this->_value = $value->format($this->_phpFormatString);
        } elseif (is_numeric($value)) { // The value is numeric, we make sure it is an integer
            $this->_value = date($this->_phpFormatString, (int)$value);
        } else {
<<<<<<< HEAD
            $timestamp = new DateTime($value);
            if ($timestamp === false) { // cannot convert the value to a timestamp
=======
            $timestamp = strtotime($value);
            if ($timestamp === false || $timestamp == -1) { // cannot convert the value to a timestamp
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
                require_once 'Zend/XmlRpc/Value/Exception.php';
                throw new Zend_XmlRpc_Value_Exception('Cannot convert given value \''. $value .'\' to a timestamp');
            }

<<<<<<< HEAD
            $this->_value = $timestamp->format($this->_phpFormatString); // Convert the timestamp to iso8601 format
=======
            $this->_value = date($this->_phpFormatString, $timestamp); // Convert the timestamp to iso8601 format
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        }
    }

    /**
     * Return the value of this object as iso8601 dateTime value
     *
     * @return int As a Unix timestamp
     */
    public function getValue()
    {
        return $this->_value;
    }
}
