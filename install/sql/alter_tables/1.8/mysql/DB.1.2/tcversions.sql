/* 
$Revision: 1.1 $
$Date: 2008/01/02 18:56:05 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE tcversions ADD COLUMN execution_type tinyint(1) 
NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated';

ALTER TABLE tcversions COMMENT = 'Updated to TL 1.8.0 Development - DB 1.2';