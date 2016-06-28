<?php

class Reports implements IResource {
	private $db;
	private $user;
	private $subtitles;
	private $requests;
	private $mailbox;
	private $comments;
	private $torrent;
	private $forum;
	private $log;

	public function __construct($db, $user = null, $torrent = null, $subtitles = null, $requests = null, $forum = null, $mailbox = null, $comments = null, $log = null) {
		$this->db = $db;
		$this->user = $user;
		$this->subtitles = $subtitles;
		$this->torrent = $torrent;
		$this->requests = $requests;
		$this->mailbox = $mailbox;
		$this->comments = $comments;
		$this->forum = $forum;
		$this->log = $log;
	}

	public function create($postdata) {
		if (!preg_match("/^(torrent|post|pm|request|comment|subtitle|user)$/", $postdata["type"])) {
			throw new Exception(L::get("REPORT_WRONG_TYPE"), 400);
		}

		if (strlen($postdata["reason"]) < 2) {
			throw new Exception(L::get("REPORT_REASON_TOO_SHORT"), 400);
		}

		$sth = $this->db->prepare('INSERT INTO reports (added, userid, reason, targetid, type) VALUES (NOW(), ?, ?, ?, ?)');
		$sth->bindValue(1, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(2, $postdata["reason"],		PDO::PARAM_STR);
		$sth->bindParam(3, $postdata["targetid"],	PDO::PARAM_STR);
		$sth->bindParam(4, $postdata["type"],		PDO::PARAM_INT);
		$sth->execute();
	}

	public function update($id, $postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$report = $this->get($id);

		if ($report["handledBy"] != 0 && $report["handledBy"] != $this->user->getId()) {
			throw new Exception(L::get("REPORT_ALREADY_PROCESSING"), 401);
		}

		$sth = $this->db->prepare('UPDATE reports SET handledBy = ? WHERE id = ?');
		$sth->bindValue(1, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(2, $id,						PDO::PARAM_INT);
		$sth->execute();
	}

	public function delete($id, $postdata = null) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$report = $this->get($id);

		if ($report["handledBy"] != 0 && $report["handledBy"] != $this->user->getId()) {
			throw new Exception(L::get("REPORT_ALREADY_PROCESSING"), 401);
		}

		$sth = $this->db->prepare('DELETE FROM reports WHERE id = ?');
		$sth->bindParam(1, $id,						PDO::PARAM_INT);
		$sth->execute();
	}

	public function get($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare("SELECT * FROM reports WHERE id = ?");
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$report = $sth->fetch(PDO::FETCH_ASSOC);

		if (!$report) {
			throw new Exception(L::get("REPORTS_NOT_FOUND"), 404);
		}

		return $report;
	}

	public function query($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$limit = (int)$postdata["limit"] ?: 25;
		$index = (int)$postdata["index"] ?: 0;

		$sth = $this->db->query("SELECT COUNT(*) FROM reports");
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare("SELECT reports.added AS added2, reports.targetid, reports.type, reports.id AS reportid, reports.reason, reports.handledBy, ".implode(',', User::getDefaultFields())." FROM reports LEFT JOIN users ON reports.userid = users.id ORDER BY reports.id DESC LIMIT ?, ?");
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = Array();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$r = array();
			$r["id"] = $row["reportid"];
			$r["added"] = $row["added2"];
			$r["type"] = $row["type"];
			$r["reason"] = $row["reason"];
			$r["handledBy"] = $row["handledBy"] == 0 ? null : $this->user->get($row["handledBy"]);
			$r["user"] = $this->user->generateUserObject($row);

			switch($row["type"]) {
				case 'torrent':
					try {
						$r["torrent"] = $this->torrent->get($row["targetid"], true);
						if ($r["torrent"]["imdbid"]) {
							$r["relatedTorrents"] = $this->torrent->getRelated($r["torrent"]["imdbid"], $r["torrent"]["id"]);
						} else {
							$r["relatedTorrents"] = [];
						}
					} catch (Exception $e){
						$r["torrent"] = null;
						$r["deleted"] = true;
					}
					break;
				case 'post':
					try {
						$r["post"] = $this->forum->getPost($row["targetid"]);
						$topic = $this->forum->getTopic($r["post"]["topicid"]);
						$r["post"]["forumid"] = $topic["forumid"];
						try {
							$r["post"]["user"] = $this->user->get($r["post"]["userid"]);
						} catch (Exception $e){
							$r["post"]["user"] = null;
						}
					} catch (Exception $e){
						$r["post"] = null;
						$r["deleted"] = true;
					}
					break;
				case 'pm':
					try {
						$r["pm"] = $this->mailbox->get($row["targetid"]);
						try {
							$r["pm"]["user"] = $this->user->get($r["pm"]["sender"]);
						} catch (Exception $e){
							$r["pm"]["user"] = null;
						}
					} catch (Exception $e){
						$r["pm"] = null;
						$r["deleted"] = true;
					}
					break;
				case 'request':
					try {
						$r["request"] = $this->requests->get($row["targetid"]);
					} catch (Exception $e){
						$r["request"] = null;
						$r["deleted"] = true;
					}
					break;
				case 'comment':
					try {
						$r["comment"] = $this->comments->get($row["targetid"]);
						try {
							$r["comment"]["user"] = $this->user->get($r["comment"]["user"]);
						} catch (Exception $e){
							$r["comment"]["user"] = null;
						}
					} catch (Exception $e){
						$r["comment"] = null;
						$r["deleted"] = true;
					}
					break;
				case 'subtitle':
					try {
						$r["subtitle"] = $this->subtitles->get($row["targetid"]);
						try {
							$r["subtitle"]["user"] = $this->user->get($r["subtitle"]["userid"]);
						} catch (Exception $e){
							$r["subtitle"]["user"] = null;
						}
					} catch (Exception $e){
						$r["subtitle"] = null;
						$r["deleted"] = true;
					}
					break;
				case 'user':
					try {
						$r["reportedUser"] = $this->user->get($row["targetid"]);
					} catch (Exception $e){
						$r["reportedUser"] = null;
						$r["deleted"] = true;
					}
					break;
			}

			array_push($result, $r);
		}

		return Array($result, $totalCount);
	}
}
