<?php

class AdminMailbox implements IResource {
	private $db;
	private $user;

	public function __construct($db, $user = null, $torrent = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$limit = (int)$postdata["limit"] ?: 10;
		$index = (int)$postdata["index"] ?: 0;

		$sth = $this->db->prepare('SELECT COUNT(*) FROM staffmessages');
		$sth->bindParam(1, $location, PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT u2.username AS username2, u2.id AS id2, staffmessages.id AS pid, staffmessages.added AS padded, staffmessages.msg AS pbody, staffmessages.sender, staffmessages.fromprivate, staffmessages.subject, staffmessages.answeredby, staffmessages.answered, staffmessages.answer, staffmessages.svaradwhen, '.implode(',', User::getDefaultFields()).' FROM staffmessages LEFT JOIN users ON users.id = staffmessages.sender LEFT JOIN users AS u2 ON u2.id = staffmessages.answeredby ORDER BY staffmessages.id DESC LIMIT ?, ?');
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["receiver"] = $post["receiver"];
			$row["sender"] = $post["sender"];
			$row["subject"] = $post["subject"];
			$row["fromprivate"] = $post["fromprivate"];
			$row["answer"] = $post["answer"];
			$row["answered"] = $post["answered"];
			$row["answeredAt"] = $post["svaradwhen"];
			$row["answeredBy"] = array(
				"id" => $post["id2"],
				"username" => $post["username2"]);

			$row["user"] = array(
				"id" => $post["id"],
				"username" => $post["username"],
				"enabled" => $post["enabled"],
				"added" => $post["added"],
				"last_access" => $post["last_access"],
				"bonuspoang" => $post["bonuspoang"],
				"class" => $this->user->calculateClass($post["class"], $post["doljuploader"]),
				"coin" => $post["coin"],
				"crown" => $post["crown"],
				"gender" => $post["gender"],
				"donor" => $post["donor"],
				"leechbonus" => $post["leechbonus"],
				"parkerad" => $post["parkerad"],
				"pokal" => $post["pokal"],
				"title" => $post["title"],
				"warned" => $post["warned"],
				"avatar" => $post["avatar"]);

			$result[] = $row;
		}

		return Array($result, $totalCount);
	}

	public function get($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$sth = $this->db->prepare('SELECT * FROM staffmessages WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception(L::get("MESSAGE_NOT_FOUND"), 404);
		}
		return $res;
	}

	public function update($id, $postData) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}
		$message = $this->get($id);

		if ($message["answeredby"] != 0 && $message["answeredby"] != $this->user->getId()) {
			throw new Exception(L::get("MESSAGE_ALREADY_PROCESSING"), 401);
		}

		$sth = $this->db->prepare('UPDATE staffmessages SET answered = ?, answeredby = ?, answer = ?, svaradwhen = ? WHERE id = ?');
		$sth->bindParam(1, $postData["answered"],			PDO::PARAM_INT);
		$sth->bindParam(2, $postData["answeredBy"]["id"],	PDO::PARAM_INT);
		$sth->bindParam(3, $postData["answer"],				PDO::PARAM_STR);
		$sth->bindParam(4, $postData["answeredAt"],			PDO::PARAM_STR);
		$sth->bindParam(5, $id,								PDO::PARAM_INT);
		$sth->execute();
	}

	public function create($postData) {
		if (strlen($postData["body"]) < 2) {
			throw new Exception(L::get("MESSAGE_TOO_SHORT"), 412);
		}

		if (strlen($postData["subject"]) < 3) {
			$postData["subject"] = substr($postData["body"], 0, 30);
		}

		$sth = $this->db->prepare("INSERT INTO staffmessages (sender, added, msg, subject, fromprivate) VALUES(?, NOW(), ?, ?, ?)");
		$sth->bindParam(1, $postData["sender"],			PDO::PARAM_INT);
		$sth->bindParam(2, $postData["body"],			PDO::PARAM_INT);
		$sth->bindParam(3, $postData["subject"],		PDO::PARAM_STR);
		$sth->bindParam(4, $postData["fromprivate"],	PDO::PARAM_STR);
		$sth->execute();
	}

	public function delete($id, $reason) {}
}
