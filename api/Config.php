<?php

class Config {

	const NAME = "Rartracker";
	const SITE_NAME = "rartracker.org";
	const SITE_URL = "https://rartracker.org";
	const SITE_MAIL = "no-reply@rartracker.org";

	const TRACKER_URL = "http://rartracker.org";
	const TRACKER_URL_SSL = "https://rartracker.org";

	public static $userClasses = array(
		0 => "Statist",
		1 => "Skådis",
		2 => "Filmstjärna",
		3 => "Regissör",
		4 => "Producent",
		6 => "Uploader",
		7 => "VIP",
		8 => "Staff");

}
