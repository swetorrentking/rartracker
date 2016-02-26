ALTER TABLE `forumheads` ADD `minclassread` INT NOT NULL DEFAULT '0' ;
ALTER TABLE `topics` ADD `slug` VARCHAR(250) NOT NULL ;
ALTER TABLE `requests` ADD `slug` VARCHAR(250) NOT NULL ;
ALTER TABLE `requests` ADD `comments` INT NOT NULL ;
ALTER TABLE `subs` ADD `quality` ENUM('','custom','retail') NOT NULL ;
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