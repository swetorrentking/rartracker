<?php

class AdminLogs implements IResource {
	private $db;
	private $user;

	public function __construct($db, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function create($text) {
		$searchText = Helper::searchfield($text);
		$sth = $this->db->prepare('INSERT INTO adminlog (added, txt, userid, search_text) VALUES (NOW(), ?, ?, ?)');
		$sth->bindParam(1, $text,						PDO::PARAM_STR);
		$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(3, $searchText,				PDO::PARAM_STR);
		$sth->execute();
	}

	public function query($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$limit = (int)$postdata["limit"] ?: 25;
		$index = (int)$postdata["index"] ?: 0;
		$search = $postdata["search"] ?: "";

		$where = "";

		if (strlen($search) > 0) {
			$searchWords = Helper::searchTextToWordParams($search);
			$where = "WHERE MATCH (adminlog.search_text) AGAINST (" . $this->db->quote($searchWords) . " IN BOOLEAN MODE)";
		}

		$sth = $this->db->query("SELECT COUNT(*) FROM adminlog ".$where);
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare("SELECT adminlog.added, adminlog.id AS aid, adminlog.txt, users.id, users.username FROM adminlog LEFT JOIN users ON adminlog.userid = users.id ".$where." ORDER BY adminlog.id DESC LIMIT ?, ?");
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = Array();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$r = array();
			$r["id"] = $row["aid"];
			$r["added"] = $row["added"];
			$r["txt"] = str_replace("{{username}}", "[url=/user/".$row["id"] ."/".$row["username"]."][b]".$row["username"]."[/b][/url]", $row["txt"]);
			array_push($result, $r);
		}

		return Array($result, $totalCount);
	}

	public function get($id) {}
	public function update($id, $postdata) {}
	public function delete($id, $reason) {}
}
