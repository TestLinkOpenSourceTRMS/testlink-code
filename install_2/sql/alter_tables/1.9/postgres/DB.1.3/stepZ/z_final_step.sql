/* 
$Revision: 1.5.6.2 $
$Date: 2011/01/22 13:53:25 $
$Author: franciscom $
$Name:  $
*/

/*
Important Notice:
We are updating a column that we have ADDED as part of upgrade.
At least for MySQL when this was done inside db_schema_update.sql update was not done.
*/
UPDATE /*prefix*/req_versions SET log_message='Requirement version migrated from Testlink 1.8.x'; 

-- update config data
INSERT INTO /*prefix*/rights (id,description) VALUES (24 ,'platform_management');
INSERT INTO /*prefix*/rights (id,description) VALUES (25 ,'platform_view');
INSERT INTO /*prefix*/rights (id,description) VALUES (26 ,'project_inventory_management');
INSERT INTO /*prefix*/rights (id,description) VALUES (27 ,'project_inventory_view');

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

INSERT INTO /*prefix*/db_version ("version","upgrade_ts","notes") VALUES ('DB 1.4',now(),'');
