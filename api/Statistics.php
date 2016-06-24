<?php

class Statistics {
	private $db;
	private $user;

	public function __construct($db, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function getAllStats($days) {
		if ($this->user->getClass() < User::CLASS_SKADIS) {
			throw new Exception("Du har inte rättigheter till statistiken", 401);
		}

		$sth = $this->db->query("SELECT COUNT(*) FROM statistics");
		$res = $sth->fetch();
   		$totalCount = $res[0];

		$modulus = "";
   		if ($totalCount >= 80) { /* Show 4 month old results */
   			$modulus = "WHERE MOD(id, 4) = 0";
   		} else if ($totalCount >= 60) { /* Show 3 month old results */
   			$modulus = "WHERE MOD(id, 3) = 0";
   		} else if ($totalCount >= 40) { /* Show 2 month old results */
   			$modulus = "WHERE MOD(id, 2) = 0";
   		}

		$limit = 20;
		$sth = $this->db->query("SELECT * FROM statistics ".$modulus." ORDER BY id DESC LIMIT " . $limit);
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getStartStats() {
		$date = time() - 900; // 15 min
	 	$active = $this->db->query("SELECT COUNT(*) FROM users WHERE last_access > FROM_UNIXTIME(" . $date . ")");
		$active = $active->fetch();
		$actuve15m = $active[0];

		$date = time() - 86400; // 24 h
	 	$active = $this->db->query("SELECT COUNT(*) FROM users WHERE last_access > FROM_UNIXTIME(" . $date . ")");
		$active = $active->fetch();
		$active24h = $active[0];

		$users = $this->db->query("SELECT COUNT(*) FROM users WHERE enabled = 'yes'");
		$users = $users->fetch();
		$users = $users[0];

		$torrents = $this->db->query("SELECT COUNT(*) FROM torrents");
		$torrents = $torrents->fetch();
		$torrents = $torrents[0];

		$seeders = $this->db->query("SELECT COUNT(DISTINCT userid, torrent) FROM peers WHERE to_go = 0");
		$seeders = $seeders->fetch();
		$seeders = $seeders[0];

		$leechers = $this->db->query("SELECT COUNT(DISTINCT userid, torrent) FROM peers WHERE to_go > 0");
		$leechers = $leechers->fetch();
		$leechers = $leechers[0];

		$peersRecord = $this->db->query("SELECT value_i FROM settings WHERE arg = 'peers_rekord'");
		$peersRecord = $peersRecord->fetch();
		$peersRecord = $peersRecord[0];

		$peers = $seeders + $leechers;

		return array(
			"users" => $users,
			"torrents" => $torrents,
			"seeders" => $seeders,
			"leechers" => $leechers,
			"peersRecord" => $peersRecord,
			"active15m" => $actuve15m,
			"active24h" => $active24h);
	}

	public function run() {

		if ($_SERVER['SERVER_ADDR'] != $_SERVER["REMOTE_ADDR"]) {
			throw new Exception("Must be run by server.", 401);
		}

		$todaysDateOnlyDay = date('Y-m-d');

		$oneDayTimestamp = 86400;
		$todaysDate = date('Y-m-d') . ' 18:00';
		$yesterdayDate = date('Y-m-d', time() - $oneDayTimestamp) . ' 18:00';

		$r = $this->db->query("SELECT COUNT(id) FROM peers WHERE to_go = 0 GROUP BY torrent, userid");
		$numSeeders = $r->rowCount();
		$r = $this->db->query("SELECT COUNT(id) FROM peers WHERE to_go > 0 GROUP BY torrent, userid");
		$numLeechers = $r->rowCount();

		$peers = $this->db->query("SELECT COUNT(id) FROM peers GROUP BY userid");
		$activeClients = $peers->rowCount();

		$dt = time() - $oneDayTimestamp;
		$numActive = $this->get_row_count("users", "WHERE enabled = 'yes' AND last_access > FROM_UNIXTIME($dt)");
		$numUsers = $this->get_row_count("users", "WHERE enabled = 'yes'");
		$num100LeechbonusUsers = $this->get_row_count("users", "WHERE leechbonus = 100");

		$numCat1Torrents = $this->get_row_count("torrents", "WHERE category = 1");
		$numCat2Torrents = $this->get_row_count("torrents", "WHERE category = 2");
		$numCat3Torrents = $this->get_row_count("torrents", "WHERE category = 3");
		$numCat4Torrents = $this->get_row_count("torrents", "WHERE category = 4");
		$numCat5Torrents = $this->get_row_count("torrents", "WHERE category = 5");
		$numCat6Torrents = $this->get_row_count("torrents", "WHERE category = 6");
		$numCat7Torrents = $this->get_row_count("torrents", "WHERE category = 7");
		$numCat8Torrents = $this->get_row_count("torrents", "WHERE category = 8");

		$numCat1NewTorrents = $this->get_row_count("torrents", "WHERE category = 1 AND section = 'new' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat2NewTorrents = $this->get_row_count("torrents", "WHERE category = 2 AND section = 'new' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat3NewTorrents = $this->get_row_count("torrents", "WHERE category = 3 AND section = 'new' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat4NewTorrents = $this->get_row_count("torrents", "WHERE category = 4 AND section = 'new' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat5NewTorrents = $this->get_row_count("torrents", "WHERE category = 5 AND section = 'new' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat6NewTorrents = $this->get_row_count("torrents", "WHERE category = 6 AND section = 'new' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat7NewTorrents = $this->get_row_count("torrents", "WHERE category = 7 AND section = 'new' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat8NewTorrents = $this->get_row_count("torrents", "WHERE category = 8 AND section = 'new' AND added > '$yesterdayDate' AND added < '$todaysDate'");

		$numCat1NewArchiveTorrents = $this->get_row_count("torrents", "WHERE category = 1 AND section = 'archive' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat2NewArchiveTorrents = $this->get_row_count("torrents", "WHERE category = 2 AND section = 'archive' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat3NewArchiveTorrents = $this->get_row_count("torrents", "WHERE category = 3 AND section = 'archive' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat4NewArchiveTorrents = $this->get_row_count("torrents", "WHERE category = 4 AND section = 'archive' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat5NewArchiveTorrents = $this->get_row_count("torrents", "WHERE category = 5 AND section = 'archive' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat6NewArchiveTorrents = $this->get_row_count("torrents", "WHERE category = 6 AND section = 'archive' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat7NewArchiveTorrents = $this->get_row_count("torrents", "WHERE category = 7 AND section = 'archive' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numCat8NewArchiveTorrents = $this->get_row_count("torrents", "WHERE category = 8 AND section = 'archive' AND added > '$yesterdayDate' AND added < '$todaysDate'");

		$numNewUsers = $this->get_row_count("users", "WHERE enabled = 'yes' AND added > '$yesterdayDate' AND added < '$todaysDate'");
		$numForumPosts = $this->get_row_count("posts", "WHERE added > '$yesterdayDate' AND added < '$todaysDate'");
		$numComments = $this->get_row_count("comments", "WHERE added > '$yesterdayDate' AND added < '$todaysDate'");

		$numUserClass0 = $this->get_row_count("users", "WHERE enabled = 'yes' AND class = 0");
		$numUserClass1 = $this->get_row_count("users", "WHERE enabled = 'yes' AND class = 1");
		$numUserClass2 = $this->get_row_count("users", "WHERE enabled = 'yes' AND class = 2");
		$numUserClass3 = $this->get_row_count("users", "WHERE enabled = 'yes' AND class = 3");
		$numUserClass6 = $this->get_row_count("users", "WHERE enabled = 'yes' AND class = 6");
		$numUserClass7 = $this->get_row_count("users", "WHERE enabled = 'yes' AND class = 7");

		$res = $this->db->query('SELECT torrents.size, peers.to_go FROM peers JOIN torrents ON peers.torrent = torrents.id GROUP BY userid, torrent');
		$seedat = 0;
		while($r = $res->fetch(PDO::FETCH_ASSOC)) {
		  $seedat += ($r["size"] - $r["to_go"]);
		}
		$sumTotalShareGb = round($seedat / 1073741824);

		$numUserDesign0 = $this->get_row_count("users", "WHERE enabled = 'yes' AND design = 0");
		$numUserDesign2 = $this->get_row_count("users", "WHERE enabled = 'yes' AND design = 2");
		$numUserDesign3 = $this->get_row_count("users", "WHERE enabled = 'yes' AND design = 3");
		$numUserDesign4 = $this->get_row_count("users", "WHERE enabled = 'yes' AND design = 4");
		$numUserDesign5 = $this->get_row_count("users", "WHERE enabled = 'yes' AND design = 5");
		$numUserDesign6 = $this->get_row_count("users", "WHERE enabled = 'yes' AND design = 6");
		$numUserDesign7 = $this->get_row_count("users", "WHERE enabled = 'yes' AND design = 7");

		$sql = "INSERT INTO statistics(datum, seeders, leechers, activeclients, activeusers, users, newusers, 100leechbonus, cat1torrents, cat2torrents, cat3torrents, cat4torrents, cat5torrents, cat6torrents, cat7torrents, cat8torrents, cat1newtorrents, cat2newtorrents, cat3newtorrents, cat4newtorrents, cat5newtorrents, cat6newtorrents, cat7newtorrents, cat8newtorrents, cat1newarchivetorrents, cat2newarchivetorrents, cat3newarchivetorrents, cat4newarchivetorrents, cat5newarchivetorrents, cat6newarchivetorrents, cat7newarchivetorrents, cat8newarchivetorrents, newforumposts, newcomments, numusersclass0, numusersclass1, numusersclass2, numusersclass3, numusersclass6, numusersclass7, totalsharegb, userdesign0, userdesign2, userdesign3, userdesign4, userdesign5, userdesign6, userdesign7) VALUES('$todaysDateOnlyDay', $numSeeders, $numLeechers, $activeClients, $numActive, $numUsers, $numNewUsers, $num100LeechbonusUsers, $numCat1Torrents, $numCat2Torrents, $numCat3Torrents, $numCat4Torrents, $numCat5Torrents, $numCat6Torrents, $numCat7Torrents, $numCat8Torrents, $numCat1NewTorrents, $numCat2NewTorrents, $numCat3NewTorrents, $numCat4NewTorrents, $numCat5NewTorrents, $numCat6NewTorrents, $numCat7NewTorrents, $numCat8NewTorrents, $numCat1NewArchiveTorrents, $numCat2NewArchiveTorrents, $numCat3NewArchiveTorrents, $numCat4NewArchiveTorrents, $numCat5NewArchiveTorrents, $numCat6NewArchiveTorrents, $numCat7NewArchiveTorrents, $numCat8NewArchiveTorrents, $numForumPosts, $numComments, $numUserClass0, $numUserClass1, $numUserClass2, $numUserClass3, $numUserClass6, $numUserClass7, $sumTotalShareGb, $numUserDesign0, $numUserDesign2, $numUserDesign3, $numUserDesign4, $numUserDesign5, $numUserDesign6, $numUserDesign7)";

		$this->db->query($sql);
	}

	private function get_row_count ($table, $suffix = "") {
		if ($suffix) {
			$suffix = " $suffix";
		}
		$r = $this->db->query("SELECT COUNT(*) FROM $table$suffix");
		$a = $r->fetch();
		return $a[0];
	}
}
