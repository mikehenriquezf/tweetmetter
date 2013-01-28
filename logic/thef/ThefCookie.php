<?php

/**
 * 	Interface for the use of cookies
 */
class ThefCookie
{

    public static $expires = 2580000; // Expiration time in seconds, default: 30 day

    /**
     * Set default expiration time in secunds (minimum: 60)
     * @param Integer $time	Time in seconds
     */

    public static function setExpireTime($time)
    {
	$time = max(60, $time);
	self::$expires = $time;
    }



    /**
     * Add or Modify the value of a cookie
     * @param String $name Name of the cookie
     * @param Mixed $value Value to store
     * @param Integer $expires Time in seconds to expires (default: self::expires)
     * @return Boolean True if successfully stored
     */
    public static function set($name, $value, $expires = null)
    {
	$expires = (is_null($expires)) ? self::$expires : $expires;
	return setcookie($name, $value, time() + $expires, '/', '', false, true);
    }



    /**
     * Return the value of a cookie
     * @param String $name Name of the cookie
     * @return Mixed Value of the cookie (null if not exists)
     */
    public static function get($name)
    {
	$cookie = null;
	if (isset($_COOKIE[$name])) {
	    $cookie = $_COOKIE[$name];
	}
	return $cookie;
    }



    /**
     * 	Delete a cookie
     * @param String $name Name of the cookie
     * @return Boolean True if successfully removed
     */
    public static function delete($name)
    {
	unset($_COOKIE[$name]);
    }



}

