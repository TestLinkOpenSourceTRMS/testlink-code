ALTER TABLE `build` DROP PRIMARY KEY;
ALTER TABLE `build` MODIFY `build` int(10) unsigned NOT NULL default '0';
ALTER TABLE `build` MODIFY `projid` int(10) unsigned NOT NULL default '0';
ALTER TABLE `build` ADD COLUMN `name` varchar(100) NOT NULL default 'undefined';
ALTER TABLE `build` ADD COLUMN `note` text;
ALTER TABLE `build` ADD COLUMN `id` int(10) unsigned NOT NULL auto_increment, ADD PRIMARY KEY (id);
ALTER TABLE `build` ADD INDEX `projid` (`projid`);
ALTER TABLE `build` COMMENT = 'Updated to TL 1.6';
