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
CREATE TABLE /*prefix*/testcase_relations (
  id int IDENTITY(1,1) NOT NULL,
  source_id INT NOT NULL DEFAULT '0',
  destination_id  INT NOT NULL DEFAULT '0',
  relation_type INT NOT NULL DEFAULT '1',
  author_id int NOT NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_testcase_relations_creation_ts DEFAULT (getdate()),
  CONSTRAINT /*prefix*/PK_req_relations PRIMARY KEY  CLUSTERED 
  (
    id
  )  ON [PRIMARY]
) ON [PRIMARY];