ALTER TABLE `category` MODIFY  `name` varchar(100) NOT NULL default 'undefined',
ALTER TABLE `category` MODIFY `mgtcatid` int(10) unsigned NOT NULL default '0';
ALTER TABLE `category` MODIFY `CATorder` int(10) NOT NULL default '0';

ALTER TABLE `category` COMMENT = 'Updated to TL 1.6';

