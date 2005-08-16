ALTER TABLE `rights` MODIFY  `id` tinyint(5) unsigned NOT NULL auto_increment;
ALTER TABLE `rights` ADD UNIQUE KEY `role` (`role`);

ALTER TABLE `rights` COMMENT = 'Updated to TL 1.6';

