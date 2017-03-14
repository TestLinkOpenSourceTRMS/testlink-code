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
SET IDENTITY_INSERT /*prefix*/rights ON
INSERT INTO /*prefix*/rights (id,description) VALUES (47,'testcase_freeze');
INSERT INTO /*prefix*/rights (id,description) VALUES (48,'mgt_plugins');
SET IDENTITY_INSERT /*prefix*/rights OFF

--  Rights for Administrator role
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,47);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,48);

ALTER TABLE /*prefix*/cfield_testprojects ADD monitorable INT NOT NULL default '0';

ALTER TABLE /*prefix*/users ALTER COLUMN "login" VARCHAR(100);
ALTER TABLE /*prefix*/users ALTER COLUMN "first" VARCHAR(50);
ALTER TABLE /*prefix*/users ALTER COLUMN "last" VARCHAR(50);

CREATE TABLE /*prefix*/req_monitor (
  req_id INT NOT NULL DEFAULT '0',
  user_id  INT NOT NULL DEFAULT '0',
  testproject_id INT NOT NULL DEFAULT '0',
  CONSTRAINT /*prefix*/PK_req_monitor PRIMARY KEY  CLUSTERED 
  (
    req_id,user_id,testproject_id
  )  ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/plugins (
  plugin_id int NOT NULL IDENTITY(1,1) CONSTRAINT /*prefix*/DF_plugins_plugin_id DEFAULT ((0)),
  basename VARCHAR(100) NOT NULL,
  enabled tinyint NOT NULL CONSTRAINT /*prefix*/DF_plugins_enabled DEFAULT ((0)),
  author_id int NOT NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_plugins_creation_ts DEFAULT (getdate()),
 CONSTRAINT /*prefix*/PK_plugins PRIMARY KEY CLUSTERED
 (
  plugin_id ASC
 ) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/plugins_configuration (
  plugin_config_id int IDENTITY(1,1) NOT NULL CONSTRAINT /*prefix*/DF_plugins_configuration_plugin_config_id DEFAULT ((0)),
  testproject_id int NOT NULL CONSTRAINT /*prefix*/DF_plugins_configuration__testproject_id DEFAULT ((0)),
  config_key VARCHAR(255) NOT NULL,
  config_type int NOT NULL,
  config_value VARCHAR(255) NOT NULL,
  author_id int NOT NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_plugins_configuration__creation_ts DEFAULT (getdate()),
 CONSTRAINT /*prefix*/PK_plugins_configuration PRIMARY KEY CLUSTERED
 (
  plugin_config_id ASC
 ) ON [PRIMARY]
) ON [PRIMARY];
