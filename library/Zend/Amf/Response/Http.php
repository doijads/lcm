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
 * @package    Zend_Amf
 * @subpackage Response
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Http.php 24593 2012-01-05 20:35:02Z matthew $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Http.php 23775 2011-03-01 17:25:24Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */

/** Zend_Amf_Response */
require_once 'Zend/Amf/Response.php';

/**
 * Creates the proper http headers and send the serialized AMF stream to standard out.
 *
 * @package    Zend_Amf
 * @subpackage Response
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Response_Http extends Zend_Amf_Response
{
    /**
     * Create the application response header for AMF and sends the serialized AMF string
     *
     * @return string
     */
    public function getResponse()
    {
        if (!headers_sent()) {
<<<<<<< HEAD
            if ($this->isIeOverSsl()) {
                header('Cache-Control: cache, must-revalidate');
                header('Pragma: public');
            } else {
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
            }
            header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
=======
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
            header('Pragma: no-cache');
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            header('Content-Type: application/x-amf');
        }
        return parent::getResponse();
    }
<<<<<<< HEAD

    protected function isIeOverSsl()
    {
        $ssl = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : false;
        if (!$ssl || ($ssl == 'off')) {
            // IIS reports "off", whereas other browsers simply don't populate
            return false;
        }

        $ua  = $_SERVER['HTTP_USER_AGENT'];
        if (!preg_match('/; MSIE \d+\.\d+;/', $ua)) {
            // Not MicroSoft Internet Explorer
            return false;
        }

        return true;
    }
=======
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
}
