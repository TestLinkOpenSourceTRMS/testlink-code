ALTER TABLE  `user` MODIFY   `password` varchar(32) NOT NULL default '';
ALTER TABLE  `user` MODIFY   `login` varchar(30) NOT NULL default '';
ALTER TABLE  `user` MODIFY   `rightsid` tinyint(3) unsigned NOT NULL default '0';
ALTER TABLE  `user` MODIFY   `email` varchar(100) NOT NULL default '';
ALTER TABLE  `user` MODIFY   `first` varchar(30) NOT NULL default '';
ALTER TABLE  `user` MODIFY   `last` varchar(30) NOT NULL default '';
ALTER TABLE  `user` ADD COLUMN `locale` varchar(10) NOT NULL default 'en_GB';
ALTER TABLE `user` ADD `default_product` INT(10) NULL;

ALTER TABLE  `user` ADD UNIQUE KEY `login` (`login`);
ALTER TABLE  `user` COMMENT='user information - Updated to TL 1.6';
