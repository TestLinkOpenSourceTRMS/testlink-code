-- $Revision: 1.2.2.2 $
-- $Date: 2010/11/23 21:36:40 $
-- $Author: franciscom $
-- $RCSfile: db_schema_update.sql,v $
-- DB: MSSQL
--
-- Important Warning: 
-- This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
-- It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
-- USE SEMICOLONS ONLY to signal END of SQL Statements.
--
--
-- rev: 
-- 20101123 - franciscom - fixed errors on builds ADD release_date 
-- 20101119 - franciscom - bad default for date (now() -> getdate())
-- 20100705 - asimon - added new column build_id to user_assignments
--
-- Step 1 - Drops if needed

-- Step 2 - new tables
--

-- Step 3 - table changes

-- testprojects
ALTER TABLE /*prefix*/testprojects ADD is_public tinyint NOT NULL DEFAULT '1';

-- testplans
ALTER TABLE /*prefix*/testplans ADD is_public tinyint NOT NULL DEFAULT '1';

-- builds
ALTER TABLE /*prefix*/builds ADD author_id INT NULL DEFAULT NULL;
ALTER TABLE /*prefix*/builds ADD creation_ts datetime NOT NULL DEFAULT getdate();
ALTER TABLE /*prefix*/builds ADD release_date datetime NULL;

-- user_assignments
ALTER TABLE /*prefix*/user_assignments ADD build_id INT NULL DEFAULT NULL;

-- cfield_testprojects
ALTER TABLE /*prefix*/cfield_testprojects  ADD location tinyint NOT NULL DEFAULT '1';

-- tcversions

-- testplan_tcversions
ALTER TABLE testplan_tcversions ADD author_id INT NULL DEFAULT NULL;
ALTER TABLE testplan_tcversions ADD creation_ts DATETIME NOT NULL DEFAULT GETDATE();
