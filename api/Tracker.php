<?php

class Tracker {
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function getTrackersByReleaseId($id = 0) {
	 	$sth = $this->db->prepare('SELECT datum, namn, trackers.namn FROM rls_tracker JOIN trackers ON rls_tracker.trackerid = trackers.id WHERE releaseid = ? ORDER BY rls_tracker.datum ASC');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getAllTrackers() {
		$sth = $this->db->prepare('SELECT * FROM trackers ORDER BY namn ASC');
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

}