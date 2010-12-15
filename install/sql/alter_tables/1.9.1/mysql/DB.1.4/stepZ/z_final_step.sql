/* 
$Revision: 1.1.2.1 $
$Date: 2010/12/15 22:01:51 $
$Author: franciscom $
$Name:  $

z_final_step.sql
MySQL
*/

/* database version update */
INSERT INTO /*prefix*/db_version (version,notes,upgrade_ts) VALUES('DB 1.4', 'TestLink 1.9.1',CURRENT_TIMESTAMP());
