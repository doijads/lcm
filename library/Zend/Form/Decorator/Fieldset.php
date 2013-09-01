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
 * @package    Zend_Form
 * @subpackage Decorator
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Decorator_Abstract */
require_once 'Zend/Form/Decorator/Abstract.php';

/**
 * Zend_Form_Decorator_Fieldset
 *
 * Any options passed will be used as HTML attributes of the fieldset tag.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Fieldset.php 24961 2012-06-15 14:15:47Z adamlundrigan $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Fieldset.php 23775 2011-03-01 17:25:24Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */
class Zend_Form_Decorator_Fieldset extends Zend_Form_Decorator_Abstract
{
    /**
     * Attribs that should be removed prior to rendering
     * @var array
     */
    public $stripAttribs = array(
        'action',
        'enctype',
        'helper',
        'method',
        'name',
<<<<<<< HEAD
        'accept-charset',
=======
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    );

    /**
     * Fieldset legend
     * @var string
     */
    protected $_legend;

    /**
     * Default placement: surround content
     * @var string
     */
    protected $_placement = null;

    /**
     * Get options
     *
     * Merges in element attributes as well.
     *
     * @return array
     */
    public function getOptions()
    {
        $options = parent::getOptions();
        if (null !== ($element = $this->getElement())) {
            $attribs = $element->getAttribs();
            $options = array_merge($attribs, $options);
            $this->setOptions($options);
        }
        return $options;
    }

    /**
     * Set legend
     *
     * @param  string $value
     * @return Zend_Form_Decorator_Fieldset
     */
    public function setLegend($value)
    {
        $this->_legend = (string) $value;
        return $this;
    }

    /**
     * Get legend
     *
     * @return string
     */
    public function getLegend()
    {
        $legend = $this->_legend;
        if ((null === $legend) && (null !== ($element = $this->getElement()))) {
            if (method_exists($element, 'getLegend')) {
                $legend = $element->getLegend();
                $this->setLegend($legend);
            }
        }
        if ((null === $legend) && (null !== ($legend = $this->getOption('legend')))) {
            $this->setLegend($legend);
            $this->removeOption('legend');
        }

        return $legend;
    }

    /**
     * Render a fieldset
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $legend  = $this->getLegend();
        $attribs = $this->getOptions();
        $name    = $element->getFullyQualifiedName();
        $id      = (string)$element->getId();

<<<<<<< HEAD
        if ((!array_key_exists('id', $attribs) || $attribs['id'] == $id) && '' !== $id) {
=======
        if (!array_key_exists('id', $attribs) && '' !== $id) {
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            $attribs['id'] = 'fieldset-' . $id;
        }

        if (null !== $legend) {
            if (null !== ($translator = $element->getTranslator())) {
                $legend = $translator->translate($legend);
            }

            $attribs['legend'] = $legend;
        }

        foreach (array_keys($attribs) as $attrib) {
            $testAttrib = strtolower($attrib);
            if (in_array($testAttrib, $this->stripAttribs)) {
                unset($attribs[$attrib]);
            }
        }

        return $view->fieldset($name, $content, $attribs);
    }
}
