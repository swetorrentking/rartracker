<?php

class Torrent {
	private $db;
	private $user;
	private $log;
	private $movieData;
	private $sweTv;
	private $requests;
	private $mailbox;
	private $subtitles;
	private $adminlog;
	private $torrentDir = "../torrents/";
	private $subsDir = "../subs/";
	public static $torrentFieldsUser = array('torrents.id', 'name', 'category', 'size', 'torrents.added', 'type', 'numfiles', 'comments', 'times_completed', 'leechers', 'seeders', 'reqid', 'torrents.section', 'torrents.frileech', 'torrents.imdbid', 'p2p', 'swesub', 'sweaudio', 'pack', '3d');

	const DVDR_PAL = 1;
	const DVDR_CUSTOM = 2;
	const DVDR_TV = 3;
	const MOVIE_720P = 4;
	const MOVIE_1080P = 5;
	const TV_720P = 6;
	const TV_1080P = 7;
	const TV_SWE = 8;
	const AUDIOBOOKS = 9;
	const EBOOKS = 10;
	const EPAPERS = 11;
	const MUSIC = 12;
	const BLURAY = 13;
	const SUBPACK = 14;
	const MOVIE_4K = 15;

	public function __construct($db, $user = null, $log = null, $movieData = null, $sweTv = null, $requests = null, $mailbox = null, $subtitles = null, $adminlog = null) {
		$this->db = $db;
		$this->user = $user;
		$this->log = $log;
		$this->adminlog = $adminlog;
		$this->movieData = $movieData;
		$this->sweTv = $sweTv;
		$this->requests = $requests;
		$this->mailbox = $mailbox;
		$this->subtitles = $subtitles;
	}

	public function search($params) {
		$limit = (int) $params["limit"];
		if (!$limit) {
			$limit = 20;
		}

		$index = (int) $params["index"];
		if (!$index) {
			$index = 0;
		}

		$catStr = "";
		if ($params["categories"]) {

			foreach($params["categories"] as &$cat) {
				$cat = (int) $cat;
			}

			$catStr = implode($params["categories"], ',');
		}

		switch ($params["sort"]) {
			case 'c': $sortColumn = 'torrents.comments'; break;
			case 's': $sortColumn = 'torrents.size'; break;
			case 'n': $sortColumn = 'torrents.name'; break;
			case 'i': $sortColumn = 'imdbinfo.rating'; break;
			case 'f': $sortColumn = 'torrents.times_completed'; break;
			case 'up': $sortColumn = 'torrents.seeders'; break;
			case 'dl': $sortColumn = 'torrents.leechers'; break;
			default: $sortColumn = 'torrents.id';
		}

		if ($params["order"] == "asc") {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$where = [];
		if (strlen($params["searchText"]) > 0) {

			$searchWords = Helper::searchTextToWordParams($params["searchText"]);

			if (strlen($searchWords) > 0) {
				if ($params["extendedSearch"] == "true") {
					$where[] = '(MATCH (search_text) AGAINST (\''.$searchWords.'\' IN BOOLEAN MODE) OR MATCH (search_text2) AGAINST (\''.$searchWords.'\' IN BOOLEAN MODE))';
				} else {
					$where[] = 'MATCH (search_text) AGAINST (\''.$searchWords.'\' IN BOOLEAN MODE)';
				}
			}

		}

		if ($catStr != '') {
			$where[] = 'category IN ('.$catStr.')';
		}

		if ($params["p2p"] === "true") {
			$where[] = 'p2p = 1';
		} else if ($params["p2p"] === "false") {
			$where[] = 'p2p = 0';
		}

		if ($params["swesub"] == "true") {
			$where[] = "torrents.swesub > 0";
		}

		if ($params["sweaudio"] == "true") {
			$where[] = "sweaudio = 1";
		}

		if ($params["freeleech"] == "true") {
			$where[] = "frileech = 1";
		}

		if ($params["stereoscopic"] == "true") {
			$where[] = "`3d` = 1";
		}

		if ($params["section"] == 'new') {
			$where[] = "section = 'new'";
		} else if ($params["section"] == 'archive') {
			$where[] = "section = 'archive'";
		}

		if ($params["watchview"] === "true") {

			$sth = $this->db->query("SELECT COUNT(*) FROM bevaka JOIN torrents on bevaka.imdbid = torrents.imdbid WHERE (((torrents.category IN(4,5,6,7)) AND torrents.pack = 0 AND bevaka.swesub = 1 AND torrents.swesub = 1) OR ((torrents.category IN(4,5,6,7)) AND bevaka.swesub = 0) OR (torrents.category NOT IN (4,5,6,7))) AND FIND_IN_SET(torrents.category, bevaka.format) AND (category = 2 AND torrents.p2p = 1 OR category <> 2 AND torrents.p2p = 0) AND torrents.pack = 0 AND torrents.3d = 0 AND bevaka.userid = " . $this->user->getId() . (count($where) > 0 ? ' AND '.implode($where, ' AND ' ) : ''));
			$arr = $sth->fetch();
			$totalCount = $arr[0];

			$sth = $this->db->prepare("SELECT imdbinfo.genres, imdbinfo.photo, imdbinfo.rating, imdbinfo.imdbid AS imdbid2, torrents.* FROM bevaka JOIN torrents on bevaka.imdbid = torrents.imdbid LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id WHERE (((torrents.category IN(4,5,6,7)) AND bevaka.swesub = 1 AND torrents.swesub = 1) OR ((torrents.category IN(4,5,6,7)) AND bevaka.swesub = 0) OR (torrents.category NOT IN (4,5,6,7))) AND FIND_IN_SET(torrents.category, bevaka.format) AND (category = 2 AND torrents.p2p = 1 OR category <> 2 AND torrents.p2p = 0) AND torrents.pack = 0 AND torrents.3d = 0 AND bevaka.userid = ? " . (count($where) > 0 ? ' AND '.implode($where, ' AND ' ) : '') ." ORDER BY ".$sortColumn." ".$order." LIMIT ?, ?");
			$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
			$sth->bindParam(2, $index, PDO::PARAM_INT);
			$sth->bindParam(3, $limit, PDO::PARAM_INT);
			$sth->execute();

		} else {

			$sth = $this->db->query('SELECT COUNT(*) FROM torrents LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id ' . (count($where) > 0 ? ' WHERE '.implode($where, ' AND ' ) : ''));
			$arr = $sth->fetch();
			$totalCount = $arr[0];

			$sth = $this->db->prepare('SELECT imdbinfo.genres, imdbinfo.photo, imdbinfo.rating, imdbinfo.imdbid AS imdbid2, '.implode(self::$torrentFieldsUser, ', ').' FROM torrents LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id  ' . (count($where) > 0 ? ' WHERE '.implode($where, ' AND ' ) : '') .' ORDER BY '.$sortColumn.' '.$order.' LIMIT ?, ?');
			$sth->bindParam(1, $index, PDO::PARAM_INT);
			$sth->bindParam(2, $limit, PDO::PARAM_INT);
			$sth->execute();

		}

		return Array($sth->fetchAll(PDO::FETCH_ASSOC), $totalCount);
	}

	public function get($id, $wantUser = false) {
		$sth = $this->db->prepare('SELECT *, FROM_UNIXTIME(pre) AS preDate FROM torrents WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();

		$torrent = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$torrent) {
			throw new Exception(L::get("TORRENT_NOT_FOUND"), 404);
		}

		if ($wantUser) {
			if ($torrent["ano_owner"] == 1 && $this->user->getClass() < USER::CLASS_ADMIN && $torrent["owner"] != $this->user->getId()) {
				$torrent["user"] = null;
			} else {
				try {
					$torrent["user"] = $this->user->get($torrent["owner"]);
					if ($torrent["ano_owner"] == 1) {
						$torrent["user"]["anonymous"] = true;
					}
				} catch(Exception $e) {
					$torrent["user"] = null;
				}
			}
		}
		$torrent["owner"] = null;

		return $torrent;
	}

	public function getPackFolders($torrentId) {
		$sth = $this->db->prepare('SELECT filename FROM packfiles WHERE torrent = ? ORDER BY filename ASC');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getByMovieId($movieId) {
		$sth = $this->db->prepare('SELECT '.implode(self::$torrentFieldsUser, ', ').', imdbinfo.imdbid AS imdbid2, imdbinfo.genres, imdbinfo.rating FROM torrents LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id WHERE torrents.imdbid = ? ORDER BY category ASC');
		$sth->bindParam(1, $movieId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getRelated($movieId, $decludeId) {
		$sth = $this->db->prepare('SELECT imdbinfo.genres, imdbinfo.imdbid AS imdbid2, imdbinfo.rating, '.implode(self::$torrentFieldsUser, ', ').' FROM torrents LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id WHERE torrents.id != ? AND torrents.imdbid = ? ORDER BY torrents.name ASC');
		$sth->bindParam(1, $decludeId, PDO::PARAM_INT);
		$sth->bindParam(2, $movieId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getByIdList($idList) {
		$sth = $this->db->prepare('SELECT imdbinfo.genres, imdbinfo.imdbid AS imdbid2, imdbinfo.rating, '.implode(self::$torrentFieldsUser, ', ').' FROM torrents LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id WHERE FIND_IN_SET(torrents.id, ?) ORDER BY torrents.name ASC');
		$sth->bindParam(1, $idList, PDO::PARAM_STR);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getFiles($torrentId) {
		$sth = $this->db->prepare('SELECT filename, size FROM files WHERE torrent = ? ORDER BY filename ASC');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getPeers($torrentId) {

		$fields = 'peers.ip, port, peers.uploaded, peers.downloaded, to_go, seeder, started, last_action, connectable, userid, agent, finishedat, downloadoffset, uploadoffset, UNIX_TIMESTAMP(started) AS st, UNIX_TIMESTAMP(last_action) AS la';

		$sth = $this->db->prepare('SELECT users.username, users.anonym, users.class, '.$fields.'  FROM peers JOIN users ON peers.userid = users.id WHERE seeder="yes" AND torrent = ? ORDER BY peers.uploaded DESC');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->execute();
		$seeders = array();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			if ($row["anonym"] == "yes" && $this->user->getClass() < User::CLASS_ADMIN && $this->user->getId() != $row["userid"]) {
				$row["userid"] = null;
				$row["username"] = null;
			}
			array_push($seeders, $row);
		}

		$sth = $this->db->prepare('SELECT users.username, users.anonym, users.class, '.$fields.' FROM peers JOIN users ON peers.userid = users.id WHERE seeder="no" AND torrent = ? ORDER BY to_go ASC');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->execute();
		$leechers = array();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			if ($row["anonym"] == "yes"  && $this->user->getClass() < User::CLASS_ADMIN) {
				$row["userid"] = null;
				$row["username"] = null;
			}
			array_push($leechers, $row);
		}

		return array($seeders, $leechers);
	}

	public function getHighlightTorrents($time, $type, $format, $newOrArchive, $sort, $genres) {
		$wherea = array();

		$titel = '';

		if ($time == 0) {
			$daysAgo = date("Y-m-d H:i:s", time() - 172800); // 2 dar
			$wherea[] = 't.added > "' . $daysAgo .'"';
			$timeString = L::get("TORRENT_HIGHLIGHT_TIME_DAY");
		} else if ($time == 1) {
			$week = date("Y-m-d H:i:s", time() - 604800); // 7 dar
			$wherea[] = 't.added > "' . $week.'"';
			$timeString = L::get("TORRENT_HIGHLIGHT_TIME_WEEK");
		}
		else { // 2
			$month = date("Y-m-d H:i:s", time() - 2419200); // 1 månad
			$wherea[] = 't.added > "' . $month.'"';
			$timeString = L::get("TORRENT_HIGHLIGHT_TIME_MONTH");
		}


		if($format == 0) {

			if($type == 0) {
				$wherea[] = 't.category IN (1,2)';
				$formatString = L::get("TORRENT_HIGHLIGHT_CATEGORY_DVD_MOVIES");
			} else {
				$wherea[] = 't.category IN (3)';
				$formatString = L::get("TORRENT_HIGHLIGHT_CATEGORY_DVD_TV");
			}

		} else if ($format == 1) {

			if ($type == 0) {
				$wherea[] = 't.category IN (4)';
				$formatString = L::get("TORRENT_HIGHLIGHT_CATEGORY_HD_MOVIES");
			} else {
				$wherea[] = 't.category IN (6)';
				$formatString = L::get("TORRENT_HIGHLIGHT_CATEGORY_HD_TV");
			}

		} if ($format == 2) {

			if ($type == 0) {
				$wherea[] = 't.category IN (5)';
				$formatString = L::get("TORRENT_HIGHLIGHT_CATEGORY_FULL_HD_MOVIES");
			} else {
				$wherea[] = 't.category IN (7)';
				$formatString = L::get("TORRENT_HIGHLIGHT_CATEGORY_FULL_HD_TV");
			}

		}

		if ($newOrArchive == 0) {
			$wherea[] = "section = 'new'";
			$sectionString = '';
			$year = 'AND i.year >= 2011';
		} else {
			$wherea[] = "section = 'archive'";
			$sectionString = L::get("TORRENT_HIGHLIGHT_SECTION_ARCHIVE");
			$year = '';
		}

		if($sort == 1) {
			$sort = 'ORDER BY t.added DESC';
			$sortString = L::get("TORRENT_HIGHLIGHT_SORT_LATEST");
		} else if ($sort == 2) {
			$sort = 'ORDER BY t.seeders DESC';
			$sortString = L::get("TORRENT_HIGHLIGHT_SORT_POPULAR");
		} else {
			$sort = 'ORDER BY i.rating DESC';
			$sortString = L::get("TORRENT_HIGHLIGHT_SORT_BEST");
		}

		$tgenre = "";
		if ($genres != "") {
			$wherea[] = 'MATCH(search_text) AGAINST ('.$this->db->quote($genres).')';
			$tgenre = strtolower($genres) . ' ';
		}

		$headline = ucfirst(L::get("TORRENT_HIGHLIGHT_STRING", [$timeString, $sortString, $tgenre, $formatString, $sectionString]));

		$where = implode(" AND ", $wherea);

		$sth = $this->db->prepare('SELECT i.imdbid, i.genres, t.name, t.swesub, t.id, t.frileech, t.added, t.reqid FROM torrents AS t LEFT JOIN imdbinfo i ON t.imdbid = i.id WHERE '.$where.' AND i.photo = 1 AND t.imdbid > 0 '.$year.' GROUP BY i.imdbid '.$sort.', seeders ASC LIMIT 6');
		$sth->execute();

		return Array($headline, $sth->fetchAll(PDO::FETCH_ASSOC));
	}

	public function getSweTvGuideTorrents($dateStart, $dateEnd) {
		$sth = $this->db->prepare('SELECT added, DATE(added) AS dateShort, frileech, tv_klockslag, tv_program, tv_episode, tv_info, tv_programid, tv_kanaler.pic FROM `torrents` JOIN tv_kanaler ON tv_kanaler.id = torrents.tv_kanalid WHERE category = 8 AND tv_klockslag >= ? AND tv_klockslag <= ? AND tv_programid > 0 AND torrents.section = \'new\' GROUP BY tv_programid, tv_program ORDER BY tv_klockslag ASC');
		$sth->bindParam(1, $dateStart, PDO::PARAM_INT);
		$sth->bindParam(2, $dateEnd, PDO::PARAM_INT);
		$sth->execute();

		$data = $sth->fetchAll(PDO::FETCH_ASSOC);

		$torrents = array();
		$torrents["date"] = $dateStart;
		$torrents["day"] = array();
		foreach ($data as $row) {
			$sth = $this->db->prepare('SELECT torrents.id, frileech, torrents.added, torrents.name, tv_kanaler.pic FROM torrents JOIN tv_kanaler ON tv_kanaler.id = torrents.tv_kanalid WHERE torrents.tv_programid = ? AND torrents.tv_program = ?');
			$sth->bindParam(1, $row["tv_programid"], PDO::PARAM_INT);
			$sth->bindParam(2, $row["tv_program"], PDO::PARAM_STR);
			$sth->execute();
			$row["torrents"] = $sth->fetchAll(PDO::FETCH_ASSOC);

			$torrents["day"][] = $row;
		}

		return $torrents;
	}

	public function getToplists($limit = 15) {
		$topdata = array();
		$res = $this->db->query("SELECT ".implode(self::$torrentFieldsUser, ', ').", imdbinfo.imdbid AS imdbid2, imdbinfo.genres, imdbinfo.rating, (torrents.size * torrents.times_completed + SUM(p.downloaded)) AS data FROM torrents LEFT JOIN peers AS p ON torrents.id = p.torrent LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id WHERE p.seeder = 'no' GROUP BY torrents.id ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT " . (int) $limit);
		$topdata["active"] = $res->fetchAll(PDO::FETCH_ASSOC);

		$res = $this->db->query("SELECT ".implode(self::$torrentFieldsUser, ', ').", imdbinfo.imdbid AS imdbid2, imdbinfo.genres, imdbinfo.rating, (torrents.size * torrents.times_completed + SUM(p.downloaded)) AS data FROM torrents LEFT JOIN peers AS p ON torrents.id = p.torrent LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id WHERE p.seeder = 'no' GROUP BY torrents.id ORDER BY times_completed DESC LIMIT " . (int) $limit);
		$topdata["downloaded"] = $res->fetchAll(PDO::FETCH_ASSOC);

		$res = $this->db->query("SELECT ".implode(self::$torrentFieldsUser, ', ').", imdbinfo.imdbid AS imdbid2, imdbinfo.genres, imdbinfo.rating, (torrents.size * torrents.times_completed + SUM(p.downloaded)) AS data FROM torrents LEFT JOIN peers AS p ON torrents.id = p.torrent LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id WHERE p.seeder = 'no' AND times_completed > 0 GROUP BY torrents.id ORDER BY data DESC, added ASC LIMIT " . (int) $limit);
		$topdata["data"] = $res->fetchAll(PDO::FETCH_ASSOC);

		$res = $this->db->query("SELECT ".implode(self::$torrentFieldsUser, ', ').", imdbinfo.imdbid AS imdbid2, imdbinfo.genres, imdbinfo.rating, (torrents.size * torrents.times_completed + SUM(p.downloaded)) AS data FROM torrents LEFT JOIN peers AS p ON torrents.id = p.torrent LEFT JOIN imdbinfo ON torrents.imdbid = imdbinfo.id WHERE p.seeder = 'no' AND seeders >= 5 GROUP BY torrents.id ORDER BY seeders / leechers DESC, seeders DESC, added ASC LIMIT " . (int) $limit);
		$topdata["seeded"] = $res->fetchAll(PDO::FETCH_ASSOC);

		return $topdata;
	}

	public function delete($id, $reason, $pmUploader = 0, $pmPeers = 0, $banRelease = 0, $attachTorrentId = 0, $restoreRequest = 0) {
		$sth = $this->db->prepare('SELECT torrents.ano_owner, torrents.owner, torrents.name, torrents.reqid, torrents.pack, torrents.p2p, torrents.size, UNIX_TIMESTAMP(torrents.added) AS added, users.language FROM torrents LEFT JOIN users ON users.id = torrents.owner WHERE torrents.id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$torrent = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$torrent) {
			throw new Exception(L::get("TORRENT_NOT_FOUND"), 404);
		}

		if ($this->user->getClass() < User::CLASS_ADMIN && $this->user->getId() != $torrent["owner"] && $this->user->getId() !== 1) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		if ($attachTorrentId > 0) {
			$betterTorrent = $this->get($attachTorrentId);
		}

		if ($this->user->getClass() < User::CLASS_ADMIN && time() - $torrent["added"] > 604800) {
			throw new Exception(L::get("TORRENT_DELETE_BLOCK"), 401);
		}

		if ($pmPeers == 1) {
			$sth = $this->db->query("SELECT peers.userid, users.language FROM peers LEFT JOIN users ON users.id = peers.userid WHERE peers.torrent = " . $id . " GROUP BY peers.userid");
			while($user = $sth->fetch(PDO::FETCH_ASSOC)) {
				if ($user["userid"] == $this->user->getId() || ($user["userid"] == $torrent["owner"] && $pmUploader == 1)) {
					continue;
				}

				$subject = L::get("TORRENT_SEEDED_DELETED_PM_SUBJECT", null, $user["language"]);
				$message = L::get("TORRENT_SEEDED_DELETED_PM_BODY", [$torrent["name"], $reason], $user["language"]);

				if ($betterTorrent) {
					$message .= "\n\n" . L::get("TORRENT_SEEDED_DELETED_PM_BODY_EXTENDED", [$betterTorrent["id"], $betterTorrent["name"], $betterTorrent["name"]], $user["language"]);
				}

				$this->mailbox->sendSystemMessage($user["userid"], $subject, $message);
			}
		}

		if ($pmUploader == 1 && $torrent["owner"] != $this->user->getId()) {
			$subject = L::get("TORRENT_YOUR_DELETED_PM_SUBJECT", null, $torrent["language"]);
			$message = L::get("TORRENT_YOUR_DELETED_PM_BODY", [$torrent["name"], $reason], $torrent["language"]);

			if ($betterTorrent) {
				$message .= "\n\n" . L::get("TORRENT_SEEDED_DELETED_PM_BODY_EXTENDED", [$betterTorrent["id"], $betterTorrent["name"], $betterTorrent["name"]], $torrent["language"]);
			}

			$this->mailbox->sendSystemMessage($torrent["owner"], $subject, $message);
		}

		if ($banRelease == 1 && $this->user->getClass() >= User::CLASS_ADMIN) {
			$sth = $this->db->prepare("INSERT INTO banned (namn, owner, comment) VALUES(?, ?, ?)");
			$sth->bindParam(1, $torrent["name"], PDO::PARAM_STR);
			$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
			$sth->bindParam(3, $reason, PDO::PARAM_STR);
			$sth->execute();
		}

		if ($torrent["reqid"] > 0) {
			if ($restoreRequest) {
				$this->requests->restore($torrent["reqid"], $reason);
			} else {
				$this->requests->purge($torrent["reqid"]);
			}
		}

		/* Remove free leech from uploader given when uploading torrent */
		if (time() - $torrent["added"] < 86400 && $torrent["reqid"] == 0 && $torrent["p2p"] == 0 && $torrent["pack"] == 0) {
			$newLeech = round(($torrent["size"]/1024/1024) * 0.02) * 100;
			$this->db->query("UPDATE users SET leechstart = FROM_UNIXTIME(UNIX_TIMESTAMP(leechstart) - " . $newLeech . ") WHERE id = " . $torrent["owner"]);
		}

		$this->db->query('DELETE FROM torrents WHERE id = ' . $id);

		foreach (explode(", ","peers, files, comments, packfiles") as $x) {
			$this->db->query("DELETE FROM $x WHERE torrent = $id");
		}

		foreach (explode(", ","bevakasubs, snatch, bookmarks") as $x) {
			$this->db->query("DELETE FROM $x WHERE torrentid = $id");
		}

		@unlink($this->torrentDir . $id . ".torrent");

		$sth = $this->db->query('SELECT * FROM subs WHERE torrentid = ' . $id);
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			@unlink($this->subsDir . $row["filnamn"]);
			$this->db->query("DELETE FROM subs WHERE id = " . $row["id"]);
		}

		if ($torrent["owner"] == $this->user->getId() && $torrent["ano_owner"] == 1) {
			$anonymous = 1;
		} else {
			$anonymous = 0;
		}
		$this->log->log(3, L::get("TORRENT_DELETED_LOG", [$torrent["name"], $reason], Config::DEFAULT_LANGUAGE), $this->user->getId(), $anonymous);
	}

	public function deleteTorrentsInPack($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->prepare('SELECT id, name FROM torrents WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$torrent = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$torrent) {
			throw new Exception(L::get("TORRENT_NOT_FOUND"), 404);
		}

		$sth = $this->db->prepare('SELECT * FROM packfiles WHERE torrent = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();

		$userToPmArray = array();

		while($packfile = $sth->fetch(PDO::FETCH_ASSOC)) {
			$sth2 = $this->db->prepare('SELECT id, name FROM torrents WHERE name = ?');
			$sth2->bindParam(1, $packfile["filename"], PDO::PARAM_STR);
			$sth2->execute();
			$packTorrent = $sth2->fetch(PDO::FETCH_ASSOC);

			if (!$packTorrent) {
				continue;
			}

			$sth3 = $this->db->query("SELECT peers.userid, users.language FROM peers LEFT JOIN users ON users.id = peers.userid WHERE torrent = ".$packTorrent["id"]." GROUP BY userid");
			while($user = $sth3->fetch()) {
				$userToPmArray[$user[0]][] = array("torrent" => $packTorrent["name"], "language" => $user[1]);
			}

			$this->delete($packTorrent["id"], L::get("TORRENT_NOW_EXISTS_IN_PACK"), 0, 0, 0);
		}

		foreach ($userToPmArray as $userid => $torrents) {
			foreach($torrents as $t) {
				$message .= "[b]".$t["torrent"]."[/b]\n";
			}
			$subject = L::get("TORRENT_REPLACED_WITH_PACK_PM_SUBJECT", null, $t["language"]);
			$message = L::get("TORRENT_REPLACED_WITH_PACK_PM_BODY", null, $t["language"]) . "\n\n";
			$message .= L::get("TORRENT_REPLACED_WITH_PACK_PM_BODY_2", null, $t["language"]) . " [url=/torrent/" . $torrent["id"] . "/".$torrent["name"]."][b]".$torrent["name"]."[/b][/url]";

			$this->mailbox->sendSystemMessage($userid, $subject, $message);
		}
	}

	public function multiDelete($options) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$userToPmArray = array();

		if ($options["attachTorrentId"] > 0) {
			$betterTorrent = $this->get($options["attachTorrentId"]);
		}

		foreach($options["torrents"] as $torrentId) {
			$sth2 = $this->db->prepare('SELECT id, name FROM torrents WHERE id = ?');
			$sth2->bindParam(1, $torrentId, PDO::PARAM_INT);
			$sth2->execute();
			$torrent = $sth2->fetch(PDO::FETCH_ASSOC);

			if (!$torrent) {
				continue;
			}

			if ($options["pmPeers"]) {
				$sth3 = $this->db->query("SELECT peers.userid, users.language FROM peers LEFT JOIN users ON users.id = peers.userid WHERE torrent = ".$torrentId." GROUP BY userid");
				while($user = $sth3->fetch()) {
					$userToPmArray[$user[0]][] = array("torrent" => $torrent["name"], "language" => $user[1]);
				}
			}

			$this->delete($torrentId, $options["reason"], 0, 0, 0);
		}

		foreach ($userToPmArray as $userid => $torrents) {
			foreach($torrents as $t) {
				$message .= "[b]".$t['torrent']."[/b]\n";
			}
			$subject = L::get("TORRENT_MULTI_SEEDING_DELETED_PM_TOPIC", null, $t["language"]);
			$message = L::get("TORRENT_REPLACED_WITH_PACK_PM_BODY", null, $t["language"]) . "\n\n";
			$message .= L::get("TORRENT_MULTI_SEEDING_DELETED_REASON", [$options["reason"]], $t["language"]);
			if ($betterTorrent) {
				$message .= "\n\n" . L::get("TORRENT_SEEDED_DELETED_PM_BODY_EXTENDED", [$betterTorrent["id"], $betterTorrent["name"], $betterTorrent["name"]], $t["language"]);
			}
			$this->mailbox->sendSystemMessage($userid, $subject, $message);
		}
	}

	public function update($id, $post) {
		$sth = $this->db->prepare('SELECT id, owner, name, reqid, swesub, imdbid, tv_programid FROM torrents WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$torrent = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$torrent) {
			throw new Exception(L::get("TORRENT_NOT_FOUND"), 404);
		}

		if ($this->user->getClass() < User::CLASS_ADMIN && $this->user->getId() != $torrent["owner"]) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		if ($torrent["swesub"] != $post["swesub"] && $post["swesub"] == 0) {
			$subtitles = $this->subtitles->fetch($torrent["id"]);
			if (count($subtitles) > 0) {
				throw new Exception(L::get("TORRENT_HAS_SUBTITLES"));
			}
		}

		if ($post["imdbid"]) {
			$imdbInfo = $this->movieData->getData($post["imdbid"]);
			$this->movieData->updateReleaseNameStart($torrent["name"], $post["imdbid"]);
		} else {
			$imdbInfo = null;
		}

		$packFolders = $this->getPackFolders($torrent["id"]);
		if (!$packFolders) {
			$packFolders = array();
		} else {
			$packFolders = array_map(function ($f) {
				return $f["filename"];
			}, $packFolders);
		}

		/* SWE-TV */
		$tvProgramId = $post["tv_programid"] ?: 0;
		$tvChannel = $post["tv_kanalid"] ?: 0;
		$tvProgram = $post["tv_program"];
		$tvTime = $post["tv_klockslag"];
		$tvEpisode = $post["tv_episode"];
		$tvInfo = $post["tv_info"];

		if ($post["category"] == Torrent::TV_SWE && $tvProgramId > 0 && $tvChannel > 0 && $tvProgramId != $torrent["tv_programid"]) {
			/* Manual entered program */
			if ($tvProgramId == 1){
				$tvProgram = $post["programTitle"];
				$tvTime = strtotime($post["programDate"] . ' ' . $post["programTime"]);
				if (strlen($tvProgram) < 2) {
					throw new Exception(L::get("TORRENT_TV_NAME_TOO_SHORT"), 412);
				}
				$tvProgramId = 2;
			} else {
				$sweTv = $this->sweTv->getProgram($tvProgramId);
				if (!$sweTv) {
					throw new Exception(L::get("TORRENT_TV_PROGRAM_INVALID"));
				}

				$tvProgram = $sweTv["program"];
				$tvEpisode = $sweTv["episod"];
				$tvInfo = $sweTv["info"];
				$tvTime = $sweTv["datum"];
			}
		}

		$searchText = Helper::searchfield("$torrent[name] $imdbInfo[genres] $imdbInfo[imdbid] " . implode(" ", $packFolders));
		$searchText2 = Helper::searchfield("$imdbInfo[director] $imdbInfo[writer] $imdbInfo[cast]");

		$sth = $this->db->prepare("UPDATE torrents SET ano_owner = :anoymous, descr = :descr, category = :category, imdbid = :imdbid, swesub = :swesub, p2p = :p2p, 3d = :3d, search_text = :searchText, search_text2 = :searchText2, tv_kanalid = :tvChannel, tv_programid = :tvProgramId, tv_program = :tvProgram, tv_episode = :tvEpisode, tv_info = :tvInfo, tv_klockslag = :tvTime, section = :section, sweaudio = :sweaudio WHERE id = :id");

		$sth->bindParam(":id",				$id,					PDO::PARAM_INT);
		$sth->bindParam(":anoymous",		$post["ano_owner"],		PDO::PARAM_INT);
		$sth->bindParam(":descr",			$post["descr"],			PDO::PARAM_STR);
		$sth->bindParam(":category",		$post["category"],		PDO::PARAM_INT);
		$sth->bindParam(":imdbid",			$post["imdbid"],		PDO::PARAM_INT);
		$sth->bindParam(":p2p",				$post["p2p"],			PDO::PARAM_INT);
		$sth->bindParam(":3d",				$post["3d"],			PDO::PARAM_INT);
		$sth->bindParam(":swesub",			$post["swesub"],		PDO::PARAM_INT);
		$sth->bindParam(":searchText",		$searchText,			PDO::PARAM_STR);
		$sth->bindParam(":searchText2",		$searchText2,			PDO::PARAM_STR);
		$sth->bindParam(":tvChannel",		$tvChannel,				PDO::PARAM_INT);
		$sth->bindParam(":tvProgram",		$tvProgram,				PDO::PARAM_STR);
		$sth->bindParam(":tvEpisode",		$tvEpisode,				PDO::PARAM_STR);
		$sth->bindParam(":tvInfo",			$tvInfo,				PDO::PARAM_STR);
		$sth->bindParam(":tvTime",			$tvTime,				PDO::PARAM_INT);
		$sth->bindParam(":tvProgramId",		$tvProgramId,			PDO::PARAM_INT);
		$sth->bindParam(":section",			$post["section"],		PDO::PARAM_STR);
		$sth->bindParam(":sweaudio",		$post["sweaudio"],		PDO::PARAM_INT);

		$sth->execute();

		if ($this->user->getClass() >= User::CLASS_ADMIN) {
			$sth = $this->db->prepare("UPDATE torrents SET frileech = :freeLeech WHERE id = :id");

			$sth->bindParam(":id",				$id,					PDO::PARAM_INT);
			$sth->bindParam(":freeLeech",		$post["frileech"],		PDO::PARAM_INT);

			$sth->execute();
		}

		if ($torrent["owner"] == $this->user->getId() && $post["ano_owner"] == 1) {
			$anonymousEdit = 1;
		} else {
			$anonymousEdit = 0;
		}

		$this->log->log(2, L::get("TORRENT_EDITED_LOG", [$torrent["id"], $torrent["name"], $torrent["name"]], Config::DEFAULT_LANGUAGE), $this->user->getId(), $anonymousEdit);
	}

	public function upload($uploaded_file, $post) {

		if ($this->user->isUploadBanned()) {
			throw new Exception(L::get("TORRENT_UPLOAD_BANNED"), 401);
		}

		$max_torrent_size = 10000000;
		ini_set("upload_max_filesize", $max_torrent_size);

		include('benc.php');

		if ($post["category"] < 1 || $post["category"] > 15) {
			throw new Exception(L::get("TORRENT_INVALID_CATEGORY"));
		}

		if ($post["section"] !== 'new' && $post["section"] !== 'archive') {
			throw new Exception(L::get("TORRENT_INVALID_SECTION"));
		}

		if (!preg_match("/\.torrent$/", $uploaded_file["name"], $match)) {
			throw new Exception(L::get("TORRENT_INVLID_FILE"));
		}

		if (!is_uploaded_file($uploaded_file["tmp_name"])) {
			throw new Exception(L::get("TORRENT_UPLOAD_ERROR"));
		}

		if (!filesize($uploaded_file["tmp_name"])) {
			throw new Exception(L::get("TORRENT_EMPTY_FILE_ERROR"));
		}

		if ($post["category"] == Torrent::TV_SWE && $post["section"] == 'new' && ($post["channel"] == 0 || $post["program"] == 0)){
			throw new Exception(L::get("TORRENT_SWE_TV_DATA_MISSING"), 412);
		}

		if ($post["reqid"] > 0) {
			$request = $this->requests->get($post["reqid"]);
			if ($this->user->getId() == $request["user"]["id"]) {
				throw new Exception(L::get("TORRENT_FILL_OWN_REQUEST_ERROR"), 400);
			}
			if ($request["filled"] == 1) {
				throw new Exception(L::get("TORRENT_REQUEST_ALREADY_FILLED_ERROR"), 400);
			}
		}

		$name = preg_replace("/\.torrent$/", '', $uploaded_file["name"]);
		$category = $post["category"];
		$section = $post["section"];
		$reqid = $post["reqid"];
		$anonymousUpload = $post["anonymousUpload"];
		$nfo = $post["nfo"];
		$imdbId = $post["imdbId"];
		$p2p = $post["p2p"];
		$freeleech = 0;
		$sweaudio = $post["sweaudio"] ?: 0;
		$stereoscopic = 0;

		$swesub = 0;
		/* The following categories should always be tagged with "has swesub" */
		if (in_array($category, array(
			Torrent::DVDR_PAL,
			Torrent::DVDR_CUSTOM,
			Torrent::DVDR_TV,
			Torrent::EBOOKS,
			Torrent::EPAPERS,
			Torrent::BLURAY,
			Torrent::SUBPACK))) {
			$swesub = 2;
		}
		/* The following categories should be marked with "has swesub" if release "contains" swesub */
		if ($post["swesub"] == 1 && in_array($category, array(
				Torrent::MOVIE_720P,
				Torrent::MOVIE_1080P,
				Torrent::TV_720P,
				Torrent::TV_1080P,
				Torrent::MOVIE_4K))) {
			$swesub = 2;
		}

		/* SWE TV is excepted from swe audio tag */
		if ($post["sweaudio"] && in_array($category, array(
				Torrent::TV_SWE,
				Torrent::AUDIOBOOKS,
				Torrent::EBOOKS,
				Torrent::EPAPERS,
				Torrent::MUSIC,
				Torrent::SUBPACK))) {
			$sweaudio = 0;
		}

		/* SWE-TV */
		$tvProgramId = $post["program"] ?: 0;
		$tvChannel = $post["channel"] ?: 0;
		$tvProgram = '';
		$tvEpisode = '';
		$tvInfo = '';
		$tvTime = 0;

		if ($category == Torrent::TV_SWE && $tvProgramId > 0 && $tvChannel > 0) {
			/* Manual entered program */
			if ($tvProgramId == 1){
				$tvProgram = $post["programTitle"];
				$tvTime = strtotime($post["programDate"]);
				if (strlen($tvProgram) < 2) {
					throw new Exception(L::get("TORRENT_TV_NAME_TOO_SHORT"));
				}
				$tvProgramId = 2;
			} else {
				$sweTv = $this->sweTv->getProgram($tvProgramId);
				if (!$sweTv) {
					throw new Exception(L::get("TORRENT_TV_PROGRAM_INVALID"));
				}

				$tvProgram = $sweTv["program"];
				$tvEpisode = $sweTv["episod"];
				$tvInfo = $sweTv["info"];
				$tvTime = $sweTv["datum"];
			}
		}


		if ($this->user->getClass() < User::CLASS_UPLOADER && $section == 'new') {
			throw new Exception(L::get("TORRENT_UPLOADER_REQUIRED_NEW"));
		}

		$sth = $this->db->prepare("SELECT COUNT(*) FROM torrents WHERE name = ?");
		$sth->execute(Array($name));
		$arr = $sth->fetch();
		if ($arr[0] == 1) {
			throw new Exception(L::get("TORRENT_CONFLICT"), 409);
		}

		$sth = $this->db->prepare("SELECT COUNT(*) FROM packfiles WHERE filename = ?");
		$sth->execute(Array($name));
		$arr = $sth->fetch();
		if ($arr[0] == 1) {
			throw new Exception(L::get("TORRENT_CONFLICT_IN_PACK"), 409);
		}

		$sth = $this->db->prepare("SELECT comment FROM banned WHERE namn = ?");
		$sth->execute(Array($name));
		while ($arr = $sth->fetch(PDO::FETCH_ASSOC)) {
			throw new Exception(L::get("TORRENT_BANNED", [$arr["comment"]]));
		}

		$dict = bdec_file($uploaded_file["tmp_name"], $max_torrent_size);
		if (!isset($dict)) {
			throw new Exception(L::get("TORRENT_FILE_ERROR"));
		}

		function dict_check($d, $s) {
			if ($d["type"] != "dictionary") {
				throw new Exception(L::get("TORRENT_INVLID_FILE"));
			}
			$a = explode(":", $s);
			$dd = $d["value"];
			$ret = array();
			foreach ($a as $k) {
				unset($t);
				if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
					$k = $m[1];
					$t = $m[2];
				}
				if (isset($t)) {
					if ($dd[$k]["type"] != $t) {
						throw new Exception(L::get("TORRENT_MISSING_ANNOUNCE_URL"));
					}
					$ret[] = $dd[$k]["value"];
				}
				else
					$ret[] = $dd[$k];
			}
			return $ret;
		}

		function dict_get($d, $k, $t) {
			if ($d["type"] != "dictionary") {
				throw new Exception(L::get("TORRENT_FILE_ERROR"));
			}
			$dd = $d["value"];
			if (!isset($dd[$k]))
				return;
			$v = $dd[$k];
			if ($v["type"] != $t) {
				throw new Exception(L::get("TORRENT_FILE_ERROR"));
			}
			return $v["value"];
		}

		list($ann, $info) = dict_check($dict, "announce(string):info");
		list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

		if (strlen($pieces) % 20 != 0) {
			throw new Exception(L::get("TORRENT_FILE_ERROR"));
		}

		$filelist = array();
		$totallen = dict_get($info, "length", "integer");
		if (isset($totallen)) {
			$filelist[] = array($dname, $totallen);
			$type = "single";
		}
		else {
			$flist = dict_get($info, "files", "list");
			if (!isset($flist)) {
				throw new Exception(L::get("TORRENT_MISSSING_FILES_LENGTH"));
			}
			if (!count($flist)) {
				throw new Exception(L::get("TORRENT_MISSING_FILES"));
			}
			$totallen = 0;
			foreach ($flist as $fn) {
				list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
				$totallen += $ll;
				$ffa = array();
				foreach ($ff as $ffe) {
					if ($ffe["type"] != "string"){
						throw new Exception(L::get("TORRENT_FILE_NAME_ERROR"));
					}
					$ffa[] = $ffe["value"];
				}
				if (!count($ffa)) {
					throw new Exception(L::get("TORRENT_FILE_NAME_ERROR"));
				}
				$ffe = implode("/", $ffa);
				$filelist[] = array($ffe, $ll);
			}
			$type = "multi";
		}


		$foundBanned = Array();
		$bannedfiles = Array(".DS_Store", "._.DS_Store", "ufxpcrc.log", "Thumbs.db", ".checked", ".message", "desktop.ini", "Default.PLS", ".url", ".html", "imdb.nfo", ".missing", "-missing", ".torrent");

		foreach ($filelist as $file) {
			foreach($bannedfiles as $bfile) {
				if (preg_match("/".$bfile."$/", $file[0])) {
					$foundBanned[] = $file[0];
				}
			}
		}

		if (count($foundBanned) > 0) {
			$files = '';
			foreach($foundBanned as $f)
				$files .= '\''.$f. '\', ';

			$this->adminlog->create(L::get("TORRENT_CONTAINING_BANNED_FILES_LOG", [$this->user->getUsername(), $name, $files], Config::DEFAULT_LANGUAGE));
			throw new Exception(L::get("TORRENT_CONTAINING_BANNED_FILES", [$files]));
		}

		if(($txt = $this->detectMissingFiles($filelist)) != false) {
			$this->adminlog->create(L::get("TORRENT_PREVENTED_BANNED_FILE", [$this->user->getUsername(), $name], Config::DEFAULT_LANGUAGE) . $txt);
			throw new Exception(L::get("TORRENT_MISSING_FOLLOWING_FILES") . $txt);
		}

		$info['value']['source']['type'] = "string";
		$info['value']['source']['value'] = Config::SITE_NAME;
		$info['value']['source']['strlen'] = strlen($info['value']['source']['value']);
		$info['value']['private']['type'] = "integer";
		$info['value']['private']['value'] = 1;
		$dict['value']['info'] = $info;
		$dict = benc($dict);
		$dict = bdec($dict);
		list($ann, $info) = dict_check($dict, "announce(string):info");

		$infohash = bin2hex(pack("H*", sha1($info["string"])));

		$sth = $this->db->prepare("SELECT COUNT(*) FROM torrents WHERE info_hash = ?");
		$sth->bindParam(1, $infohash, PDO::PARAM_STR);
		$sth->execute();
		$arr = $sth->fetch();
		if ($arr[0] == 1) {
			throw new Exception(L::get("TORRENT_CONFLICT"), 409);
		}

		$pre = Helper::preCheck($name);
		if (!$pre) {
			$pre = 0;
		} else {
			if ($pre > time()){
				$pre = time() - 100;
			}
			/* Use pre-time to determine New or Archive section */
			if ($section == 'archive' && $pre > time() - 604800) {
				$section = 'new';
				$this->adminlog->create(L::get("TORRENT_AUTO_MOVED_NEW", [$this->user->getUsername(), $name], Config::DEFAULT_LANGUAGE));
			} else if ($section == 'new' && time() - $pre > 604800) {
				$this->adminlog->create(L::get("TORRENT_AUTO_MOVED_ARCHIVE", [$this->user->getUsername(), $name], Config::DEFAULT_LANGUAGE));
				$section = 'archive';
			}
		}

		if (stripos($name, '.3d.') > -1 || stripos($name, '.hsbs.') > -1 || stripos($name, '-sbs.') > -1) {
			$stereoscopic = 1;
		}

		/* Presume p2p release when not rar archive */
		if (in_array($category, [
			Torrent::DVDR_PAL,
			Torrent::DVDR_CUSTOM,
			Torrent::DVDR_TV,
			Torrent::MOVIE_720P,
			Torrent::MOVIE_1080P,
			Torrent::TV_720P,
			Torrent::TV_1080P,
			Torrent::BLURAY,
			Torrent::MOVIE_4K
			]) && count($filelist) < 10) {
			$p2p = 1;
		}


		/* Block or p2p-mark non-scene groups */
		$sth = $this->db->prepare("SELECT * FROM nonscene WHERE groupname = ?");
		$sth->bindValue(1, $this->matchGroupName($name), PDO::PARAM_STR);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if ($res) {
			if ($res["whitelist"] == 0) {
				throw new Exception(L::get("TORRENT_RELEASE_GROUP_BANNED", [$res["comment"]]), 401);
			} else {
				$p2p = 1;
			}
		}

		/* Torrents sized 15GB+ should be free leech */
		if ($totallen > 16106127360 && $category != Torrent::BLURAY && $category != Torrent::MOVIE_4K) {
			$freeleech = 1;
		}

		/* Detect if torrent is a "pack" with releases inside */
		$packFolders = array();
		foreach ($filelist as $file) {
		    preg_match('/^(.*?)\//', $file[0], $match);
		    if (strlen($match[1]) > 6) {
		   		array_push($packFolders, $match[1]);
		   	}
		}
		$packFolders = array_unique($packFolders);

		if (count($packFolders) > 1) {
			$pack = 1;
		} else {
			$pack = 0;
		}

		if ($imdbId) {
			$imdbInfo = $this->movieData->getData($imdbId);
			/* Always replace the release name start when empty or tv-show to keep it up to date for auto-matching */
			if ($imdbInfo["releaseNameStart"] == "" || in_array($category, [Torrent::TV_720P, Torrent::TV_1080P])) {
				$this->movieData->updateReleaseNameStart($name, $imdbId);
			}
		}

		$fname = $name . ".torrent";

		$searchText = Helper::searchfield("$name $imdbInfo[genres] $imdbInfo[imdbid] " . implode(" ", $packFolders));
		$searchText2 = Helper::searchfield("$imdbInfo[director] $imdbInfo[writer] $imdbInfo[cast]");

		$sth = $this->db->prepare("INSERT INTO torrents (name, filename, search_text, search_text2, owner, visible, info_hash, size, numfiles, type, ano_owner, descr, category, added, last_action,  frileech, tv_kanalid, tv_program, tv_episode, tv_info, imdbid, tv_klockslag, tv_programid, reqid, section, pre, p2p, 3d, pack, swesub, sweaudio) VALUES (:name, :filename, :searchText, :searchText2, :owner, 'no', :infoHash, :size, :numfiles, :type, :anoymous, :descr, :category, NOW(), NOW(), :freeLeech, :tvChannel, :tvProgram, :tvEpisode, :tvInfo, :imdbid, :tvTime, :tvProgramId, :reqid, :section, :pre, :p2p, :3d, :pack, :swesub, :sweaudio)");

		$sth->bindParam(":name",			$name, 					PDO::PARAM_STR);
		$sth->bindParam(":filename",		$fname,					PDO::PARAM_STR);
		$sth->bindParam(":searchText",		$searchText,			PDO::PARAM_STR);
		$sth->bindParam(":searchText2",		$searchText2,			PDO::PARAM_STR);
		$sth->bindValue(":owner",			$this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(":infoHash",		$infohash,				PDO::PARAM_STR);
		$sth->bindParam(":size",			$totallen,				PDO::PARAM_INT);
		$sth->bindValue(":numfiles",		count($filelist),		PDO::PARAM_INT);
		$sth->bindParam(":type",			$type,					PDO::PARAM_STR);
		$sth->bindParam(":anoymous",		$anonymousUpload,		PDO::PARAM_INT);
		$sth->bindParam(":descr",			$nfo,					PDO::PARAM_STR);
		$sth->bindParam(":category",		$category,				PDO::PARAM_INT);
		$sth->bindParam(":freeLeech",		$freeleech,				PDO::PARAM_INT);
		$sth->bindParam(":tvChannel",		$tvChannel,				PDO::PARAM_INT);
		$sth->bindParam(":tvProgram",		$tvProgram,				PDO::PARAM_STR);
		$sth->bindParam(":tvEpisode",		$tvEpisode,				PDO::PARAM_STR);
		$sth->bindParam(":tvInfo",			$tvInfo,				PDO::PARAM_STR);
		$sth->bindParam(":tvTime",			$tvTime,				PDO::PARAM_INT);
		$sth->bindParam(":tvProgramId",		$tvProgramId,			PDO::PARAM_INT);
		$sth->bindParam(":imdbid",			$imdbId,				PDO::PARAM_INT);
		$sth->bindParam(":reqid",			$reqid,					PDO::PARAM_INT);
		$sth->bindParam(":section",			$section,				PDO::PARAM_STR);
		$sth->bindParam(":pre",				$pre,					PDO::PARAM_INT);
		$sth->bindParam(":p2p",				$p2p,					PDO::PARAM_INT);
		$sth->bindParam(":3d",				$stereoscopic,			PDO::PARAM_INT);
		$sth->bindParam(":pack",			$pack,					PDO::PARAM_INT);
		$sth->bindParam(":swesub",			$swesub,				PDO::PARAM_INT);
		$sth->bindParam(":sweaudio",		$sweaudio,				PDO::PARAM_INT);

		$sth->execute();

		$insertId = $this->db->lastInsertId();

		$sth = $this->db->prepare("INSERT INTO files (torrent, filename, size) VALUES (?, ?, ?)");
		foreach ($filelist as $file) {
			$sth->bindParam(1,	$insertId,	PDO::PARAM_INT);
			$sth->bindParam(2,	$file[0],	PDO::PARAM_STR);
			$sth->bindParam(3,	$file[1],	PDO::PARAM_INT);
			$sth->execute();
		}

		if (count($packFolders) > 1) {
			$sth = $this->db->prepare("INSERT INTO packfiles(torrent, filename) VALUES (?, ?)");
			foreach($packFolders as $folder) {
				$sth->bindParam(1, $insertId, PDO::PARAM_INT);
				$sth->bindParam(2, $folder, PDO::PARAM_STR);
				$sth->execute();
			}
		}

		move_uploaded_file($uploaded_file["tmp_name"], $this->torrentDir.$insertId.".torrent");

		$fp = fopen($this->torrentDir.$insertId.".torrent", "w");
		if ($fp) {
			@fwrite($fp, benc($dict), strlen(benc($dict)));
			fclose($fp);
		}

		$this->user->initTorrentComments();

		if ($reqid > 0) {
			$this->requests->fill($reqid);

  			$votes = $this->requests->getVotes($reqid);

			$uploader = "[url=/user/".$this->user->getId() ."/".$this->user->getUsername()."][b]".$this->user->getUsername()."[/b][/url]";

  			foreach ($votes as $vote) {
				if ($anonymousUpload) {
					$uploader = "[i]".L::get("TORRENT_ANONYMOUS", null, $vote["user"]["language"])."[/i]";
	  			}
				$message = L::get("TORRENT_REQUEST_FILLED_PM_BODY", [$insertId, $name, $name, $uploader], $vote["user"]["language"]);
  				if ($vote["user"]["id"] !== $this->user->getId()) {
  					$this->mailbox->sendSystemMessage($vote["user"]["id"], L::get("TORRENT_REQUEST_FILLED_PM_TOPIC", null, $vote["user"]["language"]), $message);
  				}
			}
		}

		$this->log->log(1, L::get("TORRENT_UPLOADED_LOG", [$insertId, $name, $name], Config::DEFAULT_LANGUAGE), $this->user->getId(), $anonymousUpload);

		/* Flush memcached */
		if ($memcached && $category == 8) {
			$memcached->delete('swetvguide-0');
			$memcached->delete('swetvguide-1');
			$memcached->delete('swetvguide-2');
		}

		/* Give uploaders more free leech when uploading torrent */
		if ($section == 'new' && $p2p == 0 && $pack == 0) {
			$leechStart = strtotime($this->user->getLeechStart());
			$newLeech = round(($totallen/1024/1024) * 0.02);
			if ($leechStart > time()) {
				$newLeech = $leechStart + ($newLeech*100);
			} else {
				$newLeech = time() + ($newLeech*100);
			}
			$this->db->query("UPDATE users SET leechstart = FROM_UNIXTIME(" . $newLeech . ") WHERE id = " . $this->user->getId());
		}

		return Array("id" => $insertId, "name" => $name);
	}

	public function download($id) {
		$torrent = $this->get($id);

		include('benc.php');

		$filepath = $this->torrentDir . $torrent["id"] . ".torrent";

		if (!file_exists($filepath)) {
			throw new Exception(L::get("TORRENT_NOT_FOUND"), 404);
		}

		if ($this->user->getHttps()) {
			$announce = Config::TRACKER_URL_SSL . "/tracker.php/".$this->user->getPasskey()."/announce";
		} else {
			$announce = Config::TRACKER_URL . "/tracker.php/".$this->user->getPasskey()."/announce";
		}

		$dict = bdec_file($filepath, filesize($filepath));
		$dict['value']['announce']['value'] = $announce;
		$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
		$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);

		$dict["value"]["comment"]["type"] = "string";
		$dict["value"]["comment"]["value"] = Config::SITE_NAME;
		$dict["value"]["comment"]["strlen"] = strlen(strlen(Config::SITE_NAME) . ":" . Config::SITE_NAME);
		$dict["value"]["comment"]["string"] = strlen(Config::SITE_NAME) . ":" . Config::SITE_NAME;

		unset($dict['value']['announce-list']);

		header('Content-Disposition: attachment;filename="'.$torrent['filename'].'"');
		header("Content-Type: application/x-bittorrent");

		print(benc($dict));
		exit;
	}

	private function detectMissingFiles($array) {
		$typ = 0;
		$tid = $t["id"];
		$breaked = false;
		$arr = array();
		foreach($array as $b) {
			if (stripos($b[0], 'disc') > - 1) {
				return false;
			}

			if (strpos($b[0], '/') > - 1) {
				continue;
			}

			if ($typ == 0) {
				if (strpos($b[0], '.part0') > - 1) {
					$typ = 2;
				}
				else
				if (strpos($b[0], '.r') > - 1) {
					$typ = 1;
				}
				else {
					continue;
				}
			}

			if ($typ == 1) {
				if (is_numeric($s = substr($b[0], -2))) {
					$arr[] = array(
						id => $s,
						s => $b[1]
					);
				}
			}
			else
			if ($typ == 2) {
				if (is_numeric($s = substr($b[0], -6, 2)) && substr($b[0], -4) == '.rar') {
					$arr[] = array(
						id => $s,
						s => $b[1]
					);
				}
			}
		}

		asort($arr);
		if ($typ == 1) $sista = - 1;
		else
		if ($typ == 2) $sista = 0;
		$status = "";
		$antal = count($arr) - 1;
		if ($antal > 30) {
			foreach($arr as $ar) {
				if ($antal > $ar["id"]) {
					if ($ar["s"] < 50000000) {
						$status.= L::get("TORRENT_WRONG_RAR_FILE_SIZE", [$ar["id"]]);
					}
				}

				if ($sista + 1 != $ar["id"]) {
					$status.= L::get("TORRENT_MISSING_FILE", [$sista + 1]);
				}

				$sista = $ar["id"];
			}

			if (strlen($status) > 1 && strlen($status) < 200) return $status;
			else return 0;
		}
	}

	public function updateCommentsAmount($torrentId, $amount) {
		$sth = $this->db->prepare('UPDATE torrents SET comments = comments + ? WHERE id = ?');
		$sth->bindParam(1, $amount, PDO::PARAM_INT);
		$sth->bindParam(2, $torrentId, PDO::PARAM_INT);
		$sth->execute();
	}

	public function getSnatchLog($torrentId) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->query('SELECT snatch.*, snatch.uploaded AS s_uploaded, snatch.downloaded AS s_downloaded, snatch.id AS snatchId, '.implode(',', User::getDefaultFields()).' FROM snatch LEFT JOIN users ON snatch.userid = users.id WHERE snatch.torrentid = ' . $torrentId . ' ORDER BY klar DESC');

		$result = array();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$snatch = array();
			$snatch["id"] = $row["snatchId"];
			$snatch["ip"] = $row["ip"];
			$snatch["port"] = $row["port"];
			$snatch["uploaded"] = $row["s_uploaded"];
			$snatch["downloaded"] = $row["s_downloaded"];
			$snatch["agent"] = $row["agent"];
			$snatch["connectable"] = $row["connectable"];
			$snatch["finishedat"] = $row["klar"];
			$snatch["lastaction"] = $row["lastaction"];
			$snatch["timesStarted"] = $row["timesStarted"];
			$snatch["timesCompleted"] = $row["timesCompleted"];
			$snatch["timesStopped"] = $row["timesStopped"];
			$snatch["timesUpdated"] = $row["timesUpdated"];
			$snatch["seedtime"] = $row["seedtime"];
			$snatch["user"] = $this->user->generateUserObject($row);
			array_push($result, $snatch);
		}
		return $result;
	}

	private function matchGroupName($releaseName) {
		preg_match("/(\-|\.)([a-z0-9]{2,20})$/i", $releaseName, $match);
		if (strlen($match[2]) > 0) {
			return $match[2];
		} else {
			return "";
		}
	}
}
