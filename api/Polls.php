<?php

class Polls {
	private $db;
	private $user;
	private $forum;

	public function __construct($db, $user, $forum = null) {
		$this->db = $db;
		$this->user = $user;
		$this->forum = $forum;
	}

	public function query($limit = 25, $index = 0) {
		$sth = $this->db->query("SELECT COUNT(*) FROM polls");
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare("SELECT * FROM polls ORDER BY added DESC LIMIT ?, ?");
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();
		$polls = $sth->fetchAll(PDO::FETCH_ASSOC);

		$polls = array_map(function($poll) {
			return $this->generatePoll($poll);
		}, $polls);

		return Array($polls, $totalCount);
	}

	public function getLatest() {
		$sth = $this->db->query("SELECT * FROM polls ORDER BY added DESC LIMIT 1");
		$poll = $sth->fetch(PDO::FETCH_ASSOC);

		if (!$poll) {
			throw new Exception(L::get("POLLS_NO_POLLS"), 404);
		}

		return $this->generatePoll($poll);
	}

	private function generatePoll($poll) {
		$sth = $this->db->query("SELECT COUNT(*) AS votes, selection FROM `pollanswers` WHERE `pollid` = ".$poll["id"]." AND selection != 255 GROUP BY selection");
		$pollAnswers = $sth->fetchAll(PDO::FETCH_ASSOC);

		$sth = $this->db->query("SELECT COUNT(*) FROM `pollanswers` WHERE `pollid` = ".$poll["id"]);
		$res = $sth->fetch();
		$totalAnswers = $res[0];

		$sth = $this->db->query("SELECT COUNT(*) FROM `pollanswers` WHERE `userid` = ".$this->user->getId()." AND `pollid` = ".$poll["id"]);
		$res = $sth->fetch();
		$hasVoted = false;
		if ($res[0] == 1) {
			$hasVoted = true;
		}

		$sum = 0;
		foreach($pollAnswers as $answer) {
			$sum += $answer["votes"];
		}
		if ($sum > 0) {
			$multiplier = 100/$sum;
		} else {
			$multiplier = 1;
		}

		foreach($pollAnswers as &$answer) {
			$answer["percent"] = round($answer["votes"]*$multiplier);
		}

		$pollAnswersIndexed = array();
		foreach($pollAnswers as $a) {
			$pollAnswersIndexed[$a["selection"]] = $a;
		}

		$poll["totalAnswers"] = $totalAnswers;
		$poll["hasVoted"] = $hasVoted;

		for ($i = 0; $i < 20; $i++) {
			if (strlen($poll["option" . $i]) > 0) {
				if ($pollAnswersIndexed[$i]) {
					$pollAnswersIndexed[$i]["title"] = $poll["option" . $i];
					$item = $pollAnswersIndexed[$i];
				} else {
					$item = array("votes" => 0, "percent" => 0, "title" => $poll["option" . $i], "selection" => $i);
				}
				$poll["options"][$i] = $item;
			}
		}
		return $poll;
	}

	public function vote($pollId, $choise) {

		if ($choise < 0 || ($choise > 19 && $choise != 255)) {
			throw new Exception(L::get("POLLS_ILLEGAL_CHOISE"), 412);
		}

		$sth = $this->db->prepare("SELECT COUNT(*) FROM `pollanswers` WHERE `userid` = ? AND `pollid` = ?");
		$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $pollId, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch();
		if ($res[0] == 1) {
			throw new Exception(L::get("POLLS_ALREADY_VOTED"), 409);
		}

		$sth = $this->db->prepare("INSERT INTO pollanswers(pollid, userid, selection, class, alder) VALUES(?, ?, ?, ?, ?)");
		$sth->bindParam(1, $pollId, 				PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(), 	PDO::PARAM_INT);
		$sth->bindParam(3, $choise, 				PDO::PARAM_INT);
		$sth->bindValue(4, $this->user->getClass(), PDO::PARAM_INT);
		$sth->bindValue(5, $this->user->getAge(), 	PDO::PARAM_INT);
		$sth->execute();
	}

	public function create($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$topic = $this->forum->addTopic(Config::POLLS_FORUM_ID, $postdata["question"], '', $postdata["question"], true, 1);

		$sth = $this->db->prepare("INSERT INTO polls(added, question, topicid, option0, option1, option2, option3, option4, option5, option6, option7, option8, option9, option10, option11, option12, option13, option14, option15, option16, option17, option18, option19) VALUES(NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$sth->bindParam(1, $postdata["question"],		PDO::PARAM_STR);
		$sth->bindParam(2, $topic["id"],				PDO::PARAM_INT);
		$sth->bindParam(3, $postdata["option0"],		PDO::PARAM_STR);
		$sth->bindParam(4, $postdata["option1"],		PDO::PARAM_STR);
		$sth->bindParam(5, $postdata["option2"],		PDO::PARAM_STR);
		$sth->bindParam(6, $postdata["option3"],		PDO::PARAM_STR);
		$sth->bindParam(7, $postdata["option4"],		PDO::PARAM_STR);
		$sth->bindParam(8, $postdata["option5"],		PDO::PARAM_STR);
		$sth->bindParam(9, $postdata["option6"],		PDO::PARAM_STR);
		$sth->bindParam(10, $postdata["option7"],		PDO::PARAM_STR);
		$sth->bindParam(11, $postdata["option8"],		PDO::PARAM_STR);
		$sth->bindParam(12, $postdata["option9"],		PDO::PARAM_STR);
		$sth->bindParam(13, $postdata["option10"],		PDO::PARAM_STR);
		$sth->bindParam(14, $postdata["option11"],		PDO::PARAM_STR);
		$sth->bindParam(15, $postdata["option12"],		PDO::PARAM_STR);
		$sth->bindParam(16, $postdata["option13"],		PDO::PARAM_STR);
		$sth->bindParam(17, $postdata["option14"],		PDO::PARAM_STR);
		$sth->bindParam(18, $postdata["option15"],		PDO::PARAM_STR);
		$sth->bindParam(19, $postdata["option16"],		PDO::PARAM_STR);
		$sth->bindParam(20, $postdata["option17"],		PDO::PARAM_STR);
		$sth->bindParam(21, $postdata["option18"],		PDO::PARAM_STR);
		$sth->bindParam(22, $postdata["option19"],		PDO::PARAM_STR);
		$sth->execute();
	}

	public function update($id, $postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare("UPDATE polls SET question = ?, option0 = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, option5 = ?, option6 = ?, option7 = ?, option8 = ?, option9 = ?, option10 = ?, option11 = ?, option12 = ?, option13 = ?, option14 = ?, option15 = ?, option16 = ?, option17 = ?, option18 = ?, option19 = ? WHERE id = ?");
		$sth->bindParam(1, $postdata["question"],		PDO::PARAM_STR);
		$sth->bindParam(2, $postdata["option0"],		PDO::PARAM_STR);
		$sth->bindParam(3, $postdata["option1"],		PDO::PARAM_STR);
		$sth->bindParam(4, $postdata["option2"],		PDO::PARAM_STR);
		$sth->bindParam(5, $postdata["option3"],		PDO::PARAM_STR);
		$sth->bindParam(6, $postdata["option4"],		PDO::PARAM_STR);
		$sth->bindParam(7, $postdata["option5"],		PDO::PARAM_STR);
		$sth->bindParam(8, $postdata["option6"],		PDO::PARAM_STR);
		$sth->bindParam(9, $postdata["option7"],		PDO::PARAM_STR);
		$sth->bindParam(10, $postdata["option8"],		PDO::PARAM_STR);
		$sth->bindParam(11, $postdata["option9"],		PDO::PARAM_STR);
		$sth->bindParam(12, $postdata["option10"],		PDO::PARAM_STR);
		$sth->bindParam(13, $postdata["option11"],		PDO::PARAM_STR);
		$sth->bindParam(14, $postdata["option12"],		PDO::PARAM_STR);
		$sth->bindParam(15, $postdata["option13"],		PDO::PARAM_STR);
		$sth->bindParam(16, $postdata["option14"],		PDO::PARAM_STR);
		$sth->bindParam(17, $postdata["option15"],		PDO::PARAM_STR);
		$sth->bindParam(18, $postdata["option16"],		PDO::PARAM_STR);
		$sth->bindParam(19, $postdata["option17"],		PDO::PARAM_STR);
		$sth->bindParam(20, $postdata["option18"],		PDO::PARAM_STR);
		$sth->bindParam(21, $postdata["option19"],		PDO::PARAM_STR);
		$sth->bindParam(22, $id,						PDO::PARAM_INT);
		$sth->execute();
	}

	public function delete($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->prepare("DELETE FROM polls WHERE id = ?");
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();

		$sth = $this->db->prepare("DELETE FROM pollanswers WHERE pollid = ?");
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
	}
}
