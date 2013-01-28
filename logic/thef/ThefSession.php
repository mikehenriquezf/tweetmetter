<?php

/**
 * Basic sessions functions
 */
class ThefSession
{

    /**
     * Start new session
     */
    public static function start()
    {
	session_start();
    }



    /**
     * Get a value stored in session
     * @param String $field Field name
     * @return Mixed
     */
    public static function get($field)
    {
	$return = NULL;
	if (isset($_SESSION[$field])) {
	    $return = $_SESSION[$field];
	}
	return $return;
    }



    /**
     * Stores a value in session
     * @param String $field Field name
     * @param Mixed $value Data to store
     */
    public static function set($field, $value)
    {
	$_SESSION[$field] = $value;
	session_commit();
    }



    /**
     * Unset a variable stored in session
     * @param String $field Field name
     */
    public static function delete($field)
    {
	$_SESSION[$field] = null;
    }



    /**
     * Kill session
     */
    public static function kill()
    {
	@session_unset();
	@session_destroy();
    }



    /**
     * Restart session with differente SESSION_ID
     * @param String $id New session ID
     */
    public static function setId($id)
    {
	session_id($id);
    }



    /**
     * Get current SESSION_ID
     * @return String
     */
    public static function getId()
    {
	return session_id();
    }



}
