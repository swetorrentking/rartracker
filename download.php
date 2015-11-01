<?php

set_time_limit(0);
ignore_user_abort(1);

include("api/secrets.php");
include("api/benc.php");

mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$passkey = $_GET["passkey"];
$id = $_GET["id"];

if (!preg_match("/^[0-9]+$/", $id)) {
	echo "Invalid torrent id";
	exit;
}

if (!preg_match("/^[a-z0-9]{32}$/", $passkey)) {
	echo "Invalid passkey";
	exit;
}

$filePath = "torrents/".$id.".torrent";

$res = mysql_query("SELECT filename FROM torrents WHERE id = $id") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) !== 1) {
	echo "Torrent not found";
	exit;
}

$torrent = mysql_fetch_assoc($res);

$res = mysql_query("SELECT https FROM users WHERE passkey = '$passkey' AND enabled = 'yes'");
if (mysql_num_rows($res) !== 1) {
	echo "User not found";
	exit;
}
$user = mysql_fetch_assoc($res);

$dict = bdec_file($filePath, filesize($filePath));
$dict['value']['announce']['value'] = "http".($user["https"] == 1 ? "s" : "")."://rartracker.org:133".($user["https"] == 1 ? "8" : "7")."/tracker.php/{$passkey}/announce";
$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);

$dict["value"]["comment"]["type"] = "string";
$dict["value"]["comment"]["value"] = "rartracker.org";
$dict["value"]["comment"]["strlen"] = strlen(strlen("rartracker.org") . ":rartracker.org");
$dict["value"]["comment"]["string"] = strlen("rartracker.org") . ":rartracker.org";

unset($dict['value']['announce-list']);
header('Content-Disposition: attachment;filename="'.$torrent['filename'].'"');
header("Content-Type: application/x-bittorrent");

print(benc($dict));

?>