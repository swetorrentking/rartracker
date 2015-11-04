<?php

set_time_limit(0);
ignore_user_abort(1);

include("api/secrets.php");
include("api/benc.php");

$db = new PDO($database.':host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

$sth = $db->prepare("SELECT filename FROM torrents WHERE id = ?");
$sth->bindParam(1, $id, PDO::PARAM_INT);
$sth->execute();
$torrent = $sth->fetch(PDO::FETCH_ASSOC);

if (!$torrent) {
	echo "Torrent not found";
	exit;
}

$sth = $db->prepare("SELECT https FROM users WHERE passkey = ? AND enabled = 'yes'");
$sth->bindParam(1, $passkey, PDO::PARAM_STR);
$sth->execute();
$user = $sth->fetch(PDO::FETCH_ASSOC);

if (!$user) {
	echo "User not found";
	exit;
}

$dict = bdec_file($filePath, filesize($filePath));
$dict['value']['announce']['value'] = "http".($user["https"] == 1 ? "s" : "")."://rarat.org:133".($user["https"] == 1 ? "8" : "7")."/tracker.php/{$passkey}/announce";
$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);

$dict["value"]["comment"]["type"] = "string";
$dict["value"]["comment"]["value"] = "rarat.org";
$dict["value"]["comment"]["strlen"] = strlen(strlen("rarat.org") . ":rarat.org");
$dict["value"]["comment"]["string"] = strlen("rarat.org") . ":rarat.org";

unset($dict['value']['announce-list']);
header('Content-Disposition: attachment;filename="'.$torrent['filename'].'"');
header("Content-Type: application/x-bittorrent");

print(benc($dict));

?>