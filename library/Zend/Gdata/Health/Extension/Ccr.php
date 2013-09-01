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
 * @package    Zend_Gdata
 * @subpackage Health
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Ccr.php 24779 2012-05-08 19:13:59Z adamlundrigan $
 */

/**
 * @see Zend_Exception
 */
require_once 'Zend/Exception.php';

/**
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Ccr.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @see Zend_Gdata_App_Extension_Element
 */
require_once 'Zend/Gdata/App/Extension/Element.php';

/**
 * Concrete class for working with CCR elements.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Health
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Health_Extension_Ccr extends Zend_Gdata_App_Extension_Element
{
<<<<<<< HEAD
=======
    protected $_rootNamespace = 'ccr';
    protected $_rootElement = 'ContinuityOfCareRecord';
    protected $_ccrDom = null;

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    /**
     * Creates a Zend_Gdata_Health_Extension_Ccr entry, representing CCR data
     *
     * @param DOMElement $element (optional) DOMElement from which this
     *          object should be constructed.
     */
    public function __construct($element = null)
    {
<<<<<<< HEAD
        throw new Zend_Exception(
            'Google Health API has been discontinued by Google and was removed'
            . ' from Zend Framework in 1.12.0.  For more information see: '
            . 'http://googleblog.blogspot.ca/2011/06/update-on-google-health-and-google.html'
        );
=======
        foreach (Zend_Gdata_Health::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
    }

    /**
     * Transfers each child and attribute into member variables.
     * This is called when XML is received over the wire and the data
     * model needs to be built to represent this XML.
     *
     * @param DOMNode $node The DOMNode that represents this object's data
     */
    public function transferFromDOM($node)
    {
        $this->_ccrDom = $node;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        if ($doc === null) {
            $doc = new DOMDocument('1.0', 'utf-8');
        }
        $domElement = $doc->importNode($this->_ccrDom, true);
        return $domElement;
    }

    /**
     * Magic helper that allows drilling down and returning specific elements
     * in the CCR. For example, to retrieve the users medications
     * (/ContinuityOfCareRecord/Body/Medications) from the entry's CCR, call
     * $entry->getCcr()->getMedications().  Similarly, getConditions() would
     * return extract the user's conditions.
     *
     * @param string $name Name of the function to call
     * @param unknown $args
     * @return array.<DOMElement> A list of the appropriate CCR data
     */
    public function __call($name, $args)
    {
        if (substr($name, 0, 3) === 'get') {
            $category = substr($name, 3);

            switch ($category) {
                case 'Conditions':
                    $category = 'Problems';
                    break;
                case 'Allergies':
                    $category = 'Alerts';
                    break;
                case 'TestResults':
                    // TestResults is an alias for LabResults
                case 'LabResults':
                    $category = 'Results';
                    break;
                default:
                    // $category is already well formatted
            }

            return $this->_ccrDom->getElementsByTagNameNS($this->lookupNamespace('ccr'), $category);
        } else {
            return null;
        }
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }
}
