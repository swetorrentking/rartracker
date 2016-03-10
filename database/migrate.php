<?php

/* Run from root folder */

set_time_limit(0);
ignore_user_abort(1);

require('api/secrets.php');
require('api/Helper.php');
require('api/User.php');

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

/* 0.2.1 - Hash all email-addresses */
$user = new User($db);
$res = $db->query("SELECT id, email FROM users");
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
	$email = $user->hashEmail($row["email"]);
	$db->query("UPDATE users SET email = " . $db->quote($email) . " WHERE id = " . $row["id"]);
}
$res = $db->query("SELECT id, email FROM emaillog");
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
	$email = $user->hashEmail($row["email"]);
	$db->query("UPDATE emaillog SET email = " . $db->quote($email) . " WHERE id = " . $row["id"]);
}
$res = $db->query("SELECT id, email FROM nyregg");
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
	$email = $user->hashEmail($row["email"]);
	$db->query("UPDATE nyregg SET email = " . $db->quote($email) . " WHERE id = " . $row["id"]);
}
$res = $db->query("SELECT id, email FROM recoverlog");
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
	$email = $user->hashEmail($row["email"]);
	$db->query("UPDATE recoverlog SET email = " . $db->quote($email) . " WHERE id = " . $row["id"]);
}
