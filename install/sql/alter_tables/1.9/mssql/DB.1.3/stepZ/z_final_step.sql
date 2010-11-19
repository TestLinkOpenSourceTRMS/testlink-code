/* 
$Revision: 1.3 $
$Date: 2010/11/19 19:44:27 $
$Author: franciscom $
$Name:  $

20101119 - franciscom - fixed missing set INDENTITY_* thanks to contributor
*/
set IDENTITY_INSERT rights on;
INSERT INTO /*prefix*/rights (id,description) VALUES (24,'platform_management');
INSERT INTO /*prefix*/rights (id,description) VALUES (25,'platform_view');
INSERT INTO /*prefix*/rights (id,description) VALUES (26,'project_infrastructure_edit');
INSERT INTO /*prefix*/rights (id,description) VALUES (27,'project_infrastructure_view');
set IDENTITY_INSERT rights off;

INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,24);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,25);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,26);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,27);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (9,24);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (9,25);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (9,26);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (9,27);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (6,25);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (6,27);

INSERT INTO /*prefix*/db_version (version,upgrade_ts,notes) VALUES ('DB 1.3',GETDATE(),'');
