<?php

Class ThefPdf
{

    const CONVERT_CMD = 'convert';
    const DEFAULT_WIDHT = '1536';
    const DEFAULT_HEIGHT = '2048';

    public function __construct()
    {
	// send test command to system
	exec('command  -v ' . self::CONVERT_CMD . ' >& /dev/null && echo "1" || echo "0"', $output);

	if ($output[0] == "0") {
	    throw new Exception('El comando ' . self::CONVERT_CMD . ' no existe');
	}
    }



    public function convert($pdfSrc, $imgOut, $width = '', $height = '', $overwrite = true)
    {


	if (file_exists($pdfSrc)) {

	    if (file_exists($imgOut)) {
		if (!$overwrite) {
		    return false;
		} else if (!is_writable($imgOut)) {
		    return false;
		}
	    }

	    if (intval($width) <= 0) {
		$width = self::DEFAULT_WIDHT;
	    }

	    if (intval($height) <= 0) {
		$height = self::DEFAULT_HEIGHT;
	    }

	    $cmd = self::CONVERT_CMD . ' -density 200 -resize ' . $width . 'x' . $height . ' -colorspace RGB -quality 93 ' . $pdfSrc . '[0] ' . $imgOut;

	    exec($cmd, $output);

	    if (file_exists($imgOut)) {
		return true;
	    } else {
		return false;
	    }
	} else {
	    return false;
	}
    }



}