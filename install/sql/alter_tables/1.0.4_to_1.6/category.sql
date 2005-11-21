/* Migration from 1.0.4 to 1.6 POST RC1 - 20050925 - fm*/
ALTER TABLE `category` DROP COLUMN `name`;
ALTER TABLE `category` MODIFY `mgtcatid` int(10) unsigned NOT NULL default '0';
ALTER TABLE `category` MODIFY `CATorder` int(10) NOT NULL default '0';

ALTER TABLE `category` COMMENT = 'Updated to TL 1.6 POST RC1';


/* 20051120 - MHT - fix 237 */
ALTER TABLE  `category` MODIFY   `owner` varchar(30) default 'none';