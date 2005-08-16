ALTER TABLE `milestone` MODIFY `id` int(10) unsigned NOT NULL auto_increment;
ALTER TABLE `milestone` MODIFY `projid` int(10) unsigned NOT NULL default '0';

ALTER TABLE `milestone` MODIFY `name` varchar(100) NOT NULL default 'undefined';

ALTER TABLE `milestone` MODIFY `A` tinyint(3) unsigned zerofill NOT NULL default '000';
ALTER TABLE `milestone` MODIFY `B` tinyint(3) unsigned zerofill NOT NULL default '000';
ALTER TABLE `milestone` MODIFY `C` tinyint(3) unsigned zerofill NOT NULL default '000';
ALTER TABLE `milestone` MODIFY `name` varchar(50) NOT NULL default '';

ALTER TABLE `milestone` ADD INDEX `projid` (`projid`);

ALTER TABLE `milestone` COMMENT = 'Updated to TL 1.6'; 

