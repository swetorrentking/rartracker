<?php

class BonusShop {
	private $db;
	private $user;
	private $mailbox;

	public function __construct($db, $user = null, $mailbox = null) {
		$this->db = $db;
		$this->user = $user;
		$this->mailbox = $mailbox;
	}

	public function getShopItems() {
		$sth = $this->db->query("SELECT * FROM shop ORDER BY price ASC");
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getShopItem($id) {
		$sth = $this->db->prepare("SELECT * FROM shop WHERE id = ?");
		$sth->bindParam(1, $id, PDO::PARAM_INT);
		$sth->execute();
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if (!$res) {
			throw new Exception(L::get("BONUS_ITEM_DOESNT_EXIST"), 404);
		}
		return $res;
	}

	public function buy($id, $postdata) {
		if ($this->user->getClass() < User::CLASS_ACTOR) {
			throw new Exception(L::get("PERMISSION_DENIED"), 401);
		}

		$item = $this->getShopItem($id);

		if ($item["price"] > $this->user->getBonus()) {
			throw new Exception(L::get("NOT_ENOUGH_BONUS", 412));
		}

		switch($id) {
			case 6: // invite
				if ($this->user->getClass() < User::CLASS_DIRECTOR) {
					throw new Exception(L::get("MINIMUM_CLASS_FOR_INVITES"), 401);
				}
				$this->user->bonusLog(-$item["price"], L::get("BONUS_INVITE_PURCHASE"), $this->user->getId());
				$this->db->query("UPDATE users SET invites = invites + 1 WHERE id = " . $this->user->getId());
				break;
			case 2: // request slot
				$this->user->bonusLog(-$item["price"], L::get("BONUS_REQUEST_PURCHASE"), $this->user->getId());
				$this->db->query("UPDATE users SET reqslots = reqslots + 1 WHERE id = " . $this->user->getId());
				break;
			case 10: // icon - crown
				$user = $this->user->getUser();
				if ($user["crown"] == 1) {
					throw new Exception(L::get("BONUS_CROWN_DUPLICATE"));
				}
				$this->user->bonusLog(-$item["price"], L::get("BONUS_CROWN_PURCHASE"), $this->user->getId());
				$this->db->query("UPDATE users SET crown = 1 WHERE id = " . $this->user->getId());
				break;
			case 8: // custom title
				if (strlen($postdata["input"]) < 2) {
					throw new Exception(L::get("BONUS_CUSTOM_TITLE_TOO_SHORT"));
				}
				if (strlen($postdata["input"]) > 25) {
					throw new Exception(L::get("BONUS_CUSTOM_TITLE_TOO_LONG"));
				}
				$this->user->bonusLog(-$item["price"], L::get("BONUS_CUSTOM_TITLE_PURCHASE"), $this->user->getId());
				$sth = $this->db->prepare("UPDATE users SET title = ? WHERE id = " . $this->user->getId());
				$sth->bindParam(1, $postdata["input"], PDO::PARAM_STR);
				$sth->execute();
				break;
			case 1: // heart
				$receiver = $this->user->get($postdata["userId"]);
				if (!$receiver) {
					throw new Exception(L::get("USER_NOT_EXIST"));
				}
				if ($receiver["id"] == $this->user->getId()) {
					throw new Exception(L::get("BONUS_HEART_SELF_ERROR"));
				}
				$this->user->bonusLog(-$item["price"], L::get("BONUS_HEART_PURCHASE", [$receiver["username"]]), $this->user->getId());
				$this->db->query("UPDATE users SET hearts = hearts + 1 WHERE id = " . $receiver["id"]);
				$this->mailbox->sendSystemMessage($receiver["id"], L::get("BONUS_HEART_PM_SUBJECT", null, $receiver["language"]), L::get("BONUS_HEART_PM_BODY", [$this->user->getId(), $this->user->getUsername(), $this->user->getUsername(), $postdata["motivation"]], $receiver["language"]));
				break;
			case 3: // gigabyte to self
				$user = $this->user->getUser();
				if ($user["downloaded"] < 1073741824*$postdata["amount"] && $postdata["amount"] > 10 && $user["downloaded"] >= 9663676416) {
					throw new Exception(L::get("BONUS_GB_LIMIT"));
				}
				if ($user["downloaded"] < 8589934592 && $postdata["amount"] == 10) {
					throw new Exception(L::get("BONUS_GB_MINIMUM_REQUIREMENT"));
				}
				if ($postdata["amount"] % 10 != 0) {
					throw new Exception(L::get("BONUS_GB_VALUE_ERROR"));
				}
				if ($item["price"]*($postdata["amount"]/10) > $this->user->getBonus()) {
					throw new Exception(L::get("NOT_ENOUGH_BONUS"), 412);
				}
				$this->user->bonusLog(-$item["price"]*($postdata["amount"]/10), L::get("BONUS_PURCHASE_GB", [$postdata["amount"]]), $this->user->getId());
				if ($user["downloaded"] < 10737418240) {
					$this->db->query("UPDATE users SET downloaded = 0 WHERE id = " . $this->user->getId());
				} else {
					$this->db->query("UPDATE users SET downloaded = downloaded - " . 1073741824 * $postdata["amount"] . " WHERE id = " . $this->user->getId());
				}
				break;
			case 4: // gigabyte to friend
				$receiver = $this->user->get($postdata["userId"], true);
				if (!$receiver) {
					throw new Exception(L::get("USER_NOT_EXIST"));
				}
				if ($receiver["id"] == $this->user->getId()) {
					throw new Exception(L::get("BONUS_GB_USE_REGULAR"));
				}
				if ($receiver["downloaded"] < 1073741824*$postdata["amount"] && $postdata["amount"] > 10) {
					throw new Exception(L::get("BONUS_GB_LIMIT_FRIEND"));
				}
				if ($receiver["downloaded"] < 8589934592 && $postdata["amount"] == 10) {
					throw new Exception(L::get("BONUS_GB_MINIMUM_REQUIREMENT_FRIEND"));
				}
				if ($postdata["amount"] % 10 != 0) {
					throw new Exception(L::get("BONUS_GB_VALUE_ERROR"));
				}
				if ($item["price"]*($postdata["amount"]/10) > $this->user->getBonus()) {
					throw new Exception(L::get("NOT_ENOUGH_BONUS"));
				}
				$this->user->bonusLog(-$item["price"]*($postdata["amount"]/10), L::get("BONUS_GB_FRIEND", [$postdata["amount"], $receiver["username"]]), $this->user->getId());
				$this->mailbox->sendSystemMessage($receiver["id"], L::get("BONUS_GB_FRIEND_PM_SUBJECT", [$postdata["amount"], $this->user->getUsername()], $receiver["language"]), L::get("BONUS_GB_FRIEND_PM_BODY", [$this->user->getId(), $this->user->getUsername(), $this->user->getUsername(), $postdata["amount"]], $receiver["language"]));
				if ($receiver["downloaded"] < 10737418240) {
					$this->db->query("UPDATE users SET downloaded = 0 WHERE id = " . $receiver["id"]);
				} else {
					$this->db->query("UPDATE users SET downloaded = downloaded - " . 1073741824 * $postdata["amount"] . " WHERE id = " . $receiver["id"]);
				}
				break;
			default:
				throw new Exception(L::get("BONUS_ITEM_DOESNT_EXIST"), 404);
		}
	}
}
