/* Update from 1.5 to 1.6 */
UPDATE  build SET name=CONCAT("BUILD ",build) WHERE (name='undefined' or name IS NULL or name='');
ALTER TABLE `build` COMMENT = 'Updated to TL 1.6';
