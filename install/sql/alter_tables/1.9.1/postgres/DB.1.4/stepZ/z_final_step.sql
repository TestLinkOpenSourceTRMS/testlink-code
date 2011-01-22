/* 
$Revision: 1.1.2.3 $
$Date: 2011/01/22 13:47:31 $
$Author: franciscom $
$Name:  $
*/

-- Last step update some data
UPDATE /*prefix*/req_versions SET log_message='Requirement version migrated from Testlink 1.9.0'; 

-- update config data
INSERT INTO /*prefix*/db_version ("version","upgrade_ts","notes") VALUES ('DB 1.4',now(),'');
