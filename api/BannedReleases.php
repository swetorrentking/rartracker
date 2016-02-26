<?php

class Invite implements IResource {
	private $db;
	private $user;

	public function __construct($db = null, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query($postdata = null) {
		$sth = $this->db->query('SELECT * FROM invites WHERE userid = ' . $this->user->getId());
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function create($postdata = null) {
		if ($this->user->getClass() < User::CLASS_SKADIS) {
			throw new Exception('Du måste vara minst Skådis för att kunna bjuda in.', 401);
		}

		if ($this->user->getInvites() == 0) {
			throw new Exception('Du har inga invites.');
		}

		$this->db->query("UPDATE users SET invites = invites - 1 WHERE id = " . $this->user->getId());
		$sth = $this->db->prepare("INSERT INTO invites(userid, secret, skapad) VALUES(?, ?, NOW())");
		$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindValue(2, md5(uniqid()), PDO::PARAM_STR);
		$sth->execute();
	}

	public function delete($id, $postdata = null) {
		$invite = $this->get($id);
		if ($invite["userid"] != $this->user->getId()) {
			throw new Exception('Du saknar rättigheter att radera denna inviten.');
		}
		$this->db->query('DELETE FROM invites WHERE id = ' . $invite["id"]);
		$this->db->query("UPDATE users SET invites = invites + 1 WHERE id = " . $this->user->getId());
	}

	public function get($id) {
		$sth = $this->db->prepare('SELECT * FROM invites WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception('Inviten finns inte.');
		}
		return $res;
	}

	public function update($id, $postdata) {}
}
