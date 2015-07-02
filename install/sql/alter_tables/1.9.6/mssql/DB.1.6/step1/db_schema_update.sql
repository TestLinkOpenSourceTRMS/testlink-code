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

CREATE TABLE /*prefix*/reqmgrsystems
(
  id int IDENTITY(1,1) NOT NULL,
  name VARCHAR(100) NOT NULL,
  type int NOT NULL CONSTRAINT /*prefix*/DF_reqmgrsystems_type DEFAULT ((0)),
  cfg nvarchar(max)  NULL,
  CONSTRAINT /*prefix*/PK_reqmgrsystems PRIMARY KEY  CLUSTERED 
  (
    id
  )  ON [PRIMARY],
    CONSTRAINT /*prefix*/UIX_reqmgrsystems UNIQUE NONCLUSTERED 
   ( 
  name ASC
   ) ON [PRIMARY]  
) ON [PRIMARY];


CREATE TABLE /*prefix*/testproject_reqmgrsystem
(
  testproject_id int NOT NULL,
  reqmgrsystem_id int NOT NULL,
  CONSTRAINT /*prefix*/UIX_testproject_reqmgrsystem UNIQUE NONCLUSTERED 
  ( 
    testproject_id ASC
  ) ON [PRIMARY]    
)ON [PRIMARY];


/* testprojects */
ALTER TABLE /*prefix*/testprojects ADD reqmgr_integration_enabled tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_reqmgr_integration_enabled DEFAULT ((0));

/* new rights */
SET IDENTITY_INSERT /*prefix*/rights ON;
INSERT INTO /*prefix*/rights  (id,description) VALUES (33,'reqmgrsystem_management');
INSERT INTO /*prefix*/rights  (id,description) VALUES (34,'reqmgrsystem_view');
SET IDENTITY_INSERT /*prefix*/rights OFF;


/* update rights on admin role */
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,33);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,34);
/* ----- END ----- */