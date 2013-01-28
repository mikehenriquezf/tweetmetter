<?php


require('../include.php');

$options = gzinflate(base64_decode(strtr($_GET['o'], '-_', '+/')));
$options = (array)json_decode($options);

if (is_array($options)) {
	$oImageCrop = new ThefImageCrop();
	$oImageCrop->createCrop($options);
} else {
	if (DEBUG_ON) {
		if (!file_exists($options['img_from'])) {
			die($options['img_from']. " doesnt exists");
		} else {
			die("invalid $options: <PRE>" . print_r($options, true) . "</PRE>");
		}
	} else {
		header("HTTP/1.1 403 Forbidden");
	}
}