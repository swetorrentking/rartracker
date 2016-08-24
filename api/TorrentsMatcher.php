<?php

class TorrentsMatcher {
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function getSettings($passkey) {
		$this->validatePasskey($passkey);

		$settings = Array(
			"download-uri" => Config::SITE_URL . "/api/v1/torrents/download/{id}/{passkey}",
			"tracker-name" => Config::NAME
		);

		return $settings;
	}

	public function getTorrents($passkey) {
		$res = $this->validatePasskey($passkey);

		$torrentSth = $this->db->query("SELECT id, name FROM torrents");
		$torrents = $torrentSth->fetchAll(PDO::FETCH_ASSOC);
		
		$peersSth = $this->db->prepare("SELECT torrent FROM peers WHERE userid = ?");
		$peersSth->bindParam(1, $res["id"], PDO::PARAM_INT);
		$peersSth->execute();
		$peers = $peersSth->fetchAll(PDO::FETCH_ASSOC);

		foreach($torrents as &$torrent) {
			foreach($peers as $peer) {
				if ($peer["torrent"] === $torrent["id"]) {
					$torrent["seeding"] = true;
					break;
				}
			}
			if (!isset($torrent["seeding"])) {
				$torrent["seeding"] = false;
			}
		}

		return $torrents;
	}

	private function validatePasskey($passkey) {
		if (!preg_match("/^[a-z0-9]{32}$/", $passkey)) {
			throw new Exception(L::get("TORRENTS_FINDER_INVALID_PASSKEY"), 401);
		}
		
		$sth = $this->db->prepare('SELECT id FROM users WHERE passkey = ? AND enabled = "yes"');
		$sth->bindParam(1,	$passkey,	PDO::PARAM_STR,	32);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception(L::get("USER_NOT_FOUND_ERROR"), 401);
		}

		return $res;
	}
}
