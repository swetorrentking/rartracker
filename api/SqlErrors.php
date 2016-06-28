<?php

class SqlErrors implements IResource {
	private $db;
	private $user;

	public function __construct($db, $user) {
		$this->db = $db;
		$this->user = $user;
	}

	public function create($text) {
		$this->db->query("INSERT INTO sqlerror(datum, uid, msg) VALUES(NOW(), " . $this->user->getId() .", " . $this->db->quote($text) .")");
	}

	public function query($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$limit = (int)$postdata["limit"] ?: 25;
		$index = (int)$postdata["index"] ?: 0;

		$sth = $this->db->query("SELECT COUNT(*) FROM sqlerror");
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare("SELECT sqlerror.datum AS added, sqlerror.msg AS txt, users.id, users.username FROM sqlerror LEFT JOIN users ON sqlerror.uid = users.id ORDER BY sqlerror.id DESC LIMIT ?, ?");
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		return Array($result, $totalCount);
	}

	public function get($id) {}
	public function update($id, $postdata) {}
	public function delete($id, $postdata) {}
}