/* 
$Revision: 1.1 $
$Date: 2008/01/02 18:56:05 $
$Author: franciscom $
$Name:  $
*/
ALTER TABLE db_version ADD COLUMN notes  text;
ALTER TABLE executions COMMENT = 'Updated to TL 1.8.0 Development - DB 1.2';