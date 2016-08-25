<?php

header('Content-Type: application/json');

include('api/secrets.php');
include('api/Config.php');
include('api/Helper.php');
include('api/Torrent.php');

try {
	$db = new PDO($database.':host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	error($e->getMessage());
}

$passkey = $_GET["passkey"];
$imdbid = $_GET["imdbid"];
$search = $_GET["search"];

$categories = [
	Config::$categories["DVDR_PAL"]["id"],
	Config::$categories["DVDR_CUSTOM"]["id"],
	Config::$categories["DVDR_TV"]["id"],
	Config::$categories["MOVIE_720P"]["id"],
	Config::$categories["MOVIE_1080P"]["id"],
	Config::$categories["TV_720P"]["id"],
	Config::$categories["TV_1080P"]["id"],
	Config::$categories["BLURAY"]["id"],
	Config::$categories["MOVIE_4K"]["id"]
];

if (!preg_match("/^[a-z0-9]{32}$/", $passkey) || (empty($imdbid) && empty($search))) {
    error("Incorrect parameters.");
}

/* Just search for imdbid */
if (empty($search)) {
	$search = $imdbid;
}

$sth = $db->prepare("SELECT id FROM users WHERE passkey = ? AND enabled = 'yes'");
$sth->bindParam(1, $passkey);
$sth->execute();
if (!$sth->fetch()) {
    error("Permission denied.");
}

$torrents = new Torrent($db);

list($result, $total) = $torrents->search(array("searchText" => $search, "categories" => $categories));

$torr = array();
foreach($result as &$res) {
	$torrent = array(
		"release_name" => $res["name"],
		"torrent_id" => $res["id"],
		"details_url" => Config::SITE_URL . "/torrent/" . $res["id"] . "/" . $res["name"],
		"download_url" => Config::SITE_URL . "/api/v1/torrents/download/". $res["id"]. "/ " . $passkey,
		"imdb_id" => $res["imdbid2"],
		"freeleech" => (bool)$res["frileech"],
		"type" => typeByCategory($res["category"]),
		"size" => bitsToMb($res["size"]),
		"leechers" => $res["leechers"],
		"seeders" => $res["seeders"]
	);
	array_push($torr, $torrent);
}

$response = array(
	"results" => $torr,
	"total_results" => $total
);

echo json_encode($response, JSON_NUMERIC_CHECK);

function bitsToMb($bits) {
	return round($bits/1024/1024);
}

function typeByCategory($category) {
	switch($category) {
		case Config::$categories["DVDR_PAL"]["id"]:
		case Config::$categories["DVDR_CUSTOM"]["id"]:
		case Config::$categories["MOVIE_720P"]["id"]:
		case Config::$categories["MOVIE_1080P"]["id"]:
		case Config::$categories["BLURAY"]["id"]:
		case Config::$categories["MOVIE_4K"]["id"]:
			return "movie";
		default:
			return "show";
	}
}

function error($err) {
	echo json_encode(Array("error" => $err));
	die();
}
