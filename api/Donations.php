<?php

class Donations implements IResource {
	private $db;
	private $user;

	public function __construct($db = null, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query($postdata = null) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$limit = (int)$postdata["limit"] ?: 10;
		$index = (int)$postdata["index"] ?: 0;

		$sth = $this->db->query("SELECT COUNT(*) FROM donated");
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare('SELECT * FROM donated ORDER BY id DESC LIMIT ?, ?');
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$res = array();
			$res["id"] = $row["id"];
			$res["added"] = $row["date"];
			$res["msg"] = $row["msg"];
			$res["status"] = $row["status"];
			$res["nostar"] = $row["nostar"];
			$res["sum"] = $row["sum"];
			$res["type"] = $row["typ"];
			$res["code"] = $row["kod"];
			$res["user"] = array(
				"id" => $row["userid"],
				"username" => $row["username"]);
			array_push($result, $res);
		}

		return Array($result, $totalCount);
	}

	public function create($postdata) {
		$sth = $this->db->prepare("INSERT INTO donated (date, username, msg, userid, nostar, sum, typ, kod) VALUES(NOW(), ?, ?, ?, ?, ?, ?, ?)");

		$goldstar = ($postdata["goldstar"] ? 0 : 1);

		$sth->bindValue(1, $this->user->getUsername(),		PDO::PARAM_STR);
		$sth->bindParam(2, $postdata["comment"],			PDO::PARAM_STR);
		$sth->bindValue(3, $this->user->getId(),			PDO::PARAM_INT);
		$sth->bindParam(4, $goldstar,						PDO::PARAM_INT);
		$sth->bindParam(5, $postdata["sum"],				PDO::PARAM_STR);
		$sth->bindParam(6, $postdata["type"],				PDO::PARAM_INT);
		$sth->bindParam(7, $postdata["pin"],				PDO::PARAM_STR);
		$sth->execute();
	}

	public function delete($id, $postdata = null) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->prepare("DELETE FROM donated WHERE id = ?");
		$sth->bindParam(1, $id,	PDO::PARAM_INT);
		$sth->execute();
	}

	public function update($id, $postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->prepare("UPDATE donated SET status = ?, sum = ? WHERE id = ?");
		$sth->bindParam(1, $postdata["status"],		PDO::PARAM_INT);
		$sth->bindParam(2, $postdata["sum"],		PDO::PARAM_INT);
		$sth->bindParam(3, $id,						PDO::PARAM_INT);
		$sth->execute();
	}
	public function get($id) {}
}
