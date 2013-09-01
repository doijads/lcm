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
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Batch.php 24593 2012-01-05 20:35:02Z matthew $
 */

=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Batch.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Service_WindowsAzure_Exception
 */
require_once 'Zend/Service/WindowsAzure/Exception.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_BatchStorageAbstract
 */
require_once 'Zend/Service/WindowsAzure/Storage/BatchStorageAbstract.php';
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_Batch
{	
    /**
     * Storage client the batch is defined on
     * 
     * @var Zend_Service_WindowsAzure_Storage_BatchStorageAbstract
     */
    protected $_storageClient = null;
    
    /**
     * For table storage?
     * 
     * @var boolean
     */
    protected $_forTableStorage = false;
    
    /**
     * Base URL
     * 
     * @var string
     */
    protected $_baseUrl;
    
    /**
     * Pending operations
     * 
     * @var unknown_type
     */
    protected $_operations = array();
    
    /**
     * Does the batch contain a single select?
     * 
     * @var boolean
     */
    protected $_isSingleSelect = false;
    
    /**
     * Creates a new Zend_Service_WindowsAzure_Storage_Batch
     * 
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_Batch
{    
    /**
     * Storage client the batch is defined on
     *
     * @var Zend_Service_WindowsAzure_Storage_BatchStorageAbstract
     */
    protected $_storageClient = null;

    /**
     * For table storage?
     *
     * @var boolean
     */
    protected $_forTableStorage = false;

    /**
     * Base URL
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     * Pending operations
     *
     * @var unknown_type
     */
    protected $_operations = array();

    /**
     * Does the batch contain a single select?
     *
     * @var boolean
     */
    protected $_isSingleSelect = false;

    /**
     * Creates a new Zend_Service_WindowsAzure_Storage_Batch
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param Zend_Service_WindowsAzure_Storage_BatchStorageAbstract $storageClient Storage client the batch is defined on
     */
    public function __construct(Zend_Service_WindowsAzure_Storage_BatchStorageAbstract $storageClient = null, $baseUrl = '')
    {
        $this->_storageClient = $storageClient;
        $this->_baseUrl       = $baseUrl;
        $this->_beginBatch();
    }
<<<<<<< HEAD
    
=======

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	/**
	 * Get base URL for creating requests
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}
<<<<<<< HEAD
    
    /**
     * Starts a new batch operation set
     * 
=======

    /**
     * Starts a new batch operation set
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @throws Zend_Service_WindowsAzure_Exception
     */
    protected function _beginBatch()
    {
        $this->_storageClient->setCurrentBatch($this);
    }
<<<<<<< HEAD
    
=======

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    /**
     * Cleanup current batch
     */
    protected function _clean()
    {
        unset($this->_operations);
        $this->_storageClient->setCurrentBatch(null);
        $this->_storageClient = null;
        unset($this);
    }

	/**
	 * Enlist operation in current batch
	 *
	 * @param string $path Path
	 * @param string $queryString Query string
	 * @param string $httpVerb HTTP verb the request will use
	 * @param array $headers x-ms headers to add
	 * @param boolean $forTableStorage Is the request for table storage?
	 * @param mixed $rawData Optional RAW HTTP data to be sent over the wire
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function enlistOperation($path = '/', $queryString = '', $httpVerb = Zend_Http_Client::GET, $headers = array(), $forTableStorage = false, $rawData = null)
	{
	    // Set _forTableStorage
	    if ($forTableStorage) {
	        $this->_forTableStorage = true;
	    }
<<<<<<< HEAD
	    
	    // Set _isSingleSelect
	    if ($httpVerb == Zend_Http_Client::GET) {
	        if (count($this->_operations) > 0) {
                    require_once 'Zend/Service/WindowsAzure/Exception.php';
=======
	
	    // Set _isSingleSelect
	    if ($httpVerb == Zend_Http_Client::GET) {
	        if (count($this->_operations) > 0) {
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	            throw new Zend_Service_WindowsAzure_Exception("Select operations can only be performed in an empty batch transaction.");
	        }
	        $this->_isSingleSelect = true;
	    }
<<<<<<< HEAD
	    
=======
	
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	    // Clean path
		if (strpos($path, '/') !== 0) {
			$path = '/' . $path;
		}
			
		// Clean headers
<<<<<<< HEAD
		if (is_null($headers)) {
		    $headers = array();
		}
		    
=======
		if ($headers === null) {
		    $headers = array();
		}
		
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
		// URL encoding
		$path           = Zend_Service_WindowsAzure_Storage::urlencode($path);
		$queryString    = Zend_Service_WindowsAzure_Storage::urlencode($queryString);

		// Generate URL
		$requestUrl     = $this->getBaseUrl() . $path . $queryString;
		
		// Generate $rawData
<<<<<<< HEAD
		if (is_null($rawData)) {
		    $rawData = '';
		}
		    
=======
		if ($rawData === null) {
		    $rawData = '';
		}
		
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
		// Add headers
		if ($httpVerb != Zend_Http_Client::GET) {
    		$headers['Content-ID'] = count($this->_operations) + 1;
    		if ($httpVerb != Zend_Http_Client::DELETE) {
    		    $headers['Content-Type'] = 'application/atom+xml;type=entry';
    		}
    		$headers['Content-Length'] = strlen($rawData);
		}
<<<<<<< HEAD
		    
=======
		
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
		// Generate $operation
		$operation = '';
		$operation .= $httpVerb . ' ' . $requestUrl . ' HTTP/1.1' . "\n";
		foreach ($headers as $key => $value)
		{
		    $operation .= $key . ': ' . $value . "\n";
		}
		$operation .= "\n";
		
		// Add data
		$operation .= $rawData;

		// Store operation
<<<<<<< HEAD
		$this->_operations[] = $operation;	        
	}
    
    /**
     * Commit current batch
     * 
=======
		$this->_operations[] = $operation;	
	}

    /**
     * Commit current batch
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @return Zend_Http_Response
     * @throws Zend_Service_WindowsAzure_Exception
     */
    public function commit()
<<<<<<< HEAD
    {    	
        // Perform batch
        $response = $this->_storageClient->performBatch($this->_operations, $this->_forTableStorage, $this->_isSingleSelect);
        
        // Dispose
        $this->_clean();
        
        // Parse response
        $errors = null;
        preg_match_all('/<message (.*)>(.*)<\/message>/', $response->getBody(), $errors);
        
        // Error?
        if (count($errors[2]) > 0) {
            require_once 'Zend/Service/WindowsAzure/Exception.php';
            throw new Zend_Service_WindowsAzure_Exception('An error has occured while committing a batch: ' . $errors[2][0]);
        }
        
        // Return
        return $response;
    }
    
=======
    {
        // Perform batch
        $response = $this->_storageClient->performBatch($this->_operations, $this->_forTableStorage, $this->_isSingleSelect);

        // Dispose
        $this->_clean();

        // Parse response
        $errors = null;
        preg_match_all('/<message (.*)>(.*)<\/message>/', $response->getBody(), $errors);

        // Error?
        if (count($errors[2]) > 0) {
            throw new Zend_Service_WindowsAzure_Exception('An error has occured while committing a batch: ' . $errors[2][0]);
        }

        // Return
        return $response;
    }

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    /**
     * Rollback current batch
     */
    public function rollback()
    {
        // Dispose
        $this->_clean();
    }
<<<<<<< HEAD
    
    /**
     * Get operation count
     * 
=======

    /**
     * Get operation count
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @return integer
     */
    public function getOperationCount()
    {
        return count($this->_operations);
    }
<<<<<<< HEAD
    
    /**
     * Is single select?
     * 
=======

    /**
     * Is single select?
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @return boolean
     */
    public function isSingleSelect()
    {
        return $this->_isSingleSelect;
    }
}
