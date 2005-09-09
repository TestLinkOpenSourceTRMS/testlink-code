ALTER TABLE  `testcase` MODIFY   `title` varchar(100) default NULL;
ALTER TABLE  `testcase` MODIFY   `catid` int(10) unsigned NOT NULL default '0';
ALTER TABLE  `testcase` MODIFY   `version` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE  `testcase` MODIFY   `mgttcid` int(10) unsigned NOT NULL default '0';
ALTER TABLE  `testcase` MODIFY   `TCorder` int(10) NOT NULL default '0';

/* --------------------------------------------------------------------------- */
ALTER TABLE  `testcase` CHANGE  `active` `active_enum` enum('on','off') NOT NULL default 'on';
ALTER TABLE  `testcase` ADD COLUMN  `active` BOOL  NOT NULL default 1;

UPDATE  `testcase` SET active=1 WHERE active_enum='on';
UPDATE  `testcase` SET active=0 WHERE active_enum='off';

ALTER TABLE  `testcase` DROP COLUMN active_enum;
/* --------------------------------------------------------------------------- */


ALTER TABLE  `testcase` ADD INDEX `mgttcid` (`mgttcid`);
ALTER TABLE  `testcase` ADD INDEX `catid` (`catid`);

ALTER TABLE  `testcase` COMMENT='All of the test case information - Updated to TL 1.6';
