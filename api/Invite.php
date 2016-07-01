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

		if ($this->user->isInviteBanned()) {
			throw new Exception(L::get("INVITE_BANNED"), 401);
		}

		if ($this->user->getClass() < User::CLASS_ACTOR) {
			throw new Exception(L::get("INVITE_MINIMUM_CLASS_ERROR"), 401);
		}

		if ($this->user->getInvites() == 0) {
			throw new Exception(L::get("INVITES_DEPLETED"), 400);
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
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
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
			throw new Exception(L::get("INVITE_NOT_FOUND"), 404);
		}
		return $res;
	}

	public function checkValidity($secret) {
		$sth = $this->db->prepare('SELECT * FROM invites WHERE secret = ?');
		$sth->bindParam(1, $secret, PDO::PARAM_STR);
		$sth->execute();
		if (!$sth->fetch()) {
			throw new Exception(L::get("INVITE_NOT_FOUND"), 404);
		}
	}

	public function update($id, $postdata) {}
}
