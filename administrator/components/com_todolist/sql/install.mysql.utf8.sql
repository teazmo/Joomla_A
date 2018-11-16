CREATE TABLE `#__todolist_items` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `catid` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `state` tinyint(3) NOT NULL DEFAULT '1',
    `title` varchar(255) NOT NULL DEFAULT '',
    `description` mediumtext NOT NULL,
    `status` tinyint(3) NOT NULL DEFAULT '0',
    `ordering` int(11) NOT NULL,
    `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by` int(11) NOT NULL DEFAULT '0',
    `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;