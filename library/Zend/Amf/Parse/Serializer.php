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
 * @subpackage Parse
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Serializer.php 25179 2012-12-22 21:29:30Z rob $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Serializer.php 23775 2011-03-01 17:25:24Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */

/**
 * Base abstract class for all AMF serializers.
 *
 * @package    Zend_Amf
 * @subpackage Parse
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Amf_Parse_Serializer
{
    /**
     * Reference to the current output stream being constructed
     *
     * @var string
     */
    protected $_stream;

    /**
<<<<<<< HEAD
     * str* functions overloaded using mbstring.func_overload
     *
     * @var bool
     */
    protected $mbStringFunctionsOverloaded;

    /**
=======
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * Constructor
     *
     * @param  Zend_Amf_Parse_OutputStream $stream
     * @return void
     */
    public function __construct(Zend_Amf_Parse_OutputStream $stream)
    {
        $this->_stream = $stream;
<<<<<<< HEAD
        $this->_mbStringFunctionsOverloaded = function_exists('mb_strlen') && (ini_get('mbstring.func_overload') !== '') && ((int)ini_get('mbstring.func_overload') & 2);
=======
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }

    /**
     * Find the PHP object type and convert it into an AMF object type
     *
     * @param  mixed $content
     * @param  int $markerType
     * @param  mixed $contentByVal
     * @return void
     */
    public abstract function writeTypeMarker(&$content, $markerType = null, $contentByVal = false);
}
