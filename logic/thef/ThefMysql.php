<?php

/**
 * Interface for quering a MySQL Database
 * Support for using APC / Memcache through getCached() method
 *
 * Class Type: Singleton Or extended heredity
 */
class ThefMysql
{

    private $zendInstance;
    private $tableName;
    private $executionTimes;
    public $hasCreatedField = true;
    public $hasModifiedField = true;
    public $skipInsertExcepetionLog = false;

    const OVERLOAD_TIME = 0.5;

    private static $__instance = null;

    /**
     * Return the instance of ThefMysql
     * @return ThefMysql
     */
    public static function getInstance()
    {
	if (is_null(self::$__instance)) {
	    self::$__instance = new ThefMysql();
	}
	return self::$__instance;
    }



    /**
     * Construct for classes which extends ThefMysql
     * @param String $tableName Name of the main table in MySQL
     */
    public function __construct($tableName = '')
    {
	$this->tableName = $tableName;
	$this->zendInstance = new Zend_Db_Table($tableName);
	$this->executionTimes = array(
	    'by_query' => array(),
	    'by_position' => array()
	);
    }



    /**
     * Get the resultset of a cached query (if it's empty, query the database and store cache)
     * @param String $cache_key	Key for the cache hash
     * @param String $qry Query to excute in MySQL
     * @param Integer $ttl Time To Live (Expiration value)
     * @param Boolean $as_array If result is as array or as object (default: array)
     * @return Mixed Resultset (null if empty)
     */
    public function cachedQuery($cache_key = null, $qry = null, $ttl = 0, $as_array = true)
    {
	$result = null;
	if (USE_APC && $ttl > 0) {
	    $result = ThefAPC::get($cache_key);
	    if (is_null($result)) {
		$result = $this->mysqlQuery($qry, $as_array);
		ThefAPC::set($cache_key, $result, $ttl);
	    }
	} else
	if (USE_MEMCACHE && $ttl > 0) {
	    $result = ThefCache::get($cache_key);
	    if (is_null($result)) {
		$result = $this->mysqlQuery($qry, $as_array);
		ThefCache::set($cache_key, $result, $ttl);
	    }
	} else {
	    $result = $this->mysqlQuery($qry);
	}
	return $result;
    }



    /**
     * Query database and get result
     *
     * @param String $qry Query to be executed
     * @param Boolean $as_array If result is as array or as object (default: array)
     * @return Mixed Resultset
     */
    public function mysqlQuery($qry, $as_array = true)
    {
	try {
	    $type = ($as_array) ? Zend_Db::FETCH_ASSOC : Zend_Db::FETCH_OBJ;

	    // TIME BEFORE QUERY
	    list($usec, $sec) = explode(' ', microtime());
	    $querytime_before = ((float) $usec + (float) $sec);

	    // EXECUTE QUERY
	    $results = $this->zendInstance->getAdapter()
		    ->fetchAll($qry, array(), $type);

	    // TIME AFTER QUERY
	    list($usec, $sec) = explode(' ', microtime());
	    $querytime_after = ((float) $usec + (float) $sec);

	    // EXECUTE TIME OVERLOADED
	    $querytime = $querytime_after - $querytime_before;
	    if ($querytime >= self::OVERLOAD_TIME)
		$this->logOverload($qry, $querytime);

	    // ADD TO EXECUTION TIME ARRAY
	    $this->executionTimes['by_query'][md5($qry)] = $querytime;
	    $this->executionTimes['by_position'] [] = $querytime;

	    // LOG ENABLED?
	    if (LOG_MYSQL)
		$this->logQuery($qry, $querytime);

	    return $results;
	} catch (Exception $exc) {
	    $this->logError($qry . "<BR>\r\n" . $exc->getMessage());
	    if (DEBUG_ON) {
		throw new Exception($exc->getMessage() . ' IN QUERY: ' . $qry, $exc->getCode(), $exc);
	    } else {
		throw new Exception();
	    }
	}
    }



    /**
     * Execute query in database (not select)
     *
     * @param String $qry Query to be executed
     * @return Number Affected rows
     */
    public function mysqlNonQuery($qry)
    {
	try {
	    $affectedRows = 0;

	    // TIME BEFORE QUERY
	    list($usec, $sec) = explode(' ', microtime());
	    $querytime_before = ((float) $usec + (float) $sec);

	    // EXECUTE QUERY
	    $affectedRows = $this->zendInstance->getAdapter()
		    ->query($qry)
		    ->rowCount();

	    // TIME AFTER QUERY
	    list($usec, $sec) = explode(' ', microtime());
	    $querytime_after = ((float) $usec + (float) $sec);

	    // EXECUTE TIME OVERLOADED
	    $querytime = $querytime_after - $querytime_before;
	    if ($querytime >= self::OVERLOAD_TIME)
		$this->logOverload($qry, $querytime);

	    // ADD TO EXECUTION TIME ARRAY
	    $this->executionTimes['by_query'][md5($qry)] = $querytime;
	    $this->executionTimes['by_position'] [] = $querytime;

	    // LOG ENABLED?
	    if (LOG_MYSQL)
		$this->logQuery($qry, $querytime);

	    return $affectedRows;
	} catch (Exception $exc) {
	    $isInsert = (substr(strtoupper($qry), 0, strlen('INSERT')) == 'INSERT');
	    if (!$isInsert || !$this->skipInsertExcepetionLog) {
		$this->logError($qry . "<BR>\r\n" . $exc->getMessage());
	    }
	    if (DEBUG_ON) {
		throw new Exception($exc->getMessage() . ' IN QUERY: ' . $qry, $exc->getCode(), $exc);
	    } else {
		throw new Exception();
	    }
	}
    }



    /**
     * Find a record by id
     * @param Integer $id Id in db table
     * @param Boolean $as_array If result is as array or as object (default: array)
     * @return Mixed Result
     */
    public function mysqlFindById($id, $as_array = true)
    {
	$response = array();
	if ($id > 0) {
	    // TIME BEFORE QUERY
	    list($usec, $sec) = explode(' ', microtime());
	    $querytime_before = ((float) $usec + (float) $sec);

	    // EXECUTE QUERY
	    $response = $this->zendInstance->find($id)->current();
	    $qry = "find " . $this->tableName . " ($id) -> current ";

	    // TIME AFTER QUERY
	    list($usec, $sec) = explode(' ', microtime());
	    $querytime_after = ((float) $usec + (float) $sec);

	    // EXECUTE TIME OVERLOADED
	    $querytime = $querytime_after - $querytime_before;
	    if ($querytime >= self::OVERLOAD_TIME)
		$this->logOverload($qry, $querytime);

	    // ADD TO EXECUTION TIME ARRAY
	    $this->executionTimes['by_query'][md5($qry)] = $querytime;
	    $this->executionTimes['by_position'] [] = $querytime;

	    // LOG ENABLED?
	    if (LOG_MYSQL)
		$this->logQuery($qry, $querytime);

	    if ($response) {
		if ($as_array) {
		    $response = $response->toArray();
		}
	    } else {
		if ($as_array) {
		    $response = array();
		} else {
		    $response = new stdClass();
		}
	    }
	}
	return $response;
    }



    /**
     * Insert the associative array in the table defined in the construct method
     * @param Mixed $data Associative array (ie: $data['field'] = 'value') or Query to execute
     * @return Integer Id result of the insert
     * @throw Exception is primary key already exists
     */
    public function mysqlInsert($data)
    {
	try {
	    $id = 0;
	    if (is_array($data)) {
		if ($this->hasCreatedField)
		    $data['created'] = Date('Y:m:d H:i:s');
		$id = $this->mysqlArrayInsert($data);
	    } else {
		$id = $this->mysqlQueryInsert($data);
	    }
	    return $id;
	} catch (Exception $exc) {
	    if (!$this->skipInsertExcepetionLog) {
		$this->logError($sql . "<BR>\r\n" . $exc->getMessage());
	    }
	    if (DEBUG_ON) {
		throw new Exception($exc->getMessage() . ' IN QUERY: ' . $sql, $exc->getCode(), $exc);
	    } else {
		throw $exc;
	    }
	}
    }



    private function mysqlArrayInsert(array $data)
    {
	try {
	    $id = $this->zendInstance->insert($data);

	    // LOG ENABLED?
	    if (LOG_MYSQL) {
		$this->logQuery($sql, 'N/A');
		$sql = $this->zendInstance->getAdapter()->getProfiler()->getLastQueryProfile();
	    }

	    return $id;
	} catch (Exception $exc) {
	    throw $exc;
	}
    }



    private function mysqlQueryInsert($sql)
    {
	try {
	    $id = 0;
	    $this->zendInstance->getAdapter()->query($sql);
	    $id = $this->zendInstance->getAdapter()->lastInsertId();

	    // LOG ENABLED?
	    if (LOG_MYSQL) {
		$this->logQuery($sql, 'N/A');
		$sql = $this->zendInstance->getAdapter()->getProfiler()->getLastQueryProfile();
	    }

	    return $id;
	} catch (Exception $exc) {
	    throw new Exception($exc->getMessage() . ' IN QUERY: ' . $sql, $exc->getCode(), $exc);
	}
    }



    /**
     * Update a table with the associative array in the table defined in the construct method
     * @param Array $$data Associative array (ie: $data['field'] = 'value')
     * @param String $where_sql SQL Condition (ie: 'id = 9')
     * @return Number Affected rows
     */
    public function mysqlUpdate(array $data, $where_sql)
    {
	try {
	    if ($this->hasModifiedField) {
		$$data['modified'] = Date('Y-m-d H:i:s');
	    }

	    // TIME BEFORE QUERY
	    list($usec, $sec) = explode(' ', microtime());
	    $querytime_before = ((float) $usec + (float) $sec);

	    // EXECUTE QUERY
	    $affectedRows = 0;
	    $affectedRows = $this->zendInstance->update($data, $where_sql);

	    // TIME AFTER QUERY
	    list($usec, $sec) = explode(' ', microtime());
	    $querytime_after = ((float) $usec + (float) $sec);

	    // GET QUERY
	    $qry = "UPDATE " . print_r($data, true) . " WHERE $where_sql ";

	    // EXECUTE TIME OVERLOADED
	    $querytime = $querytime_after - $querytime_before;
	    if ($querytime >= self::OVERLOAD_TIME)
		$this->logOverload($qry, $querytime);

	    // LOG ENABLED?
	    if (LOG_MYSQL)
		$this->logQuery($qry, $querytime);

	    return $affectedRows;
	} catch (Exception $exc) {
	    throw new Exception($exc->getMessage(), $exc->getCode(), $exc);
	}
    }



    /**
     * Delete rocord/s from the table defined in the construct method
     * @param String $where_sql SQL Condition (ie: 'id = 9')
     * @return Number Affected rows
     */
    public function mysqlDelete($where)
    {
	try {
	    $affectedRows = 0;
	    $affectedRows = $this->zendInstance->delete($where);

	    // LOG ENABLED?
	    if (LOG_MYSQL) {
		$this->logQuery($sql, 'N/A');
		$sql = $this->zendInstance->getAdapter()->getProfiler()->getLastQueryProfile();
	    }

	    return $affectedRows;
	} catch (Exception $exc) {
	    throw new Exception($exc->getMessage(), $exc->getCode(), $exc);
	}
    }



    /**
     * Returns the instance adaptar from ZendDB
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
	return $this->zendInstance->getAdapter();
    }



    /**
     * Escape a String using ZendDB
     * @param String $qry Query to be escaped
     * @return String escaped
     */
    public function mysqlEscape($qry)
    {
	return $this->zendInstance->getAdapter()->quote($qry);
    }



    /**
     * Returns the execution time for the last query executed
     * @return Number execution time
     */
    public function getLastQueryExecutionTime()
    {
	$arr = $this->executionTimes['by_position'];
	return $arr[count($arr) - 1];
    }



    /**
     * Returns the execution time for a specific query
     * @return Number execution time
     */
    public function getQueryExecutionTime($qry)
    {
	$arr = $this->executionTimes['by_query'];
	return $arr[md5($qry)];
    }



    /**
     * Write local log for a especific query
     * @param String $qry Query to be logged
     * @param Integer $querytime Time of execution
     */
    public function logQuery($qry, $querytime)
    {
	$log = $qry . " <BR> execution time: $querytime seconds <BR>";
	$fp = @fopen(ROOT_PATH . "/_log/mysql/mysql_query_" . Date('Ymd') . ".txt", "a+");
	if ($fp) {
	    fwrite($fp, Date("Ymd - H:i:s") . ">" . $log . "\r\n");
	    fclose($fp);
	}
    }



    /**
     * Write local log for a overloadad query
     * @param String $qry Query to be logged
     * @param Integer $querytime Time of execution
     */
    public function logOverload($qry, $querytime)
    {
	$log = $qry . " <BR> execution time: $querytime seconds <BR>";
	$fp = @fopen(ROOT_PATH . "/_log/mysql/mysql_overload_" . Date('Ymd') . ".txt", "a+");
	if ($fp) {
	    fwrite($fp, Date("Ymd - H:i:s") . ">" . $log . "\r\n");
	    fclose($fp);
	}
    }



    /**
     * Write local log for a query with execution error
     * @param String $qry Query to be logged
     */
    public function logError($log)
    {
	$fp = @fopen(ROOT_PATH . "/_log/mysql/mysql_error_" . Date('Ymd') . ".txt", "a+");
	if ($fp) {
	    fwrite($fp, Date("Ymd - H:i:s") . ">" . $log . "\r\n");
	    fclose($fp);
	}
    }



}