<?php

class News {
	private $db;
	private $user;
	private $forum;
	private $newsForumId = 1;
	private $subjectPrefix = "Nyhet: ";

	public function __construct($db, $user = null, $forum = null) {
		$this->db = $db;
		$this->user = $user;
		$this->forum = $forum;
	}

	public function query($limit, $markAsRead) {

	 	$sth = $this->db->prepare('SELECT * FROM news ORDER BY added DESC LIMIT ?');
		$sth->bindParam(1, $limit, PDO::PARAM_INT);
		$sth->execute();
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (count($result) > 0 && $markAsRead === "true") {
			$this->db->query("UPDATE users SET lastreadnews = " . $result[0]["id"] . " WHERE id = " . $this->user->getId());
		}

		return $result;
	}

	public function create($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception("Du saknar rättigheter.", 401);
		}

		if (strlen($postdata["body"]) < 2) {
			throw new Exception("Inlägget är för kort.", 412);
		}

		if (strlen($postdata["subject"]) < 2) {
			throw new Exception("Rubriken är för kort", 412);
		}

		$topic = $this->forum->addTopic($this->newsForumId, $this->subjectPrefix . $postdata["subject"], "", $postdata["body"], true, 1);

	 	$sth = $this->db->prepare("INSERT INTO news(userid, added, subject, body, announce, forumthread, forum) VALUES(?, NOW(), ?, ?, ?, ?, ?)");
		$sth->bindValue(1, $this->user->getId(),		PDO::PARAM_INT);
		$sth->bindParam(2, $postdata["subject"],		PDO::PARAM_STR);
		$sth->bindParam(3, $postdata["body"],			PDO::PARAM_STR);
		$sth->bindParam(4, $postdata["announce"],		PDO::PARAM_INT);
		$sth->bindParam(5, $topic["id"],					PDO::PARAM_INT);
		$sth->bindValue(6, $this->newsForumId,			PDO::PARAM_INT);
		$sth->execute();

		return $topic;
	}

	public function update($id, $postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception("Du saknar rättigheter.", 401);
		}
	 	$sth = $this->db->prepare("UPDATE news SET subject = ?, body = ?, announce = ? WHERE id = ?");
		$sth->bindParam(1, $postdata["subject"],		PDO::PARAM_STR);
		$sth->bindParam(2, $postdata["body"],			PDO::PARAM_STR);
		$sth->bindParam(3, $postdata["announce"],		PDO::PARAM_INT);
		$sth->bindParam(4, $id,								PDO::PARAM_INT);
		$sth->execute();
	}

	public function delete($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception("Du saknar rättigheter.", 401);
		}
	 	$sth = $this->db->prepare("DELETE FROM news WHERE id = ?");
		$sth->bindParam(1, $id,	PDO::PARAM_INT);
		$sth->execute();
	}
}
