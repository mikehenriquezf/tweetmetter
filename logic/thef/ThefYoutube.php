<?php

/**
 * Function to show youtube videos embed and thumbs by a URL
 */
class ThefYoutube
{

    /**
     * Returns true if is a valid youtube link
     * @param String $url URL to validate
     * @return Boolean
     */
    function isYoutube($url)
    {
	return (substr_count(strtolower($url), "youtube.com"));
    }



    /**
     * Get youtube thumb for a specific URL
     * @param String $url URL of video
     * @return String URL of the main thumb
     */
    function getYoutubeThumb($url)
    {
	$arr_url = end(explode("watch?v=", $url));
	$arr_url = explode("&", $arr_url);
	$id = $arr_url[0];
	return "http://img.youtube.com/vi/$id/default.jpg";
    }



    /**
     * Get youtube embed code for a specific url
     *
     * $options['w'] = embed width (default: 640)
     * $options['h'] = embed height (default: 360)
     * $options['autoplay'] = Boolean (default: false)
     *
     * @param String $url URL of video
     * @param Array $options options to display
     * @return String HTML code of embed
     */
    public function getYoutubeVideo($url, $options = array())
    {
	if (!isset($options['w']))
	    $options['w'] = 640;
	if (!isset($options['h']))
	    $options['h'] = 360;
	if (!isset($options['autoplay']))
	    $options['autoplay'] = false;

	$arr_url = end(explode("watch?v=", $url));
	$arr_url = explode("&", $arr_url);
	$id = $arr_url[0];
	$w = $options['w'];
	$h = $options['h'];

	if ($options['autoplay']) {
	    $url = "<iframe width='" . $w . "' height='" . $h . "' src='http://www.youtube.com/embed/$id?feature=player_detailpage&autoplay=1&wmode=transparent' frameborder='0' allowfullscreen></iframe>";
	} else {
	    $url = "<object type='application/x-shockwave-flash' style='width:" . $w . "px; height:" . $h . "px;' data='http://www.youtube.com/v/$id?version=3'>
			<param name='movie' value='http://www.youtube.com/v/$id?version=3' />
			<param name='allowFullScreen' value='true' />
			<param name='allowscriptaccess' value='always' />
			<param name='wmode' value='transparent' />
		</object>";
	}

	return $url;
    }



}