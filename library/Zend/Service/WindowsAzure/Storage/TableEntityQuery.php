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
 * @version    $Id: TableEntityQuery.php 24593 2012-01-05 20:35:02Z matthew $
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TableEntityQuery.php 23775 2011-03-01 17:25:24Z ralph $
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 */

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_TableEntityQuery
{
    /**
     * From
<<<<<<< HEAD
     * 
=======
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @var string
     */
	protected $_from  = '';
	
	/**
	 * Where
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @var array
	 */
	protected $_where = array();
	
	/**
	 * Order by
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @var array
	 */
	protected $_orderBy = array();
	
	/**
	 * Top
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @var int
	 */
	protected $_top = null;
	
	/**
	 * Partition key
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @var string
	 */
	protected $_partitionKey = null;

	/**
	 * Row key
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @var string
	 */
	protected $_rowKey = null;
	
	/**
	 * Select clause
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function select()
	{
		return $this;
	}
	
	/**
	 * From clause
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string $name Table name to select entities from
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function from($name)
	{
		$this->_from = $name;
		return $this;
	}
	
	/**
	 * Specify partition key
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string $value Partition key to query for
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function wherePartitionKey($value = null)
	{
	    $this->_partitionKey = $value;
	    return $this;
	}
	
	/**
	 * Specify row key
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string $value Row key to query for
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function whereRowKey($value = null)
	{
	    $this->_rowKey = $value;
	    return $this;
	}
	
	/**
	 * Add where clause
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string       $condition   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value       Value(s) to insert in question mark (?) parameters.
	 * @param string       $cond        Condition for the clause (and/or/not)
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function where($condition, $value = null, $cond = '')
	{
	    $condition = $this->_replaceOperators($condition);
<<<<<<< HEAD
	    
	    if (!is_null($value)) {
	        $condition = $this->_quoteInto($condition, $value);
	    }
	    
=======
	
	    if ($value !== null) {
	        $condition = $this->_quoteInto($condition, $value);
	    }
	
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
		if (count($this->_where) == 0) {
			$cond = '';
		} else if ($cond !== '') {
			$cond = ' ' . strtolower(trim($cond)) . ' ';
		}
		
		$this->_where[] = $cond . $condition;
		return $this;
	}

	/**
	 * Add where clause with AND condition
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string       $condition   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value       Value(s) to insert in question mark (?) parameters.
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function andWhere($condition, $value = null)
	{
		return $this->where($condition, $value, 'and');
	}
	
	/**
	 * Add where clause with OR condition
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string       $condition   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value       Value(s) to insert in question mark (?) parameters.
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function orWhere($condition, $value = null)
	{
		return $this->where($condition, $value, 'or');
	}
	
	/**
	 * OrderBy clause
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string $column    Column to sort by
	 * @param string $direction Direction to sort (asc/desc)
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function orderBy($column, $direction = 'asc')
	{
		$this->_orderBy[] = $column . ' ' . $direction;
		return $this;
	}
<<<<<<< HEAD
    
	/**
	 * Top clause
	 * 
=======

	/**
	 * Top clause
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param int $top  Top to fetch
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
    public function top($top = null)
    {
        $this->_top  = (int)$top;
        return $this;
    }
<<<<<<< HEAD
	
    /**
     * Assembles the query string
     * 
=======
    
    /**
     * Assembles the query string
     *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
     * @param boolean $urlEncode Apply URL encoding to the query string
     * @return string
     */
	public function assembleQueryString($urlEncode = false)
	{
		$query = array();
		if (count($this->_where) != 0) {
		    $filter = implode('', $this->_where);
			$query[] = '$filter=' . ($urlEncode ? self::encodeQuery($filter) : $filter);
		}
		
		if (count($this->_orderBy) != 0) {
		    $orderBy = implode(',', $this->_orderBy);
			$query[] = '$orderby=' . ($urlEncode ? self::encodeQuery($orderBy) : $orderBy);
		}
		
<<<<<<< HEAD
		if (!is_null($this->_top)) {
=======
		if ($this->_top !== null) {
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
			$query[] = '$top=' . $this->_top;
		}
		
		if (count($query) != 0) {
			return '?' . implode('&', $query);
		}
		
		return '';
	}
	
	/**
	 * Assemble from
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param boolean $includeParentheses Include parentheses? ()
	 * @return string
	 */
	public function assembleFrom($includeParentheses = true)
	{
	    $identifier = '';
	    if ($includeParentheses) {
	        $identifier .= '(';
<<<<<<< HEAD
	        
	        if (!is_null($this->_partitionKey)) {
	            $identifier .= 'PartitionKey=\'' . self::encodeQuery($this->_partitionKey) . '\'';
	        }
	            
	        if (!is_null($this->_partitionKey) && !is_null($this->_rowKey)) {
	            $identifier .= ', ';
	        }
	            
	        if (!is_null($this->_rowKey)) {
	            $identifier .= 'RowKey=\'' . self::encodeQuery($this->_rowKey) . '\'';
	        }
	            
=======
	
	        if ($this->_partitionKey !== null) {
	            $identifier .= 'PartitionKey=\'' . $this->_partitionKey . '\'';
	        }
	
	        if ($this->_partitionKey !== null && $this->_rowKey !== null) {
	            $identifier .= ', ';
	        }
	
	        if ($this->_rowKey !== null) {
	            $identifier .= 'RowKey=\'' . $this->_rowKey . '\'';
	        }
	
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	        $identifier .= ')';
	    }
		return $this->_from . $identifier;
	}
	
	/**
	 * Assemble full query
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @return string
	 */
	public function assembleQuery()
	{
		$assembledQuery = $this->assembleFrom();
		
		$queryString = $this->assembleQueryString();
		if ($queryString !== '') {
			$assembledQuery .= $queryString;
		}
		
		return $assembledQuery;
	}
	
	/**
	 * Quotes a variable into a condition
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string       $text   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value  Value(s) to insert in question mark (?) parameters.
	 * @return string
	 */
	protected function _quoteInto($text, $value = null)
	{
		if (!is_array($value)) {
	        $text = str_replace('?', '\'' . addslashes($value) . '\'', $text);
	    } else {
	        $i = 0;
	        while(strpos($text, '?') !== false) {
	            if (is_numeric($value[$i])) {
	                $text = substr_replace($text, $value[$i++], strpos($text, '?'), 1);
	            } else {
	                $text = substr_replace($text, '\'' . addslashes($value[$i++]) . '\'', strpos($text, '?'), 1);
	            }
	        }
	    }
	    return $text;
	}
	
	/**
	 * Replace operators
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string $text
	 * @return string
	 */
	protected function _replaceOperators($text)
	{
	    $text = str_replace('==', 'eq',  $text);
	    $text = str_replace('>',  'gt',  $text);
	    $text = str_replace('<',  'lt',  $text);
	    $text = str_replace('>=', 'ge',  $text);
	    $text = str_replace('<=', 'le',  $text);
	    $text = str_replace('!=', 'ne',  $text);
<<<<<<< HEAD
	    
	    $text = str_replace('&&', 'and', $text);
	    $text = str_replace('||', 'or',  $text);
	    $text = str_replace('!',  'not', $text);
	    
=======
	
	    $text = str_replace('&&', 'and', $text);
	    $text = str_replace('||', 'or',  $text);
	    $text = str_replace('!',  'not', $text);
	
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	    return $text;
	}
	
	/**
	 * urlencode a query
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @param string $query Query to encode
	 * @return string Encoded query
	 */
	public static function encodeQuery($query)
	{
		$query = str_replace('/', '%2F', $query);
		$query = str_replace('?', '%3F', $query);
		$query = str_replace(':', '%3A', $query);
		$query = str_replace('@', '%40', $query);
		$query = str_replace('&', '%26', $query);
		$query = str_replace('=', '%3D', $query);
		$query = str_replace('+', '%2B', $query);
		$query = str_replace(',', '%2C', $query);
		$query = str_replace('$', '%24', $query);
<<<<<<< HEAD
		$query = str_replace('{', '%7B', $query);
		$query = str_replace('}', '%7D', $query);

=======
		
		
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
		$query = str_replace(' ', '%20', $query);
		
		return $query;
	}
	
	/**
	 * __toString overload
<<<<<<< HEAD
	 * 
=======
	 *
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
	 * @return string
	 */
	public function __toString()
	{
		return $this->assembleQuery();
	}
<<<<<<< HEAD
}
=======
}
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
