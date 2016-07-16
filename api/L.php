<?php

class L {
	private static $defaultLanguage;
	private static $cache = [];

	public static function setDefaultLanguage($language) {
		self::$defaultLanguage = $language;
	}

	public static function get($string, $variables = [], $language = null) {
		if (!$language || ($language && !in_array($language, Config::$languages))) {
			$language = self::$defaultLanguage;
		}
		if (!self::$cache[$language]) {
			self::$cache[$language] = json_decode(file_get_contents("locales/" . $language . ".json"), true);
		}
		$sentence = self::$cache[$language][$string];
		if (!$sentence) {
			return $string;
		}
		for($i = 0; $i < count($variables); $i++) {
			$sentence = str_replace("{".$i."}", $variables[$i], $sentence);
		}
		return $sentence;
	}

}
