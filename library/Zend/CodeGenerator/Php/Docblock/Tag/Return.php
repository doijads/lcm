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
 * @package    Zend_CodeGenerator
 * @subpackage PHP
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Return.php 24593 2012-01-05 20:35:02Z matthew $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Return.php 23775 2011-03-01 17:25:24Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */

/**
 * @see Zend_CodeGenerator_Php_Docblock_Tag
 */
require_once 'Zend/CodeGenerator/Php/Docblock/Tag.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Docblock_Tag_Return extends Zend_CodeGenerator_Php_Docblock_Tag
{

    /**
     * @var string
     */
    protected $_datatype = null;

    /**
     * @var string
     */
    protected $_description = null;

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Docblock_Tag $reflectionTagReturn
     * @return Zend_CodeGenerator_Php_Docblock_Tag_Return
     */
    public static function fromReflection(Zend_Reflection_Docblock_Tag $reflectionTagReturn)
    {
        $returnTag = new self();

        $returnTag->setName('return');
        $returnTag->setDatatype($reflectionTagReturn->getType()); // @todo rename
        $returnTag->setDescription($reflectionTagReturn->getDescription());

        return $returnTag;
    }

    /**
     * setDatatype()
     *
     * @param string $datatype
     * @return Zend_CodeGenerator_Php_Docblock_Tag_Return
     */
    public function setDatatype($datatype)
    {
        $this->_datatype = $datatype;
        return $this;
    }

    /**
     * getDatatype()
     *
     * @return string
     */
    public function getDatatype()
    {
        return $this->_datatype;
    }


    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@return ' . $this->_datatype . ' ' . $this->_description;
        return $output;
    }

}