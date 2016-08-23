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
			throw new Exception(L::get("MUST_BE_RUN_BY_SERVER_ERROR"), 401);
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
				$bonusNew = up2gb($ros["nytt_seed"]);
			}	else {
				$bonusp = up2gb($ros["arkiv_seed"]);
				$bonusNew = up2gb($ros["nytt_seed"]);
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

			$torrb = $this->db->query("SELECT owner, reqid, section, id, UNIX_TIMESTAMP(added) as added1, category FROM torrents WHERE added > '$datum' AND owner = $ros[id];");
			$torrantal = 0;
			$torrantal = $torrb->rowCount();
			while($torr = $torrb->fetch(PDO::FETCH_ASSOC)) {

				if ($torr["reqid"] > 0) {

					$req = $this->db->query("SELECT UNIX_TIMESTAMP(requests.added) as added2, (SELECT SUM(krydda) FROM reqvotes WHERE reqid = requests.id) AS krydda FROM requests WHERE id = $torr[reqid]");

					$reg = $req->fetch(PDO::FETCH_ASSOC);

					$hitte = $torr["added1"] - $reg["added2"];
					$hitte = round($hitte/86400);
					$hitte+=2;
					$requestReward += $reg["krydda"] + $hitte;

				} else if($torr["section"] == 'new') {

			 	 	if ($torr["category"] > Config::$categories["MOVIE_1080P"]["id"] || $torr["category"] == Config::$categories["BLURAY"]["id"]) {
			 	 		$torrp += 5;
			 	 	}
			 	 	else {
			 	 		$torrp += 10;
			 	 	}

				}

			}

			$finalBonus = $bonusp + $torrp + $subp + $requestReward;
			if ($finalBonus > 0 || $bonusNew > 0 || $subp > 0) {
				$totusers++;
				$totbonus += $finalBonus;

				$msg = L::get("BONUS_PAYOUT") . " ";
				if ($bonusp>0 || $bonusNew > 0) {
					$msg .= L::get("BONUS_PAYOUT_ROW", [$bonusp, $bonusu+$bonusNew, $bonusu, $bonusNew]);
				}

				if ($torrantal > 0) {
					$msg .= L::get("BONUS_PAYOUT_TORRENTS", [$torrp, $torrantal]);
				}

				if ($requestReward > 0) {
					$msg .= L::get("BONUS_PAYOUT_REQUESTS", [$requestReward]);
				}

				if ($subp > 0) {
					$msg .= L::get("BONUS_PAYOUT_SUBTITLES", [$subp, $antalsubs]);
				}

				$this->user->bonusLog($finalBonus, $msg, $ros["id"]);
			}

		}

		$this->db->query("UPDATE users SET nytt_seed = 0, arkiv_seed = 0");

		$this->log->log(0, L::get("BONUS_PAYOUT_LOG", [$totbonus, $totusers]), $this->user->getId());
	}
}
