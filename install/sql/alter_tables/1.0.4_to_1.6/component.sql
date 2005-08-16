ALTER TABLE component MODIFY `mgtcompid` int(10) unsigned NOT NULL default '0';

ALTER TABLE component MODIFY `name` varchar(100) NOT NULL default 'undefined';

/*
ALTER TABLE component ADD INDEX `id` (`id`);
ALTER TABLE component ADD INDEX `projid` (`projid`);
*/

ALTER TABLE `component` COMMENT = 'Updated to TL 1.6';
