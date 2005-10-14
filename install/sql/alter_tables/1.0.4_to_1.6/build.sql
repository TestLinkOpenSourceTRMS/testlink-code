/* 
$Revision: 1.5 $
$Date: 2005/10/14 06:44:45 $
$Author: franciscom $
$Name:  $

20051013 - fm - added Test Plan info in build name
Migration from 1.0.4 to 1.6 POST RC1 - 20050925 - fm
*/
ALTER TABLE `build` DROP PRIMARY KEY;
ALTER TABLE `build` MODIFY `projid` int(10) unsigned NOT NULL default '0';
ALTER TABLE `build` ADD COLUMN `name` varchar(100) NOT NULL default 'undefined';
UPDATE  build SET name=CONCAT("BUILD ",build, " - Test Plan ID:", projid) 
WHERE (name='undefined' or name IS NULL or name='');

ALTER TABLE `build` ADD COLUMN `note` text;
ALTER TABLE `build` ADD COLUMN `id` int(10) unsigned NOT NULL auto_increment, ADD PRIMARY KEY (id);
ALTER TABLE `build` ADD INDEX `projid` (`projid`);

ALTER TABLE `build` COMMENT = 'Updated to TL 1.6 POST RC1';
