<?php

class Watching {
	private $db;
	private $user;

	public function __construct($db, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query($userId, $imdbId = null) {
		if ($userId != $this->user->getId() && $this->getClass() < self::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		if ($imdbId) {
			$sth = $this->db->query("SELECT bevaka.*, imdbinfo.imdbid, imdbinfo.title, imdbinfo.year, imdbinfo.imdbid AS imdbid2 FROM bevaka JOIN imdbinfo ON bevaka.imdbid = imdbinfo.id WHERE bevaka.userid = ".$userId." AND bevaka.imdbid = " . $imdbId);
		} else {
			$sth = $this->db->query("SELECT bevaka.*, imdbinfo.imdbid, imdbinfo.title, imdbinfo.year, imdbinfo.imdbid AS imdbid2 FROM bevaka JOIN imdbinfo ON bevaka.imdbid = imdbinfo.id WHERE bevaka.userid = ".$userId." ORDER BY imdbinfo.title ASC");
		}

		$fixed = array();
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row["swesub"] = $row["swesub"] == 1;
			if ($row["typ"] == 0) {
				$formatsArray = explode(",", $row["format"]);
				$row["formats"] = array(
					"hd720" => array_search("4", $formatsArray) > -1,
					"hd1080" => array_search("5", $formatsArray) > -1,
					"dvdrpal" => array_search("1", $formatsArray) > -1,
					"dvdrcustom" => array_search("2", $formatsArray) > -1,
					"bluray" => array_search("13", $formatsArray) > -1
					);
			} else {
				$row["formats"] = array(
					"hd720" => strpos($row["format"], "6") > -1,
					"hd1080" => strpos($row["format"], "7") > -1,
					"dvdrpal" => strpos($row["format"], "3") > -1,
					"dvdrcustom" => strpos($row["format"], "2") > -1
					);
			}
			array_push($fixed, $row);
		}
		return $fixed;
	}

	public function create($userId, $post) {
		if ($userId != $this->user->getId() && $this->getClass() < self::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$watch = $this->get($userId, $post["imdbinfoid"]);
		if ($watch) {
			throw new Exception(L::get("WATCHER_CONFLICT"), 409);
		}

		$swesub = $post["swesub"] == 1 ? 1 : 0;

		$formats = array();
		if ($post["typ"] == 0) {
			if ($post["formats"]["hd720"]) { array_push($formats, Config::$categories["MOVIE_720P"]["id"]); }
			if ($post["formats"]["hd1080"]) { array_push($formats, Config::$categories["MOVIE_1080P"]["id"]); }
			if ($post["formats"]["dvdrpal"]) { array_push($formats, Config::$categories["DVDR_PAL"]["id"]); }
			if ($post["formats"]["dvdrcustom"]) { array_push($formats, Config::$categories["DVDR_CUSTOM"]["id"]); }
			if ($post["formats"]["bluray"]) { array_push($formats, Config::$categories["BLURAY"]["id"]); }
		} else {
			if ($post["formats"]["hd720"]) { array_push($formats, Config::$categories["TV_720P"]["id"]); }
			if ($post["formats"]["hd1080"]) { array_push($formats, Config::$categories["TV_1080P"]["id"]); }
			if ($post["formats"]["dvdrpal"]) { array_push($formats, Config::$categories["DVDR_TV"]["id"]); }
			if ($post["formats"]["dvdrcustom"]) { array_push($formats, Config::$categories["DVDR_CUSTOM"]["id"]); }
		}
		$formats = implode(",", $formats);

		$sth = $this->db->prepare('INSERT INTO bevaka (userid, imdbid, typ, format, swesub, datum) VALUES(?, ?, ?, ?, ?, NOW())');
		$sth->bindValue(1,	$this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(2,	$post["imdbinfoid"],	PDO::PARAM_INT);
		$sth->bindParam(3,	$post["typ"],			PDO::PARAM_INT);
		$sth->bindParam(4,	$formats,				PDO::PARAM_INT);
		$sth->bindParam(5,	$swesub,				PDO::PARAM_INT);
		$sth->execute();
	}

	public function update($userId, $watchId, $post) {
		if ($userId != $this->user->getId() && $this->getClass() < self::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		if ($watchId != $post["id"]) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$swesub = $post["swesub"] == 1 ? 1 : 0;

		$formats = array();
		if ($post["typ"] == 0) {
			if ($post["formats"]["hd720"]) { array_push($formats, 4); }
			if ($post["formats"]["hd1080"]) { array_push($formats, 5); }
			if ($post["formats"]["dvdrpal"]) { array_push($formats, 1); }
			if ($post["formats"]["dvdrcustom"]) { array_push($formats, 2); }
			if ($post["formats"]["bluray"]) { array_push($formats, 13); }
		} else {
			if ($post["formats"]["hd720"]) { array_push($formats, 6); }
			if ($post["formats"]["hd1080"]) { array_push($formats, 7); }
			if ($post["formats"]["dvdrpal"]) { array_push($formats, 3); }
			if ($post["formats"]["dvdrcustom"]) { array_push($formats, 2); }
		}
		$formats = implode(",", $formats);

		$sth = $this->db->prepare('UPDATE bevaka SET swesub = ?, format = ? WHERE id = ? AND userid = ?');
		$sth->bindParam(1,	$swesub,	PDO::PARAM_INT);
		$sth->bindParam(2,	$formats,	PDO::PARAM_STR);
		$sth->bindParam(3,	$watchId,	PDO::PARAM_INT);
		$sth->bindParam(4,	$userId,	PDO::PARAM_INT);
		$sth->execute();
	}

	public function delete($userId, $watchId) {
		if ($userId != $this->user->getId() && $this->getClass() < self::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare('DELETE FROM bevaka WHERE id = ? AND userid = ?');
		$sth->bindParam(1,	$watchId,	PDO::PARAM_INT);
		$sth->bindParam(2,	$userId,	PDO::PARAM_INT);
		$sth->execute();
	}

	private function get($userid, $imdbId) {
		$sth = $this->db->prepare("SELECT * FROM bevaka WHERE userid = ? AND imdbid = ?");
		$sth->bindParam(1, $userid, PDO::PARAM_INT);
		$sth->bindParam(2, $imdbId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetch();
	}

	public function getToplist() {
		$dt = time() - 86400 * 90; // Display toplists for movies added during the last XX days
		$sth = $this->db->query("SELECT COUNT(*) AS cnt, bevaka.id, bevaka.typ, bevaka.imdbid as iamwatching, bevaka.imdbid AS imdbinfoid, imdbinfo.photo, imdbinfo.imdbid, imdbinfo.seasoncount, imdbinfo.title, imdbinfo.year, imdbinfo.imdbid AS imdbid2, (SELECT bevaka.id FROM bevaka WHERE bevaka.userid = ".$this->user->getId()." AND bevaka.imdbid = iamwatching) AS myBevakId FROM `bevaka` JOIN imdbinfo ON bevaka.imdbid = imdbinfo.id WHERE bevaka.typ = 0 AND bevaka.datum > FROM_UNIXTIME(".$dt.") GROUP BY imdbid ORDER BY cnt DESC LIMIT 50");
		$movies = $sth->fetchAll(PDO::FETCH_ASSOC);

		$dt = time() - 86400 * 180; // Display toplists for movies added during the last XX days
		$sth = $this->db->query("SELECT COUNT(*) AS cnt, bevaka.id, bevaka.typ, bevaka.imdbid as iamwatching, bevaka.imdbid AS imdbinfoid, imdbinfo.photo, imdbinfo.imdbid, imdbinfo.seasoncount, imdbinfo.title, imdbinfo.year, imdbinfo.imdbid AS imdbid2, (SELECT bevaka.id FROM bevaka WHERE bevaka.userid = ".$this->user->getId()." AND bevaka.imdbid = iamwatching) AS myBevakId FROM `bevaka` JOIN imdbinfo ON bevaka.imdbid = imdbinfo.id WHERE bevaka.typ = 1 AND bevaka.datum > FROM_UNIXTIME(".$dt.") GROUP BY imdbid ORDER BY cnt DESC LIMIT 50");
		$tvseries = $sth->fetchAll(PDO::FETCH_ASSOC);

		return array("movies" => $movies, "tvseries" => $tvseries);
	}

	public function renderRssFeed($params) {
		$passkey = $params["passkey"];

		if (!preg_match("/^[a-z0-9]{32}$/", $passkey)) {
			throw new Exception(L::get("USER_EMAIL_PASSKEY_NO_MATCH"), 401);
			exit;
		}

		$sth = $this->db->prepare("SELECT id FROM users WHERE passkey = ?");
		$sth->bindParam(1, $passkey, PDO::PARAM_STR);
		$sth->execute();
		$user = $sth->fetch();

		if (!$user) {
			throw new Exception(L::get("USER_EMAIL_PASSKEY_NO_MATCH"), 401);
			exit();
		}

		$type = $params["vad"];
		$from = 0 + $params["from"];

		$SITENAME = Config::NAME;
		$DESCR = "Watch RSS Feed";
		$BASEURL = Config::SITE_URL;
		$SITEMAIL = Config::SITE_MAIL;

		$where = '';
		if ($type == 1) {
			$where .= ' AND bevaka.typ = 0';
			$SITENAME .= " Movies";
		} else if ($type == 2) {
			$where .= ' AND bevaka.typ = 1';
			$SITENAME .= " TV";
		}

		if ($from > 0) {
			$where .= ' AND torrents.added > FROM_UNIXTIME(' . $from . ')';
		}

		header("Content-Type: application/xml");
		print("<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<rss version=\"0.91\">\n<channel>\n" .
		"<title>" . $SITENAME . "</title>\n<link>" . $BASEURL . "</link>\n<description>" . $DESCR . "</description>\n" .
		"<language>en-usde</language>\n<copyright> Copyright " . $SITENAME . "</copyright>\n<webMaster>".$SITEMAIL."</webMaster>\n" .
		"<image><title>" . $SITENAME . "</title>\n<url>" . $BASEURL . "/favicon.ico</url>\n<link>" . $BASEURL . "</link>\n" .
		"<width>16</width>\n<height>16</height>\n<description>" . $DESCR . "</description>\n</image>\n");

		$res = $this->db->query("SELECT torrents.id, torrents.name, torrents.size, torrents.seeders, torrents.leechers, torrents.added FROM bevaka JOIN torrents on bevaka.imdbid = torrents.imdbid WHERE (((torrents.category IN(4,5,6,7)) AND bevaka.swesub = 1 AND torrents.swesub = 1) OR ((torrents.category IN(4,5,6,7)) AND bevaka.swesub = 0) OR (torrents.category NOT IN (4,5,6,7))) AND FIND_IN_SET(torrents.category, bevaka.format) AND (category = 2 AND torrents.p2p = 1 OR category <> 2 AND torrents.p2p = 0) AND torrents.pack = 0 AND torrents.3d = 0 AND bevaka.userid = " . $user[0] . $where . " ORDER BY torrents.id DESC LIMIT 25") or sqlerr(__FILE__, __LINE__);

		while ($row = $res->fetch()){

			list($id,$name,$size,$seeders,$leechers,$added) = $row;

			$link = $BASEURL . "/api/v1/torrents/download/$id/$passkey";

			echo("<item><title>" . htmlspecialchars($name) . "</title>\n<link>" . $link . "</link>\n<description>\nSize: " . Helper::mksize($size) ."</description>\n<pubDate>".$added."</pubDate></item> \n");
		}

		echo("</channel>\n</rss>\n");
	}
}
