<?php

class TorrentsFinder {
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function getTorrents($get) {
		if (!preg_match("/^[a-z0-9]{32}$/", $get["passkey"])) {
			throw new Exception(L::get("TORRENTS_FINDER_INVALID_PASSKEY"), 401);
		}

		$sth = $this->db->prepare('SELECT id FROM users WHERE passkey = ? AND enabled = "yes"');
		$sth->bindParam(1,	$get["passkey"],	PDO::PARAM_STR,	32);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception(L::get("USER_NOT_FOUND_ERROR"), 401);
		}

		$userid = $res["id"];

		$torrents = json_decode($get["torrents"]);

		if (!is_array($torrents)) {
			throw new Exception(L::get("TORRENTS_FINDER_PARAMETER_ERROR"), 400);
		}

		$resultArray = array();
		$i = 0;

		$torrentSth = $this->db->prepare("SELECT id FROM torrents WHERE name = ?");
		$peerSth = $this->db->prepare("SELECT id FROM peers WHERE userid = ? AND torrent = ?");

		foreach ($torrents as $torrent) {
			$torrentSth->bindParam(1, $torrent, PDO::PARAM_STR);
			$torrentSth->execute();
			if ($torrentSth->rowCount() === 0) {
				$resultArray[$i] = 0;
			} else {
				$data = $torrentSth->fetch();

				if ($get["hideseeding"]) {
					$peerSth->bindParam(1, $userid, PDO::PARAM_INT);
					$peerSth->bindParam(2, $data[0], PDO::PARAM_INT);
					$peerSth->execute();
					if ($peerSth->rowCount() > 0) {
						$resultArray[$i] = -1;
					} else {
						$resultArray[$i] = (int)$data[0];
					}
				} else {
					$resultArray[$i] = (int)$data[0];
				}
			}
			$i++;
		}

		return $resultArray;
	}
}
