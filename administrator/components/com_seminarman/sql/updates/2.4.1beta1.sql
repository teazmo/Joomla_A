ALTER TABLE `#__seminarman_fields` ADD `files` text AFTER `paypalcode`;
ALTER TABLE `#__seminarman_courses` ADD `alt_url` varchar(250) NOT NULL AFTER `url`;