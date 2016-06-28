<?php

class Comments {
	private $db;
	private $user;
	private $torrent;

	public function __construct($db, $user = null, $torrent = null) {
		$this->db = $db;
		$this->user = $user;
		$this->torrent = $torrent;
	}

	public function query($torrentId, $limit = 10, $index = 0) {
		$sth = $this->db->prepare('SELECT ano_owner, owner FROM torrents WHERE id = ?');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->execute();
		$torrent = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$torrent) {
			throw new Exception(L::get("TORRENT_NOT_EXIST"), 404);
		}

		$sth = $this->db->prepare('SELECT COUNT(*) FROM comments WHERE torrent = ?');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT comments.id AS pid,comments.added AS padded, comments.text AS pbody, comments.editedat, '.implode(',', User::getDefaultFields()).' FROM comments LEFT JOIN users ON users.id = comments.user WHERE torrent = ? ORDER BY comments.id ASC LIMIT ?, ?');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->bindParam(2, $index, PDO::PARAM_INT);
		$sth->bindParam(3, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["editedat"] = $post["editedat"];

			if ($torrent["ano_owner"] && $torrent["owner"] == $post["id"] && $torrent["owner"] != $this->user->getId()) {
				$row["user"] = null;
			} else {
				$row["user"] = $this->user->generateUserObject($post);
				if ($torrent["ano_owner"] && $torrent["owner"] == $post["id"]) {
					$row["user"]["anonymous"] = true;
				}
			}

			$result[] = $row;
		}

		return Array($result, $totalCount);
	}

	public function getCommentsForUserTorrents($userId, $limit = 10, $index = 0) {

		if ($userId != $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->prepare('SELECT COUNT(*) FROM comments LEFT JOIN users ON users.id = comments.user LEFT JOIN torrents ON torrents.id = comments.torrent WHERE torrents.owner = ? AND comments.user != ?');
		$sth->bindParam(1, $userId, PDO::PARAM_INT);
		$sth->bindParam(2, $userId, PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT torrents.name, torrents.id AS torrentid, comments.id AS pid,comments.added AS padded, comments.text AS pbody, comments.editedat, '.implode(',', User::getDefaultFields()).' FROM comments LEFT JOIN users ON users.id = comments.user LEFT JOIN torrents ON torrents.id = comments.torrent WHERE torrents.owner = ? AND comments.user != ? ORDER BY comments.id DESC LIMIT ?, ?');
		$sth->bindParam(1, $userId,		PDO::PARAM_INT);
		$sth->bindParam(2, $userId,		PDO::PARAM_INT);
		$sth->bindParam(3, $index,		PDO::PARAM_INT);
		$sth->bindParam(4, $limit,		PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["editedat"] = $post["editedat"];

			$row["torrent"] = array(
				"id" => $post["torrentid"],
				"name" => $post["name"]
				);

			$row["user"] = $this->user->generateUserObject($post);

			$result[] = $row;
		}

		if ($index == 0) {
			$this->updateLastReadComment($userId);
		}

		return Array($result, $totalCount);
	}

	public function getUserComments($userId, $limit = 10, $index = 0) {
		if ($userId != $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare('SELECT COUNT(*) FROM comments WHERE user = ?');
		$sth->bindParam(1, $userId, PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT torrents.name, torrents.id AS torrentid, comments.id AS pid,comments.added AS padded, comments.text AS pbody, comments.editedat, '.implode(',', User::getDefaultFields()).' FROM comments LEFT JOIN users ON users.id = comments.user LEFT JOIN torrents ON torrents.id = comments.torrent WHERE comments.user = ? ORDER BY comments.id DESC LIMIT ?, ?');
		$sth->bindParam(1, $userId,		PDO::PARAM_INT);
		$sth->bindParam(2, $index,		PDO::PARAM_INT);
		$sth->bindParam(3, $limit,		PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["editedat"] = $post["editedat"];

			$row["torrent"] = array(
				"id" => $post["torrentid"],
				"name" => $post["name"]
				);

			$row["user"] = $this->user->generateUserObject($post);

			$result[] = $row;
		}

		if ($index == 0) {
			$this->updateLastReadComment($userId);
		}

		return Array($result, $totalCount);
	}

	public function getAllComments($limit = 10, $index = 0) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->query('SELECT COUNT(*) FROM comments');
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT torrents.name, torrents.id AS torrentid, comments.id AS pid,comments.added AS padded, comments.text AS pbody, comments.editedat, '.implode(',', User::getDefaultFields()).' FROM comments LEFT JOIN users ON users.id = comments.user LEFT JOIN torrents ON torrents.id = comments.torrent ORDER BY comments.id DESC LIMIT ?, ?');
		$sth->bindParam(1, $index,		PDO::PARAM_INT);
		$sth->bindParam(2, $limit,		PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["editedat"] = $post["editedat"];

			$row["torrent"] = array(
				"id" => $post["torrentid"],
				"name" => $post["name"]
				);

			$row["user"] = $this->user->generateUserObject($post);

			$result[] = $row;
		}

		return Array($result, $totalCount);
	}

	public function updateLastReadComment($userId) {
		$sth = $this->db->prepare("SELECT comments.id FROM comments LEFT JOIN users ON users.id = comments.user LEFT JOIN torrents ON torrents.id = comments.torrent WHERE torrents.owner = ? AND comments.user != ? ORDER BY comments.id DESC LIMIT 1");
		$sth->bindParam(1, $userId, PDO::PARAM_INT);
		$sth->bindParam(2, $userId, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch();
		if ($res) {
			$this->user->updateLastReadTorrentComment($userId, $res[0]);
		}
	}

	private function getLastComment($torrentId) {
		$sth = $this->db->prepare('SELECT * FROM comments WHERE torrent = ? ORDER BY id DESC LIMIT 1');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function delete($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$comment = $this->get($id);

		$this->db->query("DELETE FROM comments WHERE id = " . $comment["id"]);
		$this->torrent->updateCommentsAmount($comment["torrent"], -1);
	}

	public function get($id) {
		$sth = $this->db->prepare('SELECT * FROM comments WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$comment = $sth->fetch(PDO::FETCH_ASSOC);

		if (!$comment) {
			throw new Exception(L::get("COMMENT_NOT_EXIST"), 404);
		}

		return $comment;
	}

	public function add($torrentId, $post) {

		if (!$this->torrent->get($torrentId)) {
			throw new Exception(L::get("TORRENT_NOT_EXIST"), 404);
		}

		if (strlen($post) < 2) {
			throw new Exception(L::get("COMMENT_TOO_SHORT"), 412);
		}

		$lastComment = $this->getLastComment($torrentId);
		if ($lastComment && $lastComment["user"] == $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN && (time() - strtotime($lastComment["added"]) < 86400)) {
			throw new Exception(L::get("FORUM_DOUBLE_POST"));
		}

		$sth = $this->db->prepare('INSERT INTO comments(torrent, user, added, text) VALUES(?, ?, NOW(), ?)');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(3, $post, PDO::PARAM_STR);
		$sth->execute();

		$this->torrent->updateCommentsAmount($torrentId, 1);
	}

	public function update($torrentId, $postId, $postData) {
		if (strlen($postData) < 2) {
			throw new Exception(L::get("COMMENT_TOO_SHORT"), 412);
		}

		$post = $this->get($postId);

		if ($post["torrent"] != $torrentId) {
			throw new Exception(L::get("COMMENT_TORRENT_NOT_MATCHING"));
		}

		$sth = $this->db->prepare('UPDATE comments SET ori_text = text, text = ?, editedby = ?, editedat = NOW() WHERE id = ?');
		$sth->bindParam(1, $postData, PDO::PARAM_STR);
		$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(3, $postId, PDO::PARAM_INT);
		$sth->execute();
	}
}
