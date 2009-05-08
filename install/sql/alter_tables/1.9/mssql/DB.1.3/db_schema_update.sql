-- $Revision: 1.1 $
-- $Date: 2009/05/08 06:46:34 $
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
--
-- Step 1 - Drops if needed

-- Step 2 - new tables
--

-- Step 3 - table changes
-- tcversions

-- testplan_tcversions
ALTER TABLE testplan_tcversions ADD author_id INT NULL DEFAULT NULL;
ALTER TABLE testplan_tcversions ADD creation_ts DATETIME NOT NULL DEFAULT GETDATE();