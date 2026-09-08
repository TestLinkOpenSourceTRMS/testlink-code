/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema MySQL database for TestLink 1.9 from version 1.8 
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * @filesource	db_schema_update.sql
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * @internal revisions
 * @since 1.9.8
 */

# ==============================================================================
# ATTENTION PLEASE - replace /*prefix*/ with your table prefix if you have any. 
# ==============================================================================

/* tcversions */
ALTER TABLE /*prefix*/tcversions ADD COLUMN estimated_exec_duration decimal(6,2) NULL AFTER execution_type;

/* executions */
ALTER TABLE /*prefix*/executions ADD COLUMN execution_duration decimal(6,2) NULL AFTER execution_type;


/* cfield_testprojects */
ALTER TABLE /*prefix*/cfield_testprojects ADD COLUMN required tinyint(1) NOT NULL default '0' AFTER active;

/* req_coverage */
ALTER TABLE /*prefix*/req_coverage ADD COLUMN `author_id` int(10) unsigned default NULL;
ALTER TABLE /*prefix*/req_coverage ADD COLUMN `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE /*prefix*/req_coverage ADD COLUMN `review_requester_id` int(10) unsigned default NULL;
ALTER TABLE /*prefix*/req_coverage ADD COLUMN `review_request_ts` TIMESTAMP NULL DEFAULT NULL;


insert into /*prefix*/rights (id,description) values (35,'exec_edit_notes');
insert into /*prefix*/rights (id,description) values (36,'exec_delete');
insert into /*prefix*/rights (id,description) values (37,'testplan_unlink_executed_testcases');
insert into /*prefix*/rights (id,description) values (38,'testproject_delete_executed_testcases');
insert into /*prefix*/rights (id,description) values (39,'testproject_edit_executed_testcases');

insert into /*prefix*/role_rights (role_id,right_id) values (8,35);
insert into /*prefix*/role_rights (role_id,right_id) values (8,36);
insert into /*prefix*/role_rights (role_id,right_id) values (8,37);
insert into /*prefix*/role_rights (role_id,right_id) values (8,38);
insert into /*prefix*/role_rights (role_id,right_id) values (8,39);
/* ----- END ----- */