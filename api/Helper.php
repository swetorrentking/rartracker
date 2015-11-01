<?php

class Helper {

	public static $name = "Rartracker";
	public static $siteName = "rartracker.org";
	public static $siteUrl = "http://127.0.0.1";
	public static $siteMail = "no-reply@rartracker.org";

	public static $trackerUrl = "http://127.0.0.1:1337";
	public static $trackerUrlSsl = "https://127.0.0.1:1338";

	public static $userClasses = array(
		0 => "Statist",
		1 => "Skådis",
		2 => "Filmstjärna",
		3 => "Regissör",
		4 => "Producent",
		6 => "Uploader",
		7 => "VIP",
		8 => "Staff");

	public static function getUserClassById($id) {
		return self::$userClasses[$id];
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
		$s = str_ireplace("  "," ", $s);
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
		$daylightSavingTime = false;

		$timezone = 7200;
		if ($daylightSavingTime) {
			$timezone = 3600;
		}

		$res = self::disguise_curl('https://pre.corrupt-net.org/search.php?search='.$releaseName.'&ts=1384198854017&pretimezone=1&timezone='.$timezone);
		preg_match('/<\/font>">&nbsp;&nbsp;(.+?)<\/td>/si', $res, $match);
		return strtotime($match[1]);
	}

	public static function getDateWithTimezoneOffset() {
		$this_tz_str = date_default_timezone_get();
		$this_tz = new DateTimeZone($this_tz_str);
		$now = new DateTime("now", $this_tz);
		$offset = $this_tz->getOffset($now);
		return time() + $offset;
	}

	private static function disguise_curl($url) {
		$curl = curl_init(); 

		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,"; 
		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5"; 
		$header[] = "Cache-Control: max-age=0"; 
		$header[] = "Connection: keep-alive"; 
		$header[] = "Keep-Alive: 300"; 
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7"; 
		$header[] = "Accept-Language: en-us,en;q=0.5"; 
		$header[] = "Pragma: "; //browsers keep this blank. 

		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3'); 
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($curl, CURLOPT_REFERER, 'http://www.google.com'); 
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate'); 
		curl_setopt($curl, CURLOPT_AUTOREFERER, true); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_TIMEOUT, 4); 

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

		$html = curl_exec($curl);
		curl_close($curl);

		return $html;
	}
}