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
 * @package    Zend_Log
 * @subpackage Formatter
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Xml.php 24593 2012-01-05 20:35:02Z matthew $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Xml.php 23981 2011-05-03 19:01:03Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */

/** Zend_Log_Formatter_Abstract */
require_once 'Zend/Log/Formatter/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Formatter
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Xml.php 24593 2012-01-05 20:35:02Z matthew $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Xml.php 23981 2011-05-03 19:01:03Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */
class Zend_Log_Formatter_Xml extends Zend_Log_Formatter_Abstract
{
    /**
     * @var string Name of root element
     */
    protected $_rootElement;

    /**
     * @var array Relates XML elements to log data field keys.
     */
    protected $_elementMap;

    /**
     * @var string Encoding to use in XML
     */
    protected $_encoding;

    /**
     * Class constructor
     * (the default encoding is UTF-8)
     *
     * @param array|Zend_Config $options
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $args = func_get_args();

            $options = array(
<<<<<<< HEAD
            	'rootElement' => array_shift($args)
=======
                'rootElement' => array_shift($args)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            );

            if (count($args)) {
                $options['elementMap'] = array_shift($args);
            }

            if (count($args)) {
                $options['encoding'] = array_shift($args);
            }
        }

        if (!array_key_exists('rootElement', $options)) {
            $options['rootElement'] = 'logEntry';
        }

        if (!array_key_exists('encoding', $options)) {
            $options['encoding'] = 'UTF-8';
        }

        $this->_rootElement = $options['rootElement'];
        $this->setEncoding($options['encoding']);

        if (array_key_exists('elementMap', $options)) {
            $this->_elementMap  = $options['elementMap'];
        }
    }

    /**
<<<<<<< HEAD
	 * Factory for Zend_Log_Formatter_Xml classe
	 *
	 * @param array|Zend_Config $options
	 * @return Zend_Log_Formatter_Xml
=======
     * Factory for Zend_Log_Formatter_Xml classe
     *
     * @param array|Zend_Config $options
     * @return Zend_Log_Formatter_Xml
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     */
    public static function factory($options)
    {
        return new self($options);
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set encoding
     *
     * @param  string $value
     * @return Zend_Log_Formatter_Xml
     */
    public function setEncoding($value)
    {
        $this->_encoding = (string) $value;
        return $this;
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        if ($this->_elementMap === null) {
            $dataToInsert = $event;
        } else {
            $dataToInsert = array();
            foreach ($this->_elementMap as $elementName => $fieldKey) {
                $dataToInsert[$elementName] = $event[$fieldKey];
            }
        }

        $enc = $this->getEncoding();
        $dom = new DOMDocument('1.0', $enc);
        $elt = $dom->appendChild(new DOMElement($this->_rootElement));

        foreach ($dataToInsert as $key => $value) {
<<<<<<< HEAD
            if (empty($value) 
                || is_scalar($value) 
                || (is_object($value) && method_exists($value,'__toString'))
            ) {
                if($key == "message") {
                    $value = htmlspecialchars($value, ENT_COMPAT, $enc);
                }
                $elt->appendChild(new DOMElement($key, (string)$value));
            }
=======
            if($key == "message") {
                $value = htmlspecialchars($value, ENT_COMPAT, $enc);
            }
            $elt->appendChild(new DOMElement($key, $value));
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        }

        $xml = $dom->saveXML();
        $xml = preg_replace('/<\?xml version="1.0"( encoding="[^\"]*")?\?>\n/u', '', $xml);

        return $xml . PHP_EOL;
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
