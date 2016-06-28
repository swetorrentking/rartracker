<?php

class Leechbonus {

	private $db;
	private $leechBonusLimitGb = 1000; /* When is 100% leechbonus reached, in GB */

	public function __construct($db) {
		$this->db = $db;
	}

	public function run() {

		if ($_SERVER['SERVER_ADDR'] != $_SERVER["REMOTE_ADDR"]) {
			throw new Exception(L::get("MUST_BE_RUN_BY_SERVER_ERROR"), 401);
		}

		/* 1. Save all users current seed amount. Run every hour */

		$now = time('Y-m-d H');
		$user = $this->db->query('SELECT * FROM users WHERE enabled = "yes"');
		while($u = $user->fetch(PDO::FETCH_ASSOC)) {

			$res = $this->db->query('SELECT torrents.size, peers.to_go FROM peers JOIN torrents ON peers.torrent = torrents.id WHERE userid = ' . $u["id"] . ' GROUP BY userid, torrent');

			$seededAmount = 0;
			while($r = $res->fetch(PDO::FETCH_ASSOC)) {
				$seededAmount += ($r["size"] - $r["to_go"]);
			}

			$gb = round($seededAmount / 1073741824);

			if ($gb > 0) {
				$this->db->query('INSERT INTO leechbonus(userid, datum, gbseed) VALUES('.$u["id"].', '.$now.', '.$gb.')');
			}

		}

		/* 2. Erase all logs older than 3 days */

		$timeSpan = time()-259200; // 3 days
		$this->db->query('DELETE FROM leechbonus WHERE datum < ' . $timeSpan);

		/* 3. Update all leechbonus percent based on the last 3 days */
		$user = $this->db->query('SELECT id, UNIX_TIMESTAMP(added) AS added FROM users');
		while($u = $user->fetch(PDO::FETCH_ASSOC)) {
			$res = $this->db->query('SELECT SUM(gbseed) AS seedsum FROM leechbonus WHERE userid = ' . $u["id"] . ' ');
			$res2 = $res->fetch(PDO::FETCH_ASSOC);

			$leechbonus = $this->leechbonus($res2["seedsum"]/72); // Split into 24*3 hours
			$this->db->query('UPDATE users SET leechbonus = '. $leechbonus .' WHERE id = '. $u["id"]);
		}

	}

	private function leechbonus($gb) {
		$percent = round(($gb / $this->leechBonusLimitGb)*100);

		if ($percent > 100) {
			$percent = 100;
		}

		return $percent;
	}
}
