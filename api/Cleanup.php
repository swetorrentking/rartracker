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
				"className" => Config::$userClasses[3],
				"classId" => 3,
				"minimumGigabyteUpload" => 1200,
				"minimumMemberDays" => 210,
				"perks" => L::get("CLASS_3_PERKS")),
			array(
				"minratio" => 1.10,
				"className" => Config::$userClasses[2],
				"classId" => 2,
				"minimumGigabyteUpload" => 300,
				"minimumMemberDays" => 105,
				"perks" => L::get("CLASS_2_PERKS")),
			array(
				"minratio" => 1.05,
				"className" => Config::$userClasses[1],
				"classId" => 1,
				"minimumGigabyteUpload" => 50,
				"minimumMemberDays" => 14,
				"perks" => L::get("CLASS_1_PERKS"))
			);
	}

	public function run() {

		if ($_SERVER['SERVER_ADDR'] != $_SERVER["REMOTE_ADDR"]) {
			throw new Exception(L::get("MUST_BE_RUN_BY_SERVER_ERROR"), 401);
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
		$reason = L::get("AUTO_DISABLED_INACTIVITY");
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
		$res = $this->db->query("SELECT invites.id, invites.userid, users.username, users.language FROM invites LEFT JOIN users ON invites.userid = users.id WHERE skapad < FROM_UNIXTIME($maxdt)");
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("DELETE FROM invites WHERE id = " . $arr["id"]);
			$this->db->query("UPDATE users SET invites = invites + 1 WHERE id = " . $arr["userid"]);
			$this->mailbox->sendSystemMessage($arr["userid"], L::get("UNCONFIRMED_INVITE_PM_SUBJECT", null, $arr["language"]), L::get("UNCONFIRMED_INVITE_PM_BODY", null, $arr["language"]));
		}

		/* Remove free leech from new torrents */
		$dt = time() - $self->max_free_leech_days * 86400;
		$this->db->query("UPDATE torrents SET frileech = 0 WHERE section = 'new' AND added < FROM_UNIXTIME($dt) AND size < 16106127360");


		/* Bad ratio warning */
		$limit = $limit*1024*1024*1024;
		$siteName = Config::NAME;
		$min_downloaded = $this->ratio_warning_minimum_gb*1024*1024*1024;
		$warned_until = time() + $this->ratio_warning_length*86400;
		$res = $this->db->query("SELECT id, username, language FROM users WHERE class = 0 AND enabled = 'yes' AND downloaded > {$min_downloaded} AND uploaded / downloaded < {$this->ratio_warning_minimum_ratio} AND warned = 'no'");
		$modcomment = date("Y-m-d") . " - " . L::get("RATIO_WARNING_LOG") . "\n";
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET warned = 'yes', warneduntil = FROM_UNIXTIME({$warned_until}), modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->mailbox->sendSystemMessage($arr["id"], L::get("RATIO_WARNING_PM_SUBJECT", null, $arr["language"]), L::get("RATIO_WARNING_PM_BODY", [$this->ratio_warning_length, $siteName], $arr["language"]));
			$this->adminlog->create(L::get("RATIO_WARNING_ADMIN_LOG", [$arr["id"], $arr["username"], $arr["username"]]));
		}

		/* Ban when warning expired and ratio still bad */
		$res = $this->db->query("SELECT id, ip, username, modcomment, language FROM users WHERE class = 0 AND warned = 'yes' AND warneduntil < NOW() AND enabled = 'yes' AND downloaded > $limit AND uploaded / downloaded < {$this->ratio_warning_minimum_ratio} AND donor = 'no'");
		$modcomment = date("Y-m-d") . " - " .L::get("BAD_RATIO_AUTO_DISABLED"). "\n";
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET enabled = 'no', modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->adminlog->create(L::get("BAD_RATIO_AUTO_DISABLED_ADMIN_LOG", [$arr["id"], $arr["username"], $arr["username"]]));
		}

		/* Remove expired warnings */
		$res = $this->db->query("SELECT id, language FROM users WHERE warned = 'yes' AND warneduntil < NOW() AND warneduntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__,__LINE__);
		$modcomment = date("Y-m-d") . " - Varning automatiskt borttagen\n";
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET warned = 'no', warneduntil = '0000-00-00 00:00:00', modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->mailbox->sendSystemMessage($arr["id"], L::get("WARNING_REMOVED_PM_SUBJECT", null, $arr["language"]), L::get("WARNING_AUTO_REMOVED_PM_BODY", null, $arr["language"]));
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
				$this->torrent->delete($arr["id"], L::get("AUTO_DELETE_INACTIVE_TORRENT", [$this->delete_inactive_torrents_after_days]));
			}
		} else {
			$this->adminlog->create(L::get("AUTO_DELETE_INACTIVE_TORRENTS_PREVENTED", [$res->rowCount()]));
		}

		/* Delete new unseeded torrents
		/*
		$dt = time() - $this->delete_unseeded_torrents_after_minutes * 60;
		$dtmax = time() - 86400;
		$res = $this->db->query("SELECT id, name, reqid FROM torrents WHERE added < FROM_UNIXTIME({$dt}) AND added > FROM_UNIXTIME({$dtmax}) AND seeders = 0 AND leechers > 0 AND reqid = 0");
		/* Prevent deletion of lots of torrents if site has been offline or similiar
		if ($res->rowCount() < 10) {
			while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
				$this->torrent->delete($arr["id"], L::get("AUTO_DELETE_UNSEEDED_TORRENTS", [$this->delete_unseeded_torrents_after_minutes]), 1);
			}
		} else {
			$this->adminlog->create(L::get("AUTO_DELETE_INACTIVE_TORRENTS_PREVENTED", [$res->rowCount()]));
		}
		*/

		/* Delete inactive requests */
		$dt = time() - $this->delete_inactive_requests_after_days * 86400;
		$res = $this->db->query("SELECT id FROM requests WHERE added < FROM_UNIXTIME({$dt}) AND filled = 0");
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->requests->delete($arr["id"], L::get("REQUEST_NOT_FILLED", [$this->delete_inactive_requests_after_days]));
		}

		/* Demote inactive Uploaders */
		$dt = time() - $this->demote_uploaders_after_days_inactive * 86400;
		$res = $this->db->query("SELECT users.id, users.username, users.language FROM `users` WHERE class = 6 AND (SELECT added FROM torrents WHERE owner = users.id ORDER BY `added` DESC LIMIT 1) < FROM_UNIXTIME({$dt})");
		$modcomment = date("Y-m-d") . " - ".L::get("UPLOADED_AUTO_DOWNGRADED").".\n";
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)){
			$this->db->query("UPDATE users SET class = 1, modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->adminlog->create(L::get("UPLOADED_AUTO_DOWNGRADED_ADMIN_LOG", [$arr["id"], $arr["username"], $arr["username"]]));
			$this->mailbox->sendSystemMessage($arr["id"], ucfirst(L::get("STATUS_DOWNGRADED", null, $arr["language"])), L::get("UPLOADED_AUTO_DOWNGRADED_PM_BODY", [$this->demote_uploaders_after_days_inactive], $arr["language"]));
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
			$who = $this->db->query("SELECT id, coin, language FROM users WHERE id = " . $arr["invited_by"]);
			while ($arr2 = $who->fetch(PDO::FETCH_ASSOC)) {
				if ($arr2["coin"] == 0) {
					$this->db->query("UPDATE users SET coin = 1 WHERE id = ". $arr2["id"]);
					$this->mailbox->sendSystemMessage($arr2["id"], L::get("GOLD_COIN_PM_SUBJECT", null, $arr2["language"]), L::get("GOLD_COIN_PM_BODY", [$arr["username"]], $arr2["language"]));
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
			$message = L::get("AUTO_PROMOTED_PM_BODY", [$class["className"]]);
			if ($class["perks"]) {
				$message .= "\n\n". L::get("AUTO_PROMOTED_PM_PERKS", [$class["className"]]) . "\n\n". $class["perks"];
			}
			$modcomment = date("Y-m-d") . " - " . L::get("AUTO_PROMOTED_LOG", [$class["className"]]) ."\n";

			$res = $this->db->query("SELECT id, class, doljuploader, title, language FROM users WHERE class < ".$class["classId"]." AND uploaded >= ".$limit." AND uploaded / downloaded >= ".$class["minratio"]." AND added < FROM_UNIXTIME({$dt})");
			while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
				$this->mailbox->sendSystemMessage($arr["id"], L::get("AUTO_PROMOTED_PM_SUBJECT", [$class["className"]], $arr["language"]), $message);
				$this->db->query("UPDATE users SET class = ".$class["classId"].", modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
				if ($class["classId"] >= 2) {
					$this->db->query('DELETE FROM iplog WHERE userid = ' . $arr["id"]);
				}
			}
		}

		/* Demote users with bad ratio */
		$modcomment = date("Y-m-d") . " - ".L::get("AUTO_DEMOTED_TO_CLASS_1")."\n";
		$res = $this->db->query("SELECT id, class, language FROM users WHERE class > 0 AND class < 4 AND uploaded / downloaded < 0.90");
		while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
			$this->db->query("UPDATE users SET class = 0, modcomment = concat('{$modcomment}', modcomment) WHERE id = " . $arr["id"]);
			$this->mailbox->sendSystemMessage($arr["id"], L::get("DEMOTED_TO_CLASS_1_PM_SUBJECT", null, $arr["language"]), L::get("DEMOTED_TO_CLASS_1_PM_BODY", null, $arr["language"]));
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
