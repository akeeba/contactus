CREATE TABLE IF NOT EXISTS `#__contactus_categories` (
  `contactus_category_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) DEFAULT '',
  `access` int(5) NOT NULL DEFAULT '1',
  `language` varchar(50) NOT NULL DEFAULT '*',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `enabled` tinyint(3) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` bigint(20) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) unsigned NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`contactus_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__contactus_items` (
  `contactus_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contactus_category_id` bigint(20) NOT NULL,
  `fromname` varchar(255) NOT NULL,
  `fromemail` varchar(255) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NOT NULL,
  `enabled` tinyint(3) NOT NULL DEFAULT 1,
  `token` char(32) DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contactus_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
