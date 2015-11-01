<?php

include('api/secrets.php');

$db = new PDO($database.':host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$subid = 0 + $_GET["id"];

if ($subid == 0 && $_GET["torrentid"]) {
	$tid = 0 + $_GET["torrentid"];
	$res = $db->query('SELECT * FROM subs WHERE torrentid = ' . $tid . ' LIMIT 1');
} else {
	$res = $db->query('SELECT * FROM subs WHERE id = '. $subid);
}

$row = $res->fetch(PDO::FETCH_ASSOC);

if ($res->rowCount() != 1 || !file_exists("subs/" . $row["filnamn"])) {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	header("Status: 404 Not Found");
	die("Det finns ingen separat undertextfil till denna releasen.");
	exit;
}

header('Content-Disposition: attachment; filename="'.$row["filnamn"].'"');

echo file_get_contents("subs/" . $row["filnamn"]);