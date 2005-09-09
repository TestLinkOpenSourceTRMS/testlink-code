/* Update From 1.5.x to 1.6 */
ALTER TABLE `mgtproduct` MODIFY `id` int(10) unsigned NOT NULL auto_increment;
ALTER TABLE `mgtproduct` MODIFY `name` varchar(100) NOT NULL default 'undefined';
ALTER TABLE `mgtproduct` ADD COLUMN `option_priority` bool NOT NULL default 1;


/* --------------------------------------------------------------------------- */
ALTER TABLE `mgtproduct` CHANGE `active` `active_enum` enum('Y','N') NOT NULL default 'Y';
ALTER TABLE `mgtproduct` ADD COLUMN `active` BOOL  NOT NULL default 1;
UPDATE  `mgtproduct` SET active=1 WHERE active_enum='Y';
UPDATE  `mgtproduct` SET active=0 WHERE active_enum='N';
/* --------------------------------------------------------------------------- */

/* --------------------------------------------------------------------------- */
ALTER TABLE `mgtproduct` ADD COLUMN `option_reqs` bool NOT NULL default 0;
UPDATE  `mgtproduct` SET option_reqs=1 WHERE option_requirements='Y';
UPDATE  `mgtproduct` SET option_reqs=0 WHERE option_requirements='N';
/* --------------------------------------------------------------------------- */


/* --------------------------------------------------------------------------- */
ALTER TABLE  mgtproduct DROP COLUMN option_requirements;
ALTER TABLE  mgtproduct DROP COLUMN active_enum;
/* --------------------------------------------------------------------------- */

ALTER TABLE `milestone` COMMENT = 'Updated to TL 1.6';
