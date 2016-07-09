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
--
-- Table structure for table "execution_tcsteps"
--
CREATE TABLE /*prefix*/execution_tcsteps (
   execution_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_execution_id DEFAULT ((0)),  
   tcstep_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_tcstep_id DEFAULT ((0)),
   notes nvarchar(max)   NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_notes DEFAULT (NULL),
   status char(1)  NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_status DEFAULT (NULL),
  CONSTRAINT /*prefix*/PK_executions PRIMARY KEY CLUSTERED 
  ( 
  execution_id,tcstep_id ASC
  ) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];
