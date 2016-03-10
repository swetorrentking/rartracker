<?php

class MovieData {
	private $db;
	private $imdbPicturesDir = "../img/imdb/";
	private $releaseTitleMatcher = "/^(.+?)(.S[0-9]{2}|.[0-9]{4}|.US.)/";

	public function __construct($db) {
		$this->db = $db;
	}

	public function getData($id) {
		$sth = $this->db->prepare('SELECT * FROM imdbinfo WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();

		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function getToplist() {
		$data = array();
		$sth = $this->db->query('SELECT imdbinfo.* FROM imdbtop20 LEFT JOIN imdbinfo ON imdbtop20.imdbid = imdbinfo.imdbid ORDER BY imdbtop20.id ASC');
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getDataByImdbId($id) {
		if (preg_match('/[^t0-9]/', $id, $match)) {
			throw new Exception('Inget giltigt IMDb-id');
		}

		$sth = $this->db->prepare("SELECT * FROM imdbinfo WHERE imdbid = ?");
		$sth->bindParam(1, $id, PDO::PARAM_STR);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);

		if ($res) {
			return $res;
		} else {
			$res = $this->fetchImdbData("http://akas.imdb.com/title/".$id."/");

			if (strlen($res["photo"]) > 10) {
				@file_put_contents($this->imdbPicturesDir . $res["imdbid"] . '.jpg', @file_get_contents($res["photo"]));
				$res["photo"] = 1;
			} else {
				$res["photo"] = 0;
			}

			$sth = $this->db->prepare('INSERT INTO imdbinfo(imdbid, title, year, rating, tagline, genres, photo, director, writer, cast, runtime, seasoncount) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

			$sth->bindParam(1,		$res["imdbid"],			PDO::PARAM_STR);
			$sth->bindValue(2,		$res["title"] ?: '',	PDO::PARAM_STR);
			$sth->bindParam(3,		$res["year"],			PDO::PARAM_INT);
			$sth->bindParam(4,		$res["rating"],			PDO::PARAM_INT);
			$sth->bindParam(5,		$res["tagline"],		PDO::PARAM_STR);
			$sth->bindParam(6,		$res["genres"],			PDO::PARAM_STR);
			$sth->bindParam(7,		$res["photo"],			PDO::PARAM_INT);
			$sth->bindParam(8,		$res["director"],		PDO::PARAM_STR);
			$sth->bindParam(9,		$res["writer"],			PDO::PARAM_STR);
			$sth->bindParam(10,		$res["cast"],			PDO::PARAM_STR);
			$sth->bindParam(11,		$res["runtime"],		PDO::PARAM_INT);
			$sth->bindParam(12,		$res["seasoncount"],	PDO::PARAM_INT);

			$sth->execute();
			$insertId = $this->db->lastInsertId();
			$res["id"] = $insertId;
			return $res;
		}
	}

	private function matchRegex($strContent, $strRegex, $intIndex = null) {
	    preg_match_all($strRegex, $strContent, $arrMatches);
	    if ($arrMatches === FALSE) return false;
	    if ($intIndex != null && is_int($intIndex)) {
	        if ($arrMatches[$intIndex]) {
	            return $arrMatches[$intIndex][0];
	        }
	        return false;
	    }
	    return $arrMatches;
	}

	private function fetchImdbData($url) {

		// Decides what language the IMDB titles should be fetched in.
		$header[] = "Accept-Language: en-us,en";

		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

		if (!$data) {
			throw new Exception('Kunde inte hämta IMDB info ifrån servern.');
		}

		/* GENRE */

		$arrReturned = $this->matchRegex($data, '~href="/genre/(.*)(?:\?.*)"(?:\s+|)>(.*)</a>~Ui');
		if (count($arrReturned[1])) {
			foreach ($arrReturned[1] as $strName) {
				$arrReturn[] = trim($strName);
		}
		$arrReturn = array_slice($arrReturn, 1, 4);
			$info["genres"] = join(", ", array_unique($arrReturn));
		}

		/* Tagline */
		$info["tagline"] = '';
		if (@preg_match('!Taglines:</h4>\s*(.*?)\s*<!ims',$data,$match)) {
			$info["tagline"] = trim($match[1]);
		}

		/* Antal säsonger */
		preg_match("/episodes\?season=(\d+)/ms", $data, $matches);
		$info["seasoncount"] = 0 + $matches[1];

		if ($info["seasoncount"] == 0) {
			preg_match("/href=\"episodes#season-(\d+)\"/ms", $data, $matches);
			$info["seasoncount"] = 0 + $matches[1];
		}

		/* Cover! */
		$info["photo"] = 0;
		if ($strReturn = $this->matchRegex($data, '~src="(.*)"\nitemprop="image" \/>~Ui', 1)) {
		  $info["photo"] = $strReturn;
		}

		/* Rating */
		if (preg_match('!<span itemprop="ratingValue">(\d{1,2}\.\d)!i', $data ,$match)){
		  $info["rating"] = $match[1];
		} else {
		  $info["rating"] = 0;
		}


		/* Title + Year  */
		if (@preg_match('!<title>(IMDb\s*-\s*)?(.*) \((.*)(\d{4}|\?{4}).*\)(.*)(\s*-\s*IMDb)?</title>!', $data,$match)) {

		$info["title"] = htmlspecialchars_decode($match[2]);

		if ($match[3]=="????")
			$info["year"] = "";
		else
		  	$info["year"]  = $match[4];
		}

		preg_match("/<span class=\"title-extra\">(.*?)<i>\(original title\)<\/i>/ms", $data, $match);

		if (strlen($match[1]) > 2) {
			$info["title"] = trim($match[1]);
		}

		/* Director */
		$info['director'] = "";
		$strContainer = $this->matchRegex($data, "~(?:Director|Directors):</h4>(.*)</div>~Uis", 1);
		$arrReturned  = $this->matchRegex($strContainer, '~href="/name/nm(\d+)/(?:.*)" itemprop=\'(?:\w+)\'><span class="itemprop" itemprop="name">(.*)</span>~Ui');

		if (count($arrReturned[2])) {
			$arrReturn = Array();
			foreach ($arrReturned[2] as $i => $strName) {
						$arrReturn[] = trim($strName);
			}
			$info['director'] = join(", ", $arrReturn);
		}

		/* Writer */
		$info['writer'] = "";
		$strContainer = $this->matchRegex($data, '~(?:Writer|Writers):</h4>(.*)</div>~Uis', 1);
		$arrReturned  = $this->matchRegex($strContainer, '~href="/name/nm(\d+)/(?:.*)" itemprop=\'(?:\w+)\'><span class="itemprop" itemprop="name">(.*)</span>~Ui');

		if (count($arrReturned[2])) {
			$arrReturn = Array();
			foreach ($arrReturned[2] as $i => $strName) {
				$arrReturn[] = trim($strName);
			}
			$info['writer'] = join(", ", $arrReturn);
		}

		$info['cast'] = "";
		$intLimit = 20;
		$arrReturned = $this->matchRegex($data, '~<span class="itemprop" itemprop="name">(.*)</span>~Ui');

		if (count($arrReturned[1])) {
			$arrReturn = Array();
			foreach ($arrReturned[1] as $i => $strName) {
				if ($i >= $intLimit) {
					break;
				}
		 		if (trim($strName) !== $info["title"]) {
					$arrReturn[] = trim($strName);
				}
			}
			$arrReturn = array_slice(array_unique($arrReturn), 0, 8);
			$info['cast'] =  join(", ", $arrReturn);
		}

		/* Runtime */
		$info['runtime'] = trim($this->match('/Runtime:<\/h4>.*?([0-9]+) min.*?<\/div>/ms', $data, 1));

		/* IMDB-ID */
		$info['imdbid'] = $this->match('/<link rel="canonical" href="http:\/\/www.imdb.com\/title\/(tt[0-9]+)\/" \/>/ms', $data, 1);

		return $info;
	}

	private function match($regex, $str, $i = 0) {
		if (preg_match($regex, $str, $match) == 1) {
			return $match[$i];
		}
		else {
			return false;
		}
	}

	public function findImdbInfoByReleaseName($release) {
		preg_match($this->releaseTitleMatcher, $release, $match);
		if ($match[1] == "") {
			return null;
		}

		$sth = $this->db->query('SELECT * FROM imdbinfo WHERE releaseNameStart = ' . $this->db->quote($match[1]) . ' ORDER BY seasoncount DESC LIMIT 1');
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		return $res;
	}

	public function updateReleaseNameStart($name, $imdbid) {
		preg_match($this->releaseTitleMatcher, $name, $match);
		$this->db->query('UPDATE imdbinfo SET releaseNameStart = ' . $this->db->quote($match[1]) . ' WHERE id = '. (int)$imdbid);
	}

	public function updateImdbInfo($id) {
		$sth = $this->db->prepare("SELECT * FROM imdbinfo WHERE id = ?");
		$sth->bindParam(1, $id, PDO::FETCH_ASSOC);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception('Finns ingen filmdata med detta id.', 404);
		}

		$url = 'http://akas.imdb.com/title/'.$res["imdbid"].'/';
		$data = $this->fetchImdbData($url);

//		if ($res["photo"] == 1) {
	//		$data["photo"] = 1;
		//} else {
			if (strlen($data["photo"]) > 10) {
				file_put_contents($this->imdbPicturesDir . $res["imdbid"] . '.jpg', file_get_contents($data["photo"]));
				$data["photo"] = 1;
			} else {
				$data["photo"] = 0;
			}
		//}

		$sth = $this->db->prepare("UPDATE imdbinfo SET rating = ?, tagline = ?, genres = ?, photo = ?, director = ?, writer = ?, cast = ?, runtime = ?, seasoncount = ?, title = ?, lastUpdated = NOW() WHERE id = ?");
		$sth->bindParam(1,	$data["rating"],		PDO::PARAM_INT);
		$sth->bindParam(2,	$data["tagline"],		PDO::PARAM_STR);
		$sth->bindParam(3,	$data["genres"],		PDO::PARAM_STR);
		$sth->bindParam(4,	$data["photo"],			PDO::PARAM_INT);
		$sth->bindParam(5,	$data["director"],		PDO::PARAM_STR);
		$sth->bindParam(6,	$data["writer"],		PDO::PARAM_STR);
		$sth->bindParam(7,	$data["cast"],			PDO::PARAM_STR);
		$sth->bindParam(8,	$data["runtime"],		PDO::PARAM_INT);
		$sth->bindParam(9,	$data["seasoncount"],	PDO::PARAM_INT);
		$sth->bindParam(10,	$data["title"],			PDO::PARAM_STR);
		$sth->bindParam(11,	$id,					PDO::PARAM_INT);
		$sth->execute();

	}

	public function search($search) {
		preg_match('/imdb.com\/title\/(tt[0-9]+)/ms', $search, $match);
		if (strlen($match[1]) > 1) {
			$imdb =  $this->getDataByImdbId($match[1]);

			return array($imdb);

		} else {
			$searchWords = Helper::searchTextToWordParams($search);

			$sth = $this->db->query('SELECT id, title, year, photo, imdbid, seasoncount FROM imdbinfo WHERE MATCH (title) AGAINST (' . $this->db->quote($searchWords) . ' IN BOOLEAN MODE) ORDER BY year DESC LIMIT 8;');
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	public function updateImdbToplist () {
		if ($_SERVER['SERVER_ADDR'] != $_SERVER["REMOTE_ADDR"]) {
			throw new Exception("Must be run by server.", 401);
		}

		$data = file_get_contents("http://akas.imdb.com/boxoffice/rentals");
		preg_match_all("/\/title\/(.*?)\//", $data, $matches);
		$array = $matches[1];

		unset($array[0]);

		$this->db->query('DELETE FROM imdbtop20');

		foreach($array as $a) {
			$this->db->query('INSERT INTO imdbtop20(imdbid) VALUES('.$this->db->quote($a).')');
			$this->getDataByImdbId($a);
		}
	}
}
