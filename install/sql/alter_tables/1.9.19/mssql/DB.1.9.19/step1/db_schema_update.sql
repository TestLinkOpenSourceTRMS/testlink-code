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

--
-- Table structure for table "execution_tcsteps_backup"
--
CREATE TABLE /*prefix*/execution_tcsteps_backup (
   id int IDENTITY(1,1) NOT NULL,
   tcstep_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_backup_tcstep_id DEFAULT ((0)),
   testplan_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_backup_testplan_id DEFAULT ((0)),
   platform_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_backup_platform_id DEFAULT ((0)),
   build_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_backup_build_id DEFAULT ((0)),
   tester_id int NULL CONSTRAINT /*prefix*/DF_executions_tester_id DEFAULT (NULL),
   backup_date datetime NOT NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_backup_backup_date DEFAULT (getdate()),
   notes nvarchar(max)   NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_backup_notes DEFAULT (NULL),
   status char(1)  NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_backup_status DEFAULT (NULL),
  CONSTRAINT /*prefix*/PK_execution_tcsteps_backup PRIMARY KEY CLUSTERED 
  ( 
    id ASC
  ) ON [PRIMARY],

  CONSTRAINT /*prefix*/UIX_execution_tcsteps_backup UNIQUE NONCLUSTERED 
  ( 
  tcstep_id,testplan_id,platform_id,build_id ASC
  ) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];


