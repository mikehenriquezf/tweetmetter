<?php

/**
 * 	Util functions to manipulate dates
 */
class ThefDate
{

    private static $daysSp = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
    private static $monthsSp = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

    /**
     * Return the date in the MySQL format
     * @param String $date Date in dd/mm/YYYY [H:i:s] format (time is optional)
     * @return String Date in MySQL format
     */
    public static function toMySQL($date)
    {
	$arr_date = explode(' ', $date);
	$arr_time = $arr_date[1];
	$arr_date = explode('/', $arr_date[0]);
	$mysql_date = $arr_date[2] . '-' . $arr_date[1] . '-' . $arr_date[0];
	if (strlen($arr_time) > 0) {
	    $mysql_date .= ' ' . $arr_time;
	}
	return $mysql_date;
    }



    /**
     * Checks the validity of the date
     * @param String $date Date in MySQL format (time not included)
     * @return Boolean True if the date is valid
     */
    public static function isValidDate($date)
    {
	$return = false;
	$arr_date = explode('-', substr($date, 0, 10));
	if (count($arr_date) == 3) {
	    list($y, $m, $d) = $arr_date;
	    $return = checkdate($m, $d, $y) && strtotime("$y-$m-$d") && preg_match('#\b\d{2}[/-]\d{2}[/-]\d{4}\b#', "$d-$m-$y");
	}
	return $return;
    }



    /**
     * Return the given date plus (or minus) the specified days
     * @param String $date Date in MySQL format
     * @param Integer $days Number of days to add (days can be negative)
     * @param String $format Format for the output date (default: MySQL)
     * @return String Date with added days
     */
    public static function addDays($date, $days, $format = 'Y-m-d')
    {
	$days = (($days > 0) ? '+' : '-') . abs($days);
	$date = strtotime(date('Y-m-d', strtotime($date)) . " $days day");
	$date = date($format, $date);
	return $date;
    }



    /**
     * Return the days between 2 specific dates
     * @param String $date_1 Date 1 in MySQL format
     * @param String $date_2 Date 2 in MySQL format
     * @return Number Difference in days
     */
    public static function getDiffDays($date_1, $date_2)
    {
	list($Y_1, $m_1, $d_1) = explode('-', $date_1);
	list($Y_2, $m_2, $d_2) = explode('-', $date_2);
	$timestamp1 = mktime(0, 0, 0, $m_1, $d_1, $Y_1);
	$timestamp2 = mktime(0, 0, 0, $m_2, $d_2, $Y_2);
	$diference = abs($timestamp1 - $timestamp2) / (60 * 60 * 24);
	return floor($diference);
    }



    /**
     * Return the age from a given date (birthday)
     * @param String $date Date in MySQL format
     * @return Number The result in years
     */
    public static function getAge($date)
    {
	$yearDiff = 0;
	list($year, $month, $day) = explode('-', substr($date, 0, 10));
	$yearDiff = date('Y') - $year;
	$monthDiff = date('m') - $month;
	$dayDiff = date('d') - $day;
	if ($monthDiff < 0) {
	    --$yearDiff;
	} else
	if (($monthDiff == 0) && ($dayDiff < 0)) {
	    --$yearDiff;
	}
	return $yearDiff;
    }



    /**
     * Return the month name in english
     * @param Number $monthNumber Number of month (January = 1)
     * @param Boolean $short True is returns should be 3 letters (default: false)
     * @return String Month name
     */
    public static function getMonthNameEnglish($monthNumber, $short = false)
    {
	$month = date('F', mktime(0, 0, 0, $monthNumber));
	if ($short) {
	    $month = substr($month, 0, 3);
	}
	return $month;
    }



    /**
     * Return the month name in spanish
     * @param Number $monthNumber Number of month (January = 1)
     * @param Boolean $short True is returns should be 3 letters (default: false)
     * @return String Month name
     */
    public static function getMonthNameSpanish($monthNumber, $short = false)
    {
	$month = ($short) ? substr(self::$monthsSp[$monthNumber - 1], 0, 3) : self::$monthsSp[$monthNumber - 1];
	return $month;
    }



    /**
     * Return the day name in english
     * @param String $date Date in MySQL format
     * @param Boolean $short True is returns should be 3 letters (default: false)
     * @return String Month name
     */
    public static function getDayNameEnglish($date, $short = false)
    {
	if ($short) {
	    $day = date('D', strtotime($date));
	} else {
	    $day = date('l', strtotime($date));
	}
	return $day;
    }



    /**
     * Return the day name in spanish
     * @param String $date Date in MySQL format
     * @param Boolean $short True is returns should be 3 letters (default: false)
     * @return String Month name
     */
    public static function getDayNameSpanish($date, $short = false)
    {
	$day = self::$daysSp[date('w', strtotime($date))];
	if ($short) {
	    $day = substr($day, 0, 3);
	}
	return $day;
    }



    /**
     * Return the date in dd/mm/YYYY format (time is optional)
     * @param String $date Date in MySQL format
     * @param Boolean $time True is time is included in return
     * @return String Date
     */
    public static function format($date, $time = true)
    {
	$result = date("d/m/Y", strtotime($date));
	if ($time) {
	    $result .= ' ' . date("H:i", strtotime($date));
	}
	return $result;
    }



    /**
     * Return the date in this format: 9 de julio de 2012 (time is optional)
     * @param String $date Date in MySQL format
     * @param Boolean $time True is time is included in return
     * @return String Date
     */
    public static function getDateInSpanish($date, $time = true)
    {
	$month_year = self::getMonthNameSpanish(date('m', strtotime($date)));
	if ($time) {
	    $time = date("h:i A", strtotime($date));
	    return date("d", strtotime($date)) . ' de ' . $month_year . ' ' . $time;
	} else {
	    return date("d", strtotime($date)) . ' de ' . $month_year;
	}
    }



    /**
     * Return the date in MySQL format
     * @param String $date Date in twitter format
     * @return String Date
     */
    public static function fromTwitterDate($date)
    {
	return gmdate('Y-m-d H:i:s', strtotime($date) + date('Z'));
    }



    /**
     * Return the date in MySQL format
     * @param String $date Date in facebook format
     * @return String Date
     */
    public static function fromFacebookDate($date)
    {
	$arr_date = explode('/', $date);
	return $arr_date[2] . '-' . $arr_date[0] . '-' . $arr_date[1];
    }



}