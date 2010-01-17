/* 
$Revision: 1.1 $
$Date: 2010/01/17 17:24:56 $
$Author: franciscom $
$Name:  $

z_final_step.sql
MySQL
*/

/* system data update */
INSERT INTO /*prefix*/rights  (id,description) VALUES (24 ,'platform_management');
INSERT INTO /*prefix*/rights  (id,description) VALUES (25 ,'platform_view');
INSERT INTO /*prefix*/rights  (id,description) VALUES (26 ,'project_infrastructure_edit');
INSERT INTO /*prefix*/rights  (id,description) VALUES (27 ,'project_infrastructure_view');

INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,24);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,25);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,26);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,27);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (9,26);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (9,27);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (6,26);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (4,26);

/* database version update */
INSERT INTO /*prefix*/db_version (version,notes,upgrade_ts) VALUES('DB 1.3', 'TestLink 1.9',CURRENT_TIMESTAMP());
