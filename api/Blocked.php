<?php

class Blocked implements IResource {
	private $db;
	private $user;

	public function __construct($db = null, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query($postdata = null) {
		$sth = $this->db->query('SELECT blocks.id AS block_id, blocks.blockid, blocks.comment, '.implode(',', User::getDefaultFields()).' FROM blocks LEFT JOIN users ON blocks.blockid = users.id WHERE userid = ' . $this->user->getId());
		$result = array();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$friend["user"] = $this->user->generateUserObject($row);
			$friend["id"] = $row["friends_id"];
			$friend["comment"] = $row["kom"];
			$friend["last_access"] = $row["last_access"];
			array_push($result, $friend);
		}
		return $result;
	}

	public function create($postdata) {
		$myEnemy = $this->user->get($postdata["blockid"]);
		if (!$myEnemy) {
			throw new Exception('Användaren finns inte.');
		}

		$sth = $this->db->prepare('SELECT 1 FROM blocks WHERE userid = ? AND blockid = ?');
		$sth->bindParam(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $myEnemy["id"], PDO::PARAM_INT);
		$sth->execute();
		if ($sth->fetch()) {
			throw new Exception('Användaren är redan blockerad.');
		}

		$sth = $this->db->prepare("INSERT INTO blocks(userid, blockid, comment) VALUES(?, ?, ?)");
		$sth->bindParam(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $myEnemy["id"], PDO::PARAM_INT);
		$sth->bindParam(3, $postdata["comment"], PDO::PARAM_STR);
		$sth->execute();
	}

	public function delete($id, $postdata) {
		$block = $this->get($id);
		if ($block["userid"] != $this->user->getId()) {
			throw new Exception('Du saknar rättigheter att radera denna blockering.');
		}
		$this->db->query('DELETE FROM blocks WHERE id = ' . $block["id"]);
	}

	public function get($id) {
		$sth = $this->db->prepare('SELECT * FROM blocks WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception('Blockeringen finns inte.');
		}
		return $res;
	}

	public function update($id, $postdata) {}
}