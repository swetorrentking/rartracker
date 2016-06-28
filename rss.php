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
	echo "User not found.";
	exit();
}

$s = $_GET["s"];

if (!$s) {
	$s = $_GET["vad"];
}

$category = array();
$category[1] = "DVDR PAL";
$category[2] = "DVDR CUSTOM";
$category[3] = "DVDR TV";
$category[4] = "720p Movie";
$category[5] = "1080p Movie";
$category[6] = "720p TV";
$category[7] = "1080p TV";
$category[8] = "Swedish TV";
$category[9] = "Audiobook";
$category[10] = "E-book";
$category[11] = "E-paper";
$category[12] = "Music";
$category[13] = "Full BluRay";
$category[14] = "Subpack";
$category[15] = "4K Movie";

$cats = $_GET["cat"];

if ($cats) {
	if (!preg_match("/^[0-9,]+$/", $cats)) {
		echo "Invalid categories";
		exit;
	}
	$cats = explode(",", $cats);
}

$where = array();
$finalWhere = "";

if ($cats) {
	$where[] = "category IN (".implode(", ", $cats).")";
}

if ($s == 1) {
	$where[] = "section = 'new'";
} else if($s == 2)
	$where[] = "section = 'archive'";
else if ($s == 3) {
	$bookmark = true;
}

if ($_GET['p2p'] != "1") {
	$where[] = 'p2p = 0';
}

if (count($where) > 0) {
	$finalWhere = "WHERE " . implode(" AND ", $where);
}

$SITENAME = Config::NAME;
$DESCR = "RSS Feeds";
$BASEURL = Config::SITE_URL;
$SITEMAIL = Config::SITE_MAIL;

header("Content-Type: application/xml");
print("<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<rss version=\"0.91\">\n<channel>\n" .
"<title>" . $SITENAME . "</title>\n<link>" . $BASEURL . "</link>\n<description>" . $DESCR . "</description>\n" .
"<language>en-usde</language>\n<copyright> Copyright " . $SITENAME . "</copyright>\n<webMaster>".$SITEMAIL."</webMaster>\n" .
"<image><title>" . $SITENAME . "</title>\n<url>" . $BASEURL . "/favicon.ico</url>\n<link>" . $BASEURL . "</link>\n" .
"<width>16</width>\n<height>16</height>\n<description>" . $DESCR . "</description>\n</image>\n");

if ($bookmark) {
	$res = $db->query("SELECT torrents.id, name, descr, filename, size, category, seeders, leechers, added FROM bookmarks LEFT JOIN torrents ON bookmarks.torrentid = torrents.id WHERE bookmarks.userid = ".$user[0]." ORDER BY bookmarks.id DESC LIMIT 15");
} else {
	$res = $db->query("SELECT id,name,descr,filename,size,category,seeders,leechers,added FROM torrents $finalWhere ORDER BY added DESC LIMIT 15");
}

while ($row = $res->fetch()){
	list($id, $name, $descr, $filename, $size, $cat, $seeders, $leechers, $added, $catname) = $row;

	$link = $BASEURL . "/download.php?id=$id&amp;passkey=$passkey";

	echo("<item><title>" . htmlspecialchars($name) . "</title>\n<link>" . $link . "</link>\n<description>Kategori: " . $category[$cat] . " \n Storlek: " . mksize($size) . "\n " . htmlspecialchars($descr) . "\n</description>\n<pubDate>".$added."</pubDate></item> \n");
}

echo("</channel>\n</rss>\n");
?>
