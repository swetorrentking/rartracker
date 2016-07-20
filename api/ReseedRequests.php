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
		if ($this->user->getClass() < User::CLASS_ACTOR) {
			throw new Exception(L::get("SEED_REQUEST_CLASS_REQUIREMENT"), 401);
		}

		$sth = $this->db->prepare('SELECT * FROM reseed_requests WHERE torrentid = ? AND added > DATE_ADD(NOW(),INTERVAL -1 MONTH)');
		$sth->bindParam(1, $postdata["torrentid"], PDO::PARAM_INT);
		$sth->execute();
		if ($sth->rowCount() > 0) {
			throw new Exception(L::get("SEED_REQUEST_ALREADY_REQUESTED"), 412);
		}

		if ($this->user->getBonus() < 5) {
			throw new Exception(L::get("NOT_ENOUGH_BONUS"), 412);
		}

		$torrent = $this->torrent->get($postdata["torrentid"]);

		if ($torrent["seeders"] > 2) {
			throw new Exception(L::get("SEED_REQUEST_SEEDERS_REQUIREMENT"), 412);
		}
		$this->user->bonusLog(-5, L::get("SEED_REQUEST_BONUS_LOG"), $this->user->getId());

		$sth = $this->db->query("SELECT snatch.userid, users.language FROM snatch LEFT JOIN users ON users.id = snatch.userid WHERE torrentid = ".$torrent["id"]." AND lastaction > DATE_ADD(NOW(),INTERVAL -6 MONTH) AND timesCompleted > 0 AND userid != " . $this->user->getId());

		while($res = $sth->fetch(PDO::FETCH_ASSOC)) {
			$this->mailbox->sendSystemMessage($res["userid"], L::get("SEED_REQUEST_PM_SUBJECT", null, $res["language"]), L::get("SEED_REQUEST_PM_BODY", [$torrent["id"], $torrent["name"], $torrent["name"]], $res["language"]));
		}

		$sth = $this->db->prepare("INSERT INTO reseed_requests(torrentid, userid, added) VALUES(?, ?, NOW())");
		$sth->bindParam(1, $torrent["id"],			PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
		$sth->execute();

		$this->log->log(1, L::get("SEED_REQUEST_SITE_LOG", [$torrent["id"], $torrent["name"], $torrent["name"]], Config::DEFAULT_LANGUAGE), $this->user->getId(), 1);
	}
}
