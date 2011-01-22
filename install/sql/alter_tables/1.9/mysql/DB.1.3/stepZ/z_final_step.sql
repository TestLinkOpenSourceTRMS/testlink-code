/* 
$Revision: 1.5.6.2 $
$Date: 2011/01/22 13:53:26 $
$Author: franciscom $
$Name:  $

z_final_step.sql
MySQL

@internal revision
20111121 - franciscom - now migrates to 1.9.1 => DB has to be 1.4 and not 1.3 anymore

*/

/*
Important Notice:
We are updating a column that we have ADDED as part of upgrade.
At least for MySQL when this was done inside db_schema_update.sql update was not done.
*/
UPDATE /*prefix*/req_versions SET log_message='Requirement version migrated from Testlink 1.8.x' WHERE id > 0; 

/* system data update */
INSERT INTO /*prefix*/rights  (id,description) VALUES (24 ,'platform_management');
INSERT INTO /*prefix*/rights  (id,description) VALUES (25 ,'platform_view');
INSERT INTO /*prefix*/rights  (id,description) VALUES (26 ,'project_inventory_management');
INSERT INTO /*prefix*/rights  (id,description) VALUES (27 ,'project_inventory_view');

/* default rights update [platforms, inventory]: leader and admin all, test analyst view) */
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

/* database version update */
INSERT INTO /*prefix*/db_version (version,notes,upgrade_ts) VALUES('DB 1.4', 'TestLink 1.9.1',CURRENT_TIMESTAMP());
