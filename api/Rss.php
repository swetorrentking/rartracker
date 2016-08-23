<?php

class Rss {
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function renderRssFeed($params) {
		$passkey = $params["passkey"];

		if (!preg_match("/^[a-z0-9]{32}$/", $passkey)) {
			throw new Exception(L::get("USER_EMAIL_PASSKEY_NO_MATCH"), 401);
		}

		$sth = $this->db->prepare("SELECT id FROM users WHERE passkey = ?");
		$sth->bindParam(1, $passkey, PDO::PARAM_STR);
		$sth->execute();
		$user = $sth->fetch();

		if (!$user) {
			throw new Exception(L::get("USER_EMAIL_PASSKEY_NO_MATCH"), 401);
		}

		$s = $params["s"];

		if (!$s) {
			$s = $params["vad"];
		}

		$cats = $params["cat"];

		if ($cats) {
			if (!preg_match("/^[0-9,]+$/", $cats)) {
				echo "Invalid categories";
				exit;
			}
			$cats = explode(",", $cats);
		}

		$where = array();
		$finalWhere = "";

		if ($cats) {
			$where[] = "category IN (".implode(", ", $cats).")";
		}

		if ($s == 1) {
			$where[] = "section = 'new'";
		} else if($s == 2)
			$where[] = "section = 'archive'";
		else if ($s == 3) {
			$bookmark = true;
		}

		if ($params['p2p'] != "1") {
			$where[] = 'p2p = 0';
		}

		if (count($where) > 0) {
			$finalWhere = "WHERE " . implode(" AND ", $where);
		}

		$SITENAME = Config::NAME;
		$DESCR = "RSS Feeds";
		$BASEURL = Config::SITE_URL;
		$SITEMAIL = Config::SITE_MAIL;

		header("Content-Type: application/xml");
		print("<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<rss version=\"0.91\">\n<channel>\n" .
		"<title>" . $SITENAME . "</title>\n<link>" . $BASEURL . "</link>\n<description>" . $DESCR . "</description>\n" .
		"<language>en-usde</language>\n<copyright> Copyright " . $SITENAME . "</copyright>\n<webMaster>".$SITEMAIL."</webMaster>\n" .
		"<image><title>" . $SITENAME . "</title>\n<url>" . $BASEURL . "/favicon.ico</url>\n<link>" . $BASEURL . "</link>\n" .
		"<width>16</width>\n<height>16</height>\n<description>" . $DESCR . "</description>\n</image>\n");

		if ($bookmark) {
			$res = $this->db->query("SELECT torrents.id, name, descr, filename, size, category, seeders, leechers, added FROM bookmarks LEFT JOIN torrents ON bookmarks.torrentid = torrents.id WHERE bookmarks.userid = ".$user[0]." ORDER BY bookmarks.id DESC LIMIT 15");
		} else {
			$res = $this->db->query("SELECT id,name,descr,filename,size,category,seeders,leechers,added FROM torrents $finalWhere ORDER BY added DESC LIMIT 15");
		}

		while ($row = $res->fetch()){
			list($id, $name, $descr, $filename, $size, $cat, $seeders, $leechers, $added, $catname) = $row;

			$link = $BASEURL . "/api/v1/torrents/download/$id/$passkey";

			echo("<item><title>" . htmlspecialchars($name) . "</title>\n<link>" . $link . "</link>\n<description>Kategori: " . Helper::getCategoryById($cat) . " \n Storlek: " . Helper::mksize($size) . "\n " . htmlspecialchars($descr) . "\n</description>\n<pubDate>".$added."</pubDate></item> \n");
		}

		echo("</channel>\n</rss>\n");
	}

}
