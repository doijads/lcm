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
 * @package    Zend_Controller
 * @subpackage Router
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Chain.php 25249 2013-02-06 09:54:24Z frosch $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Chain.php 23775 2011-03-01 17:25:24Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Controller_Router_Route_Abstract */
require_once 'Zend/Controller/Router/Route/Abstract.php';

/**
 * Chain route is used for managing route chaining.
 *
 * @package    Zend_Controller
 * @subpackage Router
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Router_Route_Chain extends Zend_Controller_Router_Route_Abstract
{
    protected $_routes = array();
    protected $_separators = array();

    /**
     * Instantiates route based on passed Zend_Config structure
     *
<<<<<<< HEAD
     * @param  Zend_Config $config Configuration object
     * @return Zend_Controller_Router_Route_Chain
=======
     * @param Zend_Config $config Configuration object
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     */
    public static function getInstance(Zend_Config $config)
    {
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        return new self($config->route, $defs);
    }

    /**
     * Add a route to this chain
     *
     * @param  Zend_Controller_Router_Route_Abstract $route
     * @param  string                                $separator
     * @return Zend_Controller_Router_Route_Chain
     */
<<<<<<< HEAD
    public function chain(Zend_Controller_Router_Route_Abstract $route, $separator = self::URI_DELIMITER)
=======
    public function chain(Zend_Controller_Router_Route_Abstract $route, $separator = '/')
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    {
        $this->_routes[]     = $route;
        $this->_separators[] = $separator;

        return $this;

    }

    /**
     * Matches a user submitted path with a previously defined route.
     * Assigns and returns an array of defaults on a successful match.
     *
     * @param  Zend_Controller_Request_Http $request Request to get the path info from
<<<<<<< HEAD
     * @param  null                         $partial
=======
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($request, $partial = null)
    {
<<<<<<< HEAD
        $path        = trim($request->getPathInfo(), self::URI_DELIMITER);
        $subPath     = $path;
        $values      = array();
        $numRoutes   = count($this->_routes);
        $matchedPath = null;
=======
        $path    = trim($request->getPathInfo(), '/');
        $subPath = $path;
        $values  = array();
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

        foreach ($this->_routes as $key => $route) {
            if ($key > 0
                && $matchedPath !== null
                && $subPath !== ''
                && $subPath !== false
            ) {
                $separator = substr($subPath, 0, strlen($this->_separators[$key]));

                if ($separator !== $this->_separators[$key]) {
                    return false;
                }

                $subPath = substr($subPath, strlen($separator));
            }

            // TODO: Should be an interface method. Hack for 1.0 BC
            if (!method_exists($route, 'getVersion') || $route->getVersion() == 1) {
                $match = $subPath;
            } else {
                $request->setPathInfo($subPath);
                $match = $request;
            }

<<<<<<< HEAD
            $res = $route->match($match, true, ($key == $numRoutes - 1));
=======
            $res = $route->match($match, true);
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            if ($res === false) {
                return false;
            }

            $matchedPath = $route->getMatchedPath();

            if ($matchedPath !== null) {
                $subPath     = substr($subPath, strlen($matchedPath));
                $separator   = substr($subPath, 0, strlen($this->_separators[$key]));
            }

            $values = $res + $values;
        }

        $request->setPathInfo($path);

        if ($subPath !== '' && $subPath !== false) {
            return false;
        }

        return $values;
    }

    /**
     * Assembles a URL path defined by this route
     *
<<<<<<< HEAD
     * @param  array $data An array of variable and value pairs used as parameters
     * @param  bool  $reset
     * @param  bool  $encode
=======
     * @param array $data An array of variable and value pairs used as parameters
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array(), $reset = false, $encode = false)
    {
        $value     = '';
        $numRoutes = count($this->_routes);

        foreach ($this->_routes as $key => $route) {
            if ($key > 0) {
                $value .= $this->_separators[$key];
            }

            $value .= $route->assemble($data, $reset, $encode, (($numRoutes - 1) > $key));

            if (method_exists($route, 'getVariables')) {
                $variables = $route->getVariables();

                foreach ($variables as $variable) {
                    $data[$variable] = null;
                }
            }
        }

        return $value;
    }

    /**
     * Set the request object for this and the child routes
     *
     * @param  Zend_Controller_Request_Abstract|null $request
     * @return void
     */
    public function setRequest(Zend_Controller_Request_Abstract $request = null)
    {
        $this->_request = $request;

        foreach ($this->_routes as $route) {
            if (method_exists($route, 'setRequest')) {
                $route->setRequest($request);
            }
        }
    }
<<<<<<< HEAD
    
    /**
     * Return a single parameter of route's defaults
     *
     * @param  string $name Array key of the parameter
     * @return string Previously set default
     */
    public function getDefault($name)
    {
        $default = null;
        foreach ($this->_routes as $route) {
            if (method_exists($route, 'getDefault')) {
                $current = $route->getDefault($name);
                if (null !== $current) {
                    $default = $current;
                }
            }
        }

        return $default;
    }

    /**
     * Return an array of defaults
     *
     * @return array Route defaults
     */
    public function getDefaults()
    {
        $defaults = array();
        foreach ($this->_routes as $route) {
            if (method_exists($route, 'getDefaults')) {
                $defaults = array_merge($defaults, $route->getDefaults());
            }
        }

        return $defaults;
    }
}
=======

}
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
