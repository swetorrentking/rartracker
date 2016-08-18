<?php

class TvData {

	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function run($params) {

		if ($_SERVER['SERVER_ADDR'] != $_SERVER["REMOTE_ADDR"]) {
			throw new Exception(L::get("MUST_BE_RUN_BY_SERVER_ERROR"), 401);
		}

		/* Fetch todays guide data */

		$res = $this->db->query('SELECT * FROM tv_kanaler WHERE visible = 1');
		$days = 86400 * (int)$params["days"];
		$dagensdatum = date('Y-m-d', time() + $days);

		while($r = $res->fetch(PDO::FETCH_ASSOC)) {

			$data = json_decode(file_get_contents('http://json.xmltv.se/' . $r["xmlid"]. '_'. $dagensdatum .'.js.gz'), true);

			if (!$data) {
				continue;
			}
			$data = $data["jsontv"];

			foreach ( $data["programme"] as $dat ) {

				$titel = $dat["title"]["sv"];
				if(strlen($titel) < 2)
					$titel = $dat["title"]["en"];
				$titel = trim($titel);
				$tid = $dat["start"];
				$episod = '';
				if($dat["episodeNum"]) {
					$episod = $dat["episodeNum"]["onscreen"];
				}
				$desc = $dat["desc"]["sv"];
				if(strlen($desc) < 2)
					$desc = $dat["desc"]["en"];

				$this->db->query('INSERT INTO tv_program (datum, kanalid, program, program_search, episod, info) VALUES('.$tid.', '.$r["id"].', '.$this->db->quote($titel).', '.$this->db->quote(Helper::searchfield($titel)).', '.$this->db->quote($episod).', '.$this->db->quote($desc).')');

			}

		}


		/* Erase old tv-data to clear up space in database */

		$dag = 86400 * 7; // Erase 7 days old
		$time = time() - $dag;
		$this->db->query('DELETE FROM tv_program WHERE datum < ' . $time);

	}
}
