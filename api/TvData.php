<?php

class TvData {

	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function run() {

		if ($_SERVER['SERVER_ADDR'] != $_SERVER["REMOTE_ADDR"]) {
			throw new Exception("Must be run by server.", 401);
		}

		/* Spara ner dagens tablåer */

		$res = $this->db->query('SELECT * FROM tv_kanaler WHERE visible = 1');

		$dagensdatum = date('Y-m-d', time()); // hämta dagens tablåer

		while($r = $res->fetch(PDO::FETCH_ASSOC)) {

			$data = json_decode(gzdecode(file_get_contents('http://xmltv.tvtab.la/json/' . $r["xmlid"]. '_'. $dagensdatum .'.js.gz')), true);
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


		/* Radera gamla tv-data så det inte blir för mycket i databasen */

		$dag = 86400 * 7; // Radera 7 dagar gamla
		$veckagammal = time() - $dag;
		$this->db->query('DELETE FROM tv_program WHERE datum < ' . $veckagammal);

	}
}