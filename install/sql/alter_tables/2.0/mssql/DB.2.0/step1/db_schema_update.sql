-- $Revision: 1.1 $
-- $Date: 2010/12/19 17:25:59 $
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

-- cfield_testprojects
ALTER TABLE /*prefix*/cfield_testprojects  ADD required tinyint NOT NULL DEFAULT '0';