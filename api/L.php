<?php

class L {

	private static $fallbackLanguage = "en";
	private static $language;
	private static $data;

	public static function setLanguage($language) {
		if (self::$language === $language) {
			return;
		}
		if (!in_array($language, Config::$languages)) {
			$language = self::$fallbackLanguage;
		}
		self::$language = $language;
		self::$data = json_decode(file_get_contents("locales/" . $language . ".json"), true);
	}

	public static function get($string, $variables = []) {
		$output = self::$data[$string];
		if (!$output) {
			return $string;
		}
		for($i = 0; $i < count($variables); $i++) {
			$output = str_replace("{".$i."}", $variables[$i], $output);
		}
		return $output;
	}

}
