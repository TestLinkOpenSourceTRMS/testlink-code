/* 
$Revision: 1.1 $
$Date: 2010/12/19 17:26:00 $
$Author: franciscom $
$Name:  $

z_final_step.sql
MySQL
*/

/* database version update */
INSERT INTO /*prefix*/db_version (version,notes,upgrade_ts) VALUES('DB 2.0', 'TestLink 2.0.0',CURRENT_TIMESTAMP());
