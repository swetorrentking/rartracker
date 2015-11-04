<?php

if ($_SERVER['SERVER_PORT'] != 1337 && $_SERVER['SERVER_PORT'] != 1338) {
	die();
}

$bannade = array(
	'btpd',
	'LimeWire',
	'BitComet',
	'BTuga',
	'ktorrent',
	'Mozilla',
	'Shareaza',
	'BitLord',
	'BitTorrent/3.4.2'
);

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?: "";

foreach ($bannade as $k) {
	if (strpos($userAgent, $k) > -1) {
		err("Klienten du använder är inte tillåten.");
	}
}

require('api/secrets.php'); //or die(err('Database error (could not connect)'));
try {
	$db = new PDO($database.':host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	err('Database error (' .$e->getMessage() .')');
}

$setting['time_me']                 = false; // calculate execution times (requires log_debug)
$setting['log_debug']               = false; // log debugging information using debuglog()
$setting['log_errors']              = false; // log all errors sent using err()
$setting['timestamp_format']        = '[d/m/y H:i:s] ';
$setting['log_file']                = '/var/www/debug.txt';
$setting['gzip']                    = true; // gzip the data sent to the clients
$setting['allow_old_protocols']     = true; // allow no_peer_id and original protocols for compatibility
$setting['allow_global_scrape']     = false; // enable scrape-statistics for all torrents if no info_hash specified - wastes bandwidth on big trackers
$setting['default_give_peers']      = 50; // how many peers to give to client by default
$setting['max_give_peers']          = 150; // maximum peers client may request
$setting['announce_interval']       = rand(3000, 3600); // 28-33 min - spread load a bit on the webserver
$setting['rate_limitation']         = true; // calculate the clients average upload-speed
$setting['rate_limitation_warn_up'] = 2; // log a warning if exceeding this amount of MB/s
$setting['rate_limitation_err_up']  = 60; // log a error and don't save stats for user if exceeding this amount of MB/s
$setting['register_stats']          = true; // save transfer statistics for the users? [0-1 extra mysql-queries]
$setting['upload_multiplier']       = 1;
$setting['download_multiplier']     = 1;
$setting['passkey_length']          = 32;

function debuglog($str) {
	if ($setting['log_debug']) {
		file_put_contents('trackerdebug.txt', date('[H:i:s]') . ' ' . $str . ' URL: ' . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
	}
}

if ($setting['time_me'] || $setting['log_debug']) {
	$start = gettimeofday();
}

$keys = explode('/', $_SERVER['REQUEST_URI']);

if (strlen($keys[2]) !== $setting['passkey_length'] || !ctype_alnum($keys[2])) { // check passkey-format
	err('Invalid passkey. Re-download torrent!'); //(Length: ' . strlen($keys[2]) . '.'.')');
}
$passkey = $keys[2];

if (strlen($_GET['info_hash']) < 4) {
	preg_match("/info_hash=(.*?)($|&)/", $_SERVER['REQUEST_URI'], $match);
	$_GET["info_hash"] = urldecode($match[1]);
}

if (strlen($_GET['peer_id']) < 4) {
	preg_match("/peer_id=(.*?)&/", $_SERVER['REQUEST_URI'], $match);
	$_GET["peer_id"] = urldecode($match[1]);
}

$info_hash_hex = bin2hex($_GET['info_hash']);

if (strpos($keys[3], 'announce') !== false) { // jump into appropriate section for announce or scrape mode
	
	$peer_id = hasheval($_GET['peer_id'], '20', 'peer_id');
	$seeder  = ($_GET['left'] == 0) ? 'yes' : 'no';
	// required values - we want numbers only
	$intvars = array(
		'port',
		'uploaded',
		'downloaded',
		'left'
	);
	foreach ($intvars as $var) {
		if (!isset($_GET[$var]) || ctype_digit($_GET[$var]) === false) {
			err('Invalid key: ' . $var . '.');
		}
	}
	
	if ($_GET['port'] > 0xffff || $_GET['port'] < 1) {
		err('Invalid port number.');
	}
	
	$ip = getip();
	
	
	// optional values - we want numbers only
	$intoptvars = array(
		'numwant',
		'compact',
		'no_peer_id'
	);
	foreach ($intoptvars as $var) {
		if (isset($_GET[$var]) && ctype_digit($_GET[$var] === false)) {
			err('Invalid opt key: ' . $var . '.');
		}
	}

	
	if (isset($_GET['event'])) {
		if (ctype_alpha($_GET['event']) === false) {
			// event was sent, but it contains invalid information
			err('Invalid event.');
		}
		$events = array(
			'started',
			'stopped',
			'completed',
			'paused'
		);
		if (!in_array($_GET['event'], $events)) {
			err('Invalid event.');
		}
		$event = $_GET['event'];
	}
	if (!$setting['allow_old_protocols']) {
		if (!isset($_GET['compact']) && ($event != 'stopped' && $event != 'completed')) { // client has not stopped or completed - should say it supports compact if doing so
			err('Please upgrade or change client.');
		}
	}
	// all values should now have checked out ok

	$sth = $db->prepare("SELECT id, downloaded, uploaded, to_go, seeder, ip, UNIX_TIMESTAMP(last_action), torrent, frileech, connectable, userid, nytt, user, leechbonus, torrentsize, UNIX_TIMESTAMP(added) FROM peers WHERE info_hash = ? AND port = ? AND ip = ?");
	$sth->bindParam(1, $info_hash_hex,	PDO::PARAM_STR);
	$sth->bindParam(2, $_GET['port'],	PDO::PARAM_INT);
	$sth->bindParam(3, $ip,				PDO::PARAM_STR);
	$sth->execute();

	if ($sth->rowCount() == 0) { // peer not found - insert into database, but only if not event=stopped

		if ($setting['log_debug']) {
			debuglog('announce: peer not found!');
		}
		if ($_GET['event'] == 'stopped') {
			err('Client sent stop, but peer not found!');
		}
		
		/* HÄMTA USER INFO - START */
		$sth = $db->prepare("SELECT id, username, class, UNIX_TIMESTAMP(leechstart) as leechstart, mbitupp, mbitner, leechbonus FROM users WHERE passkey = ? AND enabled = 'yes'");
		$sth->bindParam(1, $passkey,	PDO::PARAM_STR);
		$sth->execute();

		if ($sth->rowCount() !== 1) { // a valid passkey was not found or the account was disabled
			err('Permission denied.');
		}

		list($u_id, $u_name, $u_class, $u_leech, $u_mbitupp, $u_mbitner, $u_leechbonus) = $sth->fetch();

		/* HÄMTA USER INFO - SLUT */

		
		/* HÄMTA TORRENT INFO - START*/

		$sth = $db->prepare("SELECT id, leechers, seeders, frileech, reqid, size, added FROM torrents WHERE info_hash = ?");
		$sth->bindParam(1, $info_hash_hex,	PDO::PARAM_STR);
		$sth->execute();

		if ($sth->rowCount() != 1) { // could not find the requested torrent in the database
			err('Torrent does not exist on this tracker.');
		}
		list($t_id, $t_leechers, $t_seeders, $t_frileech, $t_reqid, $t_size, $t_added) = $sth->fetch();
		/* HÄMTA TORRENT INFO - SLUT */
		
		// retunera 0/1 om port öppen
		$ansl = connectable($ip, (int)$_GET['port']);
		
		// Om användaren har fri leech blir Peer bli leech.
		$nu = time();
		$frileech = 0;
		if ($t_frileech == 1 || $u_leech > $nu) {
			$frileech = 1;
		}

		/* Get, (INSERT?) and update Snatch */

		$timesStarted = 0;
		$timesCompleted = 0;
		$timesUpdated = 0;


		if ($event == 'completed' && $_GET['left'] == 0) {
			$timesCompleted = 1;
		} else if ($event == "started" && $_GET['left'] == $t_size) {
			$timesStarted = 1;
		} else {
			$timesUpdated = 1;
		}

		$sth = $db->prepare("SELECT id FROM snatch WHERE userid = ? AND torrentid = ?");
		$sth->bindParam(1, $u_id,	PDO::PARAM_INT);
		$sth->bindParam(2, $t_id,	PDO::PARAM_INT);
		$sth->execute();

		if ($sth->rowCount() == 1) {

			$sth = $db->prepare("UPDATE snatch SET timesStarted = timesStarted + ?, timesCompleted = timesCompleted + ?, timesUpdated = timesUpdated + ?, lastaction = NOW() WHERE userid = ? AND torrentid = ?");
			$sth->bindParam(1, $timesStarted,	PDO::PARAM_INT);
			$sth->bindParam(2, $timesCompleted,	PDO::PARAM_INT);
			$sth->bindParam(3, $timesUpdated,	PDO::PARAM_INT);
			$sth->bindParam(4, $u_id,			PDO::PARAM_INT);
			$sth->bindParam(5, $t_id,			PDO::PARAM_INT);
			$sth->execute();

		} else {

			$sth = $db->prepare("INSERT INTO snatch(userid, torrentid, ip, port, agent, connectable, klar, lastaction, timesStarted, timesCompleted, timesUpdated) VALUES(?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?)");
			$sth->bindParam(1, $u_id,						PDO::PARAM_INT);
			$sth->bindParam(2, $t_id,						PDO::PARAM_INT);
			$sth->bindParam(3, $ip,							PDO::PARAM_STR);
			$sth->bindParam(4, $_GET['port'],				PDO::PARAM_INT);
			$sth->bindParam(5, $userAgent,					PDO::PARAM_STR);
			$sth->bindParam(6, $ansl,						PDO::PARAM_INT);
			$sth->bindParam(7, $timesStarted,				PDO::PARAM_INT);
			$sth->bindParam(8, $timesCompleted,				PDO::PARAM_INT);
			$sth->bindParam(9, $timesUpdated,				PDO::PARAM_INT);
			$sth->execute();
		}

		$compact = pack('Nn', ip2long($ip), $_GET['port']);

		$sth = $db->prepare("INSERT INTO peers (torrent, userid, peer_id, ip, compact, port, uploaded, uploadoffset, downloaded, downloadoffset, to_go, seeder, started, last_action, agent, connectable, info_hash, frileech, user, mbitupp, mbitner, nytt, leechbonus, torrentsize, added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$sth->bindParam(1, $t_id,							PDO::PARAM_INT);
		$sth->bindParam(2, $u_id,							PDO::PARAM_INT);
		$sth->bindParam(3, $peer_id,						PDO::PARAM_STR);
		$sth->bindParam(4, $ip,								PDO::PARAM_STR);
		$sth->bindParam(5, $compact,						PDO::PARAM_STR);
		$sth->bindParam(6, $_GET['port'],					PDO::PARAM_INT);
		$sth->bindParam(7, $_GET['uploaded'],				PDO::PARAM_INT);
		$sth->bindParam(8, $_GET['uploaded'],				PDO::PARAM_INT);
		$sth->bindParam(9, $_GET['downloaded'],				PDO::PARAM_INT);
		$sth->bindParam(10, $_GET['downloaded'],			PDO::PARAM_INT);
		$sth->bindParam(11, $_GET['left'],					PDO::PARAM_INT);
		$sth->bindParam(12, $seeder,						PDO::PARAM_STR);
		$sth->bindParam(13, $userAgent,						PDO::PARAM_STR);
		$sth->bindParam(14, $ansl,							PDO::PARAM_INT);
		$sth->bindParam(15, $info_hash_hex,					PDO::PARAM_STR);
		$sth->bindParam(16, $frileech,						PDO::PARAM_INT);
		$sth->bindParam(17, $u_class,						PDO::PARAM_INT);
		$sth->bindParam(18, $u_mbitupp,						PDO::PARAM_INT);
		$sth->bindParam(19, $u_mbitner,						PDO::PARAM_INT);
		$sth->bindParam(20, $t_reqid,						PDO::PARAM_INT);
		$sth->bindParam(21, $u_leechbonus,					PDO::PARAM_INT);
		$sth->bindParam(22, $t_size,						PDO::PARAM_INT);
		$sth->bindParam(23, $t_added,						PDO::PARAM_STR);
		$sth->execute();

		
		if ($seeder == 'yes') {
			$sth = $db->prepare("UPDATE LOW_PRIORITY torrents SET last_action = NOW(), seeders = seeders + 1 WHERE id = ?");
			$sth->bindParam(1, $t_id,	PDO::PARAM_INT);
			$sth->execute();
		} else {
			$sth = $db->prepare("UPDATE LOW_PRIORITY torrents SET last_action = NOW(), leechers = leechers + 1 WHERE id = ?");
			$sth->bindParam(1, $t_id,	PDO::PARAM_INT);
			$sth->execute();
		}

		give_peers();
		
		
	} elseif ($sth->rowCount() == 1) {

		// peer found - update stats, check if peer is stopping, else send peer list
		list($peerid, $downloaded, $uploaded, $left, $seeder_db, $ip_db, $last_access, $t_id, $t_fri, $ansl, $u_id, $t_reqid, $u_class, $u_leechbonus, $t_size, $t_added) = $sth->fetch();
		

		// calculate download and upload speed based on difference in amounts since last time reported in
		if ($setting['rate_limitation'] === true) {
			$duration = time() - $last_access;
			if ($duration > 0) {
				$downspeed = round(($_GET['downloaded'] - $downloaded) / $duration);
				$upspeed   = round(($_GET['uploaded'] - $uploaded) / $duration);
				
				$host  = dns_timeout($ip);
				$cheatLevel = 0;
				if ($host != 0) {
					if (strpos($host, 'tbcn.telia') > -1 && $upspeed > 307200)
						$cheatLevel = 1;
					elseif (strpos($host, 'skanova') > -1 && $upspeed > 307200)
						$cheatLevel = 1;
				}
				
				if (($userAgent == 'uTorrent/161B(483)' || $userAgent == 'ABC/ABC-3.1.0') && $upspeed > 105200)
					$cheatLevel = 1;
				
				if ($upspeed > (1024000 * $setting['rate_limitation_err_up'])) { // check for excessive speeds
					//$setting['upload_multiplier'] = 0;
					log_cheater($u_id, $t_id, $_GET['downloaded'] - $downloaded, $_GET['uploaded'] - $uploaded, $duration, $userAgent, $ip, 0, $_GET['port'], $upspeed, $ansl);
				} elseif ($upspeed > (1024000 * $setting['rate_limitation_warn_up']) || $cheatLevel) {
					log_cheater($u_id, $t_id, $_GET['downloaded'] - $downloaded, $_GET['uploaded'] - $uploaded, $duration, $userAgent, $ip, $cheatLevel, $_GET['port'], $upspeed, $ansl);
				}
				
				/* If there are no leechers (or this is the only leecher), and this client claims to be uploading, it may be a cheater - log!
				if($seeder == "yes" && $event != 'completed') {
				$minleech = 0;
				} else {
				$minleech = 1;
				}
				*/
			} else {
				// less then a second or negative since last contacted tracker - suspicious, log?
				$down                      = ($_GET['downloaded'] - $downloaded);
				$up                        = ($_GET['uploaded'] - $uploaded);
				$setting['register_stats'] = false;
				debuglog('announce: user ' . $u_id . ' client hammering - up: ' . number_format($up) . ', down: ' . number_format($down));
			}
		}
		
		// only update if there has been a change, and it is a increase :)
		if ($setting['register_stats'] === true && (($_GET['downloaded'] > $downloaded) || ($_GET['uploaded'] > $uploaded))) {
			
			$add_up = $_GET['uploaded'] - $uploaded;

			$add_up_real = $add_up;
			
			$arkivupp = 0;
			if ($t_reqid != 0)
				$arkivupp = $add_up;
			else if ($t_reqid == 0) {
				$nytt_upp = $add_up;
			}
			
			if ($t_fri == 0) {
				$add_down = ($_GET['downloaded'] - $downloaded) * $setting['download_multiplier'];
			} else {
				$add_down = 0; // om torrenten är fri leech
			}
			
			if ($u_class == 0) {
				$add_down = $add_down / 2;
			}

			if (time() - $t_added < 86400 && $t_reqid == 0 && $t_fri == 0) {
				$add_down = 0;
				$add_up_real = 0;
			}
			
			// Leechbonusen
			$procent   = (100 - $u_leechbonus) / 100;
			$add_down  = $add_down * $procent;
			$add_down2 = ($_GET['downloaded'] - $downloaded); // Real download
			
			if ($setting['log_debug']) {
				debuglog('announce: updating user stats - up/down: ' . $add_up . '/' . $add_down);
			}
			
			if ($u_class > 7)
				$dip = 'Dolt IP';
			else
				$dip = $ip;
			
			/* FRI LEECH PÅSLAGET */
			//$add_down = 0;

			$sth = $db->prepare("UPDATE LOW_PRIORITY users SET uploaded = uploaded + ?, nytt_seed = nytt_seed + ?, arkiv_seed = arkiv_seed + ?, downloaded = downloaded + ?, downloaded_real = downloaded_real + ?, torrentip =? WHERE id = ?");
			$sth->bindParam(1, $add_up_real,	PDO::PARAM_INT);
			$sth->bindParam(2, $nytt_upp,		PDO::PARAM_INT);
			$sth->bindParam(3, $arkivupp,		PDO::PARAM_INT);
			$sth->bindParam(4, $add_down,		PDO::PARAM_INT);
			$sth->bindParam(5, $add_down2,		PDO::PARAM_INT);
			$sth->bindParam(6, $dip,			PDO::PARAM_STR);
			$sth->bindParam(7, $u_id,			PDO::PARAM_INT);
			$sth->execute();

			// if download just completed - add to the number on the torrent table - but only if a seeder
			if ($event == 'completed' && $_GET['left'] == 0) {
				$sth = $db->prepare("UPDATE LOW_PRIORITY torrents SET seeders = seeders + 1, leechers = leechers - 1, times_completed = times_completed + 1 WHERE id = ?");
				$sth->bindParam(1, $t_id,	PDO::PARAM_INT);
				$sth->execute();
			}

		}


		/* Update Snatch Stats */

		$timesCompleted = 0;
		$timesStopped = 0;
		$timesUpdated = 0;

		if ($event == 'completed' && $_GET['left'] == 0) {
			$timesCompleted = 1;
		} else if ($event == "stopped" && $_GET['left'] > 0) {
			$timesStopped = 1;
		} else {
			$timesUpdated = 1;
		}

		$seedtime = time() - $last_access;

		$sth = $db->prepare("UPDATE LOW_PRIORITY snatch SET timesCompleted = timesCompleted + ?, timesUpdated = timesUpdated + ?, timesStopped = timesStopped + ?, lastaction = NOW(), uploaded = uploaded + ?, downloaded = downloaded + ?, seedtime = seedtime + ? WHERE userid = ? AND torrentid = ?");
		$sth->bindParam(1, $timesCompleted,	PDO::PARAM_INT);
		$sth->bindParam(2, $timesUpdated,	PDO::PARAM_INT);
		$sth->bindParam(3, $timesStopped,	PDO::PARAM_INT);
		$sth->bindParam(4, $add_up,			PDO::PARAM_INT);
		$sth->bindParam(5, $add_down2,		PDO::PARAM_INT);
		$sth->bindParam(6, $seedtime,		PDO::PARAM_INT);
		$sth->bindParam(7, $u_id,			PDO::PARAM_INT);
		$sth->bindParam(8, $t_id,			PDO::PARAM_INT);
		$sth->execute();

		/* END snatch update */
		
		
		// peer has closed - remove the peer and exit, no updates to do or peers to send to client
		if ($event == 'stopped') {
			$sth = $db->prepare("DELETE FROM peers WHERE id = ?");
			$sth->bindParam(1, $peerid,	PDO::PARAM_INT);
			$sth->execute();
			
			if ($seeder_db == 'yes') {
				$sth = $db->prepare("UPDATE LOW_PRIORITY torrents SET seeders = seeders - 1 WHERE id = ?");
				$sth->bindParam(1, $t_id,	PDO::PARAM_INT);
				$sth->execute();
			} else {
				$sth = $db->prepare("UPDATE LOW_PRIORITY torrents SET leechers = leechers - 1 WHERE id = ?");
				$sth->bindParam(1, $t_id,	PDO::PARAM_INT);
				$sth->execute();
			}
			
			die();
		}

		// $finishedat = 

		$finishedAt = "";
		if ($event == 'completed' && $seeder == 'yes' && $seeder_db == 'no') {
			$finishedAt = ", finishedat = UNIX_TIMESTAMP(NOW())";
		}

		$sth = $db->prepare("UPDATE LOW_PRIORITY peers SET uploaded = ?, downloaded = ?, to_go = ?, seeder = ?, last_action = NOW(), ip = ? " . $finishedAt . " WHERE id = ?");
		$sth->bindParam(1, $_GET['uploaded'],			PDO::PARAM_INT);
		$sth->bindParam(2, $_GET['downloaded'],			PDO::PARAM_INT);
		$sth->bindParam(3, $_GET['left'],				PDO::PARAM_INT);
		$sth->bindParam(4, $seeder,						PDO::PARAM_STR);
		$sth->bindParam(5, $ip,							PDO::PARAM_STR);
		$sth->bindParam(6, $peerid,						PDO::PARAM_INT);
		$sth->execute();

		if ($seeder == 'yes' && rand(0, 4) == 0) {
			$sth = $db->prepare("UPDATE LOW_PRIORITY torrents SET last_action = NOW() WHERE id = ?");
			$sth->bindParam(1, $t_id,	PDO::PARAM_INT);
			$sth->execute();
		}
		
		// give the client some peers to play with
		give_peers();

		
	} else {
		// we hit multiple? but that's UNPOSSIBLE! ;)
		if ($setting['log_debug']) {
			debuglog('announce: got multiple targets in peer table!');
		}
		err('Got multiple targets in peer table!');
	}
	
	if ($setting['time_me'] && $setting['log_debug']) {
		debuglog('announce: ' . function_timer($start, gettimeofday(), 1) . ' us)');
	}
	
	die();

} else if (strpos($keys['3'], 'scrape') !== false) { // do-scrape code
	
	// compression - saves few bytes?
	if ($setting['gzip']) {
		ini_set('zlib.output_compression_level', 1);
		ob_start('ob_gzhandler');
	}

	if (strlen($info_hash_hex) != 40 && $setting['allow_global_scrape'] == false) { // if a valid info_hash was not specified, send empty - save bandwidth
		if ($setting['time_me'] && $setting['log_debug']) {
			debuglog('scrape - empty: ' . function_timer($start, gettimeofday(), 1) . ' us)');
		}
		die('d5:filesdee');
	}

	$sth = $db->prepare("SELECT unhex(info_hash) AS info_hash, times_completed, seeders, leechers FROM torrents WHERE info_hash = ?");
	$sth->bindParam(1, $info_hash_hex,	PDO::PARAM_STR);
	$sth->execute();

	$resp = 'd5:filesd';
	while ($torrent = $sth->fetch(PDO::FETCH_ASSOC)) { // yes, no bencoding functions here
		$resp .= '20:' . $torrent['info_hash'] . 'd' . '8:completei' . (int) $torrent['seeders'] . 'e' . '10:incompletei' . (int) $torrent['leechers'] . 'e' . '10:downloadedi' . (int) $torrent['times_completed'] . 'e' . 'e';
	}

	$resp .= 'ee';
	echo ($resp);
	if ($setting['time_me'] && $setting['log_debug']) {
		debuglog('scrape: ' . function_timer($start, gettimeofday(), 1) . ' us)');
	}
	
	die();
} else {
	err('Unknown action.');
}

function give_peers()
{	
	global $db, $setting, $t_id;
	$sth = $db->prepare("SELECT compact, ip, port, peer_id FROM peers WHERE torrent = ? ORDER BY RAND() LIMIT 150");
	$sth->bindParam(1, $t_id,	PDO::PARAM_INT);
	$sth->execute();

	$resp = 'd8:intervali' . $setting['announce_interval'] . 'e12:min intervali' . intval(900) . 'e5:peers';
	if ($_GET['compact'] == 1) { // compact mode - we like (gzip not gaining anything - don't use)e
		while ($peer = $sth->fetch(PDO::FETCH_ASSOC)) {
			$clients .= $peer['compact'];
		}
		echo $resp . strlen($clients) . ':' . $clients . 'ee';
		if ($setting['log_debug']) {
			debuglog('announce: gave ' . $sth->rowCount() . ' using compact protocol');
		}
	} elseif ($_GET['no_peer_id'] == 1) { // no_peer_id protocol - better then nothing
		if ($setting['gzip']) {
			ini_set('zlib.output_compression_level', 1);
			ob_start('ob_gzhandler');
		}
		$resp .= 'l';
		while ($peer = $sth->fetch(PDO::FETCH_ASSOC)) {
			$resp .= 'd2:ip' . strlen($peer['ip']) . ':' . $peer['ip'] . '4:porti' . $peer['port'] . 'ee';
		}
		echo $resp . 'ee';
		if ($setting['log_debug']) {
			debuglog('announce: gave ' . $sth->rowCount() . ' using no_peer_id protocol');
		}
	} else { // horrible! gzip to the rescue!
		if ($setting['gzip']) {
			ini_set('zlib.output_compression_level', 1);
			ob_start('ob_gzhandler');
		}
		$resp .= 'l';
		while ($peer = $sth->fetch(PDO::FETCH_ASSOC)) {
			$resp .= 'd2:ip' . strlen($peer['ip']) . ':' . $peer['ip'] . '7:peer id20:' . $peer['peer_id'] . '4:porti' . $peer['port'] . 'ee';
		}
		
		// retunera peers
		echo $resp . 'ee';
		if ($setting['log_debug']) {
			debuglog('announce: gave ' . $sth->rowCount() . ' using original protocol');
		}
		
	}
	
}

function hasheval($str, $len, $name = false) // try to get a $len-byte string, err out if not possible, give $name if possible
{
	if (strlen($str) != $len) {
		$str = stripcslashes($str);
		if (strlen($str) != $len) {
			if ($name) {
				err('Invalid ' . $name . ' (' . strlen($str) . ') ' . bin2hex($str));
				debuglog('Invalid ' . $name . ' (' . strlen($str) . ') ' . bin2hex($str));
				debuglog($_SERVER['REQUEST_URI']);
			} else {
				err('Invalid string (' . strlen($str) . ') ' . bin2hex($str));
			}
		}
	}
	return $str;
}

// Cred: ethernal
function function_timer($start, $end, $div = 1, $format = 1) // $start gettimeofday(); $end gettimeofday(); $div = number to divide by
{
	$end['usec'] = ($end['usec'] + (($end['sec'] - $start['sec']) * 1000000));
	if ($format) {
		return number_format((($end['usec'] - $start['usec']) / $div), 0);
	} else {
		return round((($end['usec'] - $start['usec']) / $div), 0);
	}
}

function err($txt, $err = '')
{
	global $start, $setting;
	
	echo ('d14:failure reason' . strlen($txt) . ':' . $txt . 'e');
	if ($setting['log_errors']) {
		debuglog($txt . '; ' . mysql_error() . $err);
	}
	die();
}

function getip()
{
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	} else {
		if (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} else {
			$ip = getenv('REMOTE_ADDR');
		}
	}
	
	return $ip;
}

function validip($ip)
{
	// modified stuff from SKORPiUS tracker
	$ip = ip2long($ip);
	if (empty($ip) || $ip == '-1') {
		return false;
	}
	// reserved IANA IPv4 addresses
	// http://www.iana.org/assignments/ipv4-address-space
	$reserved_ips = array(
		array(
			'0',
			'50331647'
		), // '0.0.0.0','2.255.255.255'
		array(
			'167772160',
			'184549375'
		), // '10.0.0.0','10.255.255.255'
		array(
			'2130706432',
			'2147483647'
		), // '127.0.0.0','127.255.255.255'
		array(
			'-1442971648',
			'-1442906113'
		), // '169.254.0.0','169.254.255.255'
		array(
			'-1408237568',
			'-1407188993'
		), // '172.16.0.0','172.31.255.255'
		array(
			'-1073741312',
			'-1073741057'
		), // '192.0.2.0','192.0.2.255'
		array(
			'-1062731776',
			'-1062666241'
		), // '192.168.0.0','192.168.255.255'
		array(
			'-256',
			'-1'
		) // '255.255.255.0','255.255.255.255'
	);
	
	foreach ($reserved_ips as $r) { // $r[0] = min, $r[1] = max
		if (($ip >= $r[0]) && ($ip <= $r[1])) {
			return false;
		}
	}
	return true;
}

function logError($type, $message, $file, $line, $context)
{
	global $setting;
	
	$errors = array(
		1 => 'E_ERROR',
		2 => 'E_WARNING',
		4 => 'E_PARSE',
		8 => 'E_NOTICE',
		16 => 'E_CORE_ERROR',
		32 => 'E_CORE_WARNING',
		64 => 'E_COMPILER_ERROR',
		128 => 'E_COMPILER_WARNING',
		256 => 'E_USER_ERROR',
		512 => 'E_USER_WARNING',
		1024 => 'E_USER_NOTICE',
		2048 => 'E_STRICT'
	);
	if ($type != 8 && $setting['log_errors'] === true) {
		$data = date($setting['timestamp_format']) . $_SERVER['REMOTE_ADDR'] . ' made a ' . $errors[$type] . ': ' . $message . ' on line ' . $line . "\n";
		file_put_contents($setting['log_file'], $data, FILE_APPEND);
	}
}


function log_cheater($u_id, $t_id, $download, $upload, $duration, $agent, $ip, $adsl, $port, $upspeed, $ansl)
{
	global $db;
	// Kolla efter dubbla klienter    
	$agdiff = 0;

	$sth = $db->prepare("SELECT COUNT(id) FROM peers WHERE userid = ? AND ip = ? GROUP BY port");
	$sth->bindParam(1, $u_id,	PDO::PARAM_INT);
	$sth->bindParam(2, $ip,		PDO::PARAM_STR);
	$sth->execute();
	$res = $sth->fetch();

	if ($res[0] > 1) {
		$agdiff = 1;
	}

	$sth = $db->prepare("INSERT INTO cheatlog (userid, torrentid, datum, downloaded, uploaded, time, agent, ip, port, agentdiff, adsl, connectable, rate) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$sth->bindParam(1, $u_id,		PDO::PARAM_INT);
	$sth->bindParam(2, $t_id,		PDO::PARAM_INT);
	$sth->bindParam(3, $download,	PDO::PARAM_INT);
	$sth->bindParam(4, $upload,		PDO::PARAM_INT);
	$sth->bindParam(5, $duration,	PDO::PARAM_INT);
	$sth->bindParam(6, $agent,		PDO::PARAM_STR);
	$sth->bindParam(7, $ip,			PDO::PARAM_STR);
	$sth->bindParam(8, $port,		PDO::PARAM_INT);
	$sth->bindParam(9, $agdiff,		PDO::PARAM_INT);
	$sth->bindParam(10, $adsl,		PDO::PARAM_INT);
	$sth->bindParam(11, $ansl,		PDO::PARAM_STR);
	$sth->bindParam(12, $upspeed,	PDO::PARAM_INT);
	$sth->execute();
}

function roundbytes($bytes)
{
	# Scale:
	# B = byte        KB = kilobyte  MB = megabyte   GB = gigabyte
	# TB = terabyte   PB = petabyte  EB = exabyte    ZB = zetabyte
	# YB = yottabyte  NB = nonabyte  DB = doggabyte
	$suffix = array(
		"B",
		"KB",
		"MB",
		"GB",
		"TB",
		"PB",
		"EB",
		"ZB",
		"YB",
		"NB",
		"DB"
	);
	$pos    = 0;
	while ($bytes >= 1024) {
		if ($pos == 10) {
			break;
		}
		$bytes /= 1024;
		$pos++;
	}
	$result = round($bytes, 2) . "" . $suffix[$pos];
	return $result;
}

function connectable($ip, $port)
{
	
	$sockres = @fsockopen($ip, $port, $errno, $errstr, 1);
	if (!$sockres)
		return 0;
	else {
		@fclose($sockres);
		return 1;
	}
}

function dns_timeout($ip)
{
	$out = gethostbyaddr($ip);
	
	if (strlen($out) > 2) {
		
		return $out;
		
	} else {
		return 0;
	}
}

?>
