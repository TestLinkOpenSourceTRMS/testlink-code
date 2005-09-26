/* Migration from 1.0.4 to 1.6 POST RC1 - 20050925 - fm*/
ALTER TABLE component MODIFY `mgtcompid` int(10) unsigned NOT NULL default '0';

ALTER TABLE component MODIFY `name` varchar(100) NOT NULL default 'undefined';
ALTER TABLE component DROP COLUMN `name`;

ALTER TABLE `component` COMMENT = 'Updated to TL 1.6 POST RC1';
