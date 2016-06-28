<?php

class IpChanges {
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

		$sth = $this->db->query("SELECT COUNT(*) FROM ipchanges");
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare("SELECT ipchanges.ip, ipchanges.userid, ipchanges.datum AS added, ipchanges.hostname, ipchanges.level, users.warned, users.enabled, users.username FROM ipchanges LEFT JOIN users ON ipchanges.userid = users.id ORDER BY ipchanges.id DESC LIMIT ?, ?");
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = Array();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$r = array();
			$r["added"] = $row["added"];
			$r["hostname"] = $row["hostname"];
			$r["ip"] = $row["ip"];
			$r["level"] = $row["level"];
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
		$sth = $this->db->prepare("INSERT INTO ipchanges (userid, datum, ip, hostname, email, log_mail, log_ip, level) VALUES(?, NOW(), ?, ?, ?, ?, ?, ?)");
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