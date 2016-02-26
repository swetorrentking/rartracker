<?php

class Bonus {
	private $db;
	private $user;
	private $torrent;
	private $mailbox;
	private $log;
	private $adminlog;
	private $requests;

	public function __construct($db, $user, $log) {
		$this->db = $db;
		$this->user = $user;
		$this->log = $log;
	}

	public function run() {

		if ($_SERVER['SERVER_ADDR'] != $_SERVER["REMOTE_ADDR"]) {
			throw new Exception("Must be run by server.", 401);
		}

		$totusers = 0;
		$totbonus = 0;

		function up2gb($siffra) {
			return round($siffra/1024/1024/1024);
		}

		$this->db->query("UPDATE users SET pokal = 0");
		$res = $this->db->query("SELECT arkiv_seed, id FROM users WHERE class >= 2 ORDER BY arkiv_seed DESC LIMIT 25");
		while($ros = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET pokal = 1 WHERE id = ".$ros["id"]);
		}
		$res = $this->db->query("SELECT nytt_seed, id FROM users WHERE class >= 2 ORDER BY nytt_seed DESC LIMIT 25");
		while($ros = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET pokal = 1 WHERE id = ".$ros["id"]);
		}

		$datum = date("Y-m-d H:i:s", time()-604800);
		$datumnix = time()-604800;
		$datumr = date("Y-m-d H:i:s", time());
		$bonusp = 0;

		$res = $this->db->query("SELECT uploaded, arkiv_seed, id, nytt_seed, username FROM users WHERE class > 0 AND enabled = 'yes'");
		while($ros = $res->fetch(PDO::FETCH_ASSOC)) {
			$torrp = 0;
			$requestReward = 0;
			if ($ros["arkiv_seed"] <= 0) {
				$bonusp = 0;
				$bonus_nytt = up2gb($ros["nytt_seed"]);
			}	else {
				$bonusp = up2gb($ros["arkiv_seed"]);
				$bonus_nytt = up2gb($ros["nytt_seed"]);
			}

			$bonusu = $bonusp;

			if ($bonusp > 50) {
				$bonusp = 50;
			}

			$subs = $this->db->query("SELECT * FROM subs WHERE datum > $datumnix AND userid = $ros[id] GROUP BY torrentid;");
			$antalsubs = $subs->rowCount();
			$subp = 0;
			while($sub = $subs->fetch(PDO::FETCH_ASSOC)) {

				$subp += 2;

			}

			$torrb = $this->db->query("SELECT owner, reqid, id, UNIX_TIMESTAMP(added) as added1, category FROM torrents WHERE added > '$datum' AND owner = $ros[id] AND reqid <> 1;");
			$torrantal = 0;
			$torrantal = $torrb->rowCount();
			while($torr = $torrb->fetch(PDO::FETCH_ASSOC)) {

				if ($torr["reqid"] > 1) {

					$req = $this->db->query("SELECT UNIX_TIMESTAMP(requests.added) as added2, (SELECT SUM(krydda) FROM reqvotes WHERE reqid = requests.id) AS krydda FROM requests WHERE id = $torr[reqid]");

					$reg = $req->fetch(PDO::FETCH_ASSOC);

					$hitte = $torr["added1"] - $reg["added2"];
					$hitte = round($hitte/86400);
					$hitte+=2;
					$requestReward += $reg["krydda"] + $hitte;

				} else {
					if ($torr["reqid"] == 0) {

				 	 	if ($torr["category"] > Torrent::MOVIE_1080P || $torr["category"] == Torrent::BLURAY) {
				 	 		$torrp += 5;
				 	 	}
				 	 	else {
				 	 		$torrp += 10;
				 	 	}

				 	 }
				}

			}

			$slutbonus = $bonusp + $torrp + $subp + $requestReward;
			if ($slutbonus > 0 || $bonus_nytt > 0 || $subp > 0) {
				$totusers++;
				$totbonus += $slutbonus;

				$msg = "Bonusutdelning. ";
				if ($bonusp>0 || $bonus_nytt > 0) {
					$msg .= $bonusp."p för ".($bonusu+$bonus_nytt)."GB uppladdat, ".$bonusu."GB på arkiv, ".$bonus_nytt."GB på nytt. ";
				}

				if ($torrantal > 0) {
					$msg .= $torrp."p för $torrantal st uppladdad".($torrantal == 1 ? '' : 'e')." nya torrents. ";
				}

				if ($requestReward > 0) {
					$msg .= $requestReward."p för hittelön på uppladdade requests. ";
				}

				if ($subp > 0) {
					$msg .= $subp."p för $antalsubs st uppladdad".($antalsubs == 1 ? '' : 'e')." undertexter. ";
				}

				$this->user->bonusLog($slutbonus, $msg, $ros["id"]);
			}

		}

		$this->db->query("UPDATE users SET nytt_seed = 0, arkiv_seed = 0");

		$this->log->log(0, "Bonusutdelning utförd. Totalt [b]{$totbonus}p[/b] till {$totusers} användare.", $this->user->getId());
	}
}
