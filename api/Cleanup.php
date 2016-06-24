<?php

class Cleanup {
	/* Settings for cleanup */
	private $signup_timeout = 2;
	private $max_dead_torrent_time_hours = 6;
	private $announce_interval_minutes = 60;
	private $max_inactive_user_days = 180;
	private $invite_code_expiration_days = 7;
	private $max_free_leech_days = 14;
	private $ratio_warning_length = 5;
	private $ratio_warning_minimum_gb = 20;
	private $ratio_warning_minimum_ratio = 0.5;
	private $move_to_archive_after_days = 30;
	private $delete_inactive_torrents_after_days = 30;
	private $delete_inactive_requests_after_days = 60;
	private $delete_unseeded_torrents_after_minutes = 30;
	private $demote_uploaders_after_days_inactive = 60;
	private $delete_messages_after_days = 30;
	private $delete_logs_after_days = 60;
	private $peer_deadtime;
	private $datetime;
	private $userClassPromotions;

	private $db;
	private $user;
	private $torrent;
	private $mailbox;
	private $log;
	private $adminlog;
	private $requests;

	public function __construct($db, $user, $torrent, $log, $adminlog, $mailbox, $requests) {
		$this->db = $db;
		$this->user = $user;
		$this->torrent = $torrent;
		$this->mailbox = $mailbox;
		$this->log = $log;
		$this->adminlog = $adminlog;
		$this->requests = $requests;

		$this->peer_deadtime = time() - floor($this->announce_interval_minutes * 60 * 1.2);
		$this->datetime = date("Y-m-d H:i:s");

		$this->userClassPromotions = array(
			array(
				"minratio" => 1.10,
				"className" => "Regissör",
				"classId" => 3,
				"minimumGigabyteUpload" => 1200,
				"minimumMemberDays" => 210,
				"perks" => "* Regissör-forumet"),
			array(
				"minratio" => 1.10,
				"className" => "Filmstjärna",
				"classId" => 2,
				"minimumGigabyteUpload" => 300,
				"minimumMemberDays" => 105,
				"perks" => "* Se alla topplistor\nSe avancerad statistik\nIP-loggning avstängd. Alla befintliga IP-loggar är rensade"),
			array(
				"minratio" => 1.05,
				"className" => "Skådis",
				"classId" => 1,
				"minimumGigabyteUpload" => 50,
				"minimumMemberDays" => 14,
				"perks" => "* Requestsystemet\n* Bonussystemet\n* Se loggen\n* Bjuda in nya användare (du har fått 2 inbjudningar)")
			);
	}

	public function run() {

		if ($_SERVER['SERVER_ADDR'] != $_SERVER["REMOTE_ADDR"]) {
			throw new Exception("Must be run by server.", 401);
		}

		/* Delete dead peers and correct all seeders, leechers amounts */
		$this->db->query("DELETE FROM peers WHERE last_action < FROM_UNIXTIME(".$this->peer_deadtime.")");

		$torrents = array();
		$res = $this->db->query("SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			if ($row["seeder"] == "yes")
				$key = "seeders";
			else
				$key = "leechers";
			$torrents[$row["torrent"]][$key] = $row["c"];
		}

		$fields = explode(":", "leechers:seeders");
		$res = $this->db->query("SELECT id, seeders, leechers FROM torrents");
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$id = $row["id"];
			$torr = array();
			if ($torrents[$id]) {
				$torr = $torrents[$id];
			}
			foreach ($fields as $field) {
				if (!isset($torr[$field]))
					$torr[$field] = 0;
			}
			$update = array();
			foreach ($fields as $field) {
				if ($torr[$field] != $row[$field])
					$update[] = "$field = " . $torr[$field];
			}
			if (count($update)) {
				$this->db->query("UPDATE torrents SET " . implode(",", $update) . " WHERE id = $id");
			}
		}

		/* Disabled inactive user accounts */
		$reason = "Automatiskt avstängd pga inaktivitet";
		$dt = time() - $this->max_inactive_user_days * 86400;
		$maxclass = 7;
		$res = $this->db->query("SELECT id FROM users WHERE class < $maxclass AND last_access < FROM_UNIXTIME($dt) AND parkerad = 0 AND enabled = 'yes'");
		$text = date("Y-m-d") . " - " .$reason .= "\n";
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET modcomment = concat('$text', modcomment), secret = '$reason' WHERE id = " . $row["id"]);
		}
		$this->db->query("UPDATE users SET enabled = 'no' WHERE class < $maxclass AND last_access < FROM_UNIXTIME($dt) AND parkerad = 0");

		/* Remove unused invit codes */
		$maxdt = time() - 86400 * $this->invite_code_expiration_days;
		$res = $this->db->query("SELECT * FROM invites WHERE skapad < FROM_UNIXTIME($maxdt)");
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("DELETE FROM invites WHERE id = " . $arr["id"]);
			$this->db->query("UPDATE users SET invites = invites + 1 WHERE id = " . $arr["userid"]);
			$this->mailbox->sendSystemMessage($arr["userid"], "Obekräftad inbjudan", "En skapad invite-länk är efter en vecka oanvänd. Den har därför annulerats och du har fått tillbaka din inbjudan.");
		}

		/* Remove free leech from new torrents */
		$dt = time() - $self->max_free_leech_days * 86400;
		$this->db->query("UPDATE torrents SET frileech = 0 WHERE section = 'new' AND added < FROM_UNIXTIME($dt) AND size < 16106127360");


		/* Bad ratio warning */
		$limit = $limit*1024*1024*1024;
		$siteName = Config::NAME;
		$msg = <<<EOD
			[b]*** VIKTIGT MEDDELANDE ***[/b]

			Du har automatiskt fått en varning eftersom din ratio är för låg.
			Du har 5 dagar på dig att bättra på din ratio.
			Om din ratio fortfarande ligger under 0.5 efter {$this->ratio_warning_length} dagar blir ditt konto automatiskt avaktiverat, annars plockas varningen bort.

			* En privat torrentsida bygger på ett givande och tagande. Låt alla torrents du laddat ner ligga kvar i din klient och på datorn så länge du kan.
			* Be en vän använda sina bonuspoäng för att bättra på din ratio
			* Du kan donera en slant till {$siteName} vilket samtidigt ökar din ratio. [url=/donate]Donera[/url]

			Lycka till!
EOD;
		$min_downloaded = $this->ratio_warning_minimum_gb*1024*1024*1024;
		$warned_until = time() + $this->ratio_warning_length*86400;
		$res = $this->db->query("SELECT id, username FROM users WHERE class = 0 AND enabled = 'yes' AND downloaded > {$min_downloaded} AND uploaded / downloaded < {$this->ratio_warning_minimum_ratio} AND warned = 'no'");
		$modcomment = date("Y-m-d") . " - Automatiskt varnad för dålig ratio\n";
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET warned = 'yes', warneduntil = FROM_UNIXTIME({$warned_until}), modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->mailbox->sendSystemMessage($arr["id"], "Ratiovarning!", $msg);
			$this->adminlog->create("[url=/user/".$arr["id"] ."/".$arr["username"]."][b]".$arr["username"]."[/b][/url] har automatiskt mottagit en varning för dålig ratio.");
		}

		/* Ban when warning expired and ratio still bad */
		$res = $this->db->query("SELECT id, ip, username, modcomment FROM users WHERE class = 0 AND warned = 'yes' AND warneduntil < NOW() AND enabled = 'yes' AND downloaded > $limit AND uploaded / downloaded < {$this->ratio_warning_minimum_ratio} AND donor = 'no'");
		$modcomment = date("Y-m-d") . " - Automatiskt avstängd pga dålig ratio trots varning\n";
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET enabled = 'no', modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->adminlog->create("[url=/user/".$arr["id"] ."/".$arr["username"]."][b]".$arr["username"]."[/b][/url] blev automatiskt avstängd pga dålig ratio trots varning.");
		}

		/* Remove expired warnings */
		$res = $this->db->query("SELECT id FROM users WHERE warned = 'yes' AND warneduntil < NOW() AND warneduntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__,__LINE__);
		$modcomment = date("Y-m-d") . " - Varning automatiskt borttagen\n";
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET warned = 'no', warneduntil = '0000-00-00 00:00:00', modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->mailbox->sendSystemMessage($arr["id"], "Varning borttagen", "Din varning har automatiskt blivit borttagen!");
		}

		/* Move torrents from New to Archive */
		$dt = time() - $this->move_to_archive_after_days * 86400;
		$res = $this->db->query("SELECT id FROM torrents WHERE added < FROM_UNIXTIME({$dt}) AND section = 'new'");
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE torrents SET section = 'archive' WHERE id =  ". $arr["id"]);
			$this->db->query("UPDATE peers SET section = 'new' WHERE torrent = ". $arr["id"]);
		}

		/* Delete inactive torrents */
		$dt = time() - $this->delete_inactive_torrents_after_days * 86400;
		$res = $this->db->query("SELECT id, name, reqid FROM torrents WHERE last_action < FROM_UNIXTIME({$dt}) AND seeders = 0 AND leechers = 0 AND section = 'archive'");

		/* Prevent deletion of "all" torrents if site has been offline or similiar */
		if ($res->rowCount() < 100) {
			while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
				$this->torrent->delete($arr["id"], "Ingen aktivitet på {$this->delete_inactive_torrents_after_days} dagar.");
			}
		} else {
			$this->adminlog->create("{{username}} försökte radera över {$res->rowCount()} torrents pga inaktivitet, men detta tillåts inte.");
		}

		/* Delete new unseeded torrents
		/*
		$dt = time() - $this->delete_unseeded_torrents_after_minutes * 60;
		$dtmax = time() - 86400;
		$res = $this->db->query("SELECT id, name, reqid FROM torrents WHERE added < FROM_UNIXTIME({$dt}) AND added > FROM_UNIXTIME({$dtmax}) AND seeders = 0 AND leechers > 0 AND reqid = 0");
		/* Prevent deletion of lots of torrents if site has been offline or similiar
		if ($res->rowCount() < 10) {
			while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
				$this->torrent->delete($arr["id"], "Ingen seed på över {$this->delete_unseeded_torrents_after_minutes} minuter.", 1);
			}
		} else {
			$this->adminlog->create("{{username}} försökte radera över {$res->rowCount()} torrents pga inaktivitet, men detta tillåts inte.");
		}
		*/

		/* Delete inactive requests */
		$dt = time() - $this->delete_inactive_requests_after_days * 86400;
		$res = $this->db->query("SELECT id FROM requests WHERE added < FROM_UNIXTIME({$dt}) AND filled = 0");
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->requests->delete($arr["id"], "Request ej fylld efter {$this->delete_inactive_requests_after_days} dagar.");
		}

		/* Demote inactive Uploaders */
		$dt = time() - $this->demote_uploaders_after_days_inactive * 86400;
		$res = $this->db->query("SELECT users.id, users.username FROM `users` WHERE class = 6 AND (SELECT added FROM torrents WHERE owner = users.id ORDER BY `added` DESC LIMIT 1) < FROM_UNIXTIME({$dt})");
		$modcomment = date("Y-m-d") . " - Automatiskt nedgraderad från Uppladdare på grund av inaktivitet.\n";
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)){
			$this->db->query("UPDATE users SET class = 1, modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->adminlog->create("[url=/user/".$arr["id"] ."/".$arr["username"]."][b]".$arr["username"]."[/b][/url] blev nedgraderad från Uppladdare på grund av inaktivitet.");
			$this->mailbox->sendSystemMessage($arr["id"], "Nedgraderad", "Du har automatiskt blivit nedgraderad ifrån uppladdare eftersom du inte laddat upp någon ny torrent på ".$this->demote_uploaders_after_days_inactive." dagar.");
		}

		/* Delete old inbox messages */
		$dt = time() - $this->delete_messages_after_days * 86400;
		$this->db->query("DELETE FROM messages WHERE last < FROM_UNIXTIME({$dt}) AND saved = 0 AND unread = 'no';");

		/* Delete old logs */
		$dt = time() - $this->delete_logs_after_days * 86400;
		$this->db->query("DELETE FROM sitelog WHERE added < FROM_UNIXTIME({$dt})");

		/* Give gold coin icon to users invited users with high leech bonus */
		$res = $this->db->query("SELECT invited_by, username FROM users WHERE leechbonus >= 25 AND invited_by > 1");
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$who = $this->db->query("SELECT id, coin FROM users WHERE id = " . $arr["invited_by"]);
			while ($arr2 = $who->fetch(PDO::FETCH_ASSOC)) {
				if ($arr2["coin"] == 0) {
					$this->db->query("UPDATE users SET coin = 1 WHERE id = ". $arr2["id"]);
					$this->mailbox->sendSystemMessage($arr2["id"], "Guldpeng!", "Du har automatiskt fått statusikonen Guldpeng eftersom du bjudit in [b]".$arr["username"]."[/b] som nått minst 25% i Leechbonus. Grattis!");
				}
			}
		}

		/* Update which suggestions that are "hot" */
		$this->db->query('UPDATE suggestions SET hotpoints = 0');
		$res = $this->db->query('SELECT id, suggestid FROM `topics` WHERE suggestid > 0');
		$dt = time() - 30 * 86400;
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$re = $this->db->query("SELECT COUNT(*) FROM `posts` WHERE topicid = ".$arr["id"]." AND added > FROM_UNIXTIME({$dt})");
			$re = $re->fetch();
			$this->db->query('UPDATE suggestions SET hotpoints = ' . $re[0] . ' WHERE id = ' . $arr["suggestid"]);
		}

		/* Update forum posts amount on suggestions */
		$res = $this->db->query("SELECT id, topicid FROM suggestions");
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$t = $this->db->query("SELECT COUNT(*) FROM posts WHERE topicid = " . $row["topicid"]);
			$r = $t->fetch();
			if ($r[0] > 0) {
				$this->db->query("UPDATE suggestions SET comments = ".($r[0] - 1)." WHERe id = " . $row["id"]);
			}
		}

		foreach ($this->userClassPromotions as $class) {
			$limit = $class["minimumGigabyteUpload"] * 1024*1024*1024;
			$dt = time() - 86400 * $class["minimumMemberDays"];
			$message = "Du har automatiskt blivit befodrad till [b]" . $class["className"] ."[/b], grattis!";
			if ($class["perks"]) {
				$message .= "\n\nSom " . $class["className"] ." får du tillgång till följande fördelar:\n\n". $class["perks"];
			}
			$modcomment = date("Y-m-d") . " - Automatiskt uppgraderad till ".$class["className"]."\n";

			$res = $this->db->query("SELECT id, class, doljuploader, title FROM users WHERE class < ".$class["classId"]." AND uploaded >= ".$limit." AND uploaded / downloaded >= ".$class["minratio"]." AND added < FROM_UNIXTIME({$dt})");
			while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
				$this->mailbox->sendSystemMessage($arr["id"], "Uppgraderad till " . $class["className"], $message);
				$this->db->query("UPDATE users SET class = ".$class["classId"].", modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
				if ($class["classId"] >= 2) {
					$this->db->query('DELETE FROM iplog WHERE userid = ' . $arr["id"]);
				}
			}
		}

		/* Demote users with bad ratio */
		$modcomment = date("Y-m-d") . " - Automatiskt nedgraderad till Statist\n";
		$res = $this->db->query("SELECT id, class FROM users WHERE class > 0 AND class < 4 AND uploaded / downloaded < 0.90");
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET class = 0, modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->mailbox->sendSystemMessage($arr["id"], "Nedgraderad till Statist", "Du har automatiskt blivit nedgraderad till [b]Statist[/b] eftersom din ratio har understigit 0.90.");
		}

		/* Update peer record */
		$peers = $this->db->query("SELECT COUNT(DISTINCT userid, torrent) FROM peers");
		$peers = $peers->fetch();
		$peersRecord = $this->db->query("SELECT value_i FROM settings WHERE arg = 'peers_rekord'");
		$peersRecord = $peersRecord->fetch();
		if ($peers[0] > $peersRecord[0]) {
			$sth = $this->db->prepare("UPDATE settings SET value_i = ? WHERE arg = 'peers_rekord'");
			$sth->bindParam(1, $peers[0], PDO::PARAM_INT);
			$sth->execute();
		}
	}
}
