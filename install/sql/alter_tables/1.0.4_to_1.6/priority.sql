ALTER TABLE `priority` MODIFY `id` int(10) unsigned NOT NULL auto_increment;
ALTER TABLE `priority` MODIFY `projid` int(10) unsigned NOT NULL default '0';
ALTER TABLE `priority` ADD INDEX `projid` (`projid`);

ALTER TABLE `priority` COMMENT = 'Updated to TL 1.6';




