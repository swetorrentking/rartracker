<?php

class Requests {
	private $db;
	private $user;
	private $log;
	private $mailbox;

	public function __construct($db, $user = null, $log = null, $mailbox = null) {
		$this->db = $db;
		$this->user = $user;
		$this->log = $log;
		$this->mailbox = $mailbox;
	}

	public function query($index = 0, $limit = 10, $sort, $order, $searchParams) {
		$sth = $this->db->query('SELECT COUNT(*) FROM requests WHERE filled = 0');
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		switch ($sort) {
			case 'votes': $sortColumn = 'votes'; break;
			case 'reward': $sortColumn = 'krydda'; break;
			case 'comments': $sortColumn = 'comments'; break;
			case 'name': $sortColumn = 'request'; break;
			default: $sortColumn = 'requestId';
		}

		if ($order == "asc") {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$sth = $this->db->prepare('SELECT '.implode(',', User::getDefaultFields()).', imdbinfo.imdbid AS imdbid2, requests.id AS requestId, requests.request, requests.added, requests.filled, requests.p2p, requests.ersatt, requests.comment, requests.comments, requests.season, requests.imdbid, requests.typ, requests.slug, (SELECT COUNT(*) AS cnt FROM reqvotes WHERE reqid = requests.id) AS votes, (SELECT SUM(krydda) FROM reqvotes WHERE reqid = requests.id) AS krydda FROM requests LEFT JOIN users ON requests.userid = users.id LEFT JOIN imdbinfo ON requests.imdbid = imdbinfo.id WHERE requests.filled = 0 ORDER BY '.$sortColumn.' '.$order.' LIMIT ?, ?');
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$arr = array();
			$arr["id"] = $row["requestId"];
			$arr["added"] = $row["added"];
			$arr["filled"] = $row["filled"];
			$arr["request"] = $row["request"];
			$arr["p2p"] = $row["p2p"];
			$arr["comment"] = $row["comment"];
			$arr["comments"] = $row["comments"];
			$arr["ersatt"] = $row["ersatt"];
			$arr["season"] = $row["season"];
			$arr["slug"] = $row["slug"];
			$arr["imdbid"] = $row["imdbid"];
			$arr["type"] = $row["typ"];
			$arr["reward"] = $row["krydda"] += $this->getVoteTimeReward(strtotime($row["added"]));
			$arr["votes"] = $row["votes"];
			$arr["imdbid2"] = $row["imdbid2"];
			$arr["user"] = $this->user->generateUserObject($row);
			array_push($result, $arr);
		}

		return Array($result, $totalCount);
	}

	public function get($id) {
		$sth = $this->db->prepare('SELECT '.implode(',', User::getDefaultFields()).', requests.id AS requestId, requests.request, requests.added, requests.filled, requests.p2p, requests.ersatt, requests.comment, requests.comments, requests.season, requests.imdbid, requests.typ, requests.slug, (SELECT COUNT(*) FROM reqvotes WHERE reqid = requests.id) AS votes, (SELECT SUM(krydda) FROM reqvotes WHERE reqid = requests.id) AS krydda FROM requests LEFT JOIN users ON requests.userid = users.id WHERE requests.id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();

		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			throw new Exception(L::get("REQUEST_NOT_FOUND"), 404);
		}

		$arr = array();
		$arr["id"] = $row["requestId"];
		$arr["added"] = $row["added"];
		$arr["filled"] = $row["filled"];
		$arr["request"] = $row["request"];
		$arr["p2p"] = $row["p2p"];
		$arr["comment"] = $row["comment"];
		$arr["comments"] = $row["comments"];
		$arr["ersatt"] = $row["ersatt"];
		$arr["season"] = $row["season"];
		$arr["slug"] = $row["slug"];
		$arr["imdbid"] = $row["imdbid"];
		$arr["type"] = $row["typ"];
		$arr["reward"] = $row["krydda"] += $this->getVoteTimeReward(strtotime($row["added"]));
		$arr["votes"] = $row["votes"];
		$arr["user"] = $this->user->generateUserObject($row);

		return $arr;
	}

	public function getVotes($id) {
		$sth = $this->db->prepare('SELECT '.implode(',', User::getDefaultFields()).', reqvotes.id AS vid, reqvotes.krydda, reqvotes.reqid FROM reqvotes LEFT JOIN users ON reqvotes.userid = users.id WHERE reqvotes.reqid = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$result = array();
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$arr = array();
			$arr["id"] = $row["vid"];
			$arr["reward"] = $row["krydda"];
			$arr["user"] = $this->user->generateUserObject($row);
			array_push($result, $arr);
		}

		return $result;
	}

	public function createOrUpdate($postData, $reqId = null) {
		if ($this->user->getClass() < User::CLASS_ACTOR) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		if ($reqId) {
			$request = $this->get($reqId);
			if ($request["user"]["id"] != $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN) {
				throw new Exception(L::get("PERMISSION_DENIED"));
			}
		} else {
			if ($this->user->getRequestSlots() <= count($this->myRequests())) {
				throw new Exception(L::get("REQUEST_SLOTS_EXCEEDED"), 401);
			}
		}

		if ($postData["category"] < Torrent::DVDR_PAL || $postData["category"] > Torrent::SUBPACK) {
			throw new Exception(L::get("REQUEST_INVALID_CATEGORY"));
		}

		if (in_array($postData["category"], Array(1,2,3,4,5,6,7)) && strlen($postData["imdbInfo"]) < 2) {
			throw new Exception(L::get("REQUEST_NO_IMDB_URL"));
		}

		if (in_array($postData["category"], Array(8,9,10,11,12)) && strlen($postData["customName"]) < 2) {
			throw new Exception(L::get("REQUEST_NAME_TOO_SHORT"));
		}

		$requestName = $postData["imdbInfo"];
		if (in_array($postData["category"], array(8,9,10,11,12))) {
			$requestName = $postData["customName"];
		}

		$slug = Helper::slugify($requestName);
		$searchText = Helper::searchfield($requestName);
		$userid = $this->user->getId();

		if ($reqId) {
			$sth = $this->db->prepare("UPDATE requests SET userid = ?, request = ?, comment = ?, search_text = ?, season = ?, imdbid = ?, typ = ?, slug = ? WHERE id = " . $request["id"]);
			$userid = $request["user"]["id"];
		} else {
			$sth = $this->db->prepare("INSERT INTO requests(userid, request, added, comment, search_text, season, imdbid, typ, slug) VALUES(?, ?, NOW(), ?, ?, ?, ?, ?, ?)");
		}

		$sth->bindParam(1, $userid,					PDO::PARAM_INT);
		$sth->bindParam(2, $requestName,			PDO::PARAM_STR);
		$sth->bindParam(3, $postData["comment"],	PDO::PARAM_STR);
		$sth->bindParam(4, $searchText,				PDO::PARAM_STR);
		$sth->bindParam(5, $postData["season"],		PDO::PARAM_INT);
		$sth->bindParam(6, $postData["imdbId"],		PDO::PARAM_INT);
		$sth->bindParam(7, $postData["category"],	PDO::PARAM_INT);
		$sth->bindParam(8, $slug,					PDO::PARAM_STR);
		$sth->execute();

		if ($reqId) {
			return Array("id" => $request["id"], "slug" => $slug);
		} else {
			$insertId = $this->db->lastInsertId();
			$this->log->log(1, L::get("REQUEST_SITE_LOG", [$insertId, $slug, $requestName]), $this->user->getId(), false);
			$this->vote($insertId, 0);
			return Array("id" => $insertId, "name" => $requestName);
		}
	}

	public function delete($reqId, $reason) {
		$request = $this->get($reqId);
		if ($request["user"]["id"] != $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN && $this->user->getId() !== 1) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$votes = $this->getVotes($reqId);
		$this->log->log(3, L::get("REQUEST_DELETED_SITE_LOG", [$request["request"], $reason]), $this->user->getId(), false);

		foreach ($votes as $vote) {
			$message = L::get("REQUEST_DELETED_PM_BODY", [$request["request"], $this->user->getUsername(), $reason]);
			if ($vote["reward"] > 0) {
				$message .= "\n\n " . L::get("REQUEST_DELETED_PM_REWARD", [$vote["reward"]]);
				$this->user->bonusLog($vote["reward"], L::get("REQUEST_REWARD_PAYBACK_BONUS_LOG", [$request["request"]]), $vote["user"]["id"]);
			}
			if ($vote["user"]["id"] != $this->user->getId()) {
				$this->mailbox->sendSystemMessage($vote["user"]["id"], L::get("REQUEST_DELETED_PM_SUBJECT"), $message);
			}
		}

		$this->purge($request["id"]);
	}

	public function purge($reqId) {
		$this->db->query("DELETE FROM requests WHERE id = " . $reqId);
		$this->db->query("DELETE FROM reqvotes WHERE reqid = " . $reqId);
		$this->db->query("DELETE FROM request_comments WHERE request = " . $reqId);
	}

	public function restore($reqId, $reason) {
		$request = $this->get($reqId);
		$this->db->query("UPDATE requests SET filled = 0 WHERE id = " . $reqId);
		$this->log->log(1, L::get("REQUEST_RESTORED_SITE_LOG", [$reqId, $request["slug"]]), 0, false);
		if ($this->user->getId() != $request["user"]["id"]) {
			$this->mailbox->sendSystemMessage($request["user"]["id"], L::get("REQUEST_RESTORED_PM_SUBJECT"), L::get("REQUEST_RESTORED_PM_BODY", [$reqId, $request["request"], $reason]));
		}
	}

	public function vote($reqid, $reward) {
		$request = $this->get($reqid);

		if ($this->user->getBonus() < $reward) {
			throw new Exception(L::get("NOT_ENOUGH_BONUS"), 412);
		}

		$res = $this->db->query("SELECT COUNT(*) FROM reqvotes WHERE reqid = " . $reqid . " AND userid = " . $this->user->getId());
		$res = $res->fetch();

		if ($res[0] == 1) {
			$this->db->query("UPDATE reqvotes set krydda = krydda + ".$reward." WHERE reqid = " . $reqid . " AND userid = " . $this->user->getId());
		} else {
			$sth = $this->db->prepare("INSERT INTO reqvotes(reqid, userid, krydda) VALUES(?, ?, ?)");
			$sth->bindParam(1, $reqid,					PDO::PARAM_INT);
			$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
			$sth->bindParam(3, $reward,					PDO::PARAM_INT);
			$sth->execute();
		}

		if ($reward > 0) {
			$this->user->bonusLog(-$reward, L::get("REQUEST_REWARD_BONUS_LOG", [$request["request"]]), $this->user->getId());
		}
		return $this->getVoteAmount($reqid, strtotime($request["added"]));
	}

	public function fill($reqid) {
		$this->db->query("UPDATE requests SET filled = 1 WHERE id = " . $reqid);
	}

	public function getMyRequests(){
		$myRequests = $this->myRequests();
		$myVotedRequests = $this->myVotedRequests();
		return Array("myRequests" => $myRequests, "myVotedRequests" => $myVotedRequests);
	}

	private function getVoteAmount($reqid, $addedUnix) {
		$sth = $this->db->query("SELECT SUM(krydda), COUNT(*) AS cnt FROM reqvotes WHERE reqid = " .$reqid);
		$res = $sth->fetch();
		$sum = $res[0];

		$sum += $this->getVoteTimeReward($addedUnix);
		return array("reward" => $sum, "votes" => $res[1]);
	}

	private function getVoteTimeReward($addedUnix) {
		$reward = time() - $addedUnix;
		// Add 1p for each day since request added
		$reward = round($reward/86400);
		// All requests are worth 2p from the start
		$reward += 2;
		return $reward;
	}

	private function myRequests() {
		$sth = $this->db->query("SELECT requests.id AS requestId, requests.request, requests.added, requests.filled, requests.p2p, requests.ersatt, requests.comment,requests.season, requests.imdbid, requests.typ, requests.slug, (SELECT COUNT(*) AS cnt FROM reqvotes WHERE reqid = requests.id) AS votes, (SELECT SUM(krydda) FROM reqvotes WHERE reqid = requests.id) AS krydda FROM requests WHERE requests.filled = 0 AND userid =  " . $this->user->getId() . " ORDER BY requestId DESC");
		$myRequests = array();
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$arr = array();
			$arr["id"] = $row["requestId"];
			$arr["added"] = $row["added"];
			$arr["filled"] = $row["filled"];
			$arr["request"] = $row["request"];
			$arr["p2p"] = $row["p2p"];
			$arr["comment"] = $row["comment"];
			$arr["comments"] = $row["comments"];
			$arr["ersatt"] = $row["ersatt"];
			$arr["slug"] = $row["slug"];
			$arr["season"] = $row["season"];
			$arr["imdbid"] = $row["imdbid"];
			$arr["type"] = $row["typ"];
			$arr["reward"] = $row["krydda"] += $this->getVoteTimeReward(strtotime($row["added"]));
			$arr["votes"] = $row["votes"];
			array_push($myRequests, $arr);
		}
		return $myRequests;
	}

	private function myVotedRequests() {
		$sth = $this->db->query("SELECT requests.*, (SELECT COUNT(*) AS cnt FROM reqvotes WHERE reqid = requests.id) AS vote, (SELECT SUM(krydda) FROM reqvotes WHERE reqid = requests.id) AS krydda FROM reqvotes JOIN requests ON reqvotes.reqid = requests.id WHERE reqvotes.userid = " . $this->user->getId() . " AND requests.filled = 0 AND requests.userid != " . $this->user->getId() . " ORDER BY requests.added DESC");
		$myVotedRequests = array();
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$arr = array();
			$arr["id"] = $row["id"];
			$arr["added"] = $row["added"];
			$arr["filled"] = $row["filled"];
			$arr["request"] = $row["request"];
			$arr["p2p"] = $row["p2p"];
			$arr["slug"] = $row["slug"];
			$arr["comment"] = $row["comment"];
			$arr["comments"] = $row["comments"];
			$arr["ersatt"] = $row["ersatt"];
			$arr["season"] = $row["season"];
			$arr["imdbid"] = $row["imdbid"];
			$arr["type"] = $row["typ"];
			$arr["reward"] = $row["krydda"] += $this->getVoteTimeReward(strtotime($row["added"]));
			$arr["votes"] = $row["votes"];
			array_push($myVotedRequests, $arr);
		}
		return $myVotedRequests;
	}

	public function updateCommentsAmount($requestId, $amount) {
		$sth = $this->db->prepare('UPDATE requests SET comments = comments + ? WHERE id = ?');
		$sth->bindParam(1, $amount, PDO::PARAM_INT);
		$sth->bindParam(2, $requestId, PDO::PARAM_INT);
		$sth->execute();
	}

}
