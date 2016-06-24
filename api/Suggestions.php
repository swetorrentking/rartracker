<?php

class Suggestions {
	private $db;
	private $user;
	private $forum;
	private $suggestionForumId = 25;

	public function __construct($db = null, $user = null, $forum = null) {
		$this->db = $db;
		$this->user = $user;
		$this->forum = $forum;
	}

	public function query($view, $limit) {
		$limit = (int)$limit;

		switch ($view) {
			case 'hot';
				$where = 'status = 0';
				$orderBy = 'hotpoints DESC, votes DESC';
				break;
			case 'top':
				$where = 'status != 3 AND status != 1';
				$orderBy = 'votes DESC';
				break;
			case 'new':
				$where = 'status != 3';
				$orderBy = 'id DESC';
				break;
			case 'denied':
				$where = 'status = 3';
				$orderBy = 'id DESC';
				break;
			case 'done':
				$where = 'status = 2';
				$orderBy = 'id DESC';
				break;
			default:
				$where = 'status <> -1';
				$orderBy = 'id DESC';
		}

		$sth = $this->db->query('SELECT * FROM suggestions WHERE ' . $where . ' ORDER BY ' . $orderBy .' LIMIT ' . $limit);

		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	private function get($id) {
		$sth = $this->db->prepare('SELECT * FROM suggestions WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();

		$suggestion = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$suggestion) {
			throw new Exception('Förslaget finns inte.', 404);
		}
		return $suggestion;
	}

	public function vote($suggestId, $direction) {
		if ($direction != "up" && $direction != "down") {
			throw new Exception('Must vote up or down.');
		}

		$sth = $this->db->prepare("SELECT 1 FROM suggestions WHERE id = ?");
		$sth->bindParam(1, $suggestId, PDO::PARAM_INT);
		$sth->execute();
		$suggest = $sth->fetch(PDO::FETCH_ASSOC);

		if (!$suggest) {
			throw new Exception("Suggestion does not exist.");
		}

		$userVoteWeight = $this->getUserVoteWeight($this->user->getClass(), $direction);

		$sth = $this->db->prepare("SELECT voteWeight FROM suggestions_votes WHERE suggestionId = ? AND userid = ?");
		$sth->bindParam(1, $suggestId, PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch();

		if (!$res) {
			$sth = $this->db->prepare("INSERT INTO suggestions_votes (userid, suggestionId, voteWeight) VALUES(?, ?, ?)");
			$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
			$sth->bindParam(2, $suggestId, PDO::PARAM_INT);
			$sth->bindParam(3, $userVoteWeight, PDO::PARAM_INT);
			$sth->execute();
		} else {
			if ($res[0] > 0 && $direction == "down" || $res[0] < 0 && $direction == "up") {
				$this->db->query('DELETE FROM suggestions_votes WHERE userid = '.$this->user->getId().' AND suggestionId = '.$suggestId);
			} else {
				if ($res[0] != $userVoteWeight) {
					$this->db->query('UPDATE suggestions_votes SET voteWeight = '.$userVoteWeight.' WHERE userid = '.$this->user->getId().' AND suggestionId = '.$suggestId);
				}
			}
		}

		$numVotes = $this->getNumVotesBySuggestion($suggestId);
		$this->updateSuggestionWithVoteSum($numVotes, $suggestId);
		return Array("numVotes" => $numVotes);
	}

	public function create($postData) {
		if (strlen($postData["body"]) < 10) {
			throw new Exception("Beskrivningen är för kort.");
		}

		if (strlen($postData["subject"]) < 5) {
			throw new Exception("Rubriken är för kort.");
		}

		$sth = $this->db->prepare("INSERT INTO suggestions(title, body, userid, added) VALUES(?, ?, ?, NOW())");
		$sth->bindParam(1, $postData["subject"], PDO::PARAM_INT);
		$sth->bindParam(2, $postData["body"], PDO::PARAM_INT);
		$sth->bindValue(3, $this->user->getId(), PDO::PARAM_INT);
		$sth->execute();

		$suggestId = $this->db->lastInsertId();

		$topic = $this->forum->addTopic($this->suggestionForumId, $postData["subject"], $postData["body"], $postData["body"], true);

		$this->db->query('UPDATE suggestions SET topicid = ' . $topic["id"] . ' WHERE id = ' . $suggestId);
		$this->vote($suggestId, "up");

		return $topic;
	}

	public function update($id, $postData) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception('Du saknar rättigheter.', 401);
		}

		$post = $this->get($id);

		$sth = $this->db->prepare("UPDATE suggestions SET status = ? WHERE id = ?");
		$sth->bindParam(1, $postData["status"],		PDO::PARAM_INT);
		$sth->bindParam(2, $id,						PDO::PARAM_INT);
		$sth->execute();

		switch($postData["status"]) {
			case 1:
				$color = "#5cb85c";
				$text = "FÄRDIGT";
				break;
			case 2:
				$color = "#FF9D13";
				$text = "GODKÄNT";
				break;
			case 3:
				$color = "#d9534f";
				$text = "NEKAT";
				break;
			case 4:
				$color = "#827B7B";
				$text = "INGEN ÅTGÄRD";
				break;
			default:
				return;
		}

		$postBody = "Förslaget har ändrat status till [b][color=" . $color. "]" .$text. "[/color][/b]";

		$this->forum->addPost($post["topicid"], array("body" => $postBody), 1);
	}

	public function delete($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception('Du saknar rättigheter.', 401);
		}

		$sth = $this->db->prepare("DELETE FROM suggestions WHERE id = ?");
		$sth->bindParam(1, $id,		PDO::PARAM_INT);
		$sth->execute();

		$sth = $this->db->prepare("DELETE FROM suggestions_votes WHERE suggestionId = ?");
		$sth->bindParam(1, $id,		PDO::PARAM_INT);
		$sth->execute();
	}

	private function getNumVotesBySuggestion($suggestId) {
		$sth = $this->db->prepare("SELECT COALESCE(SUM(voteWeight),0) FROM suggestions_votes WHERE suggestionId = ?");
		$sth->bindParam(1, $suggestId, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch();
		return $res[0];
	}

	private function updateSuggestionWithVoteSum($sum, $suggestId) {
		$sth = $this->db->prepare('UPDATE suggestions SET votes = ? WHERE id = ?');
		$sth->bindParam(1, $sum, PDO::PARAM_INT);
		$sth->bindParam(2, $suggestId, PDO::PARAM_INT);
		$sth->execute();
	}

	private function getUserVoteWeight($class, $direction) {
		switch ($class) {
			case 0:
				$weight = 1;
				break;
			case 1:
				$weight = 2;
				break;
			case 2:
				$weight = 3;
				break;
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
				$weight = 4;
				break;
			case 8:
				$weight = 5;
				break;
		}

		if ($direction == "down") {
			$weight = $weight * -1;
		}

		return $weight;
	}
}
