/* 
$Revision: 1.2 $
$Date: 2008/01/15 21:02:16 $
$Author: schlundus $
$Name:  $
*/
ALTER TABLE tcversions ADD COLUMN execution_type tinyint(1) 
ALTER TABLE tcversions ADD COLUMN `tc_external_id` int(10) unsigned NULL
NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated';

ALTER TABLE tcversions COMMENT = 'Updated to TL 1.8.0 Development - DB 1.2';