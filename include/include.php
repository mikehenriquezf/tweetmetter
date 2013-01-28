<?php
// ROOT_PATH: Ruta fisica de la aplicacion
define('ROOT_PATH', dirname(__FILE__) . '/../');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH . 'include/lib/');

// Configuraciones:
require(ROOT_PATH.'include/config_site.php');

// Configuraciones globales:
require(ROOT_PATH.'include/config_global.php');

// Base de datos:
require(ROOT_PATH.'include/config_mysql.php');

// Template Power
require(ROOT_PATH.'include/lib/class.TemplatePower.inc.php');

// Autoload para clases genericas
function __autoload($class_name)
{
	if (strrpos($class_name, 'Zend') !== false) {
        require_once ROOT_PATH . 'include/lib/' . str_replace('_', '/', $class_name) . '.php';
	} else
    if (strrpos($class_name, 'Thef') !== false) {
        require_once ROOT_PATH . 'logic/thef/' . $class_name . '.php';
    } else
	if (strrpos($class_name, 'TplFrt') !== false) {
        require_once ROOT_PATH . 'html_logic/frontend/' . $class_name . '.php';
    } else
	if (strrpos($class_name, 'TplBck') !== false) {
        require_once ROOT_PATH . 'html_logic/backend/' . $class_name . '.php';
    } else
	if (strrpos($class_name, 'DbaFrt') !== false) {
        require_once ROOT_PATH . 'logic/frontend/' . $class_name . '.php';
    } else
	if (strrpos($class_name, 'DbaBck') !== false) {
        require_once ROOT_PATH . 'logic/backend/' . $class_name . '.php';
    } else {
		require_once ROOT_PATH . 'logic/' . $class_name . '.php';
	}
}
