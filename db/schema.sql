
DROP TABLE IF EXISTS `upload`;

CREATE TABLE `upload` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `type` enum('image','text') DEFAULT NULL,
  `uid` char(36) DEFAULT '',
  `ext` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
