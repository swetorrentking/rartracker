<?php

/* Run from root folder */

require('api/secrets.php');
require('api/Helper.php');

$db = new PDO($database.':host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


/* 0.2.0 - Create slug urls on existing topics and requests */
$res = $db->query("SELECT id, subject FROM topics");
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
	$db->query("UPDATE topics SET slug = " . $db->quote(Helper::slugify($row['subject'])) . " WHERE id = " . $row["id"]);
}
$res = $db->query("SELECT id, request FROM requests");
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
	$db->query("UPDATE requests SET slug = " . $db->quote(Helper::slugify($row['request'])) . " WHERE id = " . $row["id"]);
}
