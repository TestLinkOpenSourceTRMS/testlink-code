/* 
$Revision: 1.1 $
$Date: 2007/08/18 14:06:52 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE milestones CHANGE COLUMN `date` `target_date` DATE NOT NULL DEFAULT '0000-00-00';
ALTER TABLE milestones COMMENT = 'Updated to TL 1.7.0 RC 3';