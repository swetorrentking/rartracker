ALTER TABLE `torrents` ADD `section` ENUM('new','archive') NOT NULL;
ALTER TABLE `torrents` ADD INDEX (`section`);
UPDATE torrents SET section = 'archive' WHERE reqid > 0;
UPDATE torrents SET reqid = 0 WHERE reqid = 1;
ALTER TABLE `peers` CHANGE `nytt` `section` ENUM('new','archive') NOT NULL;
ALTER TABLE `imdbinfo` ADD `youtube_id` VARCHAR(15) NOT NULL;
ALTER TABLE `topics` CHANGE `subject` `subject` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

CREATE TABLE `torrent_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `votes` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `imdbid` int(11) NOT NULL,
  `torrents` text NOT NULL,
  `type` enum('unlisted','public') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `torrent_list_bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `torrent_list` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE `torrent_list_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `torrent_list` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`,`torrent_list`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
