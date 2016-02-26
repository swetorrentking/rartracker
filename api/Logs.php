<?php

class Logs {
	private $db;
	private $user;

	public function __construct($db, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function log($type, $txt, $userid, $anonym = 0) {
		$searchText = Helper::searchfield($txt);
		$sth = $this->db->prepare('INSERT INTO sitelog (typ, added, txt, search_text, userid, anonymous) VALUES (?, NOW(), ?, ?, ?, ?)');
		$sth->bindParam(1, $type, PDO::PARAM_INT);
		$sth->bindParam(2, $txt, PDO::PARAM_STR);
		$sth->bindParam(3, $searchText, PDO::PARAM_STR);
		$sth->bindParam(4, $userid, PDO::PARAM_INT);
		$sth->bindParam(5, $anonym, PDO::PARAM_INT);
		$sth->execute();
	}

	public function get($limit = 25, $index = 0, $search = '') {
		$limit = (int)$limit;
		$index = (int)$index;

		$where = "";

		if (strlen($search) > 0) {
			$searchWords = Helper::searchTextToWordParams($search);
			$where = "WHERE MATCH (sitelog.search_text) AGAINST (" . $this->db->quote($searchWords) . " IN BOOLEAN MODE)";
		}

		$sth = $this->db->query("SELECT COUNT(*) FROM sitelog ".$where);
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare("SELECT sitelog.id AS sid, sitelog.added, sitelog.txt, sitelog.typ, sitelog.anonymous, users.id, users.username FROM sitelog LEFT JOIN users ON sitelog.userid = users.id ".$where." ORDER BY sitelog.id DESC LIMIT ?, ?");
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = Array();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$r = array();
			$r["id"] = $row["sid"];
			$r["added"] = $row["added"];
			$r["typ"] = $row["typ"];
			$r["typ"] = $row["typ"];
			if ($row["anonymous"] == 1 && $this->user->getClass() < User::CLASS_ADMIN) {
				$r["txt"] = str_replace("{{username}}", "[i]Anonym[/i]", $row["txt"]);
			} else {
				$r["txt"] = str_replace("{{username}}", "[url=/user/".$row["id"] ."/".$row["username"]."][b]".$row["username"]."[/b][/url]", $row["txt"]);
			}
			array_push($result, $r);
		}

		return Array($result, $totalCount);
	}
}