<?php

class Helper {

	public static function getUserClassById($id) {
		return Config::$userClasses[$id];
	}

	public static function searchfield($s) {
		$s = strtolower($s);
		$s = str_ireplace("å", "a", trim($s));
		$s = str_ireplace("'", "", $s);
		$s = str_ireplace("ä", "a", $s);
		$s = str_ireplace("ö", "o", $s);
		$s = str_ireplace("Å", "A", trim($s));
		$s = str_ireplace("Ä", "A", $s);
		$s = str_ireplace("Ö", "O", $s);
		$s = str_ireplace(""," ", $s);
		$s = preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
		return implode(" ", array_unique(explode(" ", $s)));
	}

	public static function searchTextToWordParams($searchText) {
		$search = Helper::searchfield($searchText);
		$arr = explode(" ", $search);
		$searchWords = '';
		foreach($arr as $word) {
			if (strlen($word) > 1) {
				$searchWords .= "+".$word."* ";
			} else if (strlen($word) == 1) {
				$searchWords .= "+".$word." ";
			}
		}
		return $searchWords;
	}

	public static function preCheck($releaseName){
		$res = @file_get_contents('http://predb.org/api/pre/'.$releaseName);
		if ($res && strlen($res) > 2) {
			return strtotime($res);
		}
	}

	public static function getDateWithTimezoneOffset() {
		$this_tz_str = date_default_timezone_get();
		$this_tz = new DateTimeZone($this_tz_str);
		$now = new DateTime("now", $this_tz);
		$offset = $this_tz->getOffset($now);
		return time() + $offset;
	}

	public static function slugify($string) {
		return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
	}
}
