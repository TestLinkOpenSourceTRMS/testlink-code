/* 
$Revision: 1.3 $
$Date: 2010/02/18 21:52:10 $
$Author: havlat $
$Name:  $

z_final_step.sql
MySQL
*/

/* system data update */
INSERT INTO /*prefix*/rights  (id,description) VALUES (24 ,'platform_management');
INSERT INTO /*prefix*/rights  (id,description) VALUES (25 ,'platform_view');
INSERT INTO /*prefix*/rights  (id,description) VALUES (26 ,'project_inventory_edit');
INSERT INTO /*prefix*/rights  (id,description) VALUES (27 ,'project_inventory_view');

/* default rights update [platforms, inventory]: leader and admin all, test analyst view)
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
INSERT INTO /*prefix*/db_version (version,notes,upgrade_ts) VALUES('DB 1.3', 'TestLink 1.9',CURRENT_TIMESTAMP());
