DROP TABLE IF EXISTS `upload`;

CREATE TABLE `upload` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`date` timestamp DEFAULT CURRENT_TIMESTAMP,
	`type` enum('image','text') DEFAULT NULL,
	`uid` char(36) DEFAULT '',
	`ext` varchar(20) DEFAULT NULL,
	`size` int(11) unsigned DEFAULT 0,
	`data` blob DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TRIGGER `upload_uid` BEFORE INSERT ON `upload` FOR EACH ROW SET NEW.uid =  REPLACE(UUID(),'-','');
