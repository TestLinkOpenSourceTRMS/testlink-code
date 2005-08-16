ALTER TABLE `mgtcomponent` MODIFY `id` int(10) unsigned NOT NULL auto_increment;
ALTER TABLE `mgtcomponent` MODIFY `prodid` int(10) unsigned NOT NULL default '0';
ALTER TABLE `mgtcomponent` MODIFY `name` varchar(100) NOT NULL default 'undefined';
ALTER TABLE `mgtcomponent` COMMENT = 'Updated to TL 1.6';
