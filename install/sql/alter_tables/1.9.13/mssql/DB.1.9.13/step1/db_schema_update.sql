--  -----------------------------------------------------------------------------------
-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- @filesource testlink_create_tables.sql
--
-- SQL script - create db tables for TL
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
--- Existent PK has also error on name
ALTER TABLE /*prefix*/execution_tcsteps DROP CONSTRAINT /*prefix*/PK_executions_tcsteps;
ALTER TABLE /*prefix*/execution_tcsteps ADD ID INT IDENTITY(1,1);
ALTER TABLE /*prefix*/execution_tcsteps ADD CONSTRAINT /*prefix*/PK_execution_tcsteps PRIMARY KEY(ID)
CREATE UNIQUE INDEX /*prefix*/UX1_execution_tcsteps  ON /*prefix*/execution_tcsteps ("execution_id","tcstep_id");