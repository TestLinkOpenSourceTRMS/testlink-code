/* 
$Revision: 1.2.6.2 $
$Date: 2010/12/18 14:21:50 $
$Author: franciscom $
$Name: testlink_1_9 $

REMEMBER set INDENTITY_* 

example:
set IDENTITY_INSERT rights on;
INSERT INTO /*prefix*/rights (id,description) VALUES (26,'project_infrastructure_edit');
INSERT INTO /*prefix*/rights (id,description) VALUES (27,'project_infrastructure_view');
set IDENTITY_INSERT rights off;

*/
UPDATE /*prefix*/req_versions SET log_message='Requirement version migrated from Testlink 1.9.0'; 

INSERT INTO /*prefix*/db_version (version,upgrade_ts,notes) VALUES ('DB 1.4',GETDATE(),'');
