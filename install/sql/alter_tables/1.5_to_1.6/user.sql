ALTER TABLE  `user` ADD COLUMN `locale` varchar(10) NOT NULL default 'en_GB';
ALTER TABLE  `user` COMMENT='user information - Updated to TL 1.6';
