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
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Xml_Security
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: EnvelopedSignature.php 24593 2012-01-05 20:35:02Z matthew $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: EnvelopedSignature.php 23775 2011-03-01 17:25:24Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */

/**
 * Zend_InfoCard_Xml_Security_Transform_Interface
 */
require_once 'Zend/InfoCard/Xml/Security/Transform/Interface.php';

/**
 * A object implementing the EnvelopedSignature XML Transform
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Xml_Security
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_InfoCard_Xml_Security_Transform_EnvelopedSignature
    implements Zend_InfoCard_Xml_Security_Transform_Interface
{
    /**
     * Transforms the XML Document according to the EnvelopedSignature Transform
     *
     * @throws Zend_InfoCard_Xml_Security_Transform_Exception
     * @param string $strXMLData The input XML data
     * @return string the transformed XML data
     */
    public function transform($strXMLData)
    {
        $sxe = simplexml_load_string($strXMLData);

        if(!$sxe->Signature) {
            require_once 'Zend/InfoCard/Xml/Security/Transform/Exception.php';
            throw new Zend_InfoCard_Xml_Security_Transform_Exception("Unable to locate Signature Block for EnvelopedSignature Transform");
        }

        unset($sxe->Signature);

        return $sxe->asXML();
    }
}
