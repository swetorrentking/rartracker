<?php

class Config {

	const NAME = "Rartracker";
	const SITE_NAME = "rartracker.org";
	const SITE_URL = "https://rartracker.org";
	const SITE_MAIL = "no-reply@rartracker.org";

	const SUGGESTION_FORUM_ID = 4;
	const POLLS_FORUM_ID = 3;
	const NEWS_FORUM_ID = 1;

	const DEFAULT_LANGUAGE = "en";
	public static $languages = ["en", "sv"];

	const TRACKER_URL = "http://rartracker.org";
    const TRACKER_URL_SSL = "https://rartracker.org";

	public static $userClasses = array(
		0 => "Extra",
		1 => "Actor",
		2 => "Movie star",
		3 => "Director",
		4 => "Producer",
		6 => "Uploader",
		7 => "VIP",
		8 => "Staff");

}
