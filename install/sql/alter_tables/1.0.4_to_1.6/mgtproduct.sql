ALTER TABLE `mgtproduct` MODIFY `id` int(10) unsigned NOT NULL auto_increment;
ALTER TABLE `mgtproduct` MODIFY `name` varchar(100) NOT NULL default '';
ALTER TABLE `mgtproduct` MODIFY `color` varchar(12) NOT NULL default '#9BD';


ALTER TABLE `mgtproduct` ADD COLUMN `active` BOOL  NOT NULL default 1;

/* 20051005 - fm - BUGID  0000158: Failed to update database! - details: Product <Name> */
ALTER TABLE `mgtproduct` ADD COLUMN `option_reqs`bool NOT NULL default 0;

ALTER TABLE `mgtproduct` ADD UNIQUE KEY `name` (`name`);
ALTER TABLE `mgtproduct` ADD INDEX  `ActiveId` (`id`,`active`);

ALTER TABLE `mgtproduct` COMMENT = 'Updated to TL 1.6';

