<?php

/**
 * Util function to manipulate strings
 */
class ThefText
{

    /**
     * Returns if a numeric IP is valid
     * @param STring $ip IP number
     * @return Boolean True for valid IP
     */
    public static function isValidIP($ip)
    {
	$val_0_to_255 = "(25[012345]|2[01234]\d|[01]?\d\d?)";
	$reg = "#^($val_0_to_255\.$val_0_to_255\.$val_0_to_255\.$val_0_to_255)$#";

	if (preg_match($reg, $ip)) {
	    return true;
	} else {
	    return false;
	}
    }



    /**
     * Creates random token
     * @param Integer $length Length for the token (Default: 50)
     * @return String Token created
     */
    public static function createToken($length = 50)
    {
	$token = '';
	$cadena = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	for ($i = 1; $i <= $length; $i++) {
	    $token .= substr($cadena, rand(0, 61), 1);
	}
	return md5($token);
    }



    /**
     * Cut a phrase by words and adds '...' at the end (only if neccesary)
     * @param String $text Text to cut
     * @param Integer $length Max length
     * @return String New string
     */
    public static function cut($text = '', $length = 80)
    {
	$result = '';
	$text = strip_tags($text);
	if (strlen($text) > $length) {
	    $arrayText = explode(' ', $text);
	    $counter = 0;
	    $final = (strlen($text) > $length) ? '...' : '';
	    while ($length >= strlen($result) + strlen($arrayText[$counter])) {
		$result .= ' ' . $arrayText[$counter];
		++$counter;
	    }
	    $result .= $final;
	    return $result;
	} else {
	    return $text;
	}
    }



    /**
     * Devuelve codigo html purificado (nl2br, stripslaces)
     * @param string $text Texto de entrada
     * @param int $length Largo para cortar en caracteres (Por Defecto: 0, no cortar)
     * @param bool $br_allowed Si permite saltos de linea (Por defecto: true)
     * @return string
     */

    /**
     * Returns the HTML code optimized for print
     *
     * Uses: cut phrase, nl2br (if br_allowed), stripslaces
     *
     * @param String $text Text to optimize
     * @param Integer $length Max length
     * @param Boolean $br_allowed True for converts \r\n in <BR>
     * @return String Text optimized
     */
    public static function optimize($text, $length = 0, $br_allowed = true)
    {
	if ($length > 0)
	    $text = self::cut($text, $length);
	if ($br_allowed)
	    $text = nl2br($text);
	$text = str_replace("\r", '', $text);
	$text = stripslashes($text);
	
	// Aplicar etiquetas <a> a las URL's
	$html_pattern = 'http:\/\/(([^\/]*)\/([a-zA-Z0-9])*)';
	$html_out = "<a href='http://$1' target='_blank'>http://$1</a>";
	$text = preg_replace('/' . $html_pattern . '/', $html_out, $text);

	// Aplicar links a twitter para las menciones de usuarios
	$html_pattern = '\@([a-zA-Z0-9_]*)';
	$html_out = "<a href='https://twitter.com/$1' target='_blank'>@$1</a>";
	$text = preg_replace('/' . $html_pattern . '/', $html_out, $text);
	
	return $text;
    }



    /**
     * Sanitize text to avoid javascript or HTML code to execute
     *
     * @param String $text Text to sanitize
     * @param Integer $length Max length (default: 0, no cut)
     * @param Boolean $br_allowed True for converts \r\n in <BR>
     * @return string
     */
    public static function sanitizeOutput($text, $length = 0, $br_allowed = false)
    {
	$text = stripslashes(htmlentities(utf8_decode($text)));
	if ($length > 0)
	    $text = self::cut($text, $length);
	if ($br_allowed)
	    $text = nl2br($text);
	return $text;
    }



    /**
     * Change text with http:// .. with <a href='http://..>url</a>
     *
     * @param String $text Text to change links
     * @param String $css CSS class for tags <a>
     * @param String $target Target for <a> tag (_blank, _self)
     * @return String Text with links
     */
    public static function enableHREFs($text, $css = '', $target = '_blank')
    {
	// Aplicar etiquetas <a> a las URL's
	$html_pattern = 'http:\/\/(([^\/]*)\/([a-zA-Z0-9])*)';
	$html_out = "<a href='http://$1' class='" . $css . "' target='" . $target . "'>http://$1</a>";
	$text = preg_replace('/' . $html_pattern . '/', $html_out, $text);
	return $text;
    }



    /**
     * Return HTML code entered by the WYSIWYG in the CMS
     * @param String $text Text to clean
     * @param Integer $length Max length (default: 0, no cut)
     * @return String Text cleaned
     */
    public static function cleanHtml($text, $length = 0)
    {
	$text = preg_replace('/(SIZE=")([0-9]{1,2})(")/', '', stripslashes($text));
	$text = str_ireplace('LETTERSPACING="0"', '', $text);
	$text = str_ireplace('<p ', '<parrafo ', $text);
	$text = str_ireplace('</p>', '</parrafo><br />', $text);
	$text = str_ireplace(' FACE="tahoma"', '', $text);
	$text = str_ireplace(' COLOR="#666666"', '', $text);
	$text = str_ireplace(' COLOR="#0000FF"', '', $text);
	$text = str_ireplace('13px', '11px', $text);
	$text = str_ireplace(' face="Verdana"', '', $text);
	$text = str_ireplace(' color="#000000"', '', $text);
	$text = str_ireplace('&apos;', "'", $text); // IE BUG FIX
	if ($length > 0)
	    $text = self::cut($text, $length);
	return $text;
    }



    /**
     * Clean a URL to avoid accents and special chars
     *
     * @param String $text URL to optimize
     * @return String URL optimized
     */
    public static function urlOptimize($text, $exclude_chars = 'a-zA-Z0-9_')
    {
	$text = self::noAccents($text);
	$text = preg_replace("/[^$exclude_chars ]/", '', $text);
	$text = str_replace(' ', '-', $text);
	$text = strtolower($text);
	return $text;
    }



    /**
     * Remove accents from string
     * @param String $text Text
     * @return String without accents
     */
    public static function noAccents($text)
    {
	$text = str_replace('á', 'a', $text);
	$text = str_replace('é', 'e', $text);
	$text = str_replace('í', 'i', $text);
	$text = str_replace('ó', 'o', $text);
	$text = str_replace('ú', 'u', $text);
	$text = str_replace('Á', 'A', $text);
	$text = str_replace('É', 'E', $text);
	$text = str_replace('Í', 'I', $text);
	$text = str_replace('Ó', 'O', $text);
	$text = str_replace('Ú', 'U', $text);
	$text = str_replace('Ú', 'U', $text);
	$text = str_replace('ü', 'u', $text);
	$text = str_replace('Ü', 'U', $text);
	$text = str_replace('ñ', 'n', $text);
	$text = str_replace('Ñ', 'N', $text);
	return $text;
    }



    /**
     * Removes the character $char_to_remove from $text if is the last ocurrence
     *
     * @param String $text Text
     * @param Char $char_to_remove Char to remove
     * @return String
     */
    public static function removeLastChar($text, $char_to_remove)
    {
	if (strrpos($text, $char_to_remove) == strlen($text) - 1) {
	    $text = substr_replace($text, '', strlen($text) - 1);
	}
	return $text;
    }



    /**
     * Strip creditcard showing * and only the last 4 numbers
     * 
     * @param String $card_number Card number with 16 numbers
     * @return String Sequence stripped
     */
    public static function stripCreditCard($card_number)
    {
	str_replace(' ', '', $card_number);
	str_replace('-', '', $card_number);
	str_replace('.', '', $card_number);
	return '************' . substr($card_number, 12, 4);
    }



}