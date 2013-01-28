<?php

/**
 * Util class to have several configuration params in quick memory
 * Informacion will be cached for 60 seconds
 */
class ThefConfig
{

    private static $cache_key = 'Config';
    private static $instance = null;
    private static $config = null;

    /**
     * Instance the ThefConfig
     * @return ThefConfig
     */
    private static function getInstance()
    {
	if (is_null(self::$instance)) {
	    self::$instance = new ThefConfig();
	    $obj = ThefCache::get(self::$cache_key);
	    if (!$obj) {
		$obj = self::$instance->loadConfigXML();
	    }
	    self::$config = $obj;
	}
	return self::$instance;
    }



    /**
     * Loads the configuration file specified in XML_THEF_CONFIG
     * @return Array content of the XML file
     */
    private function loadConfigXML()
    {
	// Read XML
	$oXML = new SimpleXMLElement(file_get_contents(XML_CONFIGURACIONES));
	$arr_config = $this->parseXML($oXML);
	ThefCache::set(self::$cache_key, $arr_config, 60);
	return $arr_config;
    }



    /**
     * Parses an XML node into array or string value
     * @param XMLNode $xml_node
     * @return Mixed
     */
    private function parseXML($xml_node)
    {
	if (count($xml_node) > 0) {
	    $result = array();
	    foreach ($xml_node as $node_name => $value) {
		if (count($value) > 2) {
		    $result[count($result)][$node_name] = $this->parseXML($value);
		} else {
		    $result[$node_name] = $this->parseXML($value);
		}
	    }
	    return $result;
	} else {
	    return (String) $xml_node;
	}
    }



    /**
     * Return the value of a particular param
     * @param String $param Param name
     * @return String Param value
     */
    public static function get($param)
    {
	self::getInstance();
	return self::$config[$param];
    }



    /**
     * Return the whole results stored in the XML
     * @return Array
     */
    public static function getAll()
    {
	return self::$config;
    }



}
