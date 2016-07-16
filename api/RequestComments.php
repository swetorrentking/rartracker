<?php

class RequestComments {
	private $db;
	private $user;
	private $request;
	private $mailbox;

	public function __construct($db, $user = null, $request = null, $mailbox = null) {
		$this->db = $db;
		$this->user = $user;
		$this->request = $request;
		$this->mailbox = $mailbox;
	}

	public function query($requestId, $limit = 10, $index = 0) {
		$request = $this->request->get($requestId);

		$sth = $this->db->prepare('SELECT COUNT(*) FROM request_comments WHERE request = ?');
		$sth->bindParam(1, $requestId, PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT request_comments.id AS pid,request_comments.added AS padded, request_comments.text AS pbody, request_comments.editedat, '.implode(',', User::getDefaultFields()).' FROM request_comments LEFT JOIN users ON users.id = request_comments.user WHERE request = ? ORDER BY request_comments.id ASC LIMIT ?, ?');
		$sth->bindParam(1, $requestId, PDO::PARAM_INT);
		$sth->bindParam(2, $index, PDO::PARAM_INT);
		$sth->bindParam(3, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["editedat"] = $post["editedat"];

			if ($request["user"]["id"] != $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN) {
				$row["user"] = null;
			} else {
				$row["user"] = $this->user->generateUserObject($post, true, true);
				$row["user"]["anonymous"] = true;
			}

			$result[] = $row;
		}

		return Array($result, $totalCount);
	}

	public function delete($id) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$comment = $this->get($id);

		$this->db->query("DELETE FROM request_comments WHERE id = " . $comment["id"]);

		$this->request->updateCommentsAmount($id, 1);
	}

	public function get($id) {
		$sth = $this->db->prepare('SELECT * FROM request_comments WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$comment = $sth->fetch(PDO::FETCH_ASSOC);

		if (!$comment) {
			throw new Exception(L::get("COMMENT_NOT_EXIST"), 404);
		}

		return $comment;
	}

	public function add($requestId, $post) {

		$request = $this->request->get($requestId);

		if (strlen($post) < 2) {
			throw new Exception(L::get("COMMENT_TOO_SHORT"), 412);
		}

		$lastComment = $this->getLastComment($requestId);
		if ($lastComment && $lastComment["user"] == $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN && (time() - strtotime($lastComment["added"]) < 86400)) {
			throw new Exception(L::get("FORUM_DOUBLE_POST"));
		}

		$sth = $this->db->prepare('INSERT INTO request_comments(request, user, added, text) VALUES(?, ?, NOW(), ?)');
		$sth->bindParam(1, $requestId, PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(3, $post, PDO::PARAM_STR);
		$sth->execute();

		// Notify requester of new comment
		if ($this->user->getId() != $request["user"]["id"]) {
			$message = L::get("REQUEST_COMMENT_PM_BODY", [$request["request"], $request["id"], $request["slug"], $request["id"]], $request["user"]["language"]);
			$this->mailbox->sendSystemMessage($request["user"]["id"], L::get("REQUEST_COMMENT_PM_SUBJECT", [$request["request"]], $request["user"]["language"]), $message);
		}

		$this->request->updateCommentsAmount($request["id"], 1);
	}

	public function update($requestId, $postId, $postData) {
		if (strlen($postData) < 2) {
			throw new Exception(L::get("COMMENT_TOO_SHORT"), 412);
		}

		$post = $this->get($postId);

		if ($post["request"] != $requestId) {
			throw new Exception(L::get("COMMENT_REQUEST_NOT_MATCHING"));
		}

		$sth = $this->db->prepare('UPDATE request_comments SET ori_text = text, text = ?, editedby = ?, editedat = NOW() WHERE id = ?');
		$sth->bindParam(1, $postData, PDO::PARAM_STR);
		$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(3, $postId, PDO::PARAM_INT);
		$sth->execute();
	}

	private function getLastComment($requestId) {
		$sth = $this->db->prepare('SELECT * FROM request_comments WHERE request = ? ORDER BY id DESC LIMIT 1');
		$sth->bindParam(1, $requestId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetch(PDO::FETCH_ASSOC);
	}
}
