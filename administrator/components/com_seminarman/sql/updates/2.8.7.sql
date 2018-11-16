CREATE TABLE IF NOT EXISTS `#__seminarman_user_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL,
  `rule_type` tinyint(1) NOT NULL DEFAULT '0',
  `rule_option` varchar(25) DEFAULT '',
  `rule_text` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  `attribs` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;