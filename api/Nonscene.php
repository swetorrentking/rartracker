<?php

class Nonscene {
	private $db;
	private $user;
	private $adminlogs;

	public function __construct($db, $user, $adminlogs) {
		$this->db = $db;
		$this->user = $user;
		$this->adminlogs = $adminlogs;
	}

	public function query() {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->query("SELECT * FROM nonscene ORDER BY groupname ASC");
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function create($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		if (strlen($postdata["comment"]) < 1) {
			throw new Exception(L::get("NOSCENE_COMMENT_TOO_SHORT"), 412);
		}

		if (strlen($postdata["groupname"]) < 1) {
			throw new Exception(L::get("NOSCENE_GROUP_NAME_TOO_SHORT"), 412);
		}

		$sth = $this->db->prepare("SELECT * FROM nonscene WHERE groupname = ?");
		$sth->bindParam(1, $postdata["groupname"], PDO::PARAM_INT);
		$sth->execute();
		if ($sth->fetch()) {
			throw new Exception(L::get("NOSCENE_GROUP_DUPLICATE"), 412);
		}

		$sth = $this->db->prepare("INSERT INTO nonscene(groupname, comment, whitelist) VALUES(?, ?, ?)");
		$sth->bindParam(1, $postdata["groupname"],	PDO::PARAM_STR);
		$sth->bindParam(2, $postdata["comment"],	PDO::PARAM_STR);
		$sth->bindParam(3, $postdata["whitelist"],	PDO::PARAM_INT);
		$sth->execute();

		$this->adminlogs->create(L::get("NOSCENE_ADDED_ADMIN_LOG", [$postdata["groupname"]], Config::DEFAULT_LANGUAGE));
	}

	public function delete($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$release = $this->get($id);
		$this->db->query('DELETE FROM nonscene WHERE id = ' . $release["id"]);
		$this->adminlogs->create(L::get("NOSCENE_REMOVED_ADMIN_LOG", [$release["groupname"]], Config::DEFAULT_LANGUAGE));
	}

	private function get($id) {
		$sth = $this->db->prepare("SELECT * FROM nonscene WHERE id = ?");
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception(L::get("ITEM_NOT_FOUND"), 404);
		}
		return $res;
	}
}
