DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT NULL,
  `account_id` int(10) DEFAULT NULL,
  `filename` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TABLE IF EXISTS `folder_messages`;
CREATE TABLE `folder_messages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) NOT NULL,
  `account_id` int(10) DEFAULT NULL,
  `message_id` varchar(255) DEFAULT NULL,
  `subject` varchar(1024) DEFAULT NULL,
  `sender` varchar(1024) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `size` double DEFAULT NULL,
  `seen` int(1) DEFAULT NULL,
  `answered` int(1) DEFAULT NULL,
  `recipient` varchar(1024) DEFAULT NULL,
  `reply_toaddress` varchar(1024) DEFAULT NULL,
  `ccaddress` varchar(1024) DEFAULT NULL,
  `bccaddress` varchar(1024) DEFAULT NULL,
  `uid` int(10) DEFAULT NULL,
  `attachments` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TABLE IF EXISTS `mail_folders`;
CREATE TABLE `mail_folders` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `account_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TABLE IF EXISTS `message_body`;
CREATE TABLE `message_body` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `message_id` int(10) DEFAULT NULL,
  `account_id` int(10) DEFAULT NULL,
  `folder_id` int(10) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `content` text,
  `msgno` int(10) DEFAULT NULL,
  `uid` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;