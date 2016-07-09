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

SET IDENTITY_INSERT /*prefix*/node_types ON
INSERT INTO /*prefix*/node_types (id,description) VALUES (12,'build');
INSERT INTO /*prefix*/node_types (id,description) VALUES (13,'platform');
INSERT INTO /*prefix*/node_types (id,description) VALUES (14,'user');
SET IDENTITY_INSERT /*prefix*/node_types OFF

--
-- users 
--
ALTER TABLE /*prefix*/users ADD "auth_method" VARCHAR(10) NULL DEFAULT ''; 

--
-- testprojects 
--
ALTER TABLE /*prefix*/testprojects ADD "api_key" varchar(64) NOT NULL DEFAULT (HashBytes('MD5',CAST(RAND() AS CHAR)) + HashBytes('MD5',CAST(RAND() AS CHAR)));
UPDATE /*prefix*/testprojects SET api_key = HashBytes('MD5',CAST(RAND() AS CHAR)) + HashBytes('MD5',CAST(RAND() AS CHAR));
CREATE UNIQUE INDEX /*prefix*/IX_testprojects_api_key ON /*prefix*/testprojects ("api_key");

--
-- testplans 
--
ALTER TABLE /*prefix*/testplans ADD "api_key" varchar(64) NOT NULL DEFAULT (HashBytes('MD5',CAST(RAND() AS CHAR)) + HashBytes('MD5',CAST(RAND() AS CHAR)));
UPDATE /*prefix*/testplans SET api_key = HashBytes('MD5',CAST(RAND() AS CHAR)) + HashBytes('MD5',CAST(RAND() AS CHAR));
CREATE UNIQUE INDEX /*prefix*/IX_testplans_api_key ON /*prefix*/testplans ("api_key");


--
CREATE TABLE /*prefix*/cfield_build_design_values (
  field_id int NOT NULL,
  node_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_build_design_values_node_id DEFAULT ((0)),
  value varchar(4000)  NOT NULL CONSTRAINT /*prefix*/DF_cfield_build_design_values_value DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_cfield_build_design_values PRIMARY KEY CLUSTERED 
(
  field_id ASC,
  node_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_cfield_build_design_values ON  /*prefix*/cfield_build_design_values 
(
  node_id ASC
) ON [PRIMARY];

/* ----- END ----- */