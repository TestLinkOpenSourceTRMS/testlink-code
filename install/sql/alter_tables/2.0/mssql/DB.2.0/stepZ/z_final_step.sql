/* 
$Revision: 1.1 $
$Date: 2010/12/19 17:26:00 $
$Author: franciscom $
$Name:  $

ATTENTION WITH set INDENTITY_* thanks to contributor
*/
--- set IDENTITY_INSERT rights on;
--- INSERT INTO /*prefix*/rights (id,description) VALUES (24,'platform_management');
--- set IDENTITY_INSERT rights off;

INSERT INTO /*prefix*/db_version (version,upgrade_ts,notes) VALUES ('DB 2.0',GETDATE(),'');
