<?php

// Configuraciones de seguridad
ini_set('register_globals', false);


// Maxima duraci�n de la sesion
ini_set('session.gc_maxlifetime', 30 * 60); // 30 minutos


// RUTAS ABSOLUTAS
$http_protcol = ($_SERVER['HTTPS']) ? 'https://' : 'http://';
define('WEB_PATH'				, $http_protcol . $_SERVER['SERVER_NAME'] . '/' . SITE_FOLDER);
define('WEB_PATH_SSL'				, 'https://' . $_SERVER['SERVER_NAME'] . '/' . SITE_FOLDER);
define('WEB_PATH_NO_SSL'			, 'http://' . $_SERVER['SERVER_NAME'] . '/' . SITE_FOLDER);
define('WEB_PATH_MOBILE'			, str_replace('www', 'm', WEB_PATH));


// TIEMPOS DE MEMCACHE
define('MEMCACHE_DEFAULT_EXPIRES'		, '60');


define('TWITTER_HASHTAG'			, 'CottoVsTrout');


// TWEETS
define('TWEETS_IMPORTED_PUBLISHED'	, true);


//CACHE
define('CACHE_PATH'				, ROOT_PATH . 'cache/');
define('CACHE_HTML_PATH'			, CACHE_PATH . 'html/');
define('CACHE_IMG_PATH'				, CACHE_PATH . 'img/');