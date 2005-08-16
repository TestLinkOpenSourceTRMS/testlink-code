ALTER TABLE `mgttestcase` MODIFY `id` int(10) unsigned NOT NULL auto_increment;
ALTER TABLE `mgttestcase` MODIFY `title` varchar(100) default NULL;
ALTER TABLE `mgttestcase` MODIFY `catid` int(10) unsigned NOT NULL default '0';
ALTER TABLE `mgttestcase` MODIFY `version` smallint(5) unsigned NOT NULL default '1';
ALTER TABLE `mgttestcase` MODIFY `TCorder` int(10) NOT NULL default '0';
ALTER TABLE `mgttestcase` ADD COLUMN `create_date` date NOT NULL default '0000-00-00';
ALTER TABLE `mgttestcase` ADD COLUMN `reviewer` varchar(30) default NULL;
ALTER TABLE `mgttestcase` ADD COLUMN `modified_date` date NOT NULL default '0000-00-00';

ALTER TABLE `mgttestcase` COMMENT = 'Updated to TL 1.6';



