ALTER TABLE `keywords` MODIFY `id` int(10) unsigned NOT NULL auto_increment;
ALTER TABLE `keywords` MODIFY `keyword` varchar(100) NOT NULL default '';
ALTER TABLE `keywords` MODIFY `prodid` int(10) unsigned NOT NULL default '0';
ALTER TABLE `keywords` ADD INDEX `prodid` (`prodid`);
ALTER TABLE `keywords` ADD INDEX `keyword` (`keyword`);

ALTER TABLE `build` COMMENT = 'Updated to TL 1.6';