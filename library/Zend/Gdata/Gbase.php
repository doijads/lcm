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
 * @subpackage Gbase
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Gbase.php 24777 2012-05-08 18:50:23Z adamlundrigan $
 */

/**
 * @see Zend_Exception
 */
require_once 'Zend/Exception.php';

/**
 * @see Zend_Gdata
 */
require_once 'Zend/Gdata.php';
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Gbase.php 23805 2011-03-16 00:55:40Z tjohns $
 */

/**
 * @see Zend_Gdata
 */
require_once 'Zend/Gdata.php';

/**
 * @see Zend_Gdata_Gbase_ItemFeed
 */
require_once 'Zend/Gdata/Gbase/ItemFeed.php';

/**
 * @see Zend_Gdata_Gbase_ItemEntry
 */
require_once 'Zend/Gdata/Gbase/ItemEntry.php';

/**
 * @see Zend_Gdata_Gbase_SnippetEntry
 */
require_once 'Zend/Gdata/Gbase/SnippetEntry.php';

/**
 * @see Zend_Gdata_Gbase_SnippetFeed
 */
require_once 'Zend/Gdata/Gbase/SnippetFeed.php';
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

/**
 * Service class for interacting with the Google Base data API
 *
 * @link http://code.google.com/apis/base
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gbase
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gbase extends Zend_Gdata
{

    /**
     * Path to the customer items feeds on the Google Base server.
     */
    const GBASE_ITEM_FEED_URI = 'https://www.google.com/base/feeds/items';

    /**
     * Path to the snippets feeds on the Google Base server.
     */
    const GBASE_SNIPPET_FEED_URI = 'https://www.google.com/base/feeds/snippets';

    /**
     * Authentication service name for Google Base
     */
    const AUTH_SERVICE_NAME = 'gbase';

    /**
<<<<<<< HEAD
=======
     * The default URI for POST methods
     *
     * @var string
     */
    protected $_defaultPostUri = self::GBASE_ITEM_FEED_URI;

    /**
     * Namespaces used for Zend_Gdata_Gbase
     *
     * @var array
     */
    public static $namespaces = array(
        array('g', 'http://base.google.com/ns/1.0', 1, 0),
        array('batch', 'http://schemas.google.com/gdata/batch', 1, 0)
    );

    /**
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * Create Zend_Gdata_Gbase object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *          when communicating with the Google Apps servers.
     * @param string $applicationId The identity of the app in the form of Company-AppName-Version
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
<<<<<<< HEAD
        throw new Zend_Exception(
            'Google Base API has been discontinued by Google and was removed'
            . ' from Zend Framework in 1.12.0.  For more information see: '
            . 'http://googlemerchantblog.blogspot.ca/2010/12/new-shopping-apis-and-deprecation-of.html'
        );
=======
        $this->registerPackage('Zend_Gdata_Gbase');
        $this->registerPackage('Zend_Gdata_Gbase_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
    }

    /**
     * Retreive feed object
     *
     * @param mixed $location The location for the feed, as a URL or Query
     * @return Zend_Gdata_Gbase_ItemFeed
     */
    public function getGbaseItemFeed($location = null)
    {
        if ($location === null) {
            $uri = self::GBASE_ITEM_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Gbase_ItemFeed');
    }

    /**
     * Retreive entry object
     *
     * @param mixed $location The location for the feed, as a URL or Query
     * @return Zend_Gdata_Gbase_ItemEntry
     */
    public function getGbaseItemEntry($location = null)
    {
        if ($location === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Gbase_ItemEntry');
    }

    /**
     * Insert an entry
     *
     * @param Zend_Gdata_Gbase_ItemEntry $entry The Base entry to upload
     * @param boolean $dryRun Flag for the 'dry-run' parameter
     * @return Zend_Gdata_Gbase_ItemFeed
     */
    public function insertGbaseItem($entry, $dryRun = false)
    {
        if ($dryRun == false) {
            $uri = $this->_defaultPostUri;
        } else {
            $uri = $this->_defaultPostUri . '?dry-run=true';
        }
        $newitem = $this->insertEntry($entry, $uri, 'Zend_Gdata_Gbase_ItemEntry');
        return $newitem;
    }

    /**
     * Update an entry
     *
     * @param Zend_Gdata_Gbase_ItemEntry $entry The Base entry to be updated
     * @param boolean $dryRun Flag for the 'dry-run' parameter
     * @return Zend_Gdata_Gbase_ItemEntry
     */
    public function updateGbaseItem($entry, $dryRun = false)
    {
        $returnedEntry = $entry->save($dryRun);
        return $returnedEntry;
    }

    /**
     * Delete an entry
     *
     * @param Zend_Gdata_Gbase_ItemEntry $entry The Base entry to remove
     * @param boolean $dryRun Flag for the 'dry-run' parameter
     * @return Zend_Gdata_Gbase_ItemFeed
     */
    public function deleteGbaseItem($entry, $dryRun = false)
    {
        $entry->delete($dryRun);
        return $this;
    }

    /**
     * Retrieve feed object
     *
     * @param mixed $location The location for the feed, as a URL or Query
     * @return Zend_Gdata_Gbase_SnippetFeed
     */
    public function getGbaseSnippetFeed($location = null)
    {
        if ($location === null) {
            $uri = self::GBASE_SNIPPET_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Gbase_SnippetFeed');
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }
}
