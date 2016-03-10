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
			throw new Exception('Du saknar rättigheter.', 401);
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
			throw new Exception('Du saknar rättigheter.', 401);
		}

		$watch = $this->get($userId, $post["imdbinfoid"]);
		if ($watch) {
			throw new Exception('Det finns redan en bevakning, redigera den istället.', 409);
		}

		$swesub = $post["swesub"] == 1 ? 1 : 0;

		$formats = array();
		if ($post["typ"] == 0) {
			if ($post["formats"]["hd720"]) { array_push($formats, Torrent::MOVIE_720P); }
			if ($post["formats"]["hd1080"]) { array_push($formats, Torrent::MOVIE_1080P); }
			if ($post["formats"]["dvdrpal"]) { array_push($formats, Torrent::DVDR_PAL); }
			if ($post["formats"]["dvdrcustom"]) { array_push($formats, Torrent::DVDR_CUSTOM); }
			if ($post["formats"]["bluray"]) { array_push($formats, Torrent::BLURAY); }
		} else {
			if ($post["formats"]["hd720"]) { array_push($formats, Torrent::TV_720P); }
			if ($post["formats"]["hd1080"]) { array_push($formats, Torrent::TV_1080P); }
			if ($post["formats"]["dvdrpal"]) { array_push($formats, Torrent::DVDR_TV); }
			if ($post["formats"]["dvdrcustom"]) { array_push($formats, Torrent::DVDR_CUSTOM); }
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
			throw new Exception('Du saknar rättigheter.', 401);
		}
		if ($watchId != $post["id"]) {
			throw new Exception('ID-nummer matchar inte.');
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
			throw new Exception('Du saknar rättigheter.', 401);
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
}
