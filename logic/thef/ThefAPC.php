<?php

/**
 *  APC Interface (Alternative PHP Cache).
 *  Faster performance than Memcache, not for use in distributed systems.
 */
class ThefAPC
{

    /**
     * Get the content stored in APC cache
     * @param String $cache_key Key for the cache hash
     * @return Mixed Content stored
     */
    public static function get($cache_key)
    {
	if (USE_APC) {
	    $var = apc_fetch($cache_key, $success);
	    if ($success) {
		return $var;
	    } else {
		return null;
	    }
	} else {
	    return null;
	}
    }



    /**
     * Set a content in the APC cache
     * @param String $cache_key	Key for the cache hash
     * @param Mixed $data Data to be stored
     * @param Integer $ttl Time To Live (Expiration value)
     * @return Boolean True if data is stored successfully
     */
    public static function set($cache_key, $data, $ttl = APC_CACHE_DEFAULT_EXPIRES)
    {
	if (USE_APC) {
	    return apc_store($cache_key, $data, $ttl);
	} else {
	    return null;
	}
    }



    /**
     * Delete data stored by a particular key
     * @param String $cache_key Key for the cache hash
     * @return Boolean True if data is deleted successfully
     */
    public static function delete($cache_key)
    {
	if (USE_APC) {
	    return apc_delete($cache_key);
	}
    }



    /**
     * Delete data stored by several keys
     * @param Array $cache_array_key Array of cache_keys
     */
    public static function deleteArray($cache_array_key)
    {
	if (USE_APC) {
	    if (count($cache_array_key) > 0) {
		foreach ($cache_array_key as $key => $value) {
		    apc_delete($key);
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
	if (USE_APC) {
	    return apc_clear_cache('user');
	}
    }



}