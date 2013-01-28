<?php

// Connecion a MySQL

$params = array(
    'host'			=> DB_HOST,
    'username'		=> DB_USER,
    'password'		=> DB_PASS,
    'dbname'		=> DB_BASE,
    'port'			=> DB_PORT,
    'charset'		=> DB_CHARSET,
    'profiler'		=> LOG_MYSQL
);

$db = Zend_Db::factory('PDO_MYSQL', $params);
$re = $db->query("SET charset '" . DB_CHARSET . "'");

Zend_Db_Table_Abstract::setDefaultAdapter($db);