CREATE TABLE IF NOT EXISTS `application_calendar` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `account_id` int(10) DEFAULT NULL,
  `cid` int(10) DEFAULT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `notes` text,
  `url` varchar(255) DEFAULT NULL,
  `rem` int(10) DEFAULT NULL,
  `loc` varchar(255) DEFAULT NULL,
  `ad` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;