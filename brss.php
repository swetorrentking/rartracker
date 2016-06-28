<?php

include('api/secrets.php');
include('api/Config.php');

$db = new PDO($database.':host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

$sth = $db->prepare("SELECT id FROM users WHERE passkey = ?");
$sth->bindParam(1, $passkey, PDO::PARAM_STR);
$sth->execute();
$user = $sth->fetch();

if (!$user) {
	echo "user not found";
	exit();
}

$type = $_GET["vad"];
$from = 0 + $_GET["from"];

$SITENAME = Config::NAME;
$DESCR = "Watch RSS Feed";
$BASEURL = Config::SITE_URL;
$SITEMAIL = Config::SITE_MAIL;

$where = '';
if ($type == 1) {
	$where .= ' AND bevaka.typ = 0';
	$SITENAME .= " Movies";
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
"<language>en-usde</language>\n<copyright> Copyright " . $SITENAME . "</copyright>\n<webMaster>".$SITEMAIL."</webMaster>\n" .
"<image><title>" . $SITENAME . "</title>\n<url>" . $BASEURL . "/favicon.ico</url>\n<link>" . $BASEURL . "</link>\n" .
"<width>16</width>\n<height>16</height>\n<description>" . $DESCR . "</description>\n</image>\n");

$res = $db->query("SELECT torrents.id, torrents.name, torrents.size, torrents.seeders, torrents.leechers, torrents.added FROM bevaka JOIN torrents on bevaka.imdbid = torrents.imdbid WHERE (((torrents.category IN(4,5,6,7)) AND bevaka.swesub = 1 AND torrents.swesub = 1) OR ((torrents.category IN(4,5,6,7)) AND bevaka.swesub = 0) OR (torrents.category NOT IN (4,5,6,7))) AND FIND_IN_SET(torrents.category, bevaka.format) AND (category = 2 AND torrents.p2p = 1 OR category <> 2 AND torrents.p2p = 0) AND torrents.pack = 0 AND torrents.3d = 0 AND bevaka.userid = " . $user[0] . $where . " ORDER BY torrents.id DESC LIMIT 25") or sqlerr(__FILE__, __LINE__);

while ($row = $res->fetch()){

	list($id,$name,$size,$seeders,$leechers,$added) = $row;

	$link = $BASEURL . "/download.php?id=$id&amp;passkey=$passkey";

	echo("<item><title>" . htmlspecialchars($name) . "</title>\n<link>" . $link . "</link>\n<description>\nSize: " . mksize($size) ."</description>\n<pubDate>".$added."</pubDate></item> \n");
}

echo("</channel>\n</rss>\n");

?>
