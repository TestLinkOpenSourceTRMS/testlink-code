ALTER TABLE `mgtcategory` MODIFY `id` int(10) unsigned NOT NULL auto_increment;
ALTER TABLE `mgtcategory` MODIFY `name` varchar(100) NOT NULL default 'undefined';
ALTER TABLE `mgtcategory` MODIFY `compid` int(10) unsigned NOT NULL default '0';
ALTER TABLE `mgtcategory` MODIFY `CATorder` int(10) NOT NULL default '0';

ALTER TABLE `build` COMMENT = 'Updated to TL 1.6';
