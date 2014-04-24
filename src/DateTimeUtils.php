<?php

namespace phweb;

/**
* phweb Date/Time utilities class
* 
* @package phweb
* @author Benjamin Nowack <mail@bnowack.de> 
*/
class DateTimeUtils {

	/**
	* Returns the UTC-normalised unix timestamp. 
	* @return int 
	*/
	static public function getUtcUts($uts = null) {
		// unix timestamp
		if ($uts == null) {
			$uts = time();
		}
		return $uts - date('Z', $uts);
	}

	/**
	* Returns the XSD date or dateTime value for the given Unix timestamp. 
	* @return string 
	*/
	static public function getUtcXsd($uts = null, $withTime = false, $isUtc = false) {
		// unix timestamp
		if ($uts == null) {
			$uts = time();
		}
		// convert to UTC, if necessary
		if (!$isUtc) {
			$uts = self::getUtcUts($uts);
		}
		// include time
		if ($withTime) {
			return date('Y-m-d\TH:i:s\Z', $uts);
		}
		// just the date
		return date('Y-m-d', $uts);
	}
	
	static public function format($format = 'd/m/Y H:i', $uts = null) {
		$uts = self::toUts($uts);
		return date($format, $uts);
	}

	/**
	* Calculates the duration between $start (as UTS or XSD) and $end (as UTS or XSD)
	* 
	* @param mixed $start
	* @param mixed $end 
	*/
	static public function getDuration($start, $end = null) {
		$start = self::toUts($start);
		$end = ($end == null) ? self::getUtcUts() : self::toUts($end);
		return $end - $start;
	}

	/**
	* Checks whether $value is a potential UTS.
	* 
	* @param mixed $value
	* @return bool 
	*/
	static public function isUts($value) {
		return is_numeric($value);
	}

	/**
	* Checks whether $value is a potential XSD.
	* 
	* @param mixed $value
	* @return bool 
	*/
	static public function isXsd($value) {
		// 2009-07-17T20:15:00+01:00
		return preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}($|T)/', $value);
	}

	/**
	* Converts $value (UTS or XSD) to UTS.
	* @param mixed $value
	* @return int
	*/
	static public function toUts($value) {
		if (self::isUts($value)) {
			return $value;
		}
		if (!self::isXsd($value)) {
			throw new \Exception('Invalid input value "' . $value . '" in DateTimeUtils::toUts');
		}
		// convert the XSD 
		$utcDiff = 0;
		// UTC already
        $m = null;
		if (preg_match('/Z$/', $value, $m)) {
			$value = str_replace('Z', '', $value);
		}
		// no time component, assume local time
		elseif (!strpos($value, 'T')) {
			$utcDiff = - date('Z', time());
		}
		// explicit TZ offset
		elseif (preg_match('/([\+\-])([0-9]{2})\:?([0-9]{2})$/', $value, $m)) {
			// remove the offset
			$value = preg_replace('/([\+\-][0-9\:]+)$/', '', $value);
			// convert the offset to minutes (as an integer)
			$offsetMinutes = (3600 * ltrim($m[2], '0')) + ltrim($m[3], '0');
			$utcDiff = ($m[1] == '-') ? 0 - $offsetMinutes : $offsetMinutes;
		}
		// generate a UTS
		return strtotime(str_replace('T', ' ', $value)) - $utcDiff;
	}

}
