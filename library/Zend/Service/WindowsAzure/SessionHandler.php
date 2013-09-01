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
 * @subpackage Session
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SessionHandler.php 24593 2012-01-05 20:35:02Z matthew $
 */

=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SessionHandler.php 23775 2011-03-01 17:25:24Z ralph $
 */

/** Zend_Service_WindowsAzure_Storage_Table */
require_once 'Zend/Service/WindowsAzure/Storage/Table.php';

/**
 * @see Zend_Service_WindowsAzure_Exception
 */
require_once 'Zend/Service/WindowsAzure/Exception.php';

>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Session
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_SessionHandler
{
<<<<<<< HEAD
	/**
	 * Maximal property size in table storage.
	 * 
	 * @var int
	 * @see http://msdn.microsoft.com/en-us/library/dd179338.aspx
	 */
	const MAX_TS_PROPERTY_SIZE = 65536;
	
	/** Storage backend type */
	const STORAGE_TYPE_TABLE = 'table';
	const STORAGE_TYPE_BLOB = 'blob';
	
    /**
     * Storage back-end
     * 
     * @var Zend_Service_WindowsAzure_Storage_Table|Zend_Service_WindowsAzure_Storage_Blob
     */
    protected $_storage;
    
    /**
     * Storage backend type
     * 
     * @var string
     */
    protected $_storageType;
    
    /**
     * Session container name
     * 
     * @var string
     */
    protected $_sessionContainer;
    
    /**
     * Session container partition
     * 
     * @var string
     */
    protected $_sessionContainerPartition;
	
    /**
     * Creates a new Zend_Service_WindowsAzure_SessionHandler instance
     * 
     * @param Zend_Service_WindowsAzure_Storage_Table|Zend_Service_WindowsAzure_Storage_Blob $storage Storage back-end, can be table storage and blob storage
     * @param string $sessionContainer Session container name
     * @param string $sessionContainerPartition Session container partition
     */
    public function __construct(Zend_Service_WindowsAzure_Storage $storage, $sessionContainer = 'phpsessions', $sessionContainerPartition = 'sessions')
	{
		// Validate $storage
		if (!($storage instanceof Zend_Service_WindowsAzure_Storage_Table || $storage instanceof Zend_Service_WindowsAzure_Storage_Blob)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Invalid storage back-end given. Storage back-end should be of type Zend_Service_WindowsAzure_Storage_Table or Zend_Service_WindowsAzure_Storage_Blob.');
		}
		
		// Validate other parameters
		if ($sessionContainer == '' || $sessionContainerPartition == '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Session container and session partition should be specified.');
		}
		
		// Determine storage type
		$storageType = self::STORAGE_TYPE_TABLE;
		if ($storage instanceof Zend_Service_WindowsAzure_Storage_Blob) {
			$storageType = self::STORAGE_TYPE_BLOB;
		}
		
	    // Set properties
		$this->_storage = $storage;
		$this->_storageType = $storageType;
		$this->_sessionContainer = $sessionContainer;
		$this->_sessionContainerPartition = $sessionContainerPartition;
=======
    /**
     * Table storage
     *
     * @var Zend_Service_WindowsAzure_Storage_Table
     */
    protected $_tableStorage;

    /**
     * Session table name
     *
     * @var string
     */
    protected $_sessionTable;

    /**
     * Session table partition
     *
     * @var string
     */
    protected $_sessionTablePartition;
    
    /**
     * Creates a new Zend_Service_WindowsAzure_SessionHandler instance
     *
     * @param Zend_Service_WindowsAzure_Storage_Table $tableStorage Table storage
     * @param string $sessionTable Session table name
     * @param string $sessionTablePartition Session table partition
     */
    public function __construct(Zend_Service_WindowsAzure_Storage_Table $tableStorage, $sessionTable = 'phpsessions', $sessionTablePartition = 'sessions')
	{
	    // Set properties
		$this->_tableStorage = $tableStorage;
		$this->_sessionTable = $sessionTable;
		$this->_sessionTablePartition = $sessionTablePartition;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	}
	
	/**
	 * Registers the current session handler as PHP's session handler
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @return boolean
	 */
	public function register()
	{
        return session_set_save_handler(array($this, 'open'),
                                        array($this, 'close'),
                                        array($this, 'read'),
                                        array($this, 'write'),
                                        array($this, 'destroy'),
                                        array($this, 'gc')
        );
<<<<<<< HEAD
	}
	
    /**
     * Open the session store
     * 
=======
    }
    
    /**
     * Open the session store
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @return bool
     */
    public function open()
    {
<<<<<<< HEAD
    	// Make sure storage container exists
    	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		$this->_storage->createTableIfNotExists($this->_sessionContainer);
    	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		$this->_storage->createContainerIfNotExists($this->_sessionContainer);
    	}
    	
		// Ok!
		return true;
=======
        // Make sure table exists
        $tableExists = $this->_tableStorage->tableExists($this->_sessionTable);
        if (!$tableExists) {
            $this->_tableStorage->createTable($this->_sessionTable);
        }
        
        // Ok!
        return true;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }

    /**
     * Close the session store
<<<<<<< HEAD
     * 
=======
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @return bool
     */
    public function close()
    {
        return true;
    }
<<<<<<< HEAD
    
    /**
     * Read a specific session
     * 
=======

    /**
     * Read a specific session
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param int $id Session Id
     * @return string
     */
    public function read($id)
    {
<<<<<<< HEAD
    	// Read data
       	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		// In table storage
	        try
	        {
	            $sessionRecord = $this->_storage->retrieveEntityById(
	                $this->_sessionContainer,
	                $this->_sessionContainerPartition,
	                $id
	            );
	            return unserialize(base64_decode($sessionRecord->serializedData));
	        }
	        catch (Zend_Service_WindowsAzure_Exception $ex)
	        {
	            return '';
	        }
       	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		// In blob storage
    	    try
	        {
    			$data = $this->_storage->getBlobData(
    				$this->_sessionContainer,
    				$this->_sessionContainerPartition . '/' . $id
    			);
	            return unserialize(base64_decode($data));
	        }
	        catch (Zend_Service_WindowsAzure_Exception $ex)
	        {
	            return false;
	        }
    	}
    }
    
    /**
     * Write a specific session
     * 
     * @param int $id Session Id
     * @param string $serializedData Serialized PHP object
     * @throws Exception
     */
    public function write($id, $serializedData)
    {
    	// Encode data
    	$serializedData = base64_encode(serialize($serializedData));
    	if (strlen($serializedData) >= self::MAX_TS_PROPERTY_SIZE && $this->_storageType == self::STORAGE_TYPE_TABLE) {
    		throw new Zend_Service_WindowsAzure_Exception('Session data exceeds the maximum allowed size of ' . self::MAX_TS_PROPERTY_SIZE . ' bytes that can be stored using table storage. Consider switching to a blob storage back-end or try reducing session data size.');
    	}
    	
    	// Store data
       	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		// In table storage
       	    $sessionRecord = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($this->_sessionContainerPartition, $id);
	        $sessionRecord->sessionExpires = time();
	        $sessionRecord->serializedData = $serializedData;
	        
	        $sessionRecord->setAzurePropertyType('sessionExpires', 'Edm.Int32');
	
	        try
	        {
	            $this->_storage->updateEntity($this->_sessionContainer, $sessionRecord);
	        }
	        catch (Zend_Service_WindowsAzure_Exception $unknownRecord)
	        {
	            $this->_storage->insertEntity($this->_sessionContainer, $sessionRecord);
	        }
    	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		// In blob storage
    		$this->_storage->putBlobData(
    			$this->_sessionContainer,
    			$this->_sessionContainerPartition . '/' . $id,
    			$serializedData,
    			array('sessionexpires' => time())
    		);
    	}
    }
    
    /**
     * Destroy a specific session
     * 
=======
        try
        {
            $sessionRecord = $this->_tableStorage->retrieveEntityById(
                $this->_sessionTable,
                $this->_sessionTablePartition,
                $id
            );
            return base64_decode($sessionRecord->serializedData);
        }
        catch (Zend_Service_WindowsAzure_Exception $ex)
        {
            return '';
        }
    }

    /**
     * Write a specific session
     *
     * @param int $id Session Id
     * @param string $serializedData Serialized PHP object
     */
    public function write($id, $serializedData)
    {
        $sessionRecord = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($this->_sessionTablePartition, $id);
        $sessionRecord->sessionExpires = time();
        $sessionRecord->serializedData = base64_encode($serializedData);

        $sessionRecord->setAzurePropertyType('sessionExpires', 'Edm.Int32');

        try
        {
            $this->_tableStorage->updateEntity($this->_sessionTable, $sessionRecord);
        }
        catch (Zend_Service_WindowsAzure_Exception $unknownRecord)
        {
            $this->_tableStorage->insertEntity($this->_sessionTable, $sessionRecord);
        }
    }

    /**
     * Destroy a specific session
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param int $id Session Id
     * @return boolean
     */
    public function destroy($id)
    {
<<<<<<< HEAD
		// Destroy data
       	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		// In table storage
       	    try
	        {
	            $sessionRecord = $this->_storage->retrieveEntityById(
	                $this->_sessionContainer,
	                $this->_sessionContainerPartition,
	                $id
	            );
	            $this->_storage->deleteEntity($this->_sessionContainer, $sessionRecord);
	            
	            return true;
	        }
	        catch (Zend_Service_WindowsAzure_Exception $ex)
	        {
	            return false;
	        }
    	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		// In blob storage
    	    try
	        {
    			$this->_storage->deleteBlob(
    				$this->_sessionContainer,
    				$this->_sessionContainerPartition . '/' . $id
    			);
	            
	            return true;
	        }
	        catch (Zend_Service_WindowsAzure_Exception $ex)
	        {
	            return false;
	        }
    	}
    }
    
    /**
     * Garbage collector
     * 
=======
        try
        {
            $sessionRecord = $this->_tableStorage->retrieveEntityById(
                $this->_sessionTable,
                $this->_sessionTablePartition,
                $id
            );
            $this->_tableStorage->deleteEntity($this->_sessionTable, $sessionRecord);

            return true;
        }
        catch (Zend_Service_WindowsAzure_Exception $ex)
        {
            return false;
        }
    }

    /**
     * Garbage collector
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param int $lifeTime Session maximal lifetime
     * @see session.gc_divisor  100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability 1
     * @usage Execution rate 1/100 (session.gc_probability/session.gc_divisor)
     * @return boolean
     */
    public function gc($lifeTime)
    {
<<<<<<< HEAD
       	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		// In table storage
       	    try
	        {
	            $result = $this->_storage->retrieveEntities($this->_sessionContainer, 'PartitionKey eq \'' . $this->_sessionContainerPartition . '\' and sessionExpires lt ' . (time() - $lifeTime));
	            foreach ($result as $sessionRecord)
	            {
	                $this->_storage->deleteEntity($this->_sessionContainer, $sessionRecord);
	            }
	            return true;
	        }
	        catch (Zend_Service_WindowsAzure_exception $ex)
	        {
	            return false;
	        }
    	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		// In blob storage
    	    try
	        {
	            $result = $this->_storage->listBlobs($this->_sessionContainer, $this->_sessionContainerPartition, '', null, null, 'metadata');
	            foreach ($result as $sessionRecord)
	            {
	            	if ($sessionRecord->Metadata['sessionexpires'] < (time() - $lifeTime)) {
	                	$this->_storage->deleteBlob($this->_sessionContainer, $sessionRecord->Name);
	            	}
	            }
	            return true;
	        }
	        catch (Zend_Service_WindowsAzure_exception $ex)
	        {
	            return false;
	        }
    	}
=======
        try
        {
            $result = $this->_tableStorage->retrieveEntities($this->_sessionTable, 'PartitionKey eq \'' . $this->_sessionTablePartition . '\' and sessionExpires lt ' . (time() - $lifeTime));
            foreach ($result as $sessionRecord)
            {
                $this->_tableStorage->deleteEntity($this->_sessionTable, $sessionRecord);
            }
            return true;
        }
        catch (Zend_Service_WindowsAzure_exception $ex)
        {
            return false;
        }
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }
}
