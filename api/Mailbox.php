<?php

class Mailbox {
	private $db;
	private $user;

	public function __construct($db, $user = null, $torrent = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function query($location, $limit = 10, $index = 0) {
		$sth = $this->db->prepare('SELECT COUNT(*) FROM messages WHERE var = ? AND receiver = ?');
		$sth->bindParam(1, $location, PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT messages.svarad, messages.subject, messages.last, messages.saved, messages.unread, messages.sender, messages.receiver, messages.id AS pid, messages.added AS padded, messages.msg AS pbody, '.implode(',', User::getDefaultFields()).' FROM messages LEFT JOIN users ON users.id = messages.sender WHERE receiver = ? AND var = ? ORDER BY messages.id DESC LIMIT ?, ?');
		$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $location, PDO::PARAM_INT);
		$sth->bindParam(3, $index, PDO::PARAM_INT);
		$sth->bindParam(4, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["svarad"] = $post["svarad"];
			$row["unread"] = $post["unread"];
			$row["receiver"] = $post["receiver"];
			$row["sender"] = $post["sender"];
			$row["subject"] = $post["subject"];
			$row["saved"] = $post["saved"];
			$row["last"] = $post["last"];

			$row["user"] = $this->user->generateUserObject($post);

			$result[] = $row;
		}

		return Array($result, $totalCount);
	}

	private function getLastComment($torrentId) {
		$sth = $this->db->prepare('SELECT * FROM comments WHERE torrent = ? ORDER BY id DESC LIMIT 1');
		$sth->bindParam(1, $torrentId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function get($messageId) {
		$sth = $this->db->prepare('SELECT * FROM messages WHERE id = ?');
		$sth->bindParam(1, $messageId, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception(L::get("MESSAGE_NOT_FOUND"), 401);
		}
		return $res;
	}

	public function update($messageId, $postData) {
		if ($messageId != $postData["id"]) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$message = $this->get($messageId);

		if ($message["receiver"] != $this->user->getId()) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare('UPDATE messages SET svarad = ?, unread = ?, last = NOW(), saved = ? WHERE id = ?');
		$sth->bindParam(1, $postData["svarad"], PDO::PARAM_INT);
		$sth->bindParam(2, $postData["unread"], PDO::PARAM_STR);
		$sth->bindParam(3, $postData["saved"], PDO::PARAM_INT);
		$sth->bindParam(4, $messageId, PDO::PARAM_INT);
		$sth->execute();
	}

	public function delete($id, $postdata = null) {
		$message = $this->get($id);

		if ($message["receiver"] != $this->user->getId()) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare('DELETE FROM messages WHERE id = ?');
		$sth->bindParam(1, $id,	PDO::PARAM_INT);
		$sth->execute();
	}

	public function create($postData) {

		if (strlen($postData["body"]) < 2) {
			throw new Exception(L::get("MESSAGE_TOO_SHORT"), 412);
		}

		if ($postData["receiver"] == 1) {
			throw new Exception(L::get("MESSAGE_NOT_ALLOWED"), 401);
		}

		if ($postData["receiver"] == $this->user->getId()) {
			throw new Exception(L::get("MESSAGE_TO_SELF"), 401);
		}

		$receiver = $this->user->get($postData["receiver"]);

		if (!$receiver) {
			throw new Exception(L::get("USER_NOT_EXIST"));
		}

		if ($postData["systemMessage"] == true && $this->user->getClass() >= User::CLASS_ADMIN) {
			$this->sendSystemMessage($postData["receiver"], $postData["subject"], $postData["body"]);
			return true;
		}

		if ($receiver["enabled"] == "no") {
			throw new Exception(L::get("MESSAGE_TO_DISABLED_USER"), 401);
		}

		if ($this->user->getClass() < User::CLASS_ADMIN) {

			if ($receiver["acceptpms"] == "no") {
				throw new Exception(L::get("MESSAGE_USER_NOT_ACCEPTING"), 401);
			} else if ($receiver["acceptpms"] == "friends") {
				$sth = $this->db->prepare("SELECT 1 FROM friends WHERE userid = ? AND friendid = ?");
				$sth->bindValue(1, $this->user->getId(),	PDO::PARAM_INT);
				$sth->bindParam(2, $postData["receiver"],	PDO::PARAM_INT);
				$sth->execute();
				$res = $sth->fetch();
				if (!$res) {
					throw new Exception(L::get("MESSAGES_USER_ALLOW_FROM_FRIENDS"), 401);
				}
			}

			$sth = $this->db->prepare("SELECT 1 FROM blocks WHERE userid = ? AND blockid = ?");
			$sth->bindParam(1, $postData["receiver"],	PDO::PARAM_INT);
			$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
			$sth->execute();
			if ($sth->fetch()) {
				throw new Exception(L::get("MESSAGES_USER_BLOCKED_YOU"), 401);
			}
		}

		if ($postData["replyTo"]) {
			$message = $this->get($postData["replyTo"]);
			if ($message["receiver"] != $this->user->getId()) {
				throw new Exception(L::get("PERMISSION_DENIED"), 401);
			}
			$this->db->query("UPDATE messages SET svarad = 1 WHERE id = " . $message["id"]);
		}

		if (strlen($postData["subject"]) < 3) {
			$postData["subject"] = substr($postData["body"], 0, 30);
		}

		// For the receivers inbox
		$sth = $this->db->prepare("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(?, ?, NOW(), ?, ?)");
		$sth->bindValue(1, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(2, $postData["receiver"],	PDO::PARAM_INT);
		$sth->bindParam(3, $postData["body"],		PDO::PARAM_STR);
		$sth->bindParam(4, $postData["subject"],	PDO::PARAM_STR);
		$sth->execute();

		// For senders outbox
		$sth = $this->db->prepare("INSERT INTO messages (sender, receiver, added, unread, msg, subject, var, last) VALUES(?, ?, NOW(), 'no', ?, ?, 1, NOW())");
		$sth->bindParam(1, $postData["receiver"],	PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(3, $postData["body"],		PDO::PARAM_STR);
		$sth->bindParam(4, $postData["subject"],	PDO::PARAM_STR);
		$sth->execute();
	}

	public function sendSystemMessage($receiver, $subject, $message) {
		$sth = $this->db->prepare("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(1, ?, NOW(), ?, ?)");
		$sth->bindParam(1, $receiver,	PDO::PARAM_INT);
		$sth->bindParam(2, $message,	PDO::PARAM_STR);
		$sth->bindParam(3, $subject,	PDO::PARAM_STR);
		$sth->execute();
	}
}