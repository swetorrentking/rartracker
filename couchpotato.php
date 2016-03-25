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
	Torrent::DVDR_PAL,
	Torrent::DVDR_CUSTOM,
	Torrent::DVDR_TV,
	Torrent::MOVIE_720P,
	Torrent::MOVIE_1080P,
	Torrent::TV_720P,
	Torrent::TV_1080P,
	Torrent::BLURAY,
	Torrent::MOVIE_4K
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
		"download_url" => Config::SITE_URL . "/download.php?id=" . $res["id"] . "&passkey=" . $passkey,
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
		case Torrent::DVDR_PAL:
		case Torrent::DVDR_CUSTOM:
		case Torrent::MOVIE_720P:
		case Torrent::MOVIE_1080P:
		case Torrent::BLURAY:
		case Torrent::MOVIE_4K:
			return "movie";
		default:
			return "show";
	}
}

function error($err) {
	echo json_encode(Array("error" => $err));
	die();
}
