<?php

/* Prevent IE Cache */
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

/* Prevent user from aborting script execution */
ignore_user_abort(1);

/* Gzip compress HTTP response */
ob_start("ob_gzhandler");

/* Will auto include needed class php files */
function __autoload($class) {
	if ($class == "Memcached") {
		return;
	}
	require_once $class . '.php';
}

/* Database connection */
try {
	include('secrets.php');
	$db = new PDO($database.':host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	httpResponseError(500, $e->getMessage());
}

$user = new User($db);

/* Read HTTP params */
$http_body = file_get_contents("php://input");
$postdata = json_decode($http_body, true);
$params = explode('/', $_GET['url']);

/* Memcache */
if (class_exists('Memcached')) {
	try {
		$memcached = new Memcached;
		@$memcached->addServer('127.0.0.1', 11211);
	} catch (Exception $e) {
		$memcached = null;
	}
}

/* Routes acceptable not logged in */
try {
	switch(true) {
		case validateRoute('GET', 'auth'):
			$user->login($_GET["username"], $_GET["password"]);
			httpResponse($user->getStatus());
			break;

		case validateRoute('POST', 'auth'):
			httpResponse($user->create($postdata));
			break;

		case validateRoute('POST', 'recover/by-passkey'):
			httpResponse($user->recoverByPasskey($postdata));
			break;

		case validateRoute('GET', 'invite-validity'):
			$invite = new Invite($db, $user);
			httpResponse($invite->checkValidity($_GET["secret"]));
			break;

		case validateRoute('POST', 'recover/by-email'):
			httpResponse($user->recoverByEmail($postdata));
			break;

		case validateRoute('GET', 'recover/by-email'):
			httpResponse($user->gotRecoverByEmail($_GET["secret"]));
			break;

		case validateRoute('GET', 'find-torrents'):
			$torrentsFinder = new TorrentsFinder($db);
			httpResponse($torrentsFinder->getTorrents($_GET));
			break;

		case validateRoute('GET', 'run-cleanup'):
			$log = new Logs($db);
			$mailbox = new Mailbox($db, $user);
			$requests = new Requests($db, $user, $log, $mailbox);
			$torrent = new Torrent($db, $user, $log, null, null, $requests, $mailbox);
			$adminlog = new AdminLogs($db, $user);
			$cleanup = new Cleanup($db, $user, $torrent, $log, $adminlog, $mailbox, $requests);
			$cleanup->run();
			httpResponse();
			break;

		case validateRoute('GET', 'run-leechbonus'):
			$leechbonus = new Leechbonus($db);
			$leechbonus->run();
			httpResponse();
			break;

		case validateRoute('GET', 'fetch-tvdata'):
			$tvData = new TvData($db);
			$tvData->run();
			httpResponse();
			break;

		case validateRoute('GET', 'fetch-moviedata'):
			$movieData = new MovieData($db);
			$movieData->updateImdbToplist();
			httpResponse();
			break;

		case validateRoute('GET', 'run-statistics'):
			$statistics = new Statistics($db);
			$statistics->run();
			httpResponse();
			break;

		case validateRoute('GET', 'run-bonus'):
			$log = new Logs($db);
			$bonus = new Bonus($db, $user, $log);
			$bonus->run();
			httpResponse();
			break;
	}

	/* Login check before the following routes */
	$user->loginCheck();

	switch(true) {
		case validateRoute('GET', 'status'):
			/* IP change check and logging */
			if ($user->getClass() < User::CLASS_FILMSTJARNA && ((int)$_GET["timeSinceLastCheck"] < 5100 || $user->getBrowserIp() !== $user->getIp())) {
				$user->logIp();
			}

			/* Only update last access if user refreshed a page recently */
			if ((int)$_GET["timeSinceLastCheck"] < 5100) {
				$user->updateLastAccess();
			}

			httpResponse($user->getStatus());
			break;

		case validateRoute('GET', 'rules'):
			$rules = new Rules($db);
			httpResponse($rules->query());
			break;

		case validateRoute('POST', 'rules'):
            $rules= new Rules($db, $user);
            httpResponse($rules->create($postdata));
            break;

        case validateRoute('PATCH', 'rules/\d+'):
            $rules = new Rules($db, $user);
            httpResponse($rules->update($params[1], $postdata));
            break;

        case validateRoute('DELETE', 'rules/\d+'):
            $rules = new Rules($db, $user);
            httpResponse($rules->delete($params[1]));
			break;

		case validateRoute('GET', 'faq'):
            $faq = new Faq($db, $user);
            httpResponse($faq->query());
            break;

        case validateRoute('POST', 'faq'):
            $faq = new Faq($db, $user);
            httpResponse($faq->create($postdata));
            break;

        case validateRoute('PATCH', 'faq/\d+'):
            $faq = new Faq($db, $user);
            httpResponse($faq->update($params[1], $postdata));
            break;

        case validateRoute('DELETE', 'faq/\d+'):
            $faq = new Faq($db, $user);
            httpResponse($faq->delete($params[1]));
			break;

		case validateRoute('GET', 'polls'):
			$polls = new Polls($db, $user);
			list($result, $totalCount) = $polls->query(
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'polls/latest'):
			$polls = new Polls($db, $user);
			httpResponse($polls->getLatest());
			break;

		case validateRoute('POST', 'polls/votes/\d+'):
			$polls = new Polls($db, $user);
			httpResponse($polls->vote($params[2], (int)$postdata["choise"]));
			break;

		case validateRoute('POST', 'polls'):
			$forum = new Forum($db, $user);
			$polls = new Polls($db, $user, $forum);
			$polls->create($postdata);
			httpResponse();
			break;

		case validateRoute('PATCH', 'polls/\d+'):
			$polls = new Polls($db, $user);
			$polls->update($params[1], $postdata);
			httpResponse();
			break;

		case validateRoute('DELETE', 'polls/\d+'):
			$polls = new Polls($db, $user);
			$polls->delete($params[1], $postdata);
			httpResponse();
			break;

		case validateRoute('GET', 'torrents'):
			$torrent = new Torrent($db, $user);
			list($torrents, $total) = $torrent->search($_GET);
			if ($_GET["page"]) {
				$user->updateLastTorrentViewAccess($_GET["page"]);
			}
			httpResponse($torrents, $total);
			break;

		case validateRoute('GET', 'torrents/\d+'):
			$torrent = new Torrent($db);
			$myTorrent = $torrent->get($params[1]);
			httpResponse($myTorrent);
			break;

		case validateRoute('DELETE', 'torrents/\d+'):
			$log = new Logs($db);
			$mailbox = new Mailbox($db, $user);
			$requests = new Requests($db, $user, $log, $mailbox);
			$torrent = new Torrent($db, $user, $log, null, null, $requests, $mailbox);
			$torrent->delete(
				(int)$params[1],
				$_GET["reason"],
				(int)$_GET["pmUploader"],
				(int)$_GET["pmPeers"],
				(int)$_GET["banRelease"],
				(int)$_GET["attachTorrentId"],
				(int)$_GET["restoreRequest"]);
			httpResponse();
			break;

		case validateRoute('DELETE', 'torrents/\d+/pack-files'):
			$log = new Logs($db);
			$mailbox = new Mailbox($db, $user);
			$requests = new Requests($db, $user, $log, $mailbox);
			$torrent = new Torrent($db, $user, $log, null, null, $requests, $mailbox);
			$torrent->deleteTorrentsInPack((int)$params[1]);
			httpResponse();
			break;

		case validateRoute('DELETE', 'torrents/multi'):
			$log = new Logs($db);
			$mailbox = new Mailbox($db, $user);
			$requests = new Requests($db, $user, $log, $mailbox);
			$torrent = new Torrent($db, $user, $log, null, null, $requests, $mailbox);
			$torrent->multiDelete($_GET);
			httpResponse();
			break;

		case validateRoute('PATCH', 'torrents/\d+'):
			$log = new Logs($db);
			$movieData = new MovieData($db);
			$sweTv = new SweTv($db);
			$subtitles = new Subtitles($db, $user);
			$torrent = new Torrent($db, $user, $log, $movieData, $sweTv, null, null, $subtitles);
			$torrent->update((int)$params[1], $postdata);
			httpResponse();
			break;

		case validateRoute('GET', 'torrents/\d+/multi'):
			$torrent = new Torrent($db, $user);
			$subtitles = new Subtitles($db, $user);
			$requests = new Requests($db, $user);
			$movieData = new MovieData($db);
			$sweTv = new SweTv($db);
			$watchSubtitles = new WatchingSubtitles($db, $user);

			$myTorrent = $torrent->get($params[1], true);
			if ($myTorrent["imdbid"] > 0) {
				$relatedTorrents = $torrent->getRelated($myTorrent["imdbid"], $myTorrent["id"]);
				$moviedata = $movieData->getData($myTorrent["imdbid"]);
			}
			$subtitles = $subtitles->fetch($myTorrent["id"]);

			if ($myTorrent["reqid"] > 1) {
				$request = $requests->get($myTorrent["reqid"]);
			}

			httpResponse(Array(
				"torrent" => $myTorrent,
				"packContent" => $torrent->getPackFolders($myTorrent["id"]),
				"movieData" => $moviedata,
				"relatedTorrents" => $relatedTorrents,
				"subtitles" => $subtitles,
				"request" => $request,
				"watchSubtitles" => $watchSubtitles->getByTorrentId($params[1]),
				"tvChannel" => $sweTv->getChannel($myTorrent["tv_kanalid"])));
			break;

		case validateRoute('GET', 'related-torrents/\d+'):
			$torrent = new Torrent($db, $user);
			$relatedTorrents = $torrent->getRelated((int)$params[1], 0);
			httpResponse($relatedTorrents);
			break;

		case validateRoute('GET', 'torrents/\d+/files'):
			$torrent = new Torrent($db);
			$arr = $torrent->getFiles($params[1]);

			httpResponse($arr);
			break;

		case validateRoute('GET', 'torrents/\d+/peers'):
			$torrent = new Torrent($db, $user);
			list($seeders, $leechers) = $torrent->getPeers((int)$params[1]);

			httpResponse(Array(
				"seeders" => $seeders,
				"leechers" => $leechers));
			break;

		case validateRoute('GET', 'torrents/\d+/snatchlog'):
			$torrent = new Torrent($db, $user);
			httpResponse($torrent->getSnatchLog((int)$params[1]));
			break;

		case validateRoute('GET', 'torrents/\d+/comments'):
			$torrent = new Torrent($db, $user);
			$comments = new Comments($db, $user, $torrent);
			list($result, $totalCount) = $comments->query(
				(int)$params[1],
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);

			httpResponse($result, $totalCount);
			break;

		case validateRoute('POST', 'torrents/\d+/comments'):
			$torrent = new Torrent($db);
			$comments = new Comments($db, $user, $torrent);
			$comments->add(
				(int)$params[1],
				$postdata["data"]);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('PATCH', 'torrents/\d+/comments/\d+'):
			$comments = new Comments($db, $user);
			$comments->update(
				(int)$params[1],
				(int)$params[3],
				$postdata["postData"]);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('DELETE', 'torrents/\d+/comments/\d+'):
			$torrent = new Torrent($db, $user);
			$comments = new Comments($db, $user, $torrent);
			$comments->delete((int)$params[3]);
			httpResponse();
			break;

		case validateRoute('GET', 'torrents/toplists'):
			$cacheId = 'toplists-' . $_GET["limit"];
			if ($memcached && $cached = $memcached->get($cacheId)) {
				httpResponse($cached);
			} else {
				$torrent = new Torrent($db);
				$toplists = $torrent->getToplists($_GET["limit"] ?: 15);
				$memcached && $memcached->set($cacheId, $toplists, 60*60);
				httpResponse($toplists);
			}
			break;

		case validateRoute('GET', 'torrents/download/\d+'):
			$torrent = new Torrent($db, $user);
			$torrent->download((int)$params[2]);
			break;

		case validateRoute('POST', 'torrents/upload'):
			$log = new Logs($db);
			$movieData = new MovieData($db);
			$sweTv = new SweTv($db);
			$mailbox = new Mailbox($db, $user);
			$requests = new Requests($db, $user);
			$adminlogs = new AdminLogs($db, $user);
			$torrent = new Torrent($db, $user, $log, $movieData, $sweTv, $requests, $mailbox, null, $adminlogs);
			$torrentId = $torrent->upload($_FILES["file"], $_POST);
			httpResponse($torrentId);
			break;

		case validateRoute('GET', 'requests'):
			$requests = new Requests($db, $user);
			list($requests, $total) = $requests->query(
				(int)$_GET["index"],
				(int)$_GET["limit"],
				$_GET["sort"],
				$_GET["order"],
				$_GET["searchParams"]);
			httpResponse($requests, $total);
			break;

		case validateRoute('GET', 'requests/\d+'):
			$requests = new Requests($db, $user);
			$movieData = new MovieData($db);
			$requestResponse = $requests->get($params[1]);
			$requestVotes = $requests->getVotes($params[1]);
			$movieDataResponse = $movieData->getData($requestResponse["imdbid"]);
			httpResponse(array("request" => $requestResponse, "votes" => $requestVotes, "movieData" => $movieDataResponse));
			break;

		case validateRoute('GET', 'requests/my'):
			$requests = new Requests($db, $user);
			httpResponse($requests->getMyRequests());
			break;

		case validateRoute('POST', 'requests'):
			$logs = new Logs($db, $user);
			$requests = new Requests($db, $user, $logs);
			httpResponse($requests->createOrUpdate($postdata));
			break;

		case validateRoute('PATCH', 'requests/\d+'):
			$requests = new Requests($db, $user);
			httpResponse($requests->createOrUpdate($postdata, (int)$params[1]));
			break;

		case validateRoute('DELETE', 'requests/\d+'):
			$logs = new Logs($db, $user);
			$mailbox = new Mailbox($db, $user);
			$requests = new Requests($db, $user, $logs, $mailbox);
			httpResponse($requests->delete($params[1], $_GET["reason"]));
			break;

		case validateRoute('POST', 'requests/\d+/votes'):
			$requests = new Requests($db, $user);
			$response = $requests->vote(
				$params[1],
				(int)$postdata["reward"]);
			httpResponse($response);
			break;

		case validateRoute('GET', 'requests/\d+/comments'):
			$requests = new Requests($db, $user);
			$comments = new RequestComments($db, $user, $requests);
			list($result, $totalCount) = $comments->query(
				(int)$params[1],
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);

			httpResponse($result, $totalCount);
			break;

		case validateRoute('POST', 'requests/\d+/comments'):
			$requests = new Requests($db, $user);
			$mailbox = new Mailbox($db, $user);
			$comments = new RequestComments($db, $user, $requests, $mailbox);
			$comments->add(
				(int)$params[1],
				$postdata["data"]);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('PATCH', 'requests/\d+/comments/\d+'):
			$comments = new RequestComments($db, $user);
			$comments->update(
				(int)$params[1],
				(int)$params[3],
				$postdata["postData"]);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('DELETE', 'requests/\d+/comments/\d+'):
			$requests = new Requests($db, $user);
			$comments = new RequestComments($db, $user, $requests);
			$comments->delete((int)$params[3]);
			httpResponse();
			break;

		case validateRoute('GET', 'mailbox'):
			$mailbox = new Mailbox($db, $user);
			list($result, $totalCount) = $mailbox->query(
				(int)$_GET["location"] ?: 0,
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);

			httpResponse($result, $totalCount);
			break;

		case validateRoute('PATCH', 'mailbox/\d+'):
			$mailbox = new Mailbox($db, $user);
			httpResponse($mailbox->update((int)$params[1], $postdata));
			break;

		case validateRoute('POST', 'mailbox'):
			$mailbox = new Mailbox($db, $user);
			httpResponse($mailbox->create($postdata));
			break;

		case validateRoute('DELETE', 'mailbox/\d+'):
			$mailbox = new Mailbox($db, $user);
			httpResponse($mailbox->delete((int)$params[1]));
			break;

		case validateRoute('GET', 'moviedata/\d+'):
			$movieData = new MovieData($db);
			httpResponse($movieData->getData($params[1]));
			break;

		case validateRoute('GET', 'moviedata/\d+/refresh'):
			$movieData = new MovieData($db);
			httpResponse($movieData->updateImdbInfo($params[1]));
			break;

		case validateRoute('GET', 'moviedata/search'):
			$movieData = new MovieData($db);
			httpResponse($movieData->search($_GET["search"]));
			break;

		case validateRoute('GET', 'moviedata/guess'):
			$movieData = new MovieData($db);
			httpResponse($movieData->findImdbInfoByReleaseName($_GET["name"]));
			break;

		case validateRoute('GET', 'moviedata/imdb/\w+'):
			$movieData = new MovieData($db);
			$arr = $movieData->getDataByImdbId($params[2]);
			httpResponse($arr);
			break;

		case validateRoute('GET', 'moviedata/toplist'):
			$cacheId = 'toplists-toplist';
			if ($memcached && $cached = $memcached->get($cacheId)) {
				httpResponse($cached);
			} else {
				$movieData = new MovieData($db);
				$torrent = new Torrent($db);
				$data = $movieData->getToplist();

				$result = array();
				foreach ($data as $movie) {
					$movie["torrents"] = $torrent->getByMovieId($movie["id"]);
					$result[] = $movie;
				}
				$memcached && $memcached->set($cacheId, $result, 60*60*6);
				httpResponse($result);
			}
			break;

		case validateRoute('GET', 'start-torrents'):
			$torrent = new Torrent($db);
			$index = explode(',', $user->getIndexList());
			if (!$index[0]) {
				$index = array();
			}
			$result = [];

			foreach($index as $i) {
				$customIndex = $user->getCustomIndex($i);
				list($headline, $torrents) = $torrent->getHighlightTorrents(
					$customIndex["tid"],
					$customIndex["typ"],
					$customIndex["format"],
					$customIndex["sektion"],
					$customIndex["sort"],
					$customIndex["genre"]);
				$result[] = ["headline" => $headline, "id" => $i, "torrents" => $torrents];
			}

			httpResponse($result);
			break;

		case validateRoute('POST', 'start-torrents'):
			$arr = $user->addIndexList($postdata);
			httpResponse($arr);
			break;

		case validateRoute('PATCH', 'start-torrents'):
			if ($postdata["action"] == "move") {
				$user->moveIndexList($postdata["id"], $postdata["direction"]);
			}
			if ($postdata["action"] == "reset") {
				$user->resetIndexList($postdata["category"]);
			}
			httpResponse();
			break;

		case validateRoute('DELETE', 'start-torrents'):
			httpResponse($user->removeIndexList($_GET["id"]));
			break;

		case validateRoute('GET', 'users'):
			$arr = $user->getUsers($_GET["search"]);
			httpResponse($arr);
			break;

		case validateRoute('GET', 'users/leechbonustop'):
			$res = $user->getUsersLechbonusTop();
			httpResponse($res);
			break;

		case validateRoute('GET', 'users/topseeders'):
			$arr = $user->getTopSeeders();
			httpResponse($arr);
			break;

		case validateRoute('GET', 'users/\d+'):
			$arr = $user->get($params[1] ?: 0);
			httpResponse($arr);
			break;

		case validateRoute('DELETE', 'users/\d+'):
			$arr = $user->delete((int)$params[1]);
			httpResponse($arr);
			break;

		case validateRoute('PATCH', 'users/\d+'):
			$response = $user->update((int)$params[1], $postdata);
			httpResponse();
			break;

		case validateRoute('GET', 'users/\d+/email-test'):
			httpResponse($user->testEmail((int)$params[1], $_GET["email"]));
			break;

		case validateRoute('GET', 'users/\d+/torrents'):
			$arr = $user->getUserTorrents((int)$params[1], (int)$_GET["requests"]);
			httpResponse($arr);
			break;

		case validateRoute('GET', 'users/\d+/torrent-comments'):
			$comments = new Comments($db, $user);
			list($result, $totalCount) = $comments->getCommentsForUserTorrents(
				(int)$params[1],
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);

			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'users/\d+/comments'):
			$comments = new Comments($db, $user);
			list($result, $totalCount) = $comments->getUserComments(
				(int)$params[1],
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);

			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'users/\d+/peers'):
			list($seeding, $leeching) = $user->getPeers($params[1] ?: 0);
			httpResponse(Array("seeding" => $seeding, "leeching" => $leeching));
			break;

		case validateRoute('GET', 'users/\d+/invitees'):
			$invitees = $user->getInvitees($params[1] ?: 0);
			httpResponse($invitees);
			break;

		case validateRoute('GET', 'users/\d+/bonuslog'):
			$bonuslog = $user->getBonusLog($params[1] ?: 0, (int)$_GET["limit"] ?: 10);
			httpResponse($bonuslog);
			break;

		case validateRoute('GET', 'users/\d+/iplog'):
			$iplog = $user->getIpLog($params[1] ?: 0, (int)$_GET["limit"] ?: 10);
			httpResponse($iplog);
			break;

		case validateRoute('GET', 'users/\d+/snatchlog'):
			httpResponse($user->getSnatchLog((int)$params[1]));
			break;

		case validateRoute('GET', 'users/\d+/watching'):
			$watching = new Watching($db, $user);
			httpResponse($watching->query($params[1], (int)$_GET["imdbid"]));
			break;

		case validateRoute('GET', 'users/\d+/watching/imdb/\d+'):
			$watching = new Watching($db, $user);
			$watch = $watching->query($params[1], $params[4]);
			if (is_array($watch) && $watch[0]) {
				httpResponse($watch[0]);
			} else {
				httpResponseError(404, 'Bevakning saknas för imdb-id ' . $params[4]);
			}
			break;

		case validateRoute('POST', 'users/\d+/watching'):
			$watching = new Watching($db, $user);
			httpResponse($watching->create($params[1], $postdata));
			break;

		case validateRoute('PATCH', 'users/\d+/watching/\d+'):
			$watching = new Watching($db, $user);
			httpResponse($watching->update($params[1], $params[3], $postdata));
			break;

		case validateRoute('DELETE', 'users/\d+/watching/\d+'):
			$watching = new Watching($db, $user);
			httpResponse($watching->delete($params[1], $params[3]));
			break;

		case validateRoute('GET', 'users/\d+/watching/toplist'):
			$watching = new Watching($db, $user);
			httpResponse($watching->getToplist());
			break;

		case validateRoute('GET', 'watching-subtitles'):
			$watchSubtitles = new WatchingSubtitles($db, $user);
			httpResponse($watchSubtitles->query(null));
			break;

		case validateRoute('GET', 'watching-subtitles/\d+'):
			$watchSubtitles = new WatchingSubtitles($db, $user);
			httpResponse($watchSubtitles->get($params[1]));
			break;

		case validateRoute('POST', 'watching-subtitles'):
			$watchSubtitles = new WatchingSubtitles($db, $user);
			httpResponse($watchSubtitles->create($postdata));
			break;

		case validateRoute('DELETE', 'watching-subtitles/\d+'):
			$watchSubtitles = new WatchingSubtitles($db, $user);
			httpResponse($watchSubtitles->delete((int)$params[1]));
			break;

		case validateRoute('GET', 'users/\d+/forum-posts'):
			$forum = new Forum($db, $user);
			list($result, $totalCount) = $forum->getUserPosts(
				(int)$params[1],
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'news'):
			$news = new News($db, $user);
			$arr = $news->query((int)$_GET["limit"] ?: 2, $_GET["markAsRead"] ?: "false");
			httpResponse($arr);
			break;

		case validateRoute('POST', 'news'):
			$forum = new Forum($db, $user);
			$news = new News($db, $user, $forum);
			httpResponse($news->create($postdata));
			break;

		case validateRoute('PATCH', 'news/\d+'):
			$news = new News($db, $user);
			httpResponse($news->update($params[1], $postdata));
			break;

		case validateRoute('DELETE', 'news/\d+'):
			$news = new News($db, $user);
			httpResponse($news->delete($params[1]));
			break;

		case validateRoute('GET', 'suggestions'):
			$suggestions = new Suggestions($db, $user);
			$arr = $suggestions->query(
				$_GET["view"] ?: 'top',
				(int)$_GET["limit"] ?: 10);
			httpResponse($arr);
			break;

		case validateRoute('POST', 'suggestions/\d+/votes'):
			$suggestions = new Suggestions($db, $user);
			$arr = $suggestions->vote(
				$params[1],
				$postdata["direction"]);
			httpResponse($arr);
			break;

		case validateRoute('POST', 'suggestions'):
			$forum = new Forum($db, $user);
			$suggestions = new Suggestions($db, $user, $forum);
			httpResponse($suggestions->create($postdata));
			break;

		case validateRoute('PATCH', 'suggestions/\d+'):
			$forum = new Forum($db, $user);
			$suggestions = new Suggestions($db, $user, $forum);
			httpResponse($suggestions->update($params[1], $postdata));
			break;

		case validateRoute('DELETE', 'suggestions/\d+'):
			$suggestions = new Suggestions($db, $user);
			httpResponse($suggestions->delete($params[1]));
			break;

		case validateRoute('GET', 'sweTvGuide'):
			$week = (int)$_GET["week"];
			$cacheId = 'swetvguide-' . $week;
			if ($memcached && $cached = $memcached->get($cacheId)) {
				if ($week == 0) {
					$user->updateLastTorrentViewAccess('last_tvbrowse');
				}
				httpResponse($cached);
			} else {
				$torrent = new Torrent($db, $user);
				if ($week > 3) {
					$week = 4;
				}

				$array = array();
				for ($i = 0; $i < 8; ++$i) {
					$d = time() - (86400*$i) - ($week * 604800);
					$startDate = strtotime( date("Y-m-d", $d) . ' 00:00');
					$endDate = strtotime( date("Y-m-d", $d) . ' 23:59');
					$array[] = $torrents = $torrent->getSweTvGuideTorrents($startDate, $endDate);
				}
				$memcached && $memcached->set($cacheId, $array, 60*15);
				if ($week == 0) {
					$user->updateLastTorrentViewAccess('last_tvbrowse');
				}
				httpResponse($array);
			}
			break;

		/* Forum */

		case validateRoute('GET', 'forums'):
			$forum = new Forum($db, $user);
			$user->updateLastForumAccess();
			httpResponse($forum->getForums());
			break;

		case validateRoute('GET', 'forums/\d+'):
			$forum = new Forum($db, $user);
			$user->updateLastForumAccess();
			httpResponse($forum->getForum($params[1]));
			break;

		case validateRoute('GET', 'forums/\d+/topics'):
			$forum = new Forum($db, $user);
			$user->updateLastForumAccess();
			list($result, $totalCount) = $forum->getTopics(
				$params[1],
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'forums/\d+/topics/\d+'):
			$forum = new Forum($db, $user);
			$user->updateLastForumAccess();
			httpResponse($forum->getTopic($params[3]));
			break;

		case validateRoute('DELETE', 'forums/\d+/topics/\d+'):
			$forum = new Forum($db, $user);
			$user->updateLastForumAccess();
			httpResponse($forum->deleteTopic($params[1], $params[3]));
			break;

		case validateRoute('PATCH', 'forums/\d+/topics/\d+'):
			$forum = new Forum($db, $user);
			$user->updateLastForumAccess();
			httpResponse($forum->updateTopic($params[3], $postdata));
			break;

		case validateRoute('GET', 'forums/\d+/topics/\d+/posts'):
			$forum = new Forum($db, $user);
			$user->updateLastForumAccess();
			list($result, $totalCount) = $forum->getPosts(
				(int)$params[3],
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('POST', 'forums/\d+/topics'):
			$forum = new Forum($db, $user);
			$user->updateLastForumAccess();
			$topic = $forum ->addTopic((int)$params[1], $postdata["subject"], $postdata["sub"] ?:'', $postdata["body"]);
			httpResponse($topic);
			break;

		case validateRoute('POST', 'forums/\d+/topics/\d+/posts'):
			$mailbox = new Mailbox($db, $user);
			$forum = new Forum($db, $user, $mailbox);
			$user->updateLastForumAccess();
			$forum->addPost(
				(int)$params[3],
				$postdata);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('PATCH', 'forums/\d+/topics/\d+/posts/\d+'):
			$forum = new Forum($db, $user);
			$forum->updatePost(
				(int)$params[1],
				(int)$params[3],
				(int)$params[5],
				$postdata["postData"]);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('DELETE', 'forums/\d+/topics/\d+/posts/\d+'):
			$forum = new Forum($db, $user);
			$forum->deletePost(
				(int)$params[1],
				(int)$params[3],
				(int)$params[5]);
			httpResponse();
			break;

		case validateRoute('GET', 'forums/users-online'):
			httpResponse($user->getForumOnline());
			break;

		case validateRoute('GET', 'forums/mark-all-topics-as-read'):
			$forum = new Forum($db, $user);
			httpResponse($forum->markAllTopicsAsRead());
			break;

		case validateRoute('GET', 'forums/unread-topics'):
			$forum = new Forum($db, $user);
			list($result, $totalCount) = $forum->getUnreadTopics(
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'forums/posts'):
			$forum = new Forum($db, $user);
			list($result, $totalCount) = $forum->getAllPosts(
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'forums/search'):
			$forum = new Forum($db, $user);
			list($result, $totalCount) = $forum->search($_GET);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'statistics/start'):
			$cacheId = 'stats-start';
			if ($memcached && $cached = $memcached->get($cacheId)) {
				httpResponse($cached);
			} else {
				$stats = new Statistics($db);
				$data = $stats->getStartStats();
				$memcached && $memcached->set($cacheId, $data, 60*15);
				httpResponse($data);
			}
			break;

		case validateRoute('GET', 'statistics'):
			$stats = new Statistics($db, $user);
			$data = $stats->getAllStats($_GET);
			httpResponse($data);
			break;

		case validateRoute('GET', 'swetv/channels'):
			$swetv = new SweTv($db);
			httpResponse($swetv->getChannels());
			break;

		case validateRoute('GET', 'swetv/programs/\d+'):
			$swetv = new SweTv($db);
			httpResponse($swetv->getPrograms((int)$params[2]));
			break;

		case validateRoute('GET', 'swetv/guess'):
			$swetv = new SweTv($db);
			list($channel, $program) = $swetv->guessChannelAndProgram($_GET["name"]);
			httpResponse(array("channel" => $channel, "program" => $program));
			break;

		case validateRoute('GET', 'logs'):
			$logs = new Logs($db, $user);
			list($data, $totalCount) = $logs->get($_GET["limit"], $_GET["index"], $_GET["search"]);
			httpResponse($data, $totalCount);
			break;

		case validateRoute('GET', 'bonus-shop'):
			$bonusShop = new BonusShop($db, $user);
			httpResponse($bonusShop->getShopItems());
			break;

		case validateRoute('POST', 'bonus-shop/\d+'):
			$mailbox = new Mailbox($db, $user);
			$bonusShop = new BonusShop($db, $user, $mailbox);
			$bonusShop->buy((int)$params[1], $postdata);
			httpResponse();
			break;

		case validateRoute('GET', 'invites'):
			$invite = new Invite($db, $user);
			httpResponse($invite->query());
			break;

		case validateRoute('POST', 'invites'):
			$invite = new Invite($db, $user);
			httpResponse($invite->create());
			break;

		case validateRoute('DELETE', 'invites/\d+'):
			$invite = new Invite($db, $user);
			httpResponse($invite->delete((int)$params[1]));
			break;

		case validateRoute('GET', 'friends'):
			$friends = new Friends($db, $user);
			httpResponse($friends->query());
			break;

		case validateRoute('POST', 'friends'):
			$friends = new Friends($db, $user);
			httpResponse($friends->create($postdata));
			break;

		case validateRoute('DELETE', 'friends/\d+'):
			$friends = new Friends($db, $user);
			httpResponse($friends->delete((int)$params[1]));
			break;

		case validateRoute('PATCH', 'friends/\d+'):
			$friends = new Friends($db, $user);
			httpResponse($friends->update((int)$params[1]), $postdata);
			break;

		case validateRoute('GET', 'blocked'):
			$blocked = new Blocked($db, $user);
			httpResponse($blocked->query());
			break;

		case validateRoute('POST', 'blocked'):
			$blocked = new Blocked($db, $user);
			httpResponse($blocked->create($postdata));
			break;

		case validateRoute('DELETE', 'blocked/\d+'):
			$blocked = new Blocked($db, $user);
			httpResponse($blocked->delete((int)$params[1]));
			break;

		case validateRoute('GET', 'bookmarks'):
			$bookmarks = new Bookmarks($db, $user);
			httpResponse($bookmarks->query(null));
			break;

		case validateRoute('POST', 'bookmarks'):
			$bookmarks = new Bookmarks($db, $user);
			httpResponse($bookmarks->create($postdata));
			break;

		case validateRoute('DELETE', 'bookmarks/\d+'):
			$bookmarks = new Bookmarks($db, $user);
			httpResponse($bookmarks->delete((int)$params[1]));
			break;

		case validateRoute('GET', 'subtitles'):
			$subtitles = new Subtitles($db, $user);
			httpResponse($subtitles->fetch($_GET["torrentid"]));
			break;

		case validateRoute('POST', 'subtitles'):
			$torrent = new Torrent($db, $user);
			$log = new Logs($db);
			$mailbox = new Mailbox($db, $user);
			$subtitles = new Subtitles($db, $user, $log, $torrent, $mailbox);
			httpResponse($subtitles->upload($_FILES["file"], $_POST));
			break;

		case validateRoute('DELETE', 'subtitles/\d+'):
			$log = new Logs($db);
			$torrent = new Torrent($db, $user);
			$mailbox = new Mailbox($db, $user);
			$subtitles = new Subtitles($db, $user, $log, $torrent, $mailbox);
			httpResponse($subtitles->delete((int)$params[1], $_GET["reason"]));
			break;

		case validateRoute('GET', 'donations'):
			$donations = new Donations($db, $user);
			list($result, $totalCount) = $donations->query(array("limit" => $_GET["limit"], "index" => $_GET["index"]));
			httpResponse($result, $totalCount);
			break;

		case validateRoute('POST', 'donations'):
			$donate = new Donations($db, $user);
			httpResponse($donate->create($postdata));
			break;

		case validateRoute('PATCH', 'donations/\d+'):
			$donate = new Donations($db, $user);
			httpResponse($donate->update((int)$params[1], $postdata));
			break;

		case validateRoute('DELETE', 'donations/\d+'):
			$donate = new Donations($db, $user);
			httpResponse($donate->delete((int)$params[1], $postdata));
			break;

		case validateRoute('GET', 'login-attempts'):
			$loginAttempts = new LoginAttempts($db, $user);
			list($result, $totalCount) = $loginAttempts->query(array("limit" => $_GET["limit"], "index" => $_GET["index"]));
			httpResponse($result, $totalCount);
			break;

		case validateRoute('DELETE', 'login-attempts/\d+'):
			$loginAttempts = new LoginAttempts($db, $user);
			httpResponse($loginAttempts->delete((int)$params[1]));
			break;

		case validateRoute('GET', 'signups'):
			$signups = new Signups($db, $user);
			list($result, $totalCount) = $signups->query((int)$_GET["limit"], (int)$_GET["index"]);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'ipchanges'):
			$ipchanges = new IpChanges($db, $user);
			list($result, $totalCount) = $ipchanges->query((int)$_GET["limit"], (int)$_GET["index"]);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('POST', 'reports'):
			$reports = new Reports($db, $user);
			httpResponse($reports->create($postdata));
			break;

		case validateRoute('GET', 'reports'):
			$mailbox = new Mailbox($db, $user);
			$torrent = new Torrent($db, $user);
			$subtitles = new Subtitles($db, $user);
			$requests = new Requests($db, $user);
			$forum = new Forum($db, $user);
			$log = new Logs($db);
			$comments = new Comments($db, $user);
			$reports = new Reports($db, $user, $torrent, $subtitles, $requests, $forum, $mailbox, $comments, $log);
			list($result, $totalCount) = $reports->query(array("limit" => $_GET["limit"], "index" => $_GET["index"]));
			httpResponse($result, $totalCount);
			break;

		case validateRoute('PATCH', 'reports/\d+'):
			$reports = new Reports($db, $user);
			httpResponse($reports->update((int)$params[1], $postdata));
			break;

		case validateRoute('DELETE', 'reports/\d+'):
			$reports = new Reports($db, $user);
			httpResponse($reports->delete((int)$params[1]));
			break;

		case validateRoute('GET', 'adminlogs'):
			$adminlogs = new AdminLogs($db, $user);
			list($result, $totalCount) = $adminlogs->query(array("limit" => $_GET["limit"], "index" => $_GET["index"], "search" => $_GET["searchText"]));
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'recovery-logs'):
			$recoveryLog = new RecoveryLog($db, $user);
			list($result, $totalCount) = $recoveryLog->query(array("limit" => $_GET["limit"], "index" => $_GET["index"]));
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'sqlerrors'):
			$sqlerrors = new SqlErrors($db, $user);
			list($result, $totalCount) = $sqlerrors->query(array("limit" => $_GET["limit"], "index" => $_GET["index"]));
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'cheatlogs'):
			$cheatlogs = new CheatLogs($db, $user);
			list($result, $totalCount) = $cheatlogs->query(array(
				"limit" => $_GET["limit"],
				"index" => $_GET["index"],
				"userid" => $_GET["userid"],
				"sort" => $_GET["sort"],
				"order" => $_GET["order"]));
			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'admin-mailbox'):
			$adminMailbox = new AdminMailbox($db, $user);
			list($result, $totalCount) = $adminMailbox->query(array("limit" => $_GET["limit"], "index" =>$_GET["index"]));
			httpResponse($result, $totalCount);
			break;

		case validateRoute('POST', 'admin-mailbox'):
			$adminMailbox = new AdminMailbox($db, $user);
			$adminMailbox->create($postdata);
			httpResponse();
			break;

		case validateRoute('PATCH', 'admin-mailbox/\d+'):
			$adminMailbox = new AdminMailbox($db, $user);
			$adminMailbox->update((int)$params[1], $postdata);
			httpResponse();
			break;

		case validateRoute('GET', 'comments'):
			$comments = new Comments($db, $user, $torrent);
			list($result, $totalCount) = $comments->getAllComments(
				(int)$_GET["limit"] ?: 10,
				(int)$_GET["index"] ?: 0);

			httpResponse($result, $totalCount);
			break;

		case validateRoute('GET', 'search'):
			$sqlerrors = new SqlErrors($db, $user);
			list($result, $totalCount) = $user->search($_GET);
			httpResponse($result, $totalCount);
			break;

		case validateRoute('POST', 'reseed-requests'):
			$logs = new Logs($db, $user);
			$mailbox = new Mailbox($db, $user);
			$torrent = new Torrent($db, $user);
			$reseed = new ReseedRequests($db, $user, $torrent, $mailbox, $logs);
			$reseed->create($postdata);
			httpResponse();
			break;

		case validateRoute('GET', 'nonscene'):
			$adminlogs = new AdminLogs($db, $user);
			$nonscene = new Nonscene($db, $user, $adminlogs);
			httpResponse($nonscene->query());
			break;

		case validateRoute('POST', 'nonscene'):
			$adminlogs = new AdminLogs($db, $user);
			$nonscene = new Nonscene($db, $user, $adminlogs);
			$nonscene->create($postdata);
			httpResponse();
			break;

		case validateRoute('DELETE', 'nonscene/\d+'):
			$adminlogs = new AdminLogs($db, $user);
			$nonscene = new Nonscene($db, $user, $adminlogs);
			$nonscene->delete($params[1]);
			httpResponse();
			break;
	}

	httpResponseError(404, 'Resource not found');

} catch (Exception $e) {

	/* Don't expose SQL errors, log them. */
	if ($e instanceof PDOException) {
		$errorString = $e->getMessage() . $e->getFile() . $e->getLine();
		$sqlerrors = new SqlErrors($db, $user);
		$sqlerrors->create($errorString);
		httpResponseError(500, "Ett serverfel har inträffat. Händelsen har loggats.");
	} else {
		httpResponseError($e->getCode(), $e->getMessage());
	}

}

/* Route matcher function */
function validateRoute($method, $pattern) {
	if ($method == $_SERVER['REQUEST_METHOD'] &&
		preg_match('/^' . str_replace('/', '\/', $pattern) . '$/', $_GET['url'])) {
			return true;
	}
	return false;
}

function httpResponse($data = null, $totalCount = -1) {
	header("Access-Control-Expose-Headers: *");
	header("Content-Type: application/json; charset=utf-8");
	if ($totalCount > -1) {
		header("X-Total-Count: " . $totalCount);
	}
	if ($data) {
		echo json_encode($data, JSON_NUMERIC_CHECK);
	}
	die;
}

function httpResponseError($code = 403, $data = null) {
	header("Access-Control-Expose-Headers: *");
	switch ($code) {
		case 100: $text = 'Continue'; break;
		case 101: $text = 'Switching Protocols'; break;
		case 200: $text = 'OK'; break;
		case 201: $text = 'Created'; break;
		case 202: $text = 'Accepted'; break;
		case 203: $text = 'Non-Authoritative Information'; break;
		case 204: $text = 'No Content'; break;
		case 205: $text = 'Reset Content'; break;
		case 206: $text = 'Partial Content'; break;
		case 300: $text = 'Multiple Choices'; break;
		case 301: $text = 'Moved Permanently'; break;
		case 302: $text = 'Moved Temporarily'; break;
		case 303: $text = 'See Other'; break;
		case 304: $text = 'Not Modified'; break;
		case 305: $text = 'Use Proxy'; break;
		case 400: $text = 'Bad Request'; break;
		case 401: $text = 'Unauthorized'; break;
		case 402: $text = 'Payment Required'; break;
		case 403: $text = 'Forbidden'; break;
		case 404: $text = 'Not Found'; break;
		case 405: $text = 'Method Not Allowed'; break;
		case 406: $text = 'Not Acceptable'; break;
		case 407: $text = 'Proxy Authentication Required'; break;
		case 408: $text = 'Request Time-out'; break;
		case 409: $text = 'Conflict'; break;
		case 410: $text = 'Gone'; break;
		case 411: $text = 'Length Required'; break;
		case 412: $text = 'Precondition Failed'; break;
		case 413: $text = 'Request Entity Too Large'; break;
		case 414: $text = 'Request-URI Too Large'; break;
		case 415: $text = 'Unsupported Media Type'; break;
		case 500: $text = 'Internal Server Error'; break;
		case 501: $text = 'Not Implemented'; break;
		case 502: $text = 'Bad Gateway'; break;
		case 503: $text = 'Service Unavailable'; break;
		case 504: $text = 'Gateway Time-out'; break;
		case 505: $text = 'HTTP Version not supported'; break;
		default:
			case 401: $text = 'Unauthorized'; break;
		break;
	}

	$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	header($protocol . ' ' . $code . ' ' . $text);
	$GLOBALS['http_response_code'] = $code;

	if ($data !== null) {
		echo $data;
	}

	die;
}
