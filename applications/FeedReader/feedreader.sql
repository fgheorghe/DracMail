CREATE TABLE IF NOT EXISTS `application_feedreader_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `account_id` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE IS NOT EXISTS `application_feedreader_feeds` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `account_id` int(10) DEFAULT NULL,
  `category_id` int(10) DEFAULT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `link` varchar(1024) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `image` varchar(1024) DEFAULT NULL,
  `url` varchar(1024) DEFAULT NULL,
  `feed_url` varchar(1024) DEFAULT NULL,
  `interval` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `application_feedreader_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `account_id` int(10) DEFAULT NULL,
  `feed_id` int(10) DEFAULT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `link` varchar(1024) DEFAULT NULL,
  `image` varchar(1024) DEFAULT NULL,
  `pubDate` datetime DEFAULT NULL,
  `guid` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;