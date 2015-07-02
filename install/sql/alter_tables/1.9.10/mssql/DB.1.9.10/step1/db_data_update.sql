/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * @filesource	db_data_update.sql
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * @internal revisions
 *
 */

-- ==============================================================================
-- ATTENTION PLEASE - replace /*prefix*/ with your table prefix if you have any. 
-- ==============================================================================
SET IDENTITY_INSERT /*prefix*/rights ON;
INSERT INTO /*prefix*/rights (id,description) VALUES (40,'testplan_milestone_overview');
INSERT INTO /*prefix*/rights (id,description) VALUES (41,'exec_testcases_assigned_to_me');
INSERT INTO /*prefix*/rights (id,description) VALUES (42,'testproject_metrics_dashboard');
INSERT INTO /*prefix*/rights (id,description) VALUES (43,'testplan_add_remove_platforms');
INSERT INTO /*prefix*/rights (id,description) VALUES (44,'testplan_update_linked_testcase_versions');
INSERT INTO /*prefix*/rights (id,description) VALUES (45,'testplan_set_urgent_testcases');
INSERT INTO /*prefix*/rights (id,description) VALUES (46,'testplan_show_testcases_newest_versions');
SET IDENTITY_INSERT /*prefix*/rights OFF;

INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,40);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,41);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,42);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,43);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,44);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,45);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,46);
/* ----- END ----- */