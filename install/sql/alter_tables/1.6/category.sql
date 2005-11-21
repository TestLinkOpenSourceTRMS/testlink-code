/* Update From 1.5.x to 1.6 POST RC1*/
ALTER TABLE category DROP COLUMN `name`;
ALTER TABLE `category` COMMENT = 'Updated to TL 1.6 POST RC1';


/* 20051120 - MHT - fix 237 */
ALTER TABLE  `category` MODIFY   `owner` varchar(30) default 'none';