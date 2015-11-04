<?php

include('api/secrets.php');

$db = new PDO($database.':host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$subid = 0 + $_GET["id"];

if ($subid == 0 && $_GET["torrentid"]) {
	$tid = 0 + $_GET["torrentid"];
	$sth = $db->prepare("SELECT * FROM subs WHERE torrentid = ? LIMIT 1");
	$sth->bindParam(1, $tid, PDO::PARAM_INT);
	$sth->execute();
} else {
	$sth = $db->prepare("SELECT * FROM subs WHERE id = ?");
	$sth->bindParam(1, $subid, PDO::PARAM_INT);
	$sth->execute();
}

$row = $sth->fetch(PDO::FETCH_ASSOC);

if ($sth->rowCount() != 1 || !file_exists("subs/" . $row["filnamn"])) {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	header("Status: 404 Not Found");
	die("Det finns ingen separat undertextfil till denna releasen.");
	exit;
}

header('Content-Disposition: attachment; filename="'.$row["filnamn"].'"');

echo file_get_contents("subs/" . $row["filnamn"]);