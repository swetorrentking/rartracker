<?php

class TorrentLists {
	private $db;
	private $user;
	private $log;
	private $mailbox;
	private $torrents;

	public function __construct($db, $user = null, $log = null, $mailbox = null, $torrents = null) {
		$this->db = $db;
		$this->user = $user;
		$this->log = $log;
		$this->mailbox = $mailbox;
		$this->torrents = $torrents;
	}

	public function query($index = 0, $limit = 10, $params) {
		$sth = $this->db->query('SELECT COUNT(*) FROM torrent_lists');
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		switch ($params["sort"]) {
			case 'votes': $sortColumn = 'votes'; break;
			default: $sortColumn = 'torrent_lists.added';
		}

		if ($params["order"] == "asc") {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$sth = $this->db->prepare('SELECT '.implode(',', User::getDefaultFields()).', (SELECT 1 FROM torrent_list_bookmarks WHERE torrent_list_bookmarks.torrent_list = torrent_lists.id AND torrent_list_bookmarks.userid = ?) AS bookmarked, imdbinfo.imdbid AS imdbid2, torrent_lists.id AS listId, torrent_lists.name, torrent_lists.slug, torrent_lists.description, torrent_lists.votes, torrent_lists.added, torrent_lists.torrents, torrent_lists.type FROM torrent_lists LEFT JOIN users ON torrent_lists.userid = users.id LEFT JOIN imdbinfo ON torrent_lists.imdbid = imdbinfo.id WHERE type = "public" ORDER BY '.$sortColumn.' '.$order.' LIMIT ?, ?');
		$sth->bindValue(1, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(2, $index,					PDO::PARAM_INT);
		$sth->bindParam(3, $limit,				 	PDO::PARAM_INT);
		$sth->execute();

		$result = $this->rowToResult($sth);

		return Array($result, $totalCount);
	}

	private function rowToResult($sth) {
		$result = array();
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$arr = array();
			$arr["id"] = $row["listId"];
			$arr["added"] = $row["added"];
			$arr["description"] = $row["description"];
			$arr["name"] = $row["name"];
			$arr["slug"] = $row["slug"];
			$arr["imdbid"] = $row["imdbid"];
			$arr["votes"] = $row["votes"];
			$arr["imdbid2"] = $row["imdbid2"];
			$arr["bookmarked"] = $row["bookmarked"] == 1;
			$arr["user"] = $this->user->generateUserObject($row);
			array_push($result, $arr);
		}
		return $result;
	}

	public function queryUserLists($userId) {
		$sth = $this->db->prepare('SELECT '.implode(',', User::getDefaultFields()).', (SELECT 1 FROM torrent_list_bookmarks WHERE torrent_list_bookmarks.torrent_list = torrent_lists.id AND torrent_list_bookmarks.userid = ?) AS bookmarked, imdbinfo.imdbid AS imdbid2, torrent_lists.id AS listId, torrent_lists.name, torrent_lists.slug, torrent_lists.description, torrent_lists.votes, torrent_lists.added, torrent_lists.torrents, torrent_lists.type FROM torrent_lists LEFT JOIN users ON torrent_lists.userid = users.id LEFT JOIN imdbinfo ON torrent_lists.imdbid = imdbinfo.id WHERE torrent_lists.userid = ? ORDER BY name ASC');
		$sth->bindValue(1, $userId,	PDO::PARAM_INT);
		$sth->bindValue(2, $userId,	PDO::PARAM_INT);
		$sth->execute();

		return $this->rowToResult($sth);
	}

	public function queryPopularLists() {
		$sth = $this->db->prepare('SELECT '.implode(',', User::getDefaultFields()).', imdbinfo.imdbid AS imdbid2, torrent_lists.name, torrent_lists.slug, torrent_lists.description, torrent_lists.votes, torrent_lists.added, torrent_lists.torrents, torrent_lists.type, torrent_lists.id AS listId FROM torrent_lists LEFT JOIN users ON torrent_lists.userid = users.id LEFT JOIN imdbinfo ON torrent_lists.imdbid = imdbinfo.id WHERE type = "public" AND torrent_lists.added < DATE_ADD(NOW(), INTERVAL +1 MONTH) ORDER BY votes DESC LIMIT 6');
		$sth->execute();

		return $this->rowToResult($sth);
	}

	public function get($id, $fast = false) {
		$sth = $this->db->prepare('SELECT '.implode(',', User::getDefaultFields()).', (SELECT 1 FROM torrent_list_bookmarks WHERE torrent_list_bookmarks.torrent_list = torrent_lists.id AND torrent_list_bookmarks.userid = ?) AS bookmarked, torrent_lists.id AS listId, imdbinfo.title AS imdb_title, imdbinfo.year AS imdb_year, imdbinfo.id AS imdbid, imdbinfo.imdbid AS imdbid2, torrent_lists.name, torrent_lists.slug, torrent_lists.description, torrent_lists.votes, torrent_lists.added, torrent_lists.torrents, torrent_lists.type FROM torrent_lists LEFT JOIN users ON torrent_lists.userid = users.id LEFT JOIN imdbinfo ON torrent_lists.imdbid = imdbinfo.id WHERE torrent_lists.id = ?');
		$sth->bindValue(1, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(2, $id,						PDO::PARAM_INT);
		$sth->execute();

		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			throw new Exception(L::get("TORRENT_LIST_NOT_FOUND"), 404);
		}

		$arr = array();
		$arr["id"] = $row["listId"];
		$arr["added"] = $row["added"];
		$arr["name"] = $row["name"];
		$arr["bookmarked"] = $row["bookmarked"];
		$arr["description"] = $row["description"];
		$arr["torrents"] = explode(",", $row["torrents"]);
		$arr["slug"] = $row["slug"];
		$arr["imdbid"] = $row["imdbid"];
		$arr["imdbid2"] = $row["imdbid2"];
		$arr["imdbInfo"] = $row["imdb_title"] . ( $row["imdb_year"] > 1900 ? " (" . $row["imdb_year"] . ")" : "");
		$arr["type"] = $row["type"];
		$arr["votes"] = $row["votes"];
		$arr["user"] = $this->user->generateUserObject($row);
		if (!$fast) {
			$arr["torrents_data"] = $this->torrents->getByIdList($row["torrents"]);
		}

		return $arr;
	}

	public function createOrUpdate($postData) {
		if ($postData["id"]) {
			$torrentList = $this->get($postData["id"], true);
			if ($torrentList["user"]["id"] != $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN) {
				throw new Exception(L::get("PERMISSION_DENIED"), 401);
			}
		}

		if (!is_array($postData["torrents"]) || count($postData["torrents"]) < 1) {
			throw new Exception(L::get("TORRENT_LIST_NO_TORRENTS"), 412);
		}

		if (strlen($postData["name"]) < 1) {
			throw new Exception(L::get("TORRENT_LIST_NAME_TOO_SHORT"), 412);
		}

		$torrents = implode(",", $postData["torrents"]);

		if (!preg_match ('/^[\d,]+$/', $torrents) ){
			throw new Exception(L::get("TORRENT_LIST_INVALID"), 400);
		}

		$slug = Helper::slugify($postData["name"]);
		$userid = $this->user->getId();

		if ($postData["id"]) {
			$sth = $this->db->prepare("UPDATE torrent_lists SET userid = ?, name = ?, description = ?, imdbid = ?, torrents = ?, type = ?,  slug = ? WHERE id = " . (int)$torrentList["id"]);
			$userid = $torrentList["user"]["id"];
		} else {
			$sth = $this->db->prepare("INSERT INTO torrent_lists(userid, name, added, description, imdbid, torrents, type, slug) VALUES(?, ?, NOW(), ?, ?, ?, ?, ?)");
		}

		$sth->bindParam(1, $userid,						PDO::PARAM_INT);
		$sth->bindParam(2, $postData["name"],			PDO::PARAM_STR);
		$sth->bindParam(3, $postData["description"],	PDO::PARAM_STR);
		$sth->bindParam(4, $postData["imdbid"],			PDO::PARAM_INT);
		$sth->bindParam(5, $torrents,					PDO::PARAM_STR);
		$sth->bindParam(6, $postData["type"],			PDO::PARAM_INT);
		$sth->bindParam(7, $slug,						PDO::PARAM_STR);
		$sth->execute();

		if ($postData["id"]) {
			return Array("id" => $postData["id"], "slug" => $slug);
		} else {
			$insertId = $this->db->lastInsertId();
			return Array("id" => $insertId, "slug" => $slug);
		}
	}

	public function delete($listId, $reason) {
		$torrentList = $this->get($listId);
		if ($torrentList["user"]["id"] != $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN && $this->user->getId() !== 1) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$this->purge($torrentList["id"]);
	}

	public function purge($listId) {
		$this->db->query("DELETE FROM torrent_lists WHERE id = " . $listId);
		$this->db->query("DELETE FROM torrent_list_bookmarks WHERE torrent_list = " . $listId);
		$this->db->query("DELETE FROM torrent_list_votes WHERE torrent_list = " . $listId);
	}

	public function vote($listId) {
		$torrentList = $this->get($listId, true);

		$sth = $this->db->prepare("SELECT id FROM torrent_list_votes WHERE torrent_list = ? AND userid = ?");
		$sth->bindParam(1, $listId,					PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch();

		if ($res) {
			$this->db->query("DELETE FROM torrent_list_votes WHERE id = " . $res["id"]);
			$this->db->query("UPDATE torrent_lists SET votes = votes - 1 WHERE id = " . (int) $listId);
		} else {
			if ($torrentList["user"]["id"] == $this->user->getId()) {
				throw new Exception(L::get("TORRENT_LIST_VOTE_OWN_ERROR"), 401);
			}
			if ($res["type"] == "unlisted") {
				throw new Exception(L::get("TORRENT_LIST_VOTE_UNLISTED_ERROR"), 401);
			}
			$sth = $this->db->prepare("INSERT INTO torrent_list_votes(torrent_list, userid) VALUES(?, ?)");
			$sth->bindParam(1, $listId,					PDO::PARAM_INT);
			$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
			$sth->execute();
			$this->db->query("UPDATE torrent_lists SET votes = votes + 1 WHERE id = " . (int) $listId);
		}

		return array("votes" => $this->getVoteAmount($listId));
	}

	private function getVoteAmount($id) {
		$sth = $this->db->query("SELECT COUNT(*) FROM torrent_list_votes WHERE torrent_list = " . (int)$id);
		$res = $sth->fetch();
		return $res[0];
	}

}
