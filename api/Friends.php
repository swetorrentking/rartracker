<?php

class Friends implements IResource {
	private $db;
	private $user;

	public function __construct($db = null, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query($postdata = null) {
		$sth = $this->db->query('SELECT friends.id AS friends_id, friendid, kom, '.implode(',', User::getDefaultFields()).' FROM friends LEFT JOIN users ON friends.friendid = users.id WHERE friends.userid = ' . $this->user->getId() . ' ORDER BY users.username ASC');
		
		$result = array();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$friend["user"] = $this->user->generateUserObject($row);
			$friend["id"] = $row["friends_id"];
			$friend["comment"] = $row["kom"];
			$friend["user"]["last_access"] = $row["last_access"];
			array_push($result, $friend);
		}
		return $result;
	}

	public function create($postdata) {
		$myFriend = $this->user->get($postdata["friendid"]);
		if (!$myFriend) {
			throw new Exception('Användaren finns inte.');
		}

		$sth = $this->db->prepare('SELECT 1 FROM friends WHERE userid = ? AND friendid = ?');
		$sth->bindParam(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $myFriend["id"], PDO::PARAM_INT);
		$sth->execute();
		if ($sth->fetch()) {
			throw new Exception('Användaren är redan din vän.');
		}

		$sth = $this->db->prepare("INSERT INTO friends(userid, friendid, kom) VALUES(?, ?, ?)");
		$sth->bindParam(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $myFriend["id"], PDO::PARAM_INT);
		$sth->bindParam(3, $postdata["comment"], PDO::PARAM_STR);
		$sth->execute();
	}

	public function delete($id, $postdata = null) {
		$friend = $this->get($id);
		if ($friend["userid"] != $this->user->getId()) {
			throw new Exception('Du saknar rättigheter att radera denna vän.');
		}
		$this->db->query('DELETE FROM friends WHERE id = ' . $friend["id"]);
	}

	public function get($id) {
		$sth = $this->db->prepare('SELECT * FROM friends WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception('Vännen finns inte.');
		}
		return $res;
	}

	public function update($id, $postdata) {}
}