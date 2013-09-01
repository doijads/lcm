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
 * @package    Zend_Application
 * @subpackage Resource
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Application_Resource_UserAgent extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Http_UserAgent
     */
<<<<<<< HEAD
    protected $_userAgent;
    
=======
	protected $_userAgent;
	
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    /**
     * Intialize resource
     *
     * @return Zend_Http_UserAgent
     */
    public function init()
    {
<<<<<<< HEAD
        $userAgent = $this->getUserAgent();
=======
		$userAgent = $this->getUserAgent();
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

        // Optionally seed the UserAgent view helper
        $bootstrap = $this->getBootstrap();
        if ($bootstrap->hasResource('view') || $bootstrap->hasPluginResource('view')) {
            $bootstrap->bootstrap('view');
            $view = $bootstrap->getResource('view');
            if (null !== $view) {
                $view->userAgent($userAgent);
            }
        }

        return $userAgent;
<<<<<<< HEAD
    }
    
=======
	}
	
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    /**
     * Get UserAgent instance
     *
     * @return Zend_Http_UserAgent
     */
    public function getUserAgent()
    {
<<<<<<< HEAD
        if (null === $this->_userAgent) {
            $options = $this->getOptions();
            $this->_userAgent = new Zend_Http_UserAgent($options);
        }
        
        return $this->_userAgent;
    }
=======
		if (null === $this->_userAgent) {
			$options = $this->getOptions();
			$this->_userAgent = new Zend_Http_UserAgent($options);
		}
		
		return $this->_userAgent;
	}
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
}
