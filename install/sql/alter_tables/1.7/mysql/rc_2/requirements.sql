/* 
$Revision: 1.1 $
$Date: 2007/04/24 14:24:50 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE requirements ADD COLUMN `node_order` INT(10) UNSIGNED DEFAULT '0' AFTER `type`;
ALTER TABLE requirements COMMENT = 'Updated to TL 1.7.0 RC 2';