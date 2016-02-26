<?php

class ReseedRequests {
	private $db;
	private $user;
	private $torrent;
	private $mailbox;
	private $log;

	public function __construct($db, $user, $torrent, $mailbox, $log) {
		$this->db = $db;
		$this->user = $user;
		$this->torrent = $torrent;
		$this->mailbox = $mailbox;
		$this->log = $log;
	}

	public function create($postdata = null) {
		if ($this->user->getClass() < User::CLASS_SKADIS) {
			throw new Exception('Du måste vara minst Skådis för att kunna önska seed', 401);
		}

		$sth = $this->db->prepare('SELECT * FROM reseed_requests WHERE torrentid = ? AND added > DATE_ADD(NOW(),INTERVAL -1 MONTH)');
		$sth->bindParam(1, $postdata["torrentid"], PDO::PARAM_INT);
		$sth->execute();
		if ($sth->rowCount() > 0) {
			throw new Exception("Seed har redan önskats på denna torrent inom de senaste veckorna.", 412);
		}

		if ($this->user->getBonus() < 5) {
			throw new Exception("Du har inte tillräckligt med bonuspoäng.", 412);
		}

		$torrent = $this->torrent->get($postdata["torrentid"]);

		if ($torrent["seeders"] > 2) {
			throw new Exception("Kan inte önska seed på torrents med över 2 seedare.", 412);
		}
		$this->user->bonusLog(-5, "Önska seed på torrent.", $this->user->getId());

		$sth = $this->db->query("SELECT userid FROM snatch WHERE torrentid = ".$torrent["id"]." AND lastaction > DATE_ADD(NOW(),INTERVAL -6 MONTH) AND userid != " . $this->user->getId());

		$message = "En användare önskar seed på torrenten:\n [url=/torrent/" . $torrent["id"] . "/".$torrent["name"]."][b]".$torrent["name"]."[/b][/url]\n\nDu får detta PM eftersom du har seedat denna torrent inom det senaste halvåret.\n\nDet skulle både glädja någon samt ge dig uppladdat om du hade kunnat återseeda torrenten!";
		while($res = $sth->fetch(PDO::FETCH_ASSOC)) {
			$this->mailbox->sendSystemMessage($res["userid"], "Seed önskas!", $message);
		}

		$sth = $this->db->prepare("INSERT INTO reseed_requests(torrentid, userid, added) VALUES(?, ?, NOW())");
		$sth->bindParam(1, $torrent["id"],			PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
		$sth->execute();

		$this->log->log(1, "Seed önskas till ([url=/torrent/" . $torrent["id"] . "/".$torrent["name"]."][b]".$torrent["name"]."[/b][/url]) utav {{username}}", $this->user->getId(), 1);
	}
}
