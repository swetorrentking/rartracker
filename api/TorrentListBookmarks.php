<?php

class TorrentListBookmarks implements IResource {
	private $db;
	private $user;

	public function __construct($db = null, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function get($id) {
		$sth = $this->db->prepare('SELECT * FROM torrent_list_bookmarks WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception(L::get("TORRENT_LISTS_BOOKMARKS_NOT_FOUND"), 404);
		}
		return $res;
	}

	public function query($postdata) {
		$limit = (int)$postdata["limit"] ?: 10;
		$index = (int)$postdata["index"] ?: 0;
		$sth = $this->db->query('SELECT '.implode(',', User::getDefaultFields()).', torrent_list_bookmarks.torrent_list AS listId, torrent_list_bookmarks.id AS bookmarkId, imdbinfo.genres, imdbinfo.photo, imdbinfo.rating, imdbinfo.imdbid AS imdbid2, torrent_lists.* FROM torrent_list_bookmarks LEFT JOIN torrent_lists ON torrent_list_bookmarks.torrent_list = torrent_lists.id LEFT JOIN users ON torrent_lists.userid = users.id LEFT JOIN imdbinfo ON torrent_lists.imdbid = imdbinfo.id WHERE torrent_list_bookmarks.userid = '.$this->user->getId().' ORDER BY torrent_lists.id DESC');

		$result = array();
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$arr = array();
			$arr["id"] = $row["listId"];
			$arr["bookmarkId"] = $row["bookmarkId"];
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

	public function create($postdata) {
		$sth = $this->db->prepare('SELECT * FROM torrent_list_bookmarks WHERE userid = ? AND torrent_list = ?');
		$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $postdata["torrentList"], PDO::PARAM_INT);
		$sth->execute();
		$data = $sth->fetch();
		if (!$data) {
			if ($data["userid"] === $this->user->getId()) {
				throw new Exception(L::get("TORRENT_LISTS_BOOKMARK_OWN_LIST_ERROR"), 401);
			}
			$sth = $this->db->prepare("INSERT INTO torrent_list_bookmarks(userid, torrent_list) VALUES(?, ?)");
			$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
			$sth->bindParam(2, $postdata["torrentList"], PDO::PARAM_STR);
			$sth->execute();
			return array("bookmarked" => true);
		} else {
			$sth = $this->db->prepare("DELETE FROM torrent_list_bookmarks WHERE id = ?");
			$sth->bindParam(1, $data["id"], PDO::PARAM_INT);
			$sth->execute();
			return array("bookmarked" => false);
		}
	}

	public function delete($id, $postdata = null) {
		$bookmark = $this->get($id);
		if ($bookmark["userid"] != $this->user->getId()) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$this->db->query('DELETE FROM torrent_list_bookmarks WHERE id = ' . $bookmark["id"]);
	}

	public function update ($id, $postdata) {}
}
