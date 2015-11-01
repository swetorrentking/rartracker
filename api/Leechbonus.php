<?php

class Leechbonus {

	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function run() {

		if ($_SERVER['SERVER_ADDR'] != $_SERVER["REMOTE_ADDR"]) {
			throw new Exception("Must be run by server.", 401);
		}

		/* 1. Spara alla användares nuvarande antal seedade GB. - Ska köras varje timme */

		$nu = time('Y-m-d H');
		$user = $this->db->query('SELECT * FROM users WHERE enabled = "yes"');
		while($u = $user->fetch(PDO::FETCH_ASSOC)) {

			$res = $this->db->query('SELECT torrents.size, peers.to_go FROM peers JOIN torrents ON peers.torrent = torrents.id WHERE userid = ' . $u["id"] . ' GROUP BY userid, torrent');
			
			$seedat = 0;
			while($r = $res->fetch(PDO::FETCH_ASSOC)) {
				$seedat += ($r["size"] - $r["to_go"]);
			}
			
			$gb = round($seedat / 1073741824);

			if ($gb > 0) {
				$this->db->query('INSERT INTO leechbonus(userid, datum, gbseed) VALUES('.$u["id"].', '.$nu.', '.$gb.')');
			}

		}

		/* 2. Radera all loggning 3 dagar bakåt */
		$envecka = time()-259200; // 3 dagar bakåt
		$this->db->query('DELETE FROM leechbonus WHERE datum < ' . $envecka);

		/* 3. Uppdatera allas nuvarande LeechBonus-Procent med 3 dagar */

		$user = $this->db->query('SELECT id, UNIX_TIMESTAMP(added) AS added FROM users');

		while($u = $user->fetch(PDO::FETCH_ASSOC)) {
			$res = $this->db->query('SELECT SUM(gbseed) AS seedsum FROM leechbonus WHERE userid = ' . $u["id"] . ' ');
			$res2 = $res->fetch(PDO::FETCH_ASSOC);

			$leechbonus = $this->leechbonus($res2["seedsum"]/72); // Dela med 24*3 timmar
			$this->db->query('UPDATE users SET leechbonus = '. $leechbonus .' WHERE id = '. $u["id"]);
		}

	}

	private function leechbonus($gb) {
		$max = 1000;
		$procent = round($gb / 10);

		if ($procent > 100) {
			$procent = 100;
		}

		return $procent;
	}
}