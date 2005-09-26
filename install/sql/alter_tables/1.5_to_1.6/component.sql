/* Update From 1.5.x to 1.6 POST RC1*/
ALTER TABLE component DROP COLUMN `name`;
ALTER TABLE `component` COMMENT = 'Updated to TL 1.6 POST RC1';
