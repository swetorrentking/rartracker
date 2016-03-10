ALTER TABLE `bevaka` CHANGE `datum` `datum` DATE NOT NULL;
ALTER TABLE `users` ADD `section` ENUM('all','new','archive') NOT NULL DEFAULT 'all' ;
ALTER TABLE `users` ADD `p2p` TINYINT NOT NULL DEFAULT '1' ;
ALTER TABLE `torrents` ADD `sweaudio` TINYINT NOT NULL DEFAULT '0' AFTER `swesub`;
ALTER TABLE `users` DROP `visagammalt`;