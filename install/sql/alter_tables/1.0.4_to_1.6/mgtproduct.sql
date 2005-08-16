ALTER TABLE `mgtproduct` MODIFY `id` int(10) unsigned NOT NULL auto_increment;
ALTER TABLE `mgtproduct` MODIFY `name` varchar(100) NOT NULL default '';
ALTER TABLE `mgtproduct` MODIFY `color` varchar(12) NOT NULL default '#9BD';


ALTER TABLE `mgtproduct` ADD COLUMN `active` BOOL  NOT NULL default 1;

ALTER TABLE `mgtproduct` ADD COLUMN `option_requirements` enum('Y','N') NOT NULL default 'N';

ALTER TABLE `mgtproduct` ADD UNIQUE KEY `name` (`name`);
ALTER TABLE `mgtproduct` ADD INDEX  `ActiveId` (`id`,`active`);

ALTER TABLE `mgtproduct` COMMENT = 'Updated to TL 1.6';

