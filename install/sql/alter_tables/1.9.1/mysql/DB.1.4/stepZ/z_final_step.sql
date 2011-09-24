/* 
$Revision: 1.1.2.2 $
$Date: 2011/01/22 13:47:30 $
$Author: franciscom $
$Name:  $

z_final_step.sql
MySQL
*/


UPDATE /*prefix*/req_versions SET log_message='Requirement version migrated from Testlink 1.9.0'; 

/* database version update */
INSERT INTO /*prefix*/db_version (version,notes,upgrade_ts) VALUES('DB 1.4', 'TestLink 1.9.1',CURRENT_TIMESTAMP());
