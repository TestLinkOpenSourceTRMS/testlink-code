/* 
$Revision: 1.1 $
$Date: 2007/08/18 14:06:52 $
$Author: franciscom $
$Name:  $
*/
UPDATE rights SET description = 'testplan_user_role_assignment' WHERE id=5;
DELETE FROM rights WHERE id=19;
INSERT INTO db_version VALUES('1.7.0 RC 3', CURRENT_TIMESTAMP());