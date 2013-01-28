<?php

// LB-X

// APLICATION URLS
define('SITE_FOLDER'				, '');


// FILE - CONFIGURATIONS
define('FILE_STATIC_PATH'			, ROOT_PATH . 'include/');
define('XML_CONFIGURACIONES'			, FILE_STATIC_PATH . 'configuraciones.xml');
define('XML_KEYWORDS'				, FILE_STATIC_PATH . 'twitter_keywords.xml');
define('TXT_STOPWORDS'				, FILE_STATIC_PATH . 'twitter_stopwords.txt');

// DEBUG / CACHE / MEMCACHE
define('DEBUG_ON'				, false);
define('CACHE_ON'				, false);
define('USE_APC'				, false);
define('USE_MEMCACHE'				, true);
ini_set("zlib.output_compression"		, 1);


//MySQL
define('LOG_MYSQL'				, false);
//define('DB_HOST'				, 'twitdb.c75i6gwpwj27.us-east-1.rds.amazonaws.com');
define('DB_HOST'				, 'twitdb.c75i6gwpwj27.us-east-1.rds.amazonaws.com');
define('DB_BASE'				, 'cottovstrout');
define('DB_USER'				, 'twitdb');
define('DB_PASS'				, 'Pb1AMRUClkd&a*%N');
define('DB_PORT'				, '3306');
define('DB_CHARSET'				, 'utf8');


// Configuraciones de Ubicacion / Tiempo / Monedas
date_default_timezone_set('America/Puerto_Rico');


// FACEBOOK
define('FACEBOOK_APP_ID'			, '');
define('FACEBOOK_SECRET'			, '');


// TWITTER
define('TWITTER_CONSUMER_KEY'			, '7x2yqO3pCDYQbBRrNjkw');
define('TWITTER_CONSUMER_SECRET'		, 'FQXGi2pl69MrAVYvKTU6JhImBI8l6WqUYSBdMbcvM');
define('TWITTER_ACCESS_TOKEN'			, '15534406-fFoG2ebhu9HwAHDkxzcV3FS6KbJ16TIL5uCW4QfHu');
define('TWITTER_ACCESS_TOKEN_SECRET'		, 'Ye9vYjxNC8sG0k00b11HjK1EHwKxNynAsekvJWecPk');
define('TWITTER_SYNCHRONIZE_FROM'		, '2007-01-01 00:00:00');

// GOOGLE ANALYTICS
define('GOOGLE_ANALYTICS_ID'			, 'UA-3419264-1');


//MEMCACHE
define('MEMCACHE_SERVER'			, 'twit-cache.aqi5oq.0001.use1.cache.amazonaws.com');
define('MEMCACHE_PORT'				, '11211');
define('MEMCACHE_PREFIX'			, 'cottovstrout');

if (DEBUG_ON) {
    error_reporting(E_ALL ^ E_NOTICE);
} else {
    error_reporting(0);
}

