<?php

/**
 * ThefImageCrop
 * Creates crops for images, options explained in createCrop method
 *
 * Variables within {}
 * {Y} = Year	4 digits
 * {y} = Year	2 digits
 * {m} = Month	2 digits
 * {d} = Day	2 digits
 *
 * RESIZED: Resize image to be inside w/h with or without background (fit)
 * TRUNCATE: Resize image to fill the whole area (w/h), some part of the image could be lost (fill)
 * PROPORTIONAL: Resize image using w or h, the other side will be free resized
 */
class ThefImageCrop
{

    const TYPE_RESIZED = 1;
    const TYPE_TRUNCATE = 2;
    const TYPE_PROPORTIONAL = 3;
    const OUTPUT_FILE = 1;
    const OUTPUT_PRINT = 2;
    const ALIGN_V_TOP = 8;
    const ALIGN_V_MIDDLE = 5;
    const ALIGN_V_BOTTOM = 2;
    const ALIGN_H_LEFT = 4;
    const ALIGN_H_CENTER = 5;
    const ALIGN_H_RIGHT = 6;
    const MIME_GIF = 'image/gif';
    const MIME_JPEG = 'image/jpeg';
    const MIME_PNG = 'image/png';

    public $alignHorizonal = self::ALIGN_H_CENTER;
    public $alignVertical = self::ALIGN_V_MIDDLE;
    public $backgroundColor = '#FFFFFF';
    public $backgroundFill = true;
    public $cropType = self::TYPE_RESIZED;
    public $expandImage = false;
    public $output = self::OUTPUT_FILE;
    public $outputMime = self::MIME_JPEG;
    public $jpegQuality = 90;
    public $pngQuality = 9;
    public $pngTransparency = true;
    public $saveFolder = 'upload/images/{Y}/{m}/{d}/';

    const MSG_INVALID_IMG = 'Invalid image source';
    const MSG_CROP_CREATED_OK = 'Crop created OK';
    const MSG_CROP_CREATED_ERROR = 'Crop created ERROR';

    public function __construct()
    {
	
    }



    /**
     * Creates crop image
     * $params['img_from']			String	Absolute path in local server of the original image
     * $params['img_to']			String	Absolute path in local server of the output image
     * $params['type']				ENUM	Type of resize (TYPE_RESIZED / TYPE_TRUNCATE / TYPE_PROPORTIONAL) (d: RESIZED)
     * $params['w']					Number	Width of the output image
     * $params['h']					Number	Height of the output image
     * $params['bg_fill']			Boolean	Fill empty space with Background color (d: true)
     * $params['bg_color']			Hexa	Background color for TYPE_RESIZED (d: #FFFFFF)
     * $params['expand_image']		Boolean	If image is smaller than resized, should expand (d: false)
     * $params['output_mime']		ENUM	Mime type (MIME_GIF / MIME_JPEG / MIME_PNG) (d: JPEG)
     * $params['output']			ENUM	Output source (OUTPUT_FILE / OUTPUT_PRINT) (d: FILE)
     * $params['jpeg_quality']		Number	JPEG quality (d: 90)
     * $params['png_quality']		Number	PNG quality (d: 9)
     * $params['png_transparency']	Boolean	Preserve PNG transprency (d: true)
     * $params['img_watermark']		String	Absolute path in local server of the watermark image
     * $params['watermark_w']		Number	Width of the watermark image
     * $params['watermark_h']		Number	Height of the watermark image
     * $params['watermark_x']		Number	Position X of watermark image
     * $params['watermark_y']		Number	Position Y of watermark image
     * $params['align_v']			ENUM	Vertical alignment (ALIGN_V_TOP / ALIGN_V_MIDDLE / ALIGN_V_BOTTOM) (d: MIDDLE)
     * $params['align_h']			ENUM	Horizontal alignment (ALIGN_H_LEFT / ALIGN_H_CENTER / ALIGN_H_RIGHT) (d: CENTER)
     * @param Array $params Paramters, see function description
     * @return Boolean TRUE if image was successfully created.
     */
    public function createCrop($params)
    {
	$params = $this->loadImage($params);

	if (is_resource($params['img_ref'])) {
	    // Calculate new dimensions
	    $params = $this->calculateDimensions($params);

	    // Crop image and resize it
	    $params = $this->resize($params);

	    // Apply watermark
	    if ($params['img_watermark']) {
		$params = $this->addWatermark($params);
	    }

	    // Print image to output
	    $result = $this->printAction($params);

	    if ($params['output'] == self::OUTPUT_FILE) {
		if ($result) {
		    return array('code' => 0, 'msg' => self::MSG_CROP_CREATED_OK);
		} else {
		    return array('code' => -2, 'msg' => self::MSG_CROP_CREATED_ERROR);
		}
	    }

	    // Delete image from memory
	    imagedestroy($params['img_dst']);
	    unset($params);
	} else {
	    return array('code' => -1, 'msg' => self::MSG_INVALID_IMG);
	}
    }



    private function loadImage($params)
    {
	// Control valid image
	$img_info = getimagesize($params['img_from']);
	switch ($img_info['mime']) {
	    case self::MIME_JPEG:
		$img_ref = imagecreatefromjpeg($params['img_from']);
		break;
	    case self::MIME_GIF;
		$img_ref = imagecreatefromgif($params['img_from']);
		break;
	    case self::MIME_PNG:
		$img_ref = imagecreatefrompng($params['img_from']);
		break;
	}
	$params['img_ref'] = $img_ref;
	$params['img_info'] = $img_info;
	return $params;
    }



    private function calculateDimensions($params)
    {
	$src_w = $params['img_info'][0];
	$src_h = $params['img_info'][1];
	$src_ratio = $src_w / $src_h;
	$dst_ratio = $params['w'] / $params['h'];

	$type = ($params['type']) ? ($params['type']) : $this->cropType;

	$expand_image = (!is_null($params['expand_image'])) ? $params['expand_image'] : $this->expandImage;

	// If image should not be expanded and background fill is disabled, use source dimensions
	if (!$expand_image && !$params['bg_fill']) {
	    if ($src_w < $params['w'])
		$params['w'] = $src_w;
	    if ($src_h < $params['h'])
		$params['h'] = $src_h;
	}

	if ($type == self::TYPE_RESIZED || $type == self::TYPE_PROPORTIONAL) {
	    if ($src_ratio >= $dst_ratio) {
		$dst_w = $params['w'];
		$dst_h = $dst_w / $src_ratio;
	    } else {
		$dst_h = $params['h'];
		$dst_w = $dst_h * $src_ratio;
	    }

	    // Proportional removes
	    if ($type == self::TYPE_PROPORTIONAL) {
		$params['w'] = $dst_w;
		$params['h'] = $dst_h;
	    } else {
		// Resized - source image is smaller, fill empty space with background
		if (!$expand_image) {
		    if ($src_w < $params['w'] || $src_h < $params['h']) {
			$dst_w = $src_w;
			$dst_h = $src_h;
		    }
		}
	    }
	} else
	if ($type == self::TYPE_TRUNCATE) {
	    if ($params['w'] * $src_h / $src_w > $params['h']) {
		$dst_w = $params['w'];
		$dst_h = $dst_w / $src_ratio;
	    } else {
		$dst_h = $params['h'];
		$dst_w = $dst_h * $src_ratio;
	    }

	    // Set bg_fill = true to avoid resize of destination image
	    $params['bg_fill'] = true;

	    // If image leaves empty space and expand image, swift dimensions
	    if ($expand_image) {
		if ($dst_w < $params['w']) {
		    $dst_w = $params['w'];
		    $dst_h = $dst_w / $src_ratio;
		} else
		if ($dst_h < $params['h']) {
		    $dst_h = $params['h'];
		    $dst_w = $dst_h * $src_ratio;
		}
	    } else {
		// Avoid white spaces if destionation image w/h is smaller and no expanded
		if ($dst_w < $params['w'])
		    $params['w'] = $dst_w;
		if ($dst_h < $params['h'])
		    $params['h'] = $dst_h;
	    }
	}

	$params['dst_w'] = $dst_w;
	$params['dst_h'] = $dst_h;
	$params['dst_x'] = $dst_x;
	$params['dst_y'] = $dst_y;
	$params['src_w'] = $src_w;
	$params['src_h'] = $src_h;

	return $params;
    }



    private function resize($params)
    {
	$dst_w = $params['dst_w'];
	$dst_h = $params['dst_h'];
	$dst_x = $params['dst_x'];
	$dst_y = $params['dst_y'];
	$src_w = $params['src_w'];
	$src_h = $params['src_h'];
	$src_x = 0;
	$src_y = 0;

	$align_v = ($params['align_v']) ? $params['align_v'] : $this->alignVertical;
	$align_h = ($params['align_h']) ? $params['align_h'] : $this->alignHorizonal;

	// Horizontal Alignment
	switch ($align_h) {
	    case self::ALIGN_H_LEFT:
		$dst_x = 0;
		break;
	    case self::ALIGN_H_CENTER:
		$dst_x = $params['w'] / 2 - $dst_w / 2;
		break;
	    case self::ALIGN_H_RIGHT:
		$dst_x = $params['w'] - $dst_w;
		break;
	}

	// Verticual Alignment
	switch ($align_v) {
	    case self::ALIGN_V_TOP:
		$dst_y = 0;
		break;
	    case self::ALIGN_V_MIDDLE:
		$dst_y = $params['h'] / 2 - $dst_h / 2;
		break;
	    case self::ALIGN_V_BOTTOM:
		$dst_y = $params['h'] - $dst_h;
		break;
	}


	// Fill empty space with background.
	$bg_enabled = (!is_null($params['bg_fill'])) ? $params['bg_fill'] : $this->backgroundFill;
	if ($bg_enabled) {
	    $params['img_dst'] = imagecreatetruecolor($params['w'], $params['h']);
	    $params = $this->addBackground($params);
	} else {
	    $params['img_dst'] = imagecreatetruecolor($dst_w, $dst_h);
	    $dst_x = $dst_y = 0;
	    $params['w'] = $dst_w;
	    $params['h'] = $dst_h;
	}

	$img_ok = imagecopyresampled($params['img_dst'], $params['img_ref'], $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	if ($img_ok) {
	    return $params;
	}
    }



    private function addBackground($params)
    {
	$bg = ($params['bg_color']) ? $params['bg_color'] : $this->backgroundColor;
	$bg = str_replace('#', '', $bg);
	$red = hexdec(substr($bg, 0, 2));
	$green = hexdec(substr($bg, 2, 2));
	$blue = hexdec(substr($bg, 4, 2));

	// Create background color
	$img_bg = imagecreatetruecolor($params['w'], $params['h']);
	$img_color = imagecolorallocate($img_bg, $red, $green, $blue);

	// PNG Transparency
	$png_transparency = (!is_null($params['png_transparency'])) ? $params['png_transparency'] : $this->pngTransparency;
	if ($params['img_info']['mime'] == self::MIME_PNG && $png_transparency) {
	    imagecolortransparent($params['img_dst'], $img_color);
	}

	// Apply background color
	imagefilledrectangle($img_bg, 0, 0, $params['w'], $params['h'], $img_color);
	imagecopy($params['img_dst'], $img_bg, 0, 0, 0, 0, $params['w'], $params['h']);
	return $params;
    }



    private function addWatermark($params)
    {
	$params_wm = array('img_from' => $params['img_watermark']);
	$params_wm = $this->loadImage($params_wm);

	$wm_w = (!is_null($params['watermark_w'])) ? $params['watermark_w'] : $params_wm['img_info'][0];
	$wm_h = (!is_null($params['watermark_h'])) ? $params['watermark_h'] : $params_wm['img_info'][1];

	// Resize watermark image
	$img_wm = imagecreatetruecolor($wm_w, $wm_h);
	imagealphablending($img_wm, false);
	imagecopyresampled($img_wm, $params_wm['img_ref'], 0, 0, 0, 0, $wm_w, $wm_h, $params_wm['img_info'][0], $params_wm['img_info'][1]);

	// Watermark position (if no given, put wm at bottom right)
	if (!is_null($params['watermark_x'])) {
	    $wm_x = $params['watermark_x'];
	} else {
	    $wm_x = $params['w'] - $wm_w;
	}
	if (!is_null($params['watermark_y'])) {
	    $wm_y = $params['watermark_y'];
	} else {
	    $wm_y = $params['h'] - $wm_h;
	}

	// Copy watermark resized into destination image
	imagecopy($params['img_dst'], $img_wm, $wm_x, $wm_y, 0, 0, $wm_w, $wm_h);
	return $params;
    }



    private function printAction($params)
    {
	$output = ($params['output']) ? $params['output'] : $this->output;
	$mime_output = ($params['output_mime']) ? $params['output_mime'] : $this->outputMime;
	if ($output == self::OUTPUT_PRINT) {
	    $this->printCrop($params['img_dst'], $mime_output);
	} else
	if ($output == self::OUTPUT_FILE) {
	    return $this->printToFile($params, $mime_output);
	}
    }



    private function printCrop($img_resource, $img_type)
    {
	header('Content-type: ' . $img_type);
	switch ($img_type) {
	    case self::MIME_JPEG:
		imagejpeg($img_resource, null, $this->jpegQuality);
		break;
	    case self::MIME_GIF;
		imagegif($img_resource, null);
		break;
	    case self::MIME_PNG:
		imagepng($img_resource, null, $this->pngQuality);
		break;
	}
    }



    private function printToFile($params, $img_type)
    {
	$arr_destination = explode('/', $params['img_to']);
	$file_name = array_pop($arr_destination);

	if (count($arr_destination) > 1) { // Not local folder
	    $dir_name = implode('/', $arr_destination) . '/';

	    // Replace variables within {} with current values
	    $dir_name = str_replace('{Y}', Date('Y'), $dir_name);
	    $dir_name = str_replace('{y}', Date('y'), $dir_name);
	    $dir_name = str_replace('{m}', Date('m'), $dir_name);
	    $dir_name = str_replace('{d}', Date('d'), $dir_name);

	    if (!is_dir($dir_name))
		mkdir($dir_name, 0777, true);
	    $file_name = $dir_name . '/' . $file_name;
	}

	switch ($img_type) {
	    case self::MIME_JPEG:
		$result = imagejpeg($params['img_dst'], $file_name, $this->jpegQuality);
		break;
	    case self::MIME_GIF;
		$result = imagegif($params['img_dst'], $file_name);
		break;
	    case self::MIME_PNG:
		$result = imagepng($params['img_dst'], $file_name, $this->pngQuality);
		break;
	}

	return $result;
    }



}
