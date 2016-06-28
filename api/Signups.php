<?php

class Signups {
	private $db;
	private $user;

	public function __construct($db, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query($limit = 25, $index = 0) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->query("SELECT COUNT(*) FROM nyregg");
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare("SELECT nyregg.ip, nyregg.userid, nyregg.datum AS added, nyregg.email, nyregg.hostname, nyregg.log_mail, nyregg.log_ip, nyregg.level, users.warned, users.enabled, users.username FROM nyregg LEFT JOIN users ON nyregg.userid = users.id ORDER BY nyregg.id DESC LIMIT ?, ?");
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = Array();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$r = array();
			$r["added"] = $row["added"];
			$r["hostname"] = $row["hostname"];
			$r["ip"] = $row["ip"];
			$r["email"] = $row["email"];
			$r["log_ip"] = $row["log_ip"];
			$r["level"] = $row["level"];
			$r["log_mail"] = $row["log_mail"];
			$r["user"] = array(
					"id" => $row["userid"],
					"username" => $row["username"],
					"warned" => $row["warned"],
					"enabled" => $row["enabled"]
				);
			array_push($result, $r);
		}

		return Array($result, $totalCount);
	}

	public function create($userId, $ip, $hostname, $email, $emailLogHits, $ipHits, $warninLevel) {
		$sth = $this->db->prepare("INSERT INTO nyregg (userid, datum, ip, hostname, email, log_mail, log_ip, level) VALUES(?, NOW(), ?, ?, ?, ?, ?, ?)");
		$sth->bindParam(1,	$userId,		PDO::PARAM_INT);
		$sth->bindParam(2,	$ip,			PDO::PARAM_STR);
		$sth->bindParam(3,	$hostname,		PDO::PARAM_STR);
		$sth->bindParam(4,	$email,			PDO::PARAM_STR);
		$sth->bindParam(5,	$emailLogHits,	PDO::PARAM_INT);
		$sth->bindParam(6,	$ipHits,		PDO::PARAM_INT);
		$sth->bindParam(7,	$warninLevel,	PDO::PARAM_INT);
		$sth->execute();
	}
}