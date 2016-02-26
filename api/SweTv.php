<?php

class SweTv {
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function getChannels() {
		$sth = $this->db->query('SELECT * FROM tv_kanaler WHERE visible = 1 ORDER BY namn ASC');
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getChannel($channelId) {
		$sth = $this->db->prepare("SELECT * FROM tv_kanaler WHERE id = ?");
		$sth->bindParam(1, $channelId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function getPrograms($channelId) {
		$nu = strtotime(date('Y-m-d', time()+86400));
		$sth = $this->db->prepare('SELECT * FROM tv_program WHERE kanalid = ? AND datum < ? ORDER BY datum DESC');
		$sth->bindParam(1, $channelId, PDO::PARAM_INT);
		$sth->bindParam(2, $nu, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getProgram($id) {
		$sth = $this->db->query('SELECT * FROM tv_program WHERE id = ' . (int) $id);
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function guessChannelAndProgram($name) {
		if (($pos = stripos($name, ".SWE")) > 4) {
	   		$name = substr($name, 0, $pos);
		}
		$name = Helper::searchfield($name);
		preg_match("/E([0-9]+)/i", $name, $match);

		$releaseEpisodeNumber = $match[1];

		$hits = array();
		$sth = $this->db->prepare("SELECT tv_program.id AS programid, priority, kanalid, episod, FROM_UNIXTIME(datum), MATCH(program_search) AGAINST(?) AS relevance FROM `tv_program` LEFT JOIN tv_kanaler ON tv_program.kanalid = tv_kanaler.id  WHERE MATCH(program_search) AGAINST(?) AND FROM_UNIXTIME(datum) > (NOW() - INTERVAL 4 DAY) ORDER BY relevance DESC, datum DESC, tv_kanaler.priority ASC LIMIT 10");

		$sth->bindParam(1, $name, PDO::PARAM_STR);
		$sth->bindParam(2, $name, PDO::PARAM_STR);
		$sth->execute();

		$relevance = -1;
		while ($r = $sth->fetch(PDO::FETCH_ASSOC)) {
		    if ($relevance == -1) {
		        $relevance = $r["relevance"];
		    }
		    if ($relevance !== $r["relevance"]) {
		        break;
		    }
		    preg_match("/Del ([0-9]+) /i", $r["episod"], $match);
		    $r["episodeNumber"] = $match[1];
		    $hits[] = $r;
		}

		if (count($hits) == 0) {
			return false;
		}

		$hits = array_reverse($hits);
		$chosenEpisode = $hits[0];

		if (is_numeric($releaseEpisodeNumber)) {
			foreach($hits as $h) {
			    if($h["episodeNumber"] == $releaseEpisodeNumber) {
			        $chosenEpisode = $h;
			        break;
			    }
			}
		}

		return array($chosenEpisode["kanalid"], $chosenEpisode["programid"]);
	}
}
