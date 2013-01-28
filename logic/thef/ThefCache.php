<?php

/**
 *  Proxy between APC cache and Memcache
 *  Try to use APC first, and if it's not active, use memcache
 */
class ThefCache
{

	/**
	 * Get a contented stored in cache
	 * @param String $cache_key Key for the cache hash
	 * @return Mixed Content stored
	 */
	public static function get($cache_key)
	{
		if (USE_APC) {
			return ThefAPC::get($cache_key);
		} else
		if (USE_MEMCACHE) {
			return ThefMemcache::get($cache_key);
		} else {
			return null;
		}
	}



	/**
	 * Set a content in the cache
	 * @param String $cache_key	Key for the cache hash
	 * @param Mixed $data Data to be stored
	 * @param Integer $ttl Time To Live (Expiration value)
	 * @return Boolean True if data is stored successfully
	 */
	public static function set($cache_key, $data, $ttl = APC_CACHE_DEFAULT_EXPIRES)
	{
		if (USE_APC) {
			return ThefAPC::set($cache_key, $data, $ttl);
		} else
		if (USE_MEMCACHE) {
			return ThefMemcache::set($cache_key, $data, $ttl);
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
		if (USE_APC) {
			return ThefAPC::delete($cache_key);
		} else
		if (USE_MEMCACHE) {
			return ThefMemcache::delete($cache_key);
		} else {
			return false;
		}
	}



	/**
	 * Delete data stored by several keys
	 * @param Array $cache_array_key Array of cache_keys
	 */
	public static function deleteArray($cache_array_key)
	{
		if (USE_APC) {
			ThefAPC::deleteArray($cache_array_key);
		} else
		if (USE_MEMCACHE) {
			ThefMemcache::deleteArray($cache_array_key);
		}
	}



	/**
	 * Flush all the data stored in the cache
	 * @return Boolean True if data is flushed successfully
	 */
	public static function flush()
	{
		if (USE_APC) {
			return ThefAPC::flush();
		} else
		if (USE_MEMCACHE) {
			ThefMemcache::flush();
		}
	}



}