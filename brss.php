<?php

include('api/secrets.php');

mysql_connect($host, $username, $password);
mysql_select_db($dbname);

function mksize($bytes) {
	if ($bytes < 1000 * 1024)
		return number_format($bytes / 1024, 2) . " KiB";
	elseif ($bytes < 1000 * 1048576)
		return number_format($bytes / 1048576, 2) . " MiB";
	elseif ($bytes < 1000 * 1073741824)
		return number_format($bytes / 1073741824, 2) . " GiB";
	else
		return number_format($bytes / 1099511627776, 3) . " TiB";
}

$passkey = $_GET["passkey"];
if (!preg_match("/^[a-z0-9]{32}$/", $passkey)) {
	echo "Invalid passkey";
	exit;
}

$user = mysql_query("SELECT id FROM users WHERE passkey = '$passkey'");
if (mysql_num_rows($user) == 0) {
	echo "user not found";
	exit();
} else {
	$user = mysql_fetch_array($user);
	$userid = $user[0];
}

$type = $_GET["vad"];
$from = 0 + $_GET["from"];

$SITENAME = "Rartracker";
$DESCR = "Bevakning RSS Feed";
$BASEURL = "https://rartracker.org";

$where = '';
if ($type == 1) {
	$where .= ' AND bevaka.typ = 0';
	$SITENAME .= " Film";
} else if ($type == 2) {
	$where .= ' AND bevaka.typ = 1';
	$SITENAME .= " TV";
}

if ($from > 0) {
	$where .= ' AND torrents.added > FROM_UNIXTIME(' . $from . ')';
}

header("Content-Type: application/xml");
print("<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<rss version=\"0.91\">\n<channel>\n" .
"<title>" . $SITENAME . "</title>\n<link>" . $BASEURL . "</link>\n<description>" . $DESCR . "</description>\n" .
"<language>en-usde</language>\n<copyright> Copyright " . $SITENAME . "</copyright>\n<webMaster>noreply@rartracker.org</webMaster>\n" .
"<image><title>" . $SITENAME . "</title>\n<url>" . $BASEURL . "/favicon.ico</url>\n<link>" . $BASEURL . "</link>\n" .
"<width>16</width>\n<height>16</height>\n<description>" . $DESCR . "</description>\n</image>\n");

$res = mysql_query("SELECT torrents.id, torrents.name, torrents.size, torrents.seeders, torrents.leechers, torrents.added FROM bevaka JOIN torrents on bevaka.imdbid = torrents.imdbid WHERE (((torrents.category IN(4,5,6,7)) AND bevaka.swesub = 1 AND torrents.swesub = 1) OR ((torrents.category IN(4,5,6,7)) AND bevaka.swesub = 0) OR (torrents.category NOT IN (4,5,6,7))) AND FIND_IN_SET(torrents.category, bevaka.format) AND (category = 2 AND torrents.p2p = 1 OR category <> 2 AND torrents.p2p = 0) AND torrents.pack = 0 AND torrents.3d = 0 AND bevaka.userid = " . $userid . $where . " ORDER BY torrents.id DESC LIMIT 25") or sqlerr(__FILE__, __LINE__);

while ($row = mysql_fetch_row($res)){

	list($id,$name,$size,$seeders,$leechers,$added) = $row;

	$link = "https://rartracker.org/download.php?id=$id&amp;passkey=$passkey";

	echo("<item><title>" . htmlspecialchars($name) . "</title>\n<link>" . $link . "</link>\n<description>\nSize: " . mksize($size) ."</description>\n<pubDate>".$added."</pubDate></item> \n");
}

echo("</channel>\n</rss>\n");

?>