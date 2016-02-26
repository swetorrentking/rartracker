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
			throw new Exception('Undertexten finns inte.');
		}
		return $subtitle;
	}

	public function delete($id, $reason = '') {
		$subtitle = $this->get($id);

		if ($this->user->getClass() < User::CLASS_ADMIN && $this->user->getId() != $subtitle["userid"]) {
			throw new Exception('Du saknar rättigheter att radera undertexten.');
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
			$torrent = $this->torrent->get($subtitle["torrentid"]);
			$subject = "Undertext har raderats";
			$message = "Undertexten [b]".$subtitle["filnamn"]."[/b] som du laddat upp till [url=/torrent/" . $torrent["id"] . "/".$torrent["name"]."][b]".$torrent["name"]."[/b][/url] har blivit raderad.\n\nAnledning: [b]".$reason."[/b]";
			$this->mailbox->sendSystemMessage($subtitle["userid"], $subject, $message);
			$anonymous = 0;
		}

		$this->log->log(3, "Undertexten ([b]".$subtitle["filnamn"]."[/b]) raderades utav {{username}} med anledningen: [i]".($reason?:"-")."[/i]", $this->user->getId(), $anonymous);
	}

	public function upload($file, $post) {
		if (!preg_match("/\.(srt|zip|rar)$/", $file["name"], $match)) {
			throw new Exception('Filen måste vara i formaten .srt/.zip/.rar');
		}

		if (!is_uploaded_file($file["tmp_name"])) {
			throw new Exception('Filen kunde inte laddas upp.');
		}

		if (!filesize($file["tmp_name"])) {
			throw new Exception('Filen verkar vara tom.');
		}

		$sth = $this->db->prepare("SELECT COUNT(*) FROM subs WHERE filnamn = ?");
		$sth->bindParam(1, $file["name"], PDO::PARAM_STR);
		$sth->execute();
		$res = $sth->fetch();
		if ($res[0] > 0) {
			throw new Exception('Det finns redan en undertext med samma namn.');
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
		$this->log->log(1, "Undertext till ([url=/torrent/" . $torrent["id"] . "/".$torrent["name"]."][b]".$torrent["name"]."[/b][/url]) laddades upp utav {{username}}", $this->user->getId(), true);

		// Inform users watching for subtitles
		$sth = $this->db->prepare("SELECT * FROM bevakasubs WHERE torrentid = ? AND userid != ?");
		$sth->bindParam(1,	$torrent["id"],			PDO::PARAM_INT);
		$sth->bindValue(2,	$this->user->getId(),	PDO::PARAM_INT);
		$sth->execute();

		$subject = "Undertext har laddats upp till " . $torrent["name"];
		$message = "Undertexten [b]".$file["name"]."[/b] har laddats upp till torrenten [url=/torrent/" . $torrent["id"] . "/".$torrent["name"]."][b]".$torrent["name"]."[/b][/url].";

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$this->mailbox->sendSystemMessage($row["userid"], $subject, $message);
		}

		return array("status" => "ok");
	}
}
