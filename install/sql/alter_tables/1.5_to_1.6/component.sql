/* Update From 1.5.x to 1.6 */
ALTER TABLE component MODIFY `name` varchar(100) NOT NULL default 'undefined';
ALTER TABLE `component` COMMENT = 'Updated to TL 1.6';
