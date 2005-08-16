ALTER TABLE bugs MODIFY `tcid` int(10) unsigned NOT NULL default '0';
ALTER TABLE bugs MODIFY `bug` int(10) unsigned NOT NULL default '0';

/*
ALTER TABLE bugs ADD INDEX `tcid` (`tcid`);
ALTER TABLE bugs ADD INDEX `build` (`build`);
ALTER TABLE bugs ADD INDEX `bug` (`bug`);
*/

ALTER TABLE `bugs` COMMENT = 'Updated to TL 1.6';