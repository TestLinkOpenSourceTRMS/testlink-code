/* Migration from 1.0.4 to 1.6 POST RC1 - 20050925 - fm*/
UPDATE  build SET name=CONCAT("BUILD ",build) WHERE (name='undefined' or name IS NULL or name='');
ALTER TABLE `build` COMMENT = 'Updated to TL 1.6 POST RC1';
