<?php

/**
 *  Memcache Interface
 *
 * Class Type: Singleton
 */
class ThefMemcache
{

    private static $instance = null;

    /**
     * Return the instance of Memcache
     * @return ThefMamche Instance of Memcache
     */
    private static function getInstance()
    {
	if (USE_MEMCACHE) {
	    if (!self::$instance instanceof Memcache) {
		self::$instance = new Memcache();
	    }
	    self::$instance->pconnect(MEMCACHE_SERVER, MEMCACHE_PORT);
	    return self::$instance;
	}
    }



    /**
     * Get the content stored in Memcache
     * @param String $cache_key Key for the cache hash
     * @return Mixed Content stored
     */
    public static function get($cache_key)
    {
	$result = null;
	if (USE_MEMCACHE) {
	    if (class_exists('Memcache')) {
		$instance = self::getInstance();
		$result = $instance->get(MEMCACHE_PREFIX . $cache_key);
		if (!$result) $result = null;
	    } 
	} 
	return $result;
    }



    /**
     * Set a content in Memcache
     * @param String $cache_key	Key for the cache hash
     * @param Mixed $data Data to be stored
     * @param Integer $ttl Time To Live (Expiration value)
     * @return Boolean True if data is stored successfully
     */
    public static function set($cache_key, $data, $ttl = MEMCACHE_DEFAULT_EXPIRES)
    {
	if (USE_MEMCACHE) {
	    if (class_exists('Memcache')) {
		$instance = self::getInstance();
		$result = $instance->set(MEMCACHE_PREFIX . $cache_key, $data, 0, $ttl);
		if ($result) {
		    return true;
		} else {
		    return false;
		}
	    }
	} else {
	    return false;
	}
    }



    /**
     * Delete data stored by a particular key
     * @param String $cache_key Key for the cache hash
     * @return Boolean True if data is deleted successfully
     */
    public static function delete($cache_key)
    {
	if (USE_MEMCACHE) {
	    $instance = self::getInstance();
	    $instance->delete(MEMCACHE_PREFIX . $cache_key);
	}
    }



    /**
     * Delete data stored by several keys
     * @param Array $cache_array_key Array of cache_keys
     */
    public static function deleteArray($cache_array_key)
    {
	if (USE_MEMCACHE) {
	    if (count($cache_array_key) > 0) {
		foreach ($cache_array_key as $key => $value) {
		    self::delete($key);
		}
	    }
	}
    }



    /**
     * Flush all the data stored in the APC cache
     * @return Boolean True if data is flushed successfully
     */
    public static function flush()
    {
	if (USE_MEMCACHE) {
	    $instance = self::getInstance();
	    $instance->flush();
	}
    }



}