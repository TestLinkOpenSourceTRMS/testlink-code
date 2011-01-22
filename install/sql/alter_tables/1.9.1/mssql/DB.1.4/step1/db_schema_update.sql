-- $Revision: 1.2.2.9 $
-- $Date: 2010/12/19 10:35:01 $
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
-- 201101216 - franciscom - update to 1.9.1 DB 1.4
--
-- update some config data
SET IDENTITY_INSERT /*prefix*/node_types ON
INSERT INTO /*prefix*/node_types (id,description) VALUES (10,'requirement_revision');
SET IDENTITY_INSERT /*prefix*/node_types OFF

-- Step 1 - Drops if needed

-- Step 2 - simple structure updates
-- We need to this before creating new table because we have a FK
ALTER TABLE /*prefix*/req_versions ADD revision INTEGER NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/req_versions ADD log_message TEXT NULL DEFAULT NULL;
ALTER TABLE /*prefix*/req_versions DROP CONSTRAINT /*prefix*/PK_req_versions;
ALTER TABLE /*prefix*/req_versions ADD CONSTRAINT /*prefix*/PK_req_versions PRIMARY KEY ("id");

-- Step 3 new tables
CREATE TABLE /*prefix*/req_revisions(  
  parent_id int NOT NULL,
	id int NOT NULL,
  revision INTEGER NOT NULL DEFAULT '1',
	req_doc_id varchar(64)  NULL,
	name varchar(100) NULL,
  scope TEXT NULL DEFAULT NULL,
  status CHAR(1) NOT NULL DEFAULT 'V',
  type CHAR(1) NULL DEFAULT NULL,
  active INT NOT NULL DEFAULT '1',
  is_open INT NOT NULL DEFAULT '1',
  expected_coverage INT NOT NULL DEFAULT 1,
  log_message TEXT NULL DEFAULT NULL,
  author_id  INT NULL DEFAULT NULL,
	creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_req_revisions_creation_ts DEFAULT (getdate()),
  modifier_id INT NULL DEFAULT NULL,
	modification_ts datetime NULL,
  CONSTRAINT /*prefix*/PK_req_revisions PRIMARY KEY CLUSTERED 
  (
	  id
  ) ON [PRIMARY]
) ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/IX1_req_revisions ON  /*prefix*/req_revisions 
(
	parent_id,revision
) ON [PRIMARY];

