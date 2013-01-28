<?php

require_once('../../include/lib/jsmin/jsmin.php');
require_once('../../include/include.php');

// ARCHIVO DE SALIDA
$output_file = ROOT_PATH . 'html/js/site.js';

// LISTA DE ARCHIVOS A COMPRIMIR
$js_array = array();

// LIBRERIAS
array_push($js_array, ROOT_PATH . 'html/js/frontend/foundation.js');
array_push($js_array, ROOT_PATH . 'html/js/lib/jquery/jquery.ui.core.js');
array_push($js_array, ROOT_PATH . 'html/js/lib/jquery/jquery.ui.widget.js');
array_push($js_array, ROOT_PATH . 'html/js/lib/jquery/jquery.countdown.js');
array_push($js_array, ROOT_PATH . 'html/js/lib/jquery/jquery.mousewheel.js');
array_push($js_array, ROOT_PATH . 'html/js/lib/jquery/jquery.easing.js');
array_push($js_array, ROOT_PATH . 'html/js/lib/jquery/jquery.flexslider.js');
array_push($js_array, ROOT_PATH . 'html/js/lib/jquery/jquery.fancybox.pack.js');
array_push($js_array, ROOT_PATH . 'html/js/lib/jquery/jquery.jscrollpane.min.js');
array_push($js_array, ROOT_PATH . 'html/js/lib/jquery/jquery.qtip.min.js');


// PARTICULARES DEL SITIO
array_push($js_array, ROOT_PATH . 'html/js/frontend/html.js');
array_push($js_array, ROOT_PATH . 'html/js/frontend/lightboxs.js');

$output = '';
if (!DEBUG_ON && file_exists($output_file)) {
    $output = file_get_contents($output_file);
} else {
    foreach ($js_array as $file) {
	if (file_exists($file))
	    $output .= file_get_contents($file);
    }
    if (!DEBUG_ON) {
	$output = JSMin::minify($output);
	file_put_contents($output_file, $output);
    }
}

if (!DEBUG_ON) {
	$last_modified_time = filemtime($output_file);
	$etag = md5_file($output_file);
	header("Cache-Control: must-revalidate");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT");
	header("Expires: " . gmdate("D, d M Y H:i:s \G\M\T", time() + 24 * 60 * 60));
	header("Etag: $etag");
}
echo $output;
