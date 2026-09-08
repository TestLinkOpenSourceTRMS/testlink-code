--  -----------------------------------------------------------------------------------
-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- @filesource testlink_create_tables.sql
--
-- SQL script - update db tables for TL
-- Database Type: Microsoft SQL Server
--
-- ATTENTION: do not use a different naming convention, that one already in use.
--            TEXTIMAGE Option can be used only tables that have fields of type:
--            varchar(MAXSIZEALLOWED), nvarchar(MAXSIZEALLOWED), varbinary(MAXSIZEALLOWED), xml 
-- 
--        Find issue with custom_fields table if two fields were char(4000)
--        changed to varchar(4000) everything goes OK
--            http://www.mssqltips.com/sqlservertip/2242/row-sizes-exceeding-8060-bytes-in-sql-2005/
-- 
-- ATTENTION: 
-- 
-- @internal revisions
--                          
--  -----------------------------------------------------------------------------------
--
--- 
ALTER TABLE /*prefix*/execution_bugs DROP CONSTRAINT PK_execution_bugs;
ALTER TABLE /*prefix*/execution_bugs ADD COLUMN tcstep_id int NOT NULL;
ALTER TABLE /*prefix*/execution_bugs ADD  primary key (execution_id,bug_id,tcstep_id);