<?php

/**
 * Interface to manipulate HTML templates
 */
class ThefTemplate extends TemplatePower
{

    /**
     * Create new template instante from file
     * @param String $file File name
     * @param String $path Path from file name (default: html/tpl/frontend)
     */
    public function __construct($file, $path = null)
    {
	try {
	    if (is_null($path))
		$path = ROOT_PATH . 'html/tpl/frontend/';

	    parent::TemplatePower($path . $file);

	    $this->fileName = $file;
	    $this->prepare();
	    $this->assignGlobal('WEB_PATH', WEB_PATH);
	    if (defined(WEB_PATH_SSL)) $this->assignGlobal('WEB_PATH_SSL', WEB_PATH_SSL);
	    if (defined(WEB_PATH_NO_SSL)) $this->assignGlobal('WEB_PATH_NO_SSL', WEB_PATH_NO_SSL);
	    if (defined(WEB_PATH_MOBILE)) $this->assignGlobal('WEB_PATH_MOBILE', WEB_PATH_MOBILE);
	    if (defined(WEB_PATH_CDN)) $this->assignGlobal('WEB_PATH_CDN', WEB_PATH_CDN);
	} catch (Exception $ex) {
	    throw $ex;
	}
    }



    /**
     * Reset current instance without reading the html file again
     */
    public function reset()
    {
	try {
	    $this->serialized = false;
	    $this->index = array();
	    $this->content = array();
	    $this->prepare();
	} catch (Exception $ex) {
	    throw $ex;
	}
    }



    /**
     * Returns the HTML content of the template
     * @return String HTML code
     */
    public function getHTML()
    {
	return $this->getOutputContent();
    }



    /**
     * Prints the HTML code
     */
    public function show()
    {
	return $this->printToScreen();
    }



}
