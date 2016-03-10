SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `adminlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `search_text` text NOT NULL,
  `added` datetime DEFAULT NULL,
  `txt` text,
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `added` (`added`),
  FULLTEXT KEY `search_text` (`search_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `banned` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namn` varchar(250) NOT NULL,
  `owner` int(11) NOT NULL,
  `comment` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namn` (`namn`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bevaka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `imdbid` int(11) NOT NULL,
  `typ` tinyint(4) NOT NULL,
  `format` varchar(10) NOT NULL,
  `swesub` tinyint(4) NOT NULL,
  `datum` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`,`imdbid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bevakasubs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `torrentid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`,`torrentid`),
  KEY `torrentid` (`torrentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `blockid` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userfriend` (`userid`,`blockid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bonuslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `veckobonus` int(11) NOT NULL DEFAULT '0',
  `msg` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `userid_2` (`userid`,`veckobonus`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `torrentid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `cheatlog` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `torrentid` int(10) NOT NULL DEFAULT '0',
  `torrentname` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(64) NOT NULL DEFAULT '',
  `port` smallint(5) NOT NULL DEFAULT '0',
  `uploaded` bigint(20) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) NOT NULL DEFAULT '0',
  `rate` bigint(20) NOT NULL DEFAULT '0',
  `seeder` enum('yes','no') NOT NULL DEFAULT 'yes',
  `connectable` enum('yes','no') NOT NULL DEFAULT 'yes',
  `userid` int(10) NOT NULL DEFAULT '0',
  `username` varchar(40) NOT NULL DEFAULT '',
  `agent` varchar(60) NOT NULL DEFAULT '',
  `time` bigint(20) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL,
  `agentdiff` int(1) NOT NULL DEFAULT '0',
  `adsl` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `uploaded` (`uploaded`),
  KEY `downloaded` (`downloaded`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `ori_text` text NOT NULL,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `torrent` (`torrent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `customindex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL,
  `typ` int(11) NOT NULL,
  `format` int(11) NOT NULL,
  `sektion` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `genre` varchar(14) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `donated` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `msg` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `nostar` tinyint(4) NOT NULL DEFAULT '0',
  `sum` varchar(250) NOT NULL,
  `typ` int(11) NOT NULL,
  `kod` varchar(250) DEFAULT NULL,
  `vem` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `emaillog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `datum` date NOT NULL,
  `email` varchar(200) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `faq` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` set('categ','item') NOT NULL DEFAULT 'item',
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `flag` tinyint(1) NOT NULL DEFAULT '1',
  `categ` int(10) NOT NULL DEFAULT '0',
  `order` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrent` (`torrent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `forumheads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `minclassread` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `request_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `request` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `ori_text` text NOT NULL,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `torrent` (`request`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 ;

CREATE TABLE IF NOT EXISTS `forums` (
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forumhead` tinyint(4) NOT NULL,
  `name` varchar(60) NOT NULL DEFAULT '',
  `description` varchar(200) DEFAULT NULL,
  `minclassread` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `minclasswrite` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `postcount` int(10) unsigned NOT NULL DEFAULT '0',
  `topiccount` int(10) unsigned NOT NULL DEFAULT '0',
  `minclasscreate` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `friends` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `friendid` int(10) unsigned NOT NULL DEFAULT '0',
  `kom` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userfriend` (`userid`,`friendid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `imdbinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imdbid` varchar(10) CHARACTER SET utf8 NOT NULL,
  `title` text CHARACTER SET utf8 NOT NULL,
  `year` int(11) NOT NULL,
  `rating` double NOT NULL,
  `tagline` tinytext CHARACTER SET utf8 NOT NULL,
  `genres` varchar(250) CHARACTER SET utf8 NOT NULL,
  `photo` tinyint(4) NOT NULL,
  `director` varchar(200) CHARACTER SET utf8 NOT NULL,
  `writer` varchar(200) CHARACTER SET utf8 NOT NULL,
  `cast` tinytext CHARACTER SET utf8 NOT NULL,
  `runtime` int(11) NOT NULL,
  `seasoncount` int(11) NOT NULL,
  `mz_ingress` text CHARACTER SET utf8 NOT NULL,
  `mz_body` longtext CHARACTER SET utf8 NOT NULL,
  `trailer` varchar(300) CHARACTER SET utf8 NOT NULL,
  `releaseNameStart` varchar(100) CHARACTER SET utf8 NOT NULL,
  `lastUpdated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imdbid` (`imdbid`),
  KEY `releaseNameStart` (`releaseNameStart`),
  KEY `year` (`year`),
  FULLTEXT KEY `title` (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `imdbtop20` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imdbid` varchar(100) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `inlogg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `namn` varchar(20) NOT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `password` varchar(25) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `invites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `email` varchar(50) NOT NULL DEFAULT '',
  `skapad` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `secret` varchar(32) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `ipchanges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(16) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `level` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `iplog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `host` varchar(200) NOT NULL,
  `userid` int(11) NOT NULL,
  `lastseen` datetime NOT NULL,
  `level` tinyint(1) NOT NULL DEFAULT '0',
  `uptime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`,`userid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `leechbonus` (
  `userid` int(11) NOT NULL,
  `datum` int(11) NOT NULL,
  `gbseed` int(11) NOT NULL,
  KEY `userid` (`userid`),
  KEY `datum` (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `receiver` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `msg` text,
  `unread` enum('yes','no') NOT NULL DEFAULT 'yes',
  `saved` tinyint(1) NOT NULL DEFAULT '0',
  `subject` varchar(45) NOT NULL,
  `last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `var` tinyint(1) NOT NULL DEFAULT '0',
  `svarad` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `receiver` (`receiver`),
  KEY `unread` (`unread`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subject` varchar(100) NOT NULL,
  `body` text NOT NULL,
  `announce` tinyint(1) NOT NULL,
  `forumthread` int(11) NOT NULL,
  `forum` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `nonscene` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(20) CHARACTER SET utf8 NOT NULL,
  `comment` varchar(20) CHARACTER SET utf8 NOT NULL,
  `whitelist` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupp_2` (`groupname`),
  KEY `grupp` (`groupname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `nyregg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(16) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `email` varchar(250) NOT NULL,
  `log_mail` int(11) NOT NULL DEFAULT '0',
  `log_ip` int(11) NOT NULL DEFAULT '0',
  `level` tinyint(4) NOT NULL DEFAULT '0',
  `country` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `packfiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrent` int(11) NOT NULL,
  `filename` varchar(250) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `torrent` (`torrent`),
  KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `peers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `peer_id` binary(20) NOT NULL,
  `ip` varchar(64) NOT NULL DEFAULT '',
  `compact` varbinary(6) NOT NULL,
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `to_go` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeder` enum('yes','no') NOT NULL DEFAULT 'no',
  `started` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_action` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `connectable` tinyint(1) NOT NULL,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `agent` varchar(60) NOT NULL DEFAULT '',
  `finishedat` int(10) unsigned NOT NULL DEFAULT '0',
  `downloadoffset` bigint(20) unsigned NOT NULL DEFAULT '0',
  `uploadoffset` bigint(20) unsigned NOT NULL DEFAULT '0',
  `info_hash` varchar(40) CHARACTER SET utf8 NOT NULL,
  `frileech` tinyint(1) NOT NULL DEFAULT '0',
  `user` tinyint(1) NOT NULL DEFAULT '0',
  `mbitupp` double NOT NULL DEFAULT '0',
  `mbitner` double NOT NULL DEFAULT '0',
  `nytt` tinyint(1) NOT NULL DEFAULT '0',
  `leechbonus` int(11) NOT NULL DEFAULT '0',
  `torrentsize` bigint(20) unsigned NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `port` (`port`,`ip`,`info_hash`),
  KEY `torrent` (`torrent`),
  KEY `last_action` (`last_action`),
  KEY `userid` (`userid`),
  KEY `info_hash` (`info_hash`(5)),
  KEY `torrent_2` (`info_hash`,`peer_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `pollanswers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pollid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `selection` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `alder` int(3) NOT NULL DEFAULT '0',
  `class` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pollid` (`pollid`),
  KEY `selection` (`selection`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `polls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `question` varchar(255) NOT NULL DEFAULT '',
  `option0` varchar(40) NOT NULL DEFAULT '',
  `option1` varchar(40) NOT NULL DEFAULT '',
  `option2` varchar(40) NOT NULL DEFAULT '',
  `option3` varchar(40) NOT NULL DEFAULT '',
  `option4` varchar(40) NOT NULL DEFAULT '',
  `option5` varchar(40) NOT NULL DEFAULT '',
  `option6` varchar(40) NOT NULL DEFAULT '',
  `option7` varchar(40) NOT NULL DEFAULT '',
  `option8` varchar(40) NOT NULL DEFAULT '',
  `option9` varchar(40) NOT NULL DEFAULT '',
  `option10` varchar(40) NOT NULL DEFAULT '',
  `option11` varchar(40) NOT NULL DEFAULT '',
  `option12` varchar(40) NOT NULL DEFAULT '',
  `option13` varchar(40) NOT NULL DEFAULT '',
  `option14` varchar(40) NOT NULL DEFAULT '',
  `option15` varchar(40) NOT NULL DEFAULT '',
  `option16` varchar(40) NOT NULL DEFAULT '',
  `option17` varchar(40) NOT NULL DEFAULT '',
  `option18` varchar(40) NOT NULL DEFAULT '',
  `option19` varchar(40) NOT NULL DEFAULT '',
  `topicid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topicid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `body` text CHARACTER SET utf8,
  `body_ori` text CHARACTER SET utf8 NOT NULL,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `topicid` (`topicid`),
  KEY `userid` (`userid`),
  FULLTEXT KEY `body` (`body`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `readposts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `topicid` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpostread` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_2` (`userid`,`topicid`),
  KEY `topicid` (`topicid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `recoverlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `email` varchar(200) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(15) NOT NULL,
  `host` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `reason` text CHARACTER SET utf8 NOT NULL,
  `targetid` int(10) NOT NULL DEFAULT '0',
  `type` enum('torrent','post','request','pm','comment','subtitle','user') CHARACTER SET utf8 NOT NULL,
  `added` datetime NOT NULL,
  `handledBy` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `request` varchar(225) DEFAULT NULL,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `filled` tinyint(1) NOT NULL DEFAULT '0',
  `p2p` int(11) NOT NULL DEFAULT '0',
  `comment` varchar(300) NOT NULL,
  `ersatt` tinyint(1) NOT NULL DEFAULT '0',
  `search_text` text NOT NULL,
  `season` int(11) NOT NULL,
  `imdbid` int(11) NOT NULL,
  `typ` int(11) NOT NULL,
  `comments` int(11) NOT NULL DEFAULT '0',
  `slug` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `filled` (`filled`),
  FULLTEXT KEY `fulltext` (`search_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `reqvotes` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `reqid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `krydda` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reqid` (`reqid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `reseed_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrentid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `torrentid` (`torrentid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `class` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `settings` (
  `arg` varchar(20) NOT NULL DEFAULT '',
  `value_s` text NOT NULL,
  `value_i` int(11) NOT NULL,
  PRIMARY KEY (`arg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `shopfil` varchar(100) NOT NULL DEFAULT '',
  `price` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `sitelog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `typ` int(11) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `txt` text,
  `search_text` tinytext NOT NULL,
  `userid` int(11) NOT NULL,
  `anonymous` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `search_text` (`search_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `snatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `torrentid` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `port` int(11) NOT NULL,
  `uploaded` bigint(20) NOT NULL,
  `downloaded` bigint(20) NOT NULL,
  `agent` varchar(50) NOT NULL,
  `connectable` tinyint(1) NOT NULL,
  `klar` datetime NOT NULL,
  `lastaction` datetime NOT NULL,
  `timesStarted` int(11) NOT NULL,
  `timesCompleted` int(11) NOT NULL,
  `timesStopped` int(11) NOT NULL,
  `timesUpdated` int(11) NOT NULL,
  `seedtime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `useridtorrent` (`userid`,`torrentid`),
  KEY `torrentid` (`torrentid`),
  KEY `userid` (`userid`),
  KEY `timesCompleted` (`timesCompleted`),
  KEY `timesStarted` (`timesStarted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `sqlerror` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `uid` int(11) NOT NULL,
  `msg` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `staffmessages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `msg` text,
  `subject` varchar(100) NOT NULL DEFAULT '',
  `answeredby` int(10) unsigned NOT NULL DEFAULT '0',
  `answered` tinyint(1) NOT NULL DEFAULT '0',
  `answer` text,
  `svaradwhen` datetime NOT NULL,
  `fromprivate` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `answered` (`answered`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `seeders` int(11) NOT NULL,
  `leechers` int(11) NOT NULL,
  `activeclients` int(11) NOT NULL,
  `activeusers` int(11) NOT NULL,
  `users` int(11) NOT NULL,
  `newusers` int(11) NOT NULL,
  `100leechbonus` int(11) NOT NULL,
  `cat1torrents` int(11) NOT NULL,
  `cat2torrents` int(11) NOT NULL,
  `cat3torrents` int(11) NOT NULL,
  `cat4torrents` int(11) NOT NULL,
  `cat5torrents` int(11) NOT NULL,
  `cat6torrents` int(11) NOT NULL,
  `cat7torrents` int(11) NOT NULL,
  `cat8torrents` int(11) NOT NULL,
  `cat9torrents` int(11) NOT NULL,
  `cat10torrents` int(11) NOT NULL,
  `cat11torrents` int(11) NOT NULL,
  `cat12torrents` int(11) NOT NULL,
  `cat1newtorrents` int(11) NOT NULL,
  `cat2newtorrents` int(11) NOT NULL,
  `cat3newtorrents` int(11) NOT NULL,
  `cat4newtorrents` int(11) NOT NULL,
  `cat5newtorrents` int(11) NOT NULL,
  `cat6newtorrents` int(11) NOT NULL,
  `cat7newtorrents` int(11) NOT NULL,
  `cat8newtorrents` int(11) NOT NULL,
  `cat1newarchivetorrents` int(11) NOT NULL,
  `cat2newarchivetorrents` int(11) NOT NULL,
  `cat3newarchivetorrents` int(11) NOT NULL,
  `cat4newarchivetorrents` int(11) NOT NULL,
  `cat5newarchivetorrents` int(11) NOT NULL,
  `cat6newarchivetorrents` int(11) NOT NULL,
  `cat7newarchivetorrents` int(11) NOT NULL,
  `cat8newarchivetorrents` int(11) NOT NULL,
  `newforumposts` int(11) NOT NULL,
  `newcomments` int(11) NOT NULL,
  `numusersclass0` int(11) NOT NULL,
  `numusersclass1` int(11) NOT NULL,
  `numusersclass2` int(11) NOT NULL,
  `numusersclass3` int(11) NOT NULL,
  `numusersclass6` int(11) NOT NULL,
  `numusersclass7` int(11) NOT NULL,
  `totalsharegb` int(11) NOT NULL,
  `userdesign0` int(11) NOT NULL,
  `userdesign2` int(11) NOT NULL,
  `userdesign3` int(11) NOT NULL,
  `userdesign4` int(11) NOT NULL,
  `userdesign5` int(11) NOT NULL,
  `userdesign6` int(11) NOT NULL,
  `userdesign7` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `subs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrentid` int(11) NOT NULL,
  `filnamn` varchar(255) CHARACTER SET utf8 NOT NULL,
  `quality` enum('','custom','retail') COLLATE utf8_general_ci NOT NULL,
  `datum` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `torrentid` (`torrentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comments` int(11) NOT NULL,
  `votes` int(11) NOT NULL DEFAULT '0',
  `title` varchar(200) CHARACTER SET utf8 NOT NULL,
  `userid` int(11) NOT NULL,
  `body` text CHARACTER SET utf8 NOT NULL,
  `added` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `topicid` int(11) NOT NULL,
  `hotpoints` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `suggestions_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `suggestionId` int(11) NOT NULL,
  `voteWeight` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`,`suggestionId`),
  KEY `suggestionId` (`suggestionId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `locked` enum('yes','no') NOT NULL DEFAULT 'no',
  `forumid` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `sticky` enum('yes','no') NOT NULL DEFAULT 'no',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `sub` varchar(60) CHARACTER SET utf8 NOT NULL,
  `slug` varchar(225) DEFAULT NULL,
  `suggestid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `lastpost` (`lastpost`),
  KEY `forumid` (`forumid`),
  FULLTEXT KEY `subject` (`subject`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `torrents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `info_hash` varchar(40) CHARACTER SET utf8 NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `filename` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `descr` text CHARACTER SET utf8 NOT NULL,
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `search_text` text CHARACTER SET utf8 NOT NULL,
  `search_text2` text CHARACTER SET utf8 NOT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type` enum('single','multi') CHARACTER SET utf8 NOT NULL DEFAULT 'single',
  `numfiles` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `times_completed` int(10) unsigned NOT NULL DEFAULT '0',
  `leechers` int(10) NOT NULL DEFAULT '0',
  `seeders` int(10) NOT NULL DEFAULT '0',
  `last_action` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `banned` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'no',
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  `numratings` int(10) unsigned NOT NULL DEFAULT '0',
  `ratingsum` int(10) unsigned NOT NULL DEFAULT '0',
  `ano_owner` tinyint(1) NOT NULL DEFAULT '0',
  `nfo` text CHARACTER SET utf8 NOT NULL,
  `reqid` int(11) NOT NULL DEFAULT '0',
  `frileech` tinyint(1) NOT NULL DEFAULT '0',
  `imdbid` int(11) NOT NULL,
  `p2p` tinyint(1) NOT NULL DEFAULT '0',
  `visible` enum('yes','no') CHARACTER SET utf8 NOT NULL DEFAULT 'yes',
  `tv_kanalid` int(11) NOT NULL,
  `tv_programid` int(11) NOT NULL,
  `tv_program` varchar(250) CHARACTER SET utf8 NOT NULL,
  `tv_episode` varchar(250) CHARACTER SET utf8 NOT NULL,
  `tv_info` text CHARACTER SET utf8 NOT NULL,
  `tv_klockslag` int(11) NOT NULL,
  `pre` int(11) NOT NULL,
  `swesub` tinyint(1) NOT NULL,
  `sweaudio` tinyint(4) NOT NULL DEFAULT '0',
  `pack` tinyint(4) NOT NULL DEFAULT '0',
  `3d` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `info_hash` (`info_hash`(15)),
  KEY `owner` (`owner`),
  KEY `reqid` (`reqid`),
  KEY `category` (`category`),
  KEY `tv_programid` (`tv_programid`,`tv_klockslag`),
  KEY `imdbid` (`imdbid`),
  KEY `added` (`added`),
  KEY `seeders` (`seeders`),
  KEY `frileech` (`frileech`),
  FULLTEXT KEY `ft_search` (`search_text`),
  FULLTEXT KEY `search_text2` (`search_text2`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci DELAY_KEY_WRITE=1 ;

CREATE TABLE IF NOT EXISTS `tv_kanaler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `xmlid` varchar(200) CHARACTER SET utf8 NOT NULL,
  `namn` varchar(100) CHARACTER SET utf8 NOT NULL,
  `pic` varchar(200) CHARACTER SET utf8 NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `priority` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `xmlid` (`xmlid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `tv_program` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` int(11) NOT NULL,
  `kanalid` int(11) NOT NULL,
  `program` varchar(250) CHARACTER SET utf8 NOT NULL,
  `program_search` text CHARACTER SET utf8 NOT NULL,
  `episod` text CHARACTER SET utf8 NOT NULL,
  `info` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `program_search` (`program_search`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL DEFAULT '',
  `old_password` varchar(40) NOT NULL DEFAULT '',
  `passhash` varchar(32) NOT NULL DEFAULT '',
  `secret` varchar(50) NOT NULL,
  `email` varchar(80) NOT NULL DEFAULT '',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `info` text,
  `acceptpms` enum('yes','friends','no') NOT NULL DEFAULT 'yes',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `class` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `avatar` varchar(100) NOT NULL DEFAULT '',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '1',
  `lastweekupload` bigint(20) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '1',
  `downloaded_real` bigint(20) unsigned NOT NULL,
  `title` varchar(30) NOT NULL DEFAULT '',
  `country` int(10) unsigned NOT NULL DEFAULT '0',
  `notifs` varchar(100) NOT NULL DEFAULT '',
  `modcomment` text NOT NULL,
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `avatars` enum('yes','no') NOT NULL DEFAULT 'yes',
  `donor` enum('yes','no') NOT NULL DEFAULT 'no',
  `warned` enum('yes','no') NOT NULL DEFAULT 'no',
  `warneduntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `torrentsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `topicsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `anonym` enum('yes','no') NOT NULL DEFAULT 'no',
  `postsperpage` int(3) unsigned NOT NULL DEFAULT '0',
  `anonymratio` enum('yes','no') NOT NULL DEFAULT 'no',
  `anonymicons` enum('yes','no') NOT NULL DEFAULT 'no',
  `reqslots` tinyint(1) NOT NULL DEFAULT '1',
  `passkey` varchar(32) NOT NULL DEFAULT '',
  `last_browse` int(11) NOT NULL DEFAULT '0',
  `last_reqbrowse` int(11) NOT NULL DEFAULT '0',
  `last_tvbrowse` int(11) NOT NULL,
  `last_seriebrowse` int(11) NOT NULL,
  `last_ovrigtbrowse` int(11) NOT NULL,
  `last_allbrowse` int(11) NOT NULL,
  `last_bevakabrowse` int(11) NOT NULL,
  `invites` int(10) NOT NULL DEFAULT '2',
  `invited_by` int(11) DEFAULT NULL,
  `bonuspoang` int(11) NOT NULL DEFAULT '0',
  `leechbonus` int(11) NOT NULL,
  `leechstart` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `randomcheck` int(11) NOT NULL DEFAULT '0',
  `doljuploader` tinyint(1) NOT NULL DEFAULT '0',
  `softbet` tinyint(1) NOT NULL DEFAULT '0',
  `forumban` tinyint(1) NOT NULL DEFAULT '0',
  `parkerad` tinyint(1) NOT NULL DEFAULT '0',
  `uptime` int(11) NOT NULL DEFAULT '0',
  `forum_access` datetime NOT NULL,
  `isp` varchar(25) NOT NULL,
  `mbitupp` float unsigned NOT NULL DEFAULT '0',
  `mbitner` float unsigned NOT NULL DEFAULT '0',
  `alder` int(2) unsigned NOT NULL DEFAULT '0',
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `torrentip` varchar(15) NOT NULL DEFAULT '0.0.0.0.0',
  `skull` tinyint(1) NOT NULL,
  `crown` tinyint(1) NOT NULL,
  `pokal` tinyint(1) NOT NULL DEFAULT '0',
  `coin` tinyint(1) NOT NULL,
  `hearts` int(3) NOT NULL DEFAULT '0',
  `inviteban` tinyint(1) NOT NULL DEFAULT '0',
  `muptime` int(11) NOT NULL,
  `nytt_seed` bigint(20) NOT NULL DEFAULT '0',
  `arkiv_seed` bigint(20) NOT NULL DEFAULT '0',
  `browser` varchar(200) NOT NULL,
  `operativ` varchar(200) NOT NULL,
  `indexlist` varchar(100) NOT NULL DEFAULT '1, 2',
  `uploadban` enum('yes','no') NOT NULL DEFAULT 'no',
  `css` varchar(250) NOT NULL,
  `design` tinyint(4) NOT NULL DEFAULT '0',
  `tvvy` tinyint(4) NOT NULL,
  `https` tinyint(1) NOT NULL DEFAULT '0',
  `magnet` tinyint(1) NOT NULL,
  `lastreadnews` int(11) NOT NULL DEFAULT '0',
  `uplLastReadCommentId` int(11) NOT NULL DEFAULT '0',
  `search_sort` enum('name','added') NOT NULL DEFAULT 'name',
  `section` enum('all','new','archive') NOT NULL DEFAULT 'all',
  `p2p` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `passkey` (`passkey`),
  KEY `ip` (`ip`),
  KEY `warned` (`warned`),
  KEY `forum_access` (`forum_access`),
  KEY `email` (`email`(3)),
  KEY `enabled` (`enabled`),
  KEY `invited_by` (`invited_by`),
  KEY `arkiv_seed` (`arkiv_seed`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 ;

INSERT INTO `settings` (`arg`, `value_s`, `value_i`) VALUES
('peers_rekord', '', 0);

INSERT INTO `users` (`id`, `username`, `old_password`, `passhash`, `secret`, `email`, `added`, `last_login`, `last_access`, `info`, `acceptpms`, `ip`, `class`, `avatar`, `uploaded`, `lastweekupload`, `downloaded`, `downloaded_real`, `title`, `country`, `notifs`, `modcomment`, `enabled`, `avatars`, `donor`, `warned`, `warneduntil`, `torrentsperpage`, `topicsperpage`, `anonym`, `postsperpage`, `anonymratio`, `anonymicons`, `reqslots`, `passkey`, `last_browse`, `last_reqbrowse`, `last_tvbrowse`, `last_seriebrowse`, `last_ovrigtbrowse`, `last_allbrowse`, `last_bevakabrowse`, `invites`, `invited_by`, `bonuspoang`, `leechbonus`, `leechstart`, `randomcheck`, `doljuploader`, `softbet`, `forumban`, `parkerad`, `uptime`, `forum_access`, `isp`, `mbitupp`, `mbitner`, `alder`, `gender`, `torrentip`, `skull`, `crown`, `pokal`, `coin`, `hearts`, `inviteban`, `muptime`, `nytt_seed`, `arkiv_seed`, `browser`, `operativ`, `indexlist`, `uploadban`, `css`, `design`, `tvvy`, `https`, `magnet`, `lastreadnews`, `uplLastReadCommentId`, `search_sort`, `section`, `p2p`) VALUES
(1, 'System', '', '45d6051ba12119e8c24027d4bcb1d299', '', '', '2015-10-31 16:16:27', '2015-10-31 16:16:27', '2015-10-31 16:25:31', '', 'yes', '123.123.123.123', 8, '', 0, 0, 0, 0, '', 0, '', '', 'yes', 'yes', 'no', 'no', '0000-00-00 00:00:00', 0, 0, 'no', 0, 'no', 'no', 1, '', 1446304880, 0, 0, 0, 0, 0, 0, 9, NULL, 0, 0, '0000-00-00 00:00:00', 0, 8, 0, 0, 1, 0, '0000-00-00 00:00:00', '', 0, 0, 0, 0, '0.0.0.0.0', 0, 0, 0, 0, 0, 0, 240, 0, 0, '', '', '1, 141', 'no', '', 0, 0, 0, 0, 0, 0, 'name', 'all', 0);

INSERT INTO `forumheads` (`id`, `sort`, `name`, `minclassread`) VALUES
(1, 0, 'Rartracker', 0);

INSERT INTO `forums` (`sort`, `id`, `forumhead`, `name`, `description`, `minclassread`, `minclasswrite`, `postcount`, `topiccount`, `minclasscreate`) VALUES
(1, 1, 1, 'Rartracker', 'Diskutera allt som rör trackern.', 0, 0, 0, 0, 0),
(0, 2, 1, 'Staff', 'Forumet för staff', 8, 8, 0, 0, 8),
(2, 3, 1, 'Omröstningar', 'Diskutera omröstningen på startsidan. ', 0, 0, 1, 1, 8),
(3, 4, 1, 'Förslag', 'Här diskuteras alla förslag och idéer.', 0, 0, 0, 0, 0);

INSERT INTO `customindex` (`id`, `tid`, `typ`, `format`, `sektion`, `sort`, `genre`) VALUES
(1, 1, 0, 1, 0, 2, ''),
(2, 1, 0, 0, 0, 2, ''),
(6, 2, 0, 0, 1, 2, ''),
(11, 1, 0, 2, 0, 2, ''),
(141, 1, 1, 1, 0, 2, ''),
(163, 2, 1, 2, 0, 2, '');

INSERT INTO `shop` (`id`, `name`, `description`, `shopfil`, `price`) VALUES
(1, 'HJÄRTA', 'Skicka ett hjärtan till en vän för att visa uppmärksamhet eller tacksamhet. Hjärtan syns på profilen.', 'shop_heart.php', 25),
(2, '+1 REQUEST SLOT', 'Trött på att bara kunna ha 1 aktiv request ute samtidigt? Spendera då 50p på ytterligare 1 requestslot!', 'shop_req.php', 50),
(3, '-10GB', 'Lite taskig ratio? Köp bort 10GB från din mängd Nerladdat så känns det genast bättre!', 'shop_gb.php', 75),
(4, '-10GB PÅ KOMPIS', 'Hjälp en kompis på traven genom att köpa bort 10GB från personens mängd Nerladdat.', 'shop_gb2.php', 75),
(6, '+1 INVITE', 'Med en invite kan du bjuda in någon du känner till sidan.', 'shop_invite.php', 50),
(8, 'CUSTOM TITLE', 'Din custom titel syns efter ditt namn i forum, kommentarer istället för exempelvis (Skådis).', 'shop_ct.php', 300),
(10, 'IKON - KRONA', 'Vill du ha lite extra status kan du köpa en extra ikon brevid ditt nick som syns överallt.', 'shop_crown.php', 1000);

INSERT INTO `tv_kanaler` (`id`, `xmlid`, `namn`, `pic`, `visible`, `priority`) VALUES
(1, 'action.canalplus.se', 'Canal+ Action', '', 0, 1),
(2, 'action.tv1000.viasat.se', 'TV1000 Action', '', 0, 1),
(3, 'axess.se', 'AxessTV', 'sq-20.png', 1, 1),
(4, 'classic.tv1000.viasat.se', 'TV1000 Classic', '', 0, 1),
(5, 'disneychannel.se', 'Disney Channel', 'sq-27.png', 1, 1),
(6, 'dr1.dr.dk', 'DR1', '', 0, 1),
(7, 'dr2.dr.dk', 'DR2', '', 0, 1),
(8, 'drama.tv1000.viasat.se', 'TV1000 Drama', '', 0, 1),
(9, 'emotion.canalplus.se', 'Canal+ Emotion', '', 0, 1),
(10, 'europe.yachtandsail.com', 'Yachtandsail Europe', '', 0, 1),
(11, 'eurosport.se', 'Eurosport', 'sq-34.png', 1, 1),
(12, 'eurosport2.eurosport.com', 'Eurosport 2', '', 0, 1),
(13, 'explorer.viasat.se', 'Explorer', '', 0, 1),
(14, 'extra1.canalplus.se', 'Canal+ Extra 1', '', 0, 1),
(15, 'extra2.canalplus.se', 'Canal+ Extra 2', '', 0, 1),
(16, 'extra3.canalplus.se', 'Canal+ Extra 4', '', 0, 1),
(17, 'extra4.canalplus.se', 'Canal+ Extra 4', '', 0, 1),
(18, 'extrahd.canalplus.se', 'Canal+ Extra HD', '', 0, 1),
(19, 'fakta.tv4.se', 'TV Fakta', 'sq-10.png', 1, 1),
(20, 'family.canalplus.se', 'Canal+ Family', '', 0, 1),
(21, 'family.tv1000.viasat.se', 'TV1000 Family', '', 0, 1),
(22, 'film.tv4.se', 'TV4 Film', 'sq-11.png', 1, 1),
(23, 'filmhd.canalplus.se', 'Canal+ Film HD', '', 0, 1),
(24, 'first.canalplus.se', 'Canal+ First', '', 0, 1),
(25, 'fotboll.cmore.se', 'C More Fotboll', 'sq-30.png', 1, 1),
(26, 'fotboll.viasat.se', 'Viasat Fotboll', 'sq-36.png', 1, 1),
(27, 'fotbollhd.viasat.se', 'Viasat Fotboll HD', '', 0, 1),
(28, 'golf.viasat.se', 'Viasat Golf', '', 0, 1),
(29, 'guld.tv4.se', 'TV4 Guld', '', 0, 1),
(30, 'hd.animalplanet.discovery.com', 'Animal planet HD', '', 0, 1),
(31, 'hd.canalplus.se', 'Canal+ HD', '', 0, 1),
(32, 'hd.dr.dk', 'DR HD', '', 0, 1),
(33, 'hd.mtve.com', 'MTV HD', '', 0, 1),
(34, 'hd.ngcsverige.com', 'NGC Sverige HD', '', 0, 1),
(35, 'hd.tv1000.viasat.se', 'TV1000 HD', '', 0, 1),
(36, 'hdshowcase.discovery.com', 'Showcase HD', '', 0, 1),
(37, 'history.viasat.se', 'Viasat History', 'sq-38.png', 1, 1),
(38, 'hits-boxer.canalplus.se', 'Canal+ Hits Boxer', '', 0, 1),
(39, 'hits.canalplus.se', 'Canal+ Hits', '', 0, 1),
(40, 'hits.mtve.com', 'MTV Hits', '', 0, 1),
(41, 'hockey.cmore.se', 'C More Hockey', 'sq-31.png', 1, 1),
(42, 'hockey.viasat.se', 'Viasat Hockey', 'sq-35.png', 1, 1),
(43, 'investigation.discovery.com', 'Discovery Investigation', '', 0, 1),
(44, 'jr.nickelodeon.se', 'Nickelodeon JR', '', 0, 1),
(45, 'k2.canalplus.se', '', '', 0, 1),
(46, 'k3.canalplus.se', '', '', 0, 1),
(47, 'k4.canalplus.se', '', '', 0, 1),
(48, 'k5.canalplus.se', '', '', 0, 1),
(49, 'k6.canalplus.se', '', '', 0, 1),
(50, 'k10.canalplus.se', '', '', 0, 1),
(51, 'k11.canalplus.se', '', '', 0, 1),
(52, 'k12.canalplus.se', '', '', 0, 1),
(53, 'kanal5.se', 'Kanal 5', 'sq-4.png', 1, 1),
(54, 'kanal9.se', 'Kanal 9', 'sq-7.png', 1, 1),
(55, 'komedi.tv4.se', 'TV4 Komedi', '', 0, 1),
(56, 'kunskapskanalen.svt.se', 'Kunskapskanalen', 'sq-17.png', 1, 1),
(57, 'motor.viasat.se', 'Viasat Motor', 'sq-37.png', 1, 1),
(58, 'motorhd.viasat.se', 'Viasat Motor HD', '', 0, 1),
(59, 'nature-crime.viasat.se', '', '', 0, 1),
(60, 'ngcsverige.com', '', '', 0, 1),
(61, 'nickelodeon.se', 'Nickelodeon', '', 0, 1),
(62, 'nordic.animalplanet.discovery.com', 'Animal Planet', 'sq-24.png', 0, 1),
(63, 'discoverychannel.se', 'Discovery Channel', 'sq-21.png', 1, 1),
(64, 'nordic.mtve.com', '', '', 0, 1),
(65, 'nordic.science.discovery.com', '', '', 0, 1),
(66, 'nordic.tv1000.viasat.se', '', '', 0, 1),
(67, 'p1.sr.se', '', '', 0, 1),
(68, 'p2.sr.se', '', '', 0, 1),
(69, 'p3.sr.se', '', '', 0, 1),
(70, 'playhouse.disneychannel.se', '', '', 0, 1),
(71, 'rocks.mtve.com', '', '', 0, 1),
(72, 'se.comedycentral.tv', '', '', 0, 1),
(73, 'series.canalplus.se', '', '', 0, 1),
(74, 'sf.canalplus.se', '', '', 0, 1),
(75, 'sf.tv4.se', '', '', 0, 1),
(76, 'showcasehd.discovery.com', 'Discovery Showcase HD', '', 0, 1),
(77, 'sjuan.se', 'Sjuan', 'sjuan.png', 1, 1),
(78, 'sport-extra.canalplus.se', '', '', 0, 1),
(79, 'sport.tv4.se', 'TV4 Sport', 'sq-32.png', 1, 1),
(80, 'sport.viasat.se', 'Viasat Sport', 'sq-39.png', 1, 1),
(81, 'sport1-sf.canalplus.se', '', '', 0, 1),
(82, 'sport.cmore.se', 'C More Sport', 'sq-18.png', 1, 1),
(83, 'sport2.canalplus.se', '', '', 0, 1),
(84, 'sporthd.canalplus.se', '', '', 0, 1),
(85, 'svt1.svt.se', 'SVT1', 'sq.png', 1, 1),
(86, 'svt2.svt.se', 'SVT2', 'sq-1.png', 1, 1),
(87, 'svt24.svt.se', 'SVT24', 'sq-25.png', 1, 0),
(88, 'svtb-svt24.svt.se', '', '', 0, 1),
(89, 'svtb.svt.se', 'Barnkanalen', 'sq-33.png', 1, 0),
(90, 'tlc.discovery.com', '', '', 0, 1),
(91, 'tnt7.nonstop.tv', '', '', 0, 1),
(92, 'tv3.se', 'TV3', 'sq-2.png', 1, 1),
(93, 'tv4.se', 'TV4', 'sq-3.png', 1, 1),
(94, 'tv6.se', 'TV6', 'sq-5.png', 1, 1),
(95, 'tv8.se', 'TV8', 'sq-6.png', 1, 1),
(96, 'tv10.se', 'TV10', 'sq-8.png', 1, 1),
(97, 'tv11.sbstv.se', 'TV11', 'sq-9.png', 1, 1),
(98, 'tv1000.viasat.se', 'TV1000', 'sq-28.png', 0, 1),
(99, 'vh1.com', 'VH1', '', 0, 1),
(100, 'world.discoveryworld.se', 'Discovery World', 'sq-23.png', 1, 1),
(101, 'svtworld.svt.se', 'SVT World', 'sq-26.png', 1, 0),
(102, 'xd.disneychannel.se', '', '', 0, 1),
(103, 'natgeo.se', 'National Geographic', 'natgeo.jpg', 1, 1),
(104, 'tv12.tv4.se', 'TV12', 'tv12.tv4.se.png', 1, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
