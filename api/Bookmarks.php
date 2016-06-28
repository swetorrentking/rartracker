<?php

class Bookmarks implements IResource {
	private $db;
	private $user;

	public function __construct($db = null, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function get($id) {
		$sth = $this->db->prepare('SELECT * FROM bookmarks WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception(L::get("BOOMARK_NOT_EXIST"), 404);
		}
		return $res;
	}

	public function query($postdata) {
		$limit = (int)$postdata["limit"] ?: 10;
		$index = (int)$postdata["index"] ?: 0;
		$sth = $this->db->query('SELECT bookmarks.id AS bookmarkId, imdbinfo.genres, imdbinfo.photo, imdbinfo.rating, imdbinfo.imdbid AS imdbid2, '.implode(Torrent::$torrentFieldsUser, ', ').' FROM bookmarks LEFT JOIN torrents ON bookmarks.torrentid = torrents.id LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id  WHERE bookmarks.userid = '.$this->user->getId().' ORDER BY torrents.id DESC');
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function create($postdata) {
		$sth = $this->db->prepare('SELECT * FROM bookmarks WHERE userid = ? AND torrentid = ?');
		$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $postdata["torrentid"], PDO::PARAM_INT);
		$sth->execute();
		if ($sth->fetch()) {
			throw new Exception(L::get("ALREADY_BOOKMARKED"), 409);
		}

		$sth = $this->db->prepare("INSERT INTO bookmarks(userid, torrentid) VALUES(?, ?)");
		$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $postdata["torrentid"], PDO::PARAM_STR);
		$sth->execute();
	}

	public function delete($id, $postdata = null) {
		$bookmark = $this->get($id);
		if ($bookmark["userid"] != $this->user->getId()) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$this->db->query('DELETE FROM bookmarks WHERE id = ' . $bookmark["id"]);
	}

	public function update ($id, $postdata) {}
}
