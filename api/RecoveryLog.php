<?php

class RecoveryLog implements IResource {
	private $db;
	private $user;

	public function __construct($db, $user = null) {
		$this->db = $db;
		$this->user = $user;
	}

	public function create($postdata) {
		$sth = $this->db->prepare("INSERT INTO recoverlog(date, email, ip, userid, host) VALUES(NOW(), ?, ?, ?, ?)");
		$sth->bindParam(1,	$postdata["email"],		PDO::PARAM_STR);
		$sth->bindParam(2,	$postdata["ip"],		PDO::PARAM_STR);
		$sth->bindParam(3,	$postdata["userid"],	PDO::PARAM_INT);
		$sth->bindParam(4,	$postdata["hostname"],	PDO::PARAM_STR);
		$sth->execute();
	}

	public function query($postdata) {
		if ($this->user->getClass() < User::CLASS_ADMIN) {
			throw new Exception("Du saknar rättigheter.", 401);
		}
		$limit = (int)$postdata["limit"] ?: 25;
		$index = (int)$postdata["index"] ?: 0;

		$where = [];
		$whereStr = "";
		if ($postdata["ip"]) {
			array_push($where, "recoverlog.ip LIKE " . $this->db->quote('%' . $postdata["ip"] . '%'));
		}
		if ($postdata["email"]) {
			array_push($where, "recoverlog.email = " . $this->db->quote($postdata["email"]));
		}
		if (count($where) > 0) {
			$whereStr = " WHERE " . implode(" AND ", $where);
		}

		$sth = $this->db->query("SELECT COUNT(*) FROM recoverlog" . $whereStr);
		$res = $sth->fetch();
		$totalCount = $res[0];

		$sth = $this->db->prepare("SELECT recoverlog.*, users.id, users.username FROM recoverlog LEFT JOIN users ON recoverlog.userid = users.id ".$whereStr." ORDER BY recoverlog.id DESC LIMIT ?, ?");
		$sth->bindParam(1, $index, PDO::PARAM_INT);
		$sth->bindParam(2, $limit, PDO::PARAM_INT);
		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		return Array($result, $totalCount);
	}

	public function check($ip) {
		$date = date("Y-m-d H:i:s", time() - 86400);
		$sth = $this->db->prepare('SELECT COUNT(*) FROM recoverlog WHERE ip = ? AND date > ?');
		$sth->bindParam(1, $ip, 		PDO::PARAM_STR);
		$sth->bindParam(2, $date,		PDO::PARAM_STR);
		$sth->execute();
		$arr = $sth->fetch();
		if ($arr[0] > 5) {
			throw new Exception("Du har gjort för många recovery-försök på samma dag.", 401);
		}
	}

	public function get($id) {}
	public function update($id, $postdata) {}
	public function delete($id, $reason) {}
}
