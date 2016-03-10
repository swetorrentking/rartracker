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
			throw new Exception('The item requested does not exist.');
		}
		return $res;
	}

	public function buy($id, $postdata) {
		if ($this->user->getClass() < User::CLASS_SKADIS) {
			throw new Exception('Du har inte rättigheter till bonussystemet.', 401);
		}

		$item = $this->getShopItem($id);

		if ($item["price"] > $this->user->getBonus()) {
			throw new Exception('Du har inte tillräckligt med bonuspoäng.');
		}

		switch($id) {
			case 6: // invite
				$this->user->bonusLog(-$item["price"], "Köp av invite.", $this->user->getId());
				$this->db->query("UPDATE users SET invites = invites + 1 WHERE id = " . $this->user->getId());
				break;
			case 2: // request slot
				$this->user->bonusLog(-$item["price"], "Köp av request-slot.", $this->user->getId());
				$this->db->query("UPDATE users SET reqslots = reqslots + 1 WHERE id = " . $this->user->getId());
				break;
			case 10: // icon - crown
				$user = $this->user->getUser();
				if ($user["crown"] == 1) {
					throw new Exception('Du har redan en krona.');
				}
				$this->user->bonusLog(-$item["price"], "Köp av ikon krona.", $this->user->getId());
				$this->db->query("UPDATE users SET crown = 1 WHERE id = " . $this->user->getId());
				break;
			case 8: // custom title
				if (strlen($postdata["input"]) < 2) {
					throw new Exception('Titeln för kort.');
				}
				if (strlen($postdata["input"]) > 25) {
					throw new Exception('Titeln för lång.');
				}
				$this->user->bonusLog(-$item["price"], "Köp av custom title.", $this->user->getId());
				$sth = $this->db->prepare("UPDATE users SET title = ? WHERE id = " . $this->user->getId());
				$sth->bindParam(1, $postdata["input"], PDO::PARAM_STR);
				$sth->execute();
				break;
			case 1: // heart
				$receiver = $this->user->get($postdata["userId"]);
				if (!$receiver) {
					throw new Exception('Användaren finns inte.');
				}
				if ($receiver["id"] == $this->user->getId()) {
					throw new Exception('Du kan inte köpa hjärta till dig själv.');
				}
				$this->user->bonusLog(-$item["price"], "Köp av hjärta till [i]".$receiver["username"]."[/i].", $this->user->getId());
				$this->db->query("UPDATE users SET hearts = hearts + 1 WHERE id = " . $receiver["id"]);
				$this->mailbox->sendSystemMessage($receiver["id"], "Du har fått ett hjärta!", "[url=/user/".$this->user->getId() ."/".$this->user->getUsername()."][b]".$this->user->getUsername()."[/b][/url] har varit snäll och för sina bonuspoäng köpt ett hjärta till din profil!\n\nMotivering:[b] " .$postdata["motivation"]."[/b]");
				break;
			case 3: // gigabyte to self
				$user = $this->user->getUser();
				if ($user["downloaded"] < 1073741824*$postdata["amount"] && $postdata["amount"] > 10 && $user["downloaded"] >= 9663676416) {
					throw new Exception('Du kan inte köpa bort mer än vad du har kvar på sitt nerladdat.');
				}
				if ($user["downloaded"] < 8589934592 && $postdata["amount"] == 10) {
					throw new Exception('Du måste ha mer än 8 GB på nerladdat för att kunna köpa bort 10 GB.');
				}
				if ($postdata["amount"] % 10 != 0) {
					throw new Exception('Antal GB kan endast vara i jämna 10 tal.');
				}
				if ($item["price"]*($postdata["amount"]/10) > $this->user->getBonus()) {
					throw new Exception('Du har inte tillräckligt med bonuspoäng.');
				}
				$this->user->bonusLog(-$item["price"]*($postdata["amount"]/10), "Bortköp av ".$postdata["amount"]." GB.", $this->user->getId());
				if ($user["downloaded"] < 10737418240) {
					$this->db->query("UPDATE users SET downloaded = 0 WHERE id = " . $this->user->getId());
				} else {
					$this->db->query("UPDATE users SET downloaded = downloaded - " . 1073741824 * $postdata["amount"] . " WHERE id = " . $this->user->getId());
				}
				break;
			case 4: // gigabyte to friend
				$receiver = $this->user->get($postdata["userId"], true);
				if (!$receiver) {
					throw new Exception('Användaren finns inte.');
				}
				if ($receiver["id"] == $this->user->getId()) {
					throw new Exception('Använd vanliga -10GB för att köpa bort på dig själv.');
				}
				if ($receiver["downloaded"] < 1073741824*$postdata["amount"] && $postdata["amount"] > 10) {
					throw new Exception('Du kan inte köpa bort mer än vad användaren har kvar på sitt nerladdat.');
				}
				if ($receiver["downloaded"] < 8589934592 && $postdata["amount"] == 10) {
					throw new Exception('Användaren måste ha mer än 8 GB på nerladdat för att kunna köpa bort 10 GB.');
				}
				if ($postdata["amount"] % 10 != 0) {
					throw new Exception('Antal GB kan endast vara i jämna 10 tal.');
				}
				if ($item["price"]*($postdata["amount"]/10) > $this->user->getBonus()) {
					throw new Exception('Du har inte tillräckligt med bonuspoäng.');
				}
				$this->user->bonusLog(-$item["price"]*($postdata["amount"]/10), "Bortköp av ".$postdata["amount"]." GB på [i]".$receiver["username"]."[/i].", $this->user->getId());
				$this->mailbox->sendSystemMessage($receiver["id"], "-" . $postdata["amount"]. " GB ifrån " .$this->user->getUsername()."!", "[url=/user/".$this->user->getId() ."/".$this->user->getUsername()."][b]".$this->user->getUsername()."[/b][/url] har för sina bonuspoäng köp bort [b]" . $postdata["amount"]. " GB[/b] från din mängd nedladdat. Grattis!");
				if ($receiver["downloaded"] < 10737418240) {
					$this->db->query("UPDATE users SET downloaded = 0 WHERE id = " . $receiver["id"]);
				} else {
					$this->db->query("UPDATE users SET downloaded = downloaded - " . 1073741824 * $postdata["amount"] . " WHERE id = " . $receiver["id"]);
				}
				break;
			default:
				throw new Exception('Föremålet finns inte i bonusshoppen.');
		}
	}
}
