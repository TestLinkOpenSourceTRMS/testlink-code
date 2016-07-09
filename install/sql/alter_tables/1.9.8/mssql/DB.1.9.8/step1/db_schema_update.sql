/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema MSSQL database for TestLink 1.9.4 from version 1.8.1 
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * @filesource	db_schema_update.sql
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * @internal revisions:
 */

-- ==============================================================================
-- ATTENTION PLEASE - WHEN YOU RUN THIS using a SQL CLIENTE
-- 1. replace /*prefix*/ with your table prefix if you have any. 
-- 2. execute line by line all operations on users table, because is done
--    all as a block will fail 
--    (see 
--     http://stackoverflow.com/questions/4443262/tsql-add-column-to-table-and-then-update-it-inside-transaction-go 
--     note said: Nope, the error is related to batch and compilation. At parse time, ADDED COLUMN does not exist
--    ) 
-- ==============================================================================


/* tcversions */
ALTER TABLE /*prefix*/tcversion ADD estimated_exec_duration NULL decimal(6,2);

/* executions */
ALTER TABLE /*prefix*/executions ADD execution_duration NULL decimal(6,2);

/* cfield_testprojects */
ALTER TABLE /*prefix*/cfield_testprojects  ADD required tinyint NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_required DEFAULT ((0));

ALTER TABLE /*prefix*/req_coverage ADD author_id int NULL DEFAULT NULL;
ALTER TABLE /*prefix*/req_coverage ADD datetime NOT NULL CONSTRAINT /*prefix*/DF_req_coverage_creation_ts DEFAULT (getdate());
ALTER TABLE /*prefix*/req_coverage ADD review_requester_id int NULL DEFAULT NULL;
ALTER TABLE /*prefix*/req_coverage ADD review_request_ts datetime NULL DEFAULT NULL;

SET IDENTITY_INSERT /*prefix*/rights ON
insert into /*prefix*/rights (id,description) values (35,'exec_edit_notes');
insert into /*prefix*/rights (id,description) values (36,'exec_delete');
insert into /*prefix*/rights (id,description) values (37,'testplan_unlink_executed_testcases');
insert into /*prefix*/rights (id,description) values (38,'testproject_delete_executed_testcases');
insert into /*prefix*/rights (id,description) values (39,'testproject_edit_executed_testcases');
SET IDENTITY_INSERT /*prefix*/rights OFF


insert into /*prefix*/role_rights (role_id,right_id) values (8,35);
insert into /*prefix*/role_rights (role_id,right_id) values (8,36);
insert into /*prefix*/role_rights (role_id,right_id) values (8,37);
insert into /*prefix*/role_rights (role_id,right_id) values (8,38);
insert into /*prefix*/role_rights (role_id,right_id) values (8,39);

/* ----- END ----- */