/* Update From 1.6 to 1.6 POST RC1*/
ALTER TABLE bugs ADD COLUMN build_id int(10) NOT NULL default '0';
ALTER TABLE `bugs` COMMENT = 'Updated to TL 1.6 POST RC1';