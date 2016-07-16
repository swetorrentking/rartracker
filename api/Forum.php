<?php

class Forum {

	private $db;
	private $user;
	private $mailbox;
	private $defaultPostsPerPage = 15;

	public function __construct($db, $user, $mailbox = null) {
		$this->db = $db;
		$this->user = $user;
		$this->mailbox = $mailbox;
	}

	public function getForums() {
		$headforums = array();
		$heads = $this->db->prepare('SELECT * FROM forumheads WHERE minclassread <= ? ORDER BY sort ASC');
		$heads->bindValue(1, $this->user->getClass(), PDO::PARAM_INT);
		$heads->execute();

		$sth = $this->db->prepare('SELECT forums.*,
			(SELECT lastpost FROM topics WHERE forumid = forums.id ORDER BY lastpost DESC LIMIT 1) AS lastPostId
			FROM forums WHERE forumhead = ? AND minclassread <= ? ORDER BY sort ASC');

		$stdPostCount = $this->db->prepare('SELECT COUNT(*) FROM posts WHERE topicid = ?');

		while ($rowHead = $heads->fetch(PDO::FETCH_ASSOC)) {
			$sth->bindParam(1, $rowHead["id"], PDO::PARAM_INT);
			$sth->bindValue(2, $this->user->getClass(), PDO::PARAM_INT);
			$sth->execute();

			$forums = array();
			while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$forum = array();
				$forum["id"] = $row["id"];
				$forum["name"] = $row["name"];
				$forum["description"] = $row["description"];
				$forum["minclassread"] = $row["minclassread"];
				$forum["minclasswrite"] = $row["minclasswrite"];
				$forum["postcount"] = $row["postcount"];
				$forum["topiccount"] = $row["topiccount"];
				$forum["minclasscreate"] = $row["minclasscreate"];
				try {
					$post = $this->getPost($row["lastPostId"]);
				} catch (Exception $e) {
					$post = null;
				}
				$stdPostCount->bindParam(1, $post["topicid"], PDO::PARAM_INT);
				$stdPostCount->execute();
				$postCount = $stdPostCount->fetch();

				try {
					$topic = $this->getTopic($post["topicid"]);
				} catch(Exception $e) {
					$topic = null;
				}
				try {
					$user = $this->user->get($post["userid"]);
				} catch (Exception $e) {
					$user = null;
				}

				$lastPostRead = $this->getLastReadPost($post["topicid"]);

				$forum["topic"] = array(
					"id" => $topic["id"],
					"subject" => $topic["subject"],
					"slug" => $topic["slug"],
					"forumid" => $topic["forumid"],
					"lastPostRead" => $lastPostRead,
					"post" => array(
						"id" => $post["id"],
						"added" => $post["added"],
						"postcount" => $postCount[0]),
					"user" => array(
						"id" => $user["id"],
						"username" => $user["username"]));
				$forums[] = $forum;
			}
			$rowHead["forums"] = $forums;

			$headforums[] = $rowHead;
		}
		return $headforums;
	}

	public function getTopics($forumId, $limit, $index = 0) {
		$forum = $this->getForum($forumId);

		if ($forum["minclassread"] > $this->user->getClass()) {
			throw new Exception(L::get("FORUM_THREAD_PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare('SELECT COUNT(*) FROM topics WHERE forumid = ?');
		$sth->bindParam(1, $forumId, PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT *, topics.id AS topicId, '.implode(',',User::getDefaultFields()).',
			(SELECT COUNT(*) FROM posts WHERE topicid = topics.id) AS postcount,
			(SELECT lastpostread FROM readposts WHERE topicid = topics.id AND userid = ?) AS lastpostread FROM topics
			LEFT JOIN users ON users.id = topics.userid WHERE forumid = ? ORDER BY sticky ASC, lastpost DESC LIMIT ?, ?');

		$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $forumId, PDO::PARAM_INT);
		$sth->bindParam(3, $index, PDO::PARAM_INT);
		$sth->bindParam(4, $limit, PDO::PARAM_INT);
		$sth->execute();

		$post = $this->db->prepare("SELECT posts.userid, posts.added, users.username FROM posts LEFT JOIN users ON users.id = posts.userid WHERE topicid = ? ORDER BY posts.id DESC LIMIT 1");

		$result = array();
		while ($topic = $sth->fetch(PDO::FETCH_ASSOC)) {

			$post->bindParam(1, $topic["topicId"], PDO::PARAM_INT);
			$post->execute();
			$thePost = $post->fetch(PDO::FETCH_ASSOC);

			$row = array();
			$row["id"] = $topic["topicId"];
			$row["subject"] = $topic["subject"];
			$row["added"] = $topic["added"];
			$row["locked"] = $topic["locked"];
			$row["forumid"] = $topic["forumid"];
			$row["lastpost"] = $topic["lastpost"];
			$row["sticky"] = $topic["sticky"];
			$row["views"] = $topic["views"];
			$row["sub"] = $topic["sub"];
			$row["slug"] = $topic["slug"];
			$row["suggestid"] = $topic["suggestid"];
			$row["postcount"] = $topic["postcount"];
			$row["lastpostAdded"] = $thePost["added"];
			$row["lastpostread"] = $topic["lastpostread"];

			$row["lastpostUser"] = array(
				"id" => $thePost["userid"],
				"username" => $thePost["username"]);

			$row["user"] = array(
				"id" => $topic["userid"],
				"username" => $topic["username"]);

			$result[] = $row;
		}

		return Array($result, $totalCount);
	}

	public function getForum($id) {
		$sth = $this->db->prepare('SELECT * FROM forums WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$forum = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$forum) {
			throw new Exception(L::get("FORUM_NOT_FOUND"), 404);
		}
		if ($forum["minclassread"] > $this->user->getClass()) {
			throw new Exception(L::get("FORUM_PERMISSION_DENIED"), 401);
		}
		return $forum;
	}

	public function getTopic($id) {
		$sth = $this->db->prepare('SELECT * FROM topics WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$topic = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$topic) {
			throw new Exception(L::get("FORUM_THREAD_NOT_FOUND"), 404);
		}
		return $topic;
	}

	public function getPost($id) {
		$sth = $this->db->prepare('SELECT * FROM posts WHERE id = ?');
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$post = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$post) {
			throw new Exception(L::get("FORUM_POST_NOT_FOUND"), 404);
		}
		return $post;
	}

	private function updateTopicLastPost($topicId, $lastPostId) {
		$sth = $this->db->prepare('UPDATE topics SET lastpost = ? WHERE id = ?');
		$sth->bindParam(1, $lastPostId, PDO::PARAM_INT);
		$sth->bindParam(2, $topicId, PDO::PARAM_INT);
		$sth->execute();
	}

	public function getPosts($topicId, $limit = 10, $index = 0) {
		$topic = $this->getTopic($topicId);
		$forum = $this->getForum($topic["forumid"]);

		if ($forum["minclassread"] > $this->user->getClass()) {
			throw new Exception(L::get("FORUM_THREAD_PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare('SELECT COUNT(*) FROM posts WHERE topicid = ?');
		$sth->bindParam(1, $topicId, PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT posts.id AS pid, posts.topicid, posts.added AS padded, posts.body AS pbody, posts.editedat, '.implode(',', User::getDefaultFields()).' FROM posts LEFT JOIN users ON users.id = posts.userid WHERE topicid = ? ORDER BY posts.id ASC LIMIT ?, ?');
		$sth->bindParam(1, $topicId, PDO::PARAM_INT);
		$sth->bindParam(2, $index, PDO::PARAM_INT);
		$sth->bindParam(3, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["topicid"] = $post["topicid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["editedat"] = $post["editedat"];

			$row["user"] = $this->user->generateUserObject($post);

			if ($post["anonymicons"] == 'yes') {
				$row["user"]["leechbonus"] = null;
				$row["user"]["pokal"] = 0;
			}

			$result[] = $row;
		}

		if (count($result) > 0) {
			$lastPost = $result[count($result) -1];
			$this->updateLastReadPost($topicId, $lastPost["id"]);
			$this->db->query("UPDATE topics SET views = views + 1 WHERE id = " . $topic["id"]);
		}

		return Array($result, $totalCount);
	}

	public function getUserPosts($userId, $limit = 10, $index = 0) {
		if ($this->user->getId() != $userId && $this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare('SELECT COUNT(*) FROM posts WHERE userid = ?');
		$sth->bindParam(1, $userId, PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT posts.id AS pid, posts.topicid, posts.added AS padded, posts.body AS pbody, posts.editedat, '.implode(',', User::getDefaultFields()).', topics.subject, topics.slug, topics.forumid FROM posts LEFT JOIN users ON users.id = posts.userid LEFT JOIN topics ON topics.id = posts.topicid WHERE posts.userid = ? ORDER BY posts.id DESC LIMIT ?, ?');
		$sth->bindParam(1, $userId, PDO::PARAM_INT);
		$sth->bindParam(2, $index, PDO::PARAM_INT);
		$sth->bindParam(3, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["topicid"] = $post["topicid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["editedat"] = $post["editedat"];

			$row["topic"] = array(
					"id" => $post["topicid"],
					"forumid" => $post["forumid"],
					"subject" => $post["subject"],
					"slug" => $post["slug"]
				);

			$row["user"] = $this->user->generateUserObject($post);

			$result[] = $row;
		}

		return Array($result, $totalCount);
	}

	public function addPost($topicId, $post, $forceUserId = 0) {

		$topic = $this->getTopic($topicId);
		$forum = $this->getForum($topic["forumid"]);

		if (strlen($post["body"]) < 2) {
			throw new Exception(L::get("FORUM_POST_TOO_SHORT"), 400);
		}

		if ($this->user->isForumBanned() == true) {
			throw new Exception(L::get("FORUM_BANNED"), 401);
		}

		if ($forum["minclasswrite"] > $this->user->getClass()) {
			throw new Exception(L::get("FORUM_PERMISSION_DENIED"), 401);
		}

		$lastPost = $this->getLastPost($topicId);
		if ($lastPost && $lastPost["userid"] == $this->user->getId() && $this->user->getClass() < User::CLASS_ADMIN && (time() - strtotime($lastPost["added"]) < 43200)) {
			throw new Exception(L::get("FORUM_DOUBLE_POST"), 401);
		}

		$userId = $this->user->getId();
		if ($forceUserId > 0) {
			$userId = $forceUserId;
		}

		$sth = $this->db->prepare('INSERT INTO posts(topicid, userid, added, body) VALUES(?, ?, NOW(), ?)');
		$sth->bindParam(1, $topicId, PDO::PARAM_INT);
		$sth->bindParam(2, $userId, PDO::PARAM_INT);
		$sth->bindParam(3, $post["body"], PDO::PARAM_STR);
		$sth->execute();
		$postId = $this->db->lastInsertId();

		$this->updateTopicLastPost($topicId, $postId);

		$this->db->query("UPDATE forums SET postcount = postcount + 1 WHERE id = " .$forum["id"]);

		if ($post["quote"]) {
			$stdPostCount = $this->db->prepare('SELECT COUNT(*) FROM posts WHERE topicid = ?');
			$stdPostCount->bindParam(1, $topicId, PDO::PARAM_INT);
			$stdPostCount->execute();
			$postCount = $stdPostCount->fetch();

			$sth = $this->db->prepare('SELECT postsperpage, language FROM users WHERE id = ?');
			$sth->bindParam(1, $post["quote"], PDO::PARAM_INT);
			$sth->execute();
			$user = $sth->fetch(PDO::FETCH_ASSOC);
			if (!$user) {
				return;
			}
			$postsPerPage = $this->defaultPostsPerPage;
			if ($user["postsperpage"] > 0) {
				$postsPerPage = $user["postsperpage"];
			}
			$pageNumber = ceil($postCount[0] / $postsPerPage ?: 15);

			$this->mailbox->sendSystemMessage($post["quote"], L::get("FORUM_QUOTED_PM_SUBJECT", [$topic["subject"]], $user["language"]), L::get("FORUM_QUOTED_PM_BODY", [$this->user->getUsername(), $topic["subject"], $forum["id"], $topic["id"], $topic["slug"], $pageNumber, $postId], $user["language"]));
		}
	}

	public function updatePost($forumId, $topicId, $postId, $postData) {
		if (strlen($postData) < 2) {
			throw new Exception(L::get("FORUM_POST_TOO_SHORT"), 400);
		}

		$post = $this->getPost($postId);

		if ($post["topicid"] != $topicId) {
			throw new Exception(L::get("FORUM_POST_TOPIC_NOT_MATCHING"), 400);
		}

		$topic =  $this->getTopic($topicId);
		if ($topic["forumid"] != $forumId) {
			throw new Exception(L::get("FORUM_POST_NOT_MATCHING"), 400);
		}

		if ($this->user->getId() != $post["userid"] && $this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("FORUM_EDIT_POST_PERMISSION_DENIED"), 401);
		}

		$forum = $this->getForum($topic["forumid"]);
		if ($forum["minclasswrite"] > $this->user->getClass()) {
			throw new Exception(L::get("FORUM_WRITE_PERMISSION_DENIED"), 401);
		}

		if (time() - strtotime($post["added"]) < 300) {
			$editedat = "''";
		} else {
			$editedat = "NOW()";
		}

		$sth = $this->db->prepare('UPDATE posts SET body_ori = body, body = ?, editedby = ?, editedat = '.$editedat.' WHERE id = ?');
		$sth->bindParam(1, $postData, PDO::PARAM_STR);
		$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(3, $postId, PDO::PARAM_INT);
		$sth->execute();
	}

	public function deletePost($forumId, $topicId, $postId) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$post = $this->getPost($postId);

		if ($post["topicid"] != $topicId) {
			throw new Exception(L::get("FORUM_POST_TOPIC_NOT_MATCHING"));
		}

		$sth = $this->db->prepare("SELECT id FROM posts WHERE topicid = ? ORDER BY id ASC LIMIT 1");
		$sth->bindParam(1, $topicId, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);

		if ($res["id"] == $postId) {
			throw new Exception(L::get("FORUM_CANNOT_REMOVE_LAST_POST"), 400);
		}

		$this->db->query("DELETE FROM posts WHERE id = " . $postId);
		$this->db->query("UPDATE forums SET postcount = postcount - 1 WHERE id = " .$forumId);
		$stm = $this->db->query('SELECT id FROM posts WHERE topicid = ' . $topicId . ' ORDER BY id DESC LIMIT 1');
		$res = $stm->fetch();
		$this->updateTopicLastPost($topicId, $res[0]);
	}

	public function addTopic($forumId, $subject, $sub, $post, $force = false, $forceUserId = 0) {
		if (strlen($subject) < 2) {
			throw new Exception(L::get("FORUM_TOPIC_TOO_SHORT"), 412);
		}

		if (strlen($post) < 2) {
			throw new Exception(L::get("FORUM_POST_TOO_SHORT"), 412);
		}

		$forum = $this->getForum($forumId);

		if ($forum["minclasscreate"] > $this->user->getClass() && $force == false) {
			throw new Exception(L::get("FORUM_CREATE_PERMISSION_DENIED"), 401);
		}

		$userId = $this->user->getId();
		if ($forceUserId > 0) {
			$userId = $forceUserId;
		}

		$slug = Helper::slugify($subject);

		$sth = $this->db->prepare('INSERT INTO topics (userid, forumid, subject, sub, slug) VALUES(?, ?, ?, ?, ?)');
		$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $forumId, PDO::PARAM_INT);
		$sth->bindParam(3, $subject, PDO::PARAM_STR);
		$sth->bindParam(4, $sub, PDO::PARAM_STR);
		$sth->bindParam(5, $slug, PDO::PARAM_STR);
		$sth->execute();

		$topicId = $this->db->lastInsertId();

		$this->db->query("UPDATE forums SET topiccount = topiccount + 1 WHERE id = " .$forum["id"]);

		$this->addPost($topicId, array("body" => $post), $forceUserId);

		return Array("id" => $topicId, "slug" => $slug);
	}

	public function updateTopic($topicId, $postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$topic = $this->getTopic($topicId);
		$slug = Helper::slugify($postdata["subject"]);

		$sth = $this->db->prepare('UPDATE topics SET forumid = ?, subject = ?, sub = ?, locked = ?, sticky = ?, slug = ? WHERE id = ?');
		$sth->bindParam(1, $postdata["forumid"],	PDO::PARAM_INT);
		$sth->bindParam(2, $postdata["subject"],	PDO::PARAM_STR);
		$sth->bindParam(3, $postdata["sub"],		PDO::PARAM_STR);
		$sth->bindParam(4, $postdata["locked"],		PDO::PARAM_STR);
		$sth->bindParam(5, $postdata["sticky"],		PDO::PARAM_STR);
		$sth->bindParam(6, $slug,					PDO::PARAM_STR);
		$sth->bindParam(7, $topicId,				PDO::PARAM_STR);
		$sth->execute();

		if ($topic["forumid"] != $postdata["forumid"]) {
			$sth = $this->db->query("SELECT COUNT(*) FROM posts WHERE topicid = " . $topic["id"]);
			$res = $sth->fetch();
			$postCount = $res[0];

			$this->db->query("UPDATE forums SET topiccount = topiccount - 1, postcount = postcount - " . $postCount . " WHERE id = " . $topic["forumid"]);
			$this->db->query("UPDATE forums SET topiccount = topiccount + 1, postcount = postcount + " . $postCount . " WHERE id = " . $postdata["forumid"]);
		}
	}

	public function deleteTopic($forumId, $topicId) {
		$topic = $this->getTopic($topicId);

		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$this->db->query('DELETE FROM topics WHERE id = ' . $topicId);
		$this->db->query('DELETE FROM readposts WHERE topicid = ' . $topicId);
		$sth = $this->db->query('DELETE FROM posts WHERE topicid = ' . $topicId);
		$postCount = $sth->rowCount();
		$this->db->query("UPDATE forums SET postcount = postcount - ".$postCount." WHERE id = " . $forumId);
		$this->db->query("UPDATE forums SET topiccount = topiccount - 1 WHERE id = " . $forumId);
	}

	public function updateLastReadPost($topicId, $lastPostId) {
		$lastPostRead = $this->getLastReadPost($topicId);
		if ($lastPostRead == 0) {
			$sth = $this->db->prepare('INSERT INTO readposts (userid, topicid, lastpostread) VALUES(?, ?, ?)');
			$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
			$sth->bindParam(2, $topicId, PDO::PARAM_INT);
			$sth->bindParam(3, $lastPostId, PDO::PARAM_INT);
			$sth->execute();
		} else if ($lastPostRead < $lastPostId) {
				$sth = $this->db->prepare('UPDATE readposts SET lastpostread = ? WHERE userid = ? AND topicid = ?');
				$sth->bindParam(1, $lastPostId, PDO::PARAM_INT);
				$sth->bindValue(2, $this->user->getId(), PDO::PARAM_INT);
				$sth->bindParam(3, $topicId, PDO::PARAM_INT);
				$sth->execute();
		}
	}

	public function markAllTopicsAsRead() {
		$sth = $this->db->prepare("SELECT topics.* FROM topics LEFT JOIN forums ON forums.id = topics.forumid WHERE forums.minclassread <= ? AND ((SELECT lastpostread FROM readposts WHERE userid = ? AND topicid = topics.id) != topics.lastpost OR NOT EXISTS (SELECT lastpostread FROM readposts WHERE userid = ? AND topicid = topics.id))");
		$sth->bindValue(1, $this->user->getClass(),	PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindValue(3, $this->user->getId(),	PDO::PARAM_INT);
		$sth->execute();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$this->updateLastReadPost($row["id"], $row["lastpost"]);
		}
	}

	public function getUnreadTopics($limit, $index = 0) {

		// forums.id != 29
		// Making an exception for forum-id 29 because it's a play/spam forum.

		$sth = $this->db->prepare("SELECT COUNT(*) FROM topics LEFT JOIN forums ON forums.id = topics.forumid WHERE forums.id != 29 AND forums.minclassread <= ? AND ((SELECT lastpostread FROM readposts WHERE userid = ? AND topicid = topics.id) < topics.lastpost OR NOT EXISTS (SELECT lastpostread FROM readposts WHERE userid = ? AND topicid = topics.id))");
		$sth->bindValue(1, $this->user->getClass(),	PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindValue(3, $this->user->getId(),	PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare("SELECT topics.*, forums.name, (SELECT COUNT(*) FROM posts WHERE topicid = topics.id) AS postcount FROM topics LEFT JOIN forums ON forums.id = topics.forumid WHERE forums.id != 29 AND forums.minclassread <= ? AND ((SELECT lastpostread FROM readposts WHERE userid = ? AND topicid = topics.id) < topics.lastpost OR NOT EXISTS (SELECT lastpostread FROM readposts WHERE userid = ? AND topicid = topics.id)) ORDER BY topics.lastpost DESC LIMIT ?, ?");
		$sth->bindValue(1, $this->user->getClass(),	PDO::PARAM_INT);
		$sth->bindValue(2, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindValue(3, $this->user->getId(),	PDO::PARAM_INT);
		$sth->bindParam(4, $index,					PDO::PARAM_INT);
		$sth->bindParam(5, $limit,					PDO::PARAM_INT);
		$sth->execute();
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		return array($result, $totalCount);
	}

	public function search($params) {

		$index = abs($params["index"]) ?: 0;
		$limit = abs($params["limit"]) ?: 20;

		$searchWords = Helper::searchTextToWordParams($params["search"]);

		if ($params["table"] == "topics") {

			$sth = $this->db->prepare("SELECT COUNT(*) FROM topics LEFT JOIN forums ON topics.forumid = forums.id WHERE forums.minclassread <= ? AND MATCH(topics.subject) AGAINST (? IN BOOLEAN MODE)");
			$sth->bindValue(1, $this->user->getClass(),		PDO::PARAM_INT);
			$sth->bindParam(2, $searchWords,				PDO::PARAM_STR);
			$sth->execute();
			$arr = $sth->fetch();
			$totalCount = $arr[0];

			$sth = $this->db->prepare("SELECT topics.*, forums.name, (SELECT COUNT(*) FROM posts WHERE topicid = topics.id) AS postcount FROM topics LEFT JOIN forums ON topics.forumid = forums.id WHERE forums.minclassread <= ? AND MATCH(topics.subject) AGAINST (? IN BOOLEAN MODE) LIMIT ?, ?");
			$sth->bindValue(1, $this->user->getClass(),		PDO::PARAM_INT);
			$sth->bindParam(2, $searchWords,				PDO::PARAM_STR);
			$sth->bindParam(3, $index,						PDO::PARAM_INT);
			$sth->bindParam(4, $limit,						PDO::PARAM_INT);
			$sth->execute();
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		} else if ($params["table"] == "posts") {
			$sth = $this->db->prepare("SELECT COUNT(*) FROM posts LEFT JOIN topics ON topics.id = posts.topicid LEFT JOIN forums ON topics.forumid = forums.id WHERE forums.minclassread <= ? AND MATCH(posts.body) AGAINST (? IN BOOLEAN MODE)");
			$sth->bindValue(1, $this->user->getClass(),		PDO::PARAM_INT);
			$sth->bindParam(2, $searchWords,				PDO::PARAM_STR);
			$sth->execute();
			$arr = $sth->fetch();
			$totalCount = $arr[0];

			$sth = $this->db->prepare("SELECT subject, topics.slug, forumid, posts.id AS pid, posts.topicid, posts.added AS padded, posts.body AS pbody, posts.editedat, ".implode(',', User::getDefaultFields())." FROM posts LEFT JOIN users ON users.id = posts.userid LEFT JOIN topics ON topics.id = posts.topicid LEFT JOIN forums ON topics.forumid = forums.id WHERE forums.minclassread <= ? AND MATCH(posts.body) AGAINST (? IN BOOLEAN MODE) LIMIT ?, ?");
			$sth->bindValue(1, $this->user->getClass(),		PDO::PARAM_INT);
			$sth->bindParam(2, $searchWords,				PDO::PARAM_STR);
			$sth->bindParam(3, $index,						PDO::PARAM_INT);
			$sth->bindParam(4, $limit,						PDO::PARAM_INT);
			$sth->execute();
			$result = array();
			while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
				$row = array();
				$row["id"] = $post["pid"];
				$row["topicid"] = $post["topicid"];
				$row["added"] = $post["padded"];
				$row["body"] = $post["pbody"];
				$row["editedat"] = $post["editedat"];

				$row["topic"] = array(
						"id" => $post["topicid"],
						"forumid" => $post["forumid"],
						"subject" => $post["subject"],
						"slug" => $post["slug"]
					);

				$row["user"] = $this->user->generateUserObject($post);

				$result[] = $row;
			}
		}

		return array($result, $totalCount);
	}

	public function getAllPosts($limit = 10, $index = 0) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$sth = $this->db->prepare('SELECT COUNT(*) FROM posts');
		$sth->bindParam(1, $topicId, PDO::PARAM_INT);
		$sth->execute();
		$arr = $sth->fetch();
		$totalCount = $arr[0];

		$sth = $this->db->prepare('SELECT posts.id AS pid, posts.topicid, posts.added AS padded, posts.body AS pbody, posts.editedat, '.implode(',', User::getDefaultFields()).', topics.subject, topics.slug, topics.forumid FROM posts LEFT JOIN users ON users.id = posts.userid LEFT JOIN topics ON topics.id = posts.topicid ORDER BY posts.id DESC LIMIT ?, ?');
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = array();
		while ($post = $sth->fetch(PDO::FETCH_ASSOC)) {
			$row = array();
			$row["id"] = $post["pid"];
			$row["topicid"] = $post["topicid"];
			$row["added"] = $post["padded"];
			$row["body"] = $post["pbody"];
			$row["editedat"] = $post["editedat"];

			$row["topic"] = array(
					"id" => $post["topicid"],
					"forumid" => $post["forumid"],
					"subject" => $post["subject"],
					"slug" => $post["slug"]
				);

			$row["user"] = $this->user->generateUserObject($post);

			$result[] = $row;
		}
		return Array($result, $totalCount);
	}

	private function getLastReadPost($topicId) {
		$sth = $this->db->prepare('SELECT lastpostread FROM readposts WHERE userid = ? AND topicid = ?');
		$sth->bindValue(1, $this->user->getId(), PDO::PARAM_INT);
		$sth->bindParam(2, $topicId, PDO::PARAM_INT);
		$sth->execute();
		$post = $sth->fetch();
		return (int)$post[0];
	}

	private function getLastPost($topicId) {
		$sth = $this->db->prepare('SELECT * FROM posts WHERE topicid = ? ORDER BY id DESC LIMIT 1');
		$sth->bindParam(1, $topicId, PDO::PARAM_INT);
		$sth->execute();
		return $sth->fetch(PDO::FETCH_ASSOC);
	}
}
