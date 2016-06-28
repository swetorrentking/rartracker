<?php

class Rules {
	private $db;
	private $user;

	public function __construct($db, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query() {
	 	$sth = $this->db->query('SELECT * FROM rules ORDER BY id ASC');
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function create($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

	 	$sth = $this->db->prepare("INSERT INTO rules(title, text) VALUES(?, ?)");
		$sth->bindParam(1, $postdata["title"],		PDO::PARAM_STR);
		$sth->bindParam(2, $postdata["text"],		PDO::PARAM_STR);
		$sth->execute();
	}

	public function update($id, $postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
	 	$sth = $this->db->prepare("UPDATE rules SET title = ?, text = ? WHERE id = ?");
		$sth->bindParam(1, $postdata["title"],		PDO::PARAM_STR);
		$sth->bindParam(2, $postdata["text"],		PDO::PARAM_STR);
		$sth->bindParam(3, $id,						PDO::PARAM_INT);
		$sth->execute();
	}

	public function delete($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
	 	$sth = $this->db->prepare("DELETE FROM rules WHERE id = ?");
		$sth->bindParam(1, $id,	PDO::PARAM_INT);
		$sth->execute();
	}
}