<?php

class LoginAttempts implements IResource {
	private $db;
	private $user;
	private $maximumLoginAttempts = 10;

	public function __construct($db, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query($postdata) {
		$limit = (int)$postdata["limit"] ?: 25;
		$index = (int)$postdata["index"] ?: 0;

		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$where = array();
		$finalWhere = "";

		if ($postdata["ip"]) {
			$where[] = "inlogg.ip LIKE '".$postdata["ip"]."%'";
		}

		if ($postdata["username"]) {
			$where[] = "inlogg.namn LIKE '".$postdata["username"]."%'";
		}

		if (count($where) > 0) {
			$finalWhere = " WHERE " . implode(" AND ", $where);
		}

		$sth = $this->db->query("SELECT COUNT(*) FROM inlogg " . $finalWhere);
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare("SELECT inlogg.id, inlogg.namn, inlogg.tid AS added, inlogg.uid, inlogg.ip, inlogg.password, users.warned, users.enabled, users.username FROM inlogg LEFT JOIN users ON inlogg.uid = users.id ".$finalWhere." ORDER BY inlogg.id DESC LIMIT ?, ?");
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = Array();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$r = array();
			$r["id"] = $row["id"];
			$r["added"] = $row["added"];
			$r["password"] = $row["password"];
			$r["name"] = $row["namn"];
			$r["ip"] = $row["ip"];
			$r["user"] = array(
					"id" => $row["uid"],
					"username" => $row["username"],
					"warned" => $row["warned"],
					"enabled" => $row["enabled"]
				);
			array_push($result, $r);
		}

		return Array($result, $totalCount);
	}

	public function delete($id, $postdata = null) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->prepare("DELETE FROM inlogg WHERE id = ?");
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
	}

	public function create($postdata) {
		$username = $postdata["username"] ?: '';
		$password = $postdata["password"] ?: '';
		$uid = $postdata["uid"] ?: 0;

		$ip = $_SERVER["REMOTE_ADDR"];
		$now = date("Y-m-d H:i:s", time());
		$sth = $this->db->prepare('INSERT INTO inlogg (tid, namn, password, ip, uid) VALUES (?, ?, ?, ?, ?)');
		$sth->bindParam(1, $now,		PDO::PARAM_STR, 25);
		$sth->bindParam(2, $username,	PDO::PARAM_STR, 200);
		$sth->bindParam(3, $password,	PDO::PARAM_STR, 15);
		$sth->bindParam(4, $ip,			PDO::PARAM_STR, 15);
		$sth->bindParam(5, $uid,		PDO::PARAM_INT);
		$sth->execute();
	}

	public function check() {
		$date = date("Y-m-d H:i:s", time() - 300);
		$sth = $this->db->prepare('SELECT COUNT(*) FROM inlogg WHERE ip = ? AND tid > ?');
		$sth->bindParam(1, $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR, 15);
		$sth->bindParam(2, $date,					PDO::PARAM_STR);
		$sth->execute();
		$arr = $sth->fetch();
		if ($arr[0] > $this->maximumLoginAttempts) {
			throw new Exception(L::get("LOGIN_ATTEMPTS_EXCEEDED"), 401);
		}
	}

	public function get($id) {}
	public function update($id, $postdata) {}
}
