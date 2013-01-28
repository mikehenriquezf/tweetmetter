<?php

/**
 * 	Dinamic resize and dinamic cache of images (only small sites)
 *  Cache time and Cache folder should be defined in config_site or config_global
 */
class ThefImage
{

    private static $cache_time = 3600; // 1 hour
    private static $cache_folder = 'cache/img/';

    /**
     * Returns the resize version for an image
     *
     * @param array $params Array with options (see ThefImageCrop for more information)
     * @param Boolean $use_cache True for using cache
     */
    public static function getResized($params, $use_cache = true)
    {
	$oImageCrop = new ThefImageCrop();
	if ($use_cache) {
	    $img_ext = ThefFile::getExtension($params['img_from']);
	    $img_md5 = self::getImageHash($params['img_from'], $params);
	    $img_cache = ROOT_PATH . self::$cache_folder . $img_md5 . '.' . $img_ext;
	    if (file_exists($img_cache)) {
		$time_created = filemtime($img_cache);
		$time_limit = time() - $time_created;
		if ($time_limit > self::$cache_time) {
		    // Image out of date!
		    @unlink($img_cache);
		    return self::getResized($params, true);
		} else {
		    return WEB_PATH . str_replace(ROOT_PATH, '', $img_cache);
		}
	    } else {
		// Create cache
		$params_to_crop = $params;
		$params_to_crop['img_to'] = $img_cache;
		$params_to_crop['output'] = ThefImageCrop::OUTPUT_FILE;
		$oImageCrop->createCrop($params_to_crop);
		return self::getResized($params, true);
	    }
	} else {
	    // Create thumb on the fly
	    $params['output'] = ThefImageCrop::OUTPUT_PRINT;
	    $options = rtrim(strtr(base64_encode(gzdeflate(json_encode($params), 9)), '+/', '-_'), '=');
	    return WEB_PATH . "include/lib/thumb.php?o=$options";
	}
    }



    /**
     * Create hash (md5) for a specific file and options
     * @param String $file File path and name
     * @param Array $options Array with options
     * @return String Hash
     */
    private static function getImageHash($file, $options)
    {
	return md5($file . '_' . md5(implode($options)));
    }



}
