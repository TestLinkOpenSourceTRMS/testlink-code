ALTER TABLE  `user` ADD COLUMN `locale` varchar(10) NOT NULL default 'en_GB';
ALTER TABLE `user` ADD `default_product` INT(10) NULL;
ALTER TABLE  `user` COMMENT='user information - Updated to TL 1.6';
