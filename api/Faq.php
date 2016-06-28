<?php

class Faq {
	private $db;
	private $user;

	public function __construct($db, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query() {
	 	$sth = $this->db->query('SELECT * FROM faq ORDER BY `order` ASC');
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function create($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

	 	$sth = $this->db->prepare("INSERT INTO faq(type, question, answer, flag, categ, `order`) VALUES(?, ?, ?, ?, ?, ?)");
		$sth->bindParam(1, $postdata["type"],			PDO::PARAM_STR);
		$sth->bindParam(2, $postdata["question"],		PDO::PARAM_STR);
		$sth->bindParam(3, $postdata["answer"],			PDO::PARAM_STR);
		$sth->bindParam(4, $postdata["flag"],			PDO::PARAM_INT);
		$sth->bindParam(5, $postdata["categ"],			PDO::PARAM_INT);
		$sth->bindParam(6, $postdata["order"],			PDO::PARAM_INT);
		$sth->execute();
	}

	public function update($id, $postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
	 	$sth = $this->db->prepare("UPDATE faq SET type = ?, question = ?, answer = ?, flag = ?, categ = ?, `order` = ? WHERE id = ?");
		$sth->bindParam(1, $postdata["type"],			PDO::PARAM_STR);
		$sth->bindParam(2, $postdata["question"],		PDO::PARAM_STR);
		$sth->bindParam(3, $postdata["answer"],			PDO::PARAM_STR);
		$sth->bindParam(4, $postdata["flag"],			PDO::PARAM_INT);
		$sth->bindParam(5, $postdata["categ"],			PDO::PARAM_INT);
		$sth->bindParam(6, $postdata["order"],			PDO::PARAM_INT);
		$sth->bindParam(7, $id,							PDO::PARAM_INT);
		$sth->execute();
	}

	public function delete($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
	 	$sth = $this->db->prepare("DELETE FROM faq WHERE id = ?");
		$sth->bindParam(1, $id,	PDO::PARAM_INT);
		$sth->execute();
	}
}