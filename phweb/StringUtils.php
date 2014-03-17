<?php

namespace phweb;

class StringUtils {
    
	/**
	* Generates a camelCase string from $value.
	* 
	* @param string $value
	* @return string
	*/
	static public function camelCase($value, $lcfirst = false) {
		$result = ucfirst($value);
        $m = null;
		while (preg_match('/^(.+)[^a-z0-9](.*)$/si', $result, $m)) {
			$result = $m[1] . ucfirst($m[2]);
		}
		if ($lcfirst) {
			$result = lcfirst($result);
		}
		return $result;
	}
	
	/**
	* Generates a dash-separated string from a camelCase $value.
	* 
	* @param string $value
	* @return string
	*/
	static public function deCamelCase($value, $separator = '-') {
		$result = self::camelCase($value, true);
        $m = null;
		while (preg_match('/^([^A-Z]+)([A-Z])(.*)$/', $result, $m)) {
			$result = strtolower($m[1]) . $separator . strtolower($m[2]) . $m[3];
		}
		return strtolower($result);
	}

	/**
	* Genrates a random hex string of length $length with prefix $prefix.
	* 
	* @param int $length
	* @param string $prefix
	*/
	static public function rand($length = 12, $prefix = '') {
		return $prefix . substr(md5(mt_rand()), 0, $length);
	}

	/**
	* Returns the byte-length of $value. 
	* 
	* @param type $value 
	* @return int
	*/
	static public function byteLength($value) {
		// strlen supports multi-byte strings
		if (strlen('ř') == 2) {
			return strlen($value);
		}
		// use mb_* if available and correct
		if (function_exists('mb_strlen') && (mb_strlen('ř') == 2)) {
			return mb_strlen($value);
		}
		// use output buffering
		ob_start();
		ob_implicit_flush(0);
		echo $value;
		$result = ob_get_length();
		ob_end_clean();
		return $result;
	}

	static public function toUtf8($value) {
		$value = strval($value);
		// ascii string
		if (urlencode($value) === $value) {
            return $value;
        }
		// unencode if already encoded
		if (strpos(utf8_decode(str_replace('?', '', $value)), '?') === false) {
			$value = utf8_decode($value);
		}
		// utf-8 tweaks
		$re = '/([\x00-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3}|[\xf8-\xfb][\x80-\xbf]{4}|[\xfc-\xfd][\x80-\xbf]{5}|[^\x00-\x7f])/';
		return preg_replace_callback($re, array("self", "toUtf8Callback"), $value);
	}
    
    static public function toUtf8Callback($match) {
        $char = $match[1];
        if (strlen(trim($char)) === 1) {
            return utf8_encode($char);
        }
        $m = null;
        if (preg_match('/^([\x00-\x7f])(.+)/', $char, $m)) {
            return $m[1] . StringUtils::toUtf8($m[2]);
        }
        return $char;
    }
	
	static public function removeMagicQuotes($value) {
		if (is_array($value)) {
			return array_map(array("self", 'removeMagicQuotes'), $value);
		}
		// utf-8 is supported
		elseif (@preg_match('/\pL/u', 'test')) {
			return preg_replace(array('/\x5C(?!\x5C)/u', '/\x5C\x5C/u'), array('', '\\'), $value);
		}
		// no utf-8, may fail on escaped unicode chars
		return json_decode(stripslashes(json_encode($value, JSON_HEX_APOS)), true);
	}

}

