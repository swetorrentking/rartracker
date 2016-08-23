<?php

class Subtitles {
	private $db;
	private $user;
	private $log;
	private $torrent;
	private $mailbox;
	private $subsDir = "../subs/";

	public function __construct($db, $user = null, $log = null, $torrent = null, $mailbox = null) {
		$this->db = $db;
		$this->user = $user;
		$this->log = $log;
		$this->torrent = $torrent;
		$this->mailbox = $mailbox;
	}

	public function fetch($torrentId) {
		$sth = $this->db->prepare('SELECT * FROM subs WHERE torrentid = ?');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->execute();

		$subtitles = array();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$subtitle = array();
			$subtitle["filename"] = $row["filnamn"];
			$subtitle["date"] = $row["datum"];
			$subtitle["quality"] = $row["quality"];
			$subtitle["id"] = $row["id"];
			if ($this->user->getClass() < USER::CLASS_ADMIN && $row["userid"] != $this->user->getId()) {
				$subtitle["user"] = null;
			} else {
				$subtitle["user"] = $this->user->get($row["userid"]);
			}
			array_push($subtitles, $subtitle);
		}

		return $subtitles;
	}

	public function get($id) {
		$sth = $this->db->prepare('SELECT * FROM subs WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();

		$subtitle = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$subtitle) {
			throw new Exception(L::get("SUBTITLE_NOT_FOUND"), 404);
		}
		return $subtitle;
	}

	public function delete($id, $reason = '') {
		$subtitle = $this->get($id);

		if ($this->user->getClass() < User::CLASS_ADMIN && $this->user->getId() != $subtitle["userid"]) {
			throw new Exception(L::get("PERMISSION_DENIED"), 404);
		}

		$this->db->query('DELETE FROM subs WHERE id = ' . $subtitle["id"]);
		$sth = $this->db->query("SELECT COUNT(*) FROM subs WHERE torrentid = " . $subtitle["torrentid"]);
		$res = $sth->fetch();

		if ($res[0] == 0) {
			$this->db->query('UPDATE torrents SET swesub = 0 WHERE id = ' . $subtitle["torrentid"]);
		}

		@unlink($this->subsDir . $subtitle["filnamn"]);

		$anonymous = 1;

		if ($subtitle["userid"] != $this->user->getId()) {
			$user = $this->user->get($subtitle["userid"]);
			if ($user) {
				$torrent = $this->torrent->get($subtitle["torrentid"]);
				$subject = L::get("SUBTITLE_DELETED_PM_SUBJECT", null, $user["language"]);
				$message = L::get("SUBTITLE_DELETED_PM_BODY", [$subtitle["filnamn"], $torrent["id"], $torrent["name"], $torrent["name"], $reason], $user["language"]);
				$this->mailbox->sendSystemMessage($user["id"], $subject, $message);
				$anonymous = 0;
			}
		}

		$this->log->log(3, L::get("SUBTITLE_DELETED_SITE_LOG", [$subtitle["filnamn"], ($reason?:"-")], Config::DEFAULT_LANGUAGE), $this->user->getId(), $anonymous);
	}

	public function upload($file, $post) {
		if (!preg_match("/\.(srt|zip|rar)$/", $file["name"], $match)) {
			throw new Exception(L::get("SUBTITLE_FILE_EXTENSION_REQUIREMENT"), 412);
		}

		if (!is_uploaded_file($file["tmp_name"])) {
			throw new Exception(L::get("SUBTITLE_FILE_UPLOAD_ERROR"));
		}

		if (!filesize($file["tmp_name"])) {
			throw new Exception(L::get("SUBTITLE_FILE_EMPTY_ERROR"));
		}

		$sth = $this->db->prepare("SELECT COUNT(*) FROM subs WHERE filnamn = ?");
		$sth->bindParam(1, $file["name"], PDO::PARAM_STR);
		$sth->execute();
		$res = $sth->fetch();
		if ($res[0] > 0) {
			throw new Exception(L::get("SUBTITLE_CONFLICT_ERROR"), 409);
		}

		$sth = $this->db->prepare("INSERT INTO subs(torrentid, filnamn, datum, quality, userid) VALUES(?, ?, NOW(), ?, ?)");
		$sth->bindParam(1,	$post["torrentid"], 	PDO::PARAM_INT);
		$sth->bindParam(2,	$file["name"],			PDO::PARAM_STR);
		$sth->bindValue(3,	$post["quality"] ?: '',	PDO::PARAM_STR);
		$sth->bindValue(4,	$this->user->getId(),	PDO::PARAM_INT);
		$sth->execute();

		move_uploaded_file($file["tmp_name"], $this->subsDir.$file["name"]);

		$torrent = $this->torrent->get($post["torrentid"]);
		$this->db->query("UPDATE torrents SET swesub = 1 WHERE id = " . $torrent["id"]);
		$this->log->log(1, L::get("SUBTITLE_UPLOAD_SITE_LOG", [$torrent["id"], $torrent["name"], $torrent["name"]], Config::DEFAULT_LANGUAGE), $this->user->getId(), true);

		// Inform users watching for subtitles
		$sth = $this->db->prepare("SELECT * FROM bevakasubs WHERE torrentid = ? AND userid != ?");
		$sth->bindParam(1,	$torrent["id"],			PDO::PARAM_INT);
		$sth->bindValue(2,	$this->user->getId(),	PDO::PARAM_INT);
		$sth->execute();

		$subject = L::get("SUBTITLE_UPLOAD_PM_SUBJECT", [$torrent["name"]]);
		$message = L::get("SUBTITLE_UPLOAD_PM_BODY", [$file["name"], $torrent["id"], $torrent["name"], $torrent["name"]]);

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$this->mailbox->sendSystemMessage($row["userid"], $subject, $message);
		}

		return array("status" => "ok");
	}

	public function download($id) {
		$sth = $this->db->prepare("SELECT * FROM subs WHERE id = ?");
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		$this->downloadRow($row);
	}

	public function downloadByTorrentId($id) {
		$sth = $this->db->prepare("SELECT * FROM subs WHERE torrentid = ? LIMIT 1");
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		$this->downloadRow($row);
	}

	private function downloadRow($row){
		if (!$row || !file_exists($this->subsDir . $row["filnamn"])) {
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			header("Status: 404 Not Found");
			exit;
		}

		header('Content-Disposition: attachment; filename="'.$row["filnamn"].'"');

		echo file_get_contents($this->subsDir . $row["filnamn"]);
	}
}
