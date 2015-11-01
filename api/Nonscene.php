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
			throw new Exception('Du saknar rättigheter.', 401);
		}
		$sth = $this->db->query("SELECT * FROM nonscene ORDER BY groupname ASC");
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function create($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception('Du saknar rättigheter.', 401);
		}

		if (strlen($postdata["comment"]) < 1) {
			throw new Exception("Kommentar för kort.", 412);
		}

		if (strlen($postdata["groupname"]) < 1) {
			throw new Exception("Gruppnamn för kort.", 412);
		}

		$sth = $this->db->prepare("SELECT * FROM nonscene WHERE groupname = ?");
		$sth->bindParam(1, $postdata["groupname"], PDO::PARAM_INT);
		$sth->execute();
		if ($sth->fetch()) {
			throw new Exception("Gruppen finns redan inlagd.", 412);
		}

		$sth = $this->db->prepare("INSERT INTO nonscene(groupname, comment, whitelist) VALUES(?, ?, ?)");
		$sth->bindParam(1, $postdata["groupname"],	PDO::PARAM_STR);
		$sth->bindParam(2, $postdata["comment"],	PDO::PARAM_STR);
		$sth->bindParam(3, $postdata["whitelist"],	PDO::PARAM_INT);
		$sth->execute();

		$this->adminlogs->create("{{username}} lade till [b]".$postdata["groupname"]."[/b] på p2p-grupplistan.");
	}

	public function delete($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception('Du saknar rättigheter.', 401);
		}
		$release = $this->get($id);
		$this->db->query('DELETE FROM nonscene WHERE id = ' . $release["id"]);
		$this->adminlogs->create("{{username}} raderade [b]".$release["groupname"]."[/b] ifrån p2p-grupplistan.");
	}

	private function get($id) {
		$sth = $this->db->prepare("SELECT * FROM nonscene WHERE id = ?");
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception("Föremålet finns inte.", 404);
		}
		return $res;
	}
}