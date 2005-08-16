ALTER TABLE  `project` ADD COLUMN `prodid` int(10) unsigned NOT NULL default '0';
ALTER TABLE  `project` MODIFY `name` varchar(100) NOT NULL default 'unknown';
ALTER TABLE  `project` ADD INDEX `product` (`prodid`,`active`);

/* --------------------------------------------------------------------------- */
ALTER TABLE  `project` CHANGE `active` active_enum enum('y','n') NOT NULL default 'y';
ALTER TABLE  `project` ADD COLUMN  active  BOOL  NOT NULL default 1;

UPDATE  `project` SET active=1 WHERE active_enum='y';
UPDATE  `project` SET active=0 WHERE active_enum='n';

ALTER TABLE  `project` DROP COLUMN active_enum;
/* --------------------------------------------------------------------------- */

ALTER TABLE  `project`  COMMENT='All of the project information - Updated to TL 1.6' ;
