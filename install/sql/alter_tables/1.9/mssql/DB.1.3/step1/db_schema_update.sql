-- $Revision: 1.2.2.11 $
-- $Date: 2011/01/22 13:53:26 $
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
-- 20101219 - franciscom - MARKED operations THAT NEED TO BE DONE MANUALLY DUE TO MSSQL LIMITATION
--                         (search for string NEED TO BE DONE MANUALLY to FIND IT)
--
-- 20101214 - franciscom - update to 1.9.1 DB 1.4
-- 20101123 - franciscom - fixed errors on builds ADD release_date 
-- 20101119 - franciscom - bad default for date (now() -> getdate())
-- 20100705 - asimon - added new column build_id to user_assignments
--

-- update some config data
SET IDENTITY_INSERT /*prefix*/node_types ON
INSERT INTO /*prefix*/node_types (id,description) VALUES (8,'requirement_version');
INSERT INTO /*prefix*/node_types (id,description) VALUES (9,'testcase_step');
INSERT INTO /*prefix*/node_types (id,description) VALUES (10,'requirement_revision');
SET IDENTITY_INSERT /*prefix*/node_types OFF

-- Step 1 - Drops if needed

-- Step 2 - new tables
--
CREATE TABLE /*prefix*/req_versions(  
	id int NOT NULL,
  version INTEGER NOT NULL DEFAULT '1',
  revision INTEGER NOT NULL DEFAULT '1',
  scope TEXT NULL DEFAULT NULL,
  status CHAR(1) NOT NULL DEFAULT 'V',
  type CHAR(1) NULL DEFAULT NULL,
  active INT NOT NULL DEFAULT '1',
  is_open INT NOT NULL DEFAULT '1',
  expected_coverage INT NOT NULL DEFAULT 1,
  author_id  INT NULL DEFAULT NULL,
	creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_req_versions_creation_ts DEFAULT (getdate()),
  modifier_id INT NULL DEFAULT NULL,
	modification_ts datetime NULL,
  log_message TEXT NULL DEFAULT NULL,
  CONSTRAINT /*prefix*/PK_req_versions PRIMARY KEY CLUSTERED 
  (
	  id
  ) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/tcsteps (  
	id int NOT NULL,
  step_number INT NOT NULL DEFAULT '1',
  actions TEXT NULL DEFAULT NULL,
  expected_results TEXT NULL DEFAULT NULL,
  active INT NOT NULL DEFAULT '1',
  execution_type INT NOT NULL DEFAULT '1',
  CONSTRAINT /*prefix*/PK_tcsteps PRIMARY KEY CLUSTERED 
  (
	id ASC
  ) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE TABLE /*prefix*/platforms (
  id int IDENTITY(1,1) NOT NULL,
  name VARCHAR(100) NOT NULL,
  testproject_id int NOT NULL DEFAULT '0',
  notes text NOT NULL,
	CONSTRAINT /*prefix*/PK_platforms PRIMARY KEY  CLUSTERED 
	(
		id
	)  ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE TABLE /*prefix*/testplan_platforms (
  id int IDENTITY(1,1) NOT NULL,
  testplan_id int NOT NULL DEFAULT '0',
  platform_id int NOT NULL DEFAULT '0',
	CONSTRAINT /*prefix*/PK_testplan_platforms PRIMARY KEY  CLUSTERED 
	(
		id
	)  ON [PRIMARY]
)ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/UIX_testplan_platforms ON  /*prefix*/testplan_platforms 
(
	testplan_id,platform_id
) ON [PRIMARY];


CREATE TABLE /*prefix*/inventory (
  id int IDENTITY(1,1) NOT NULL,
	testproject_id int NOT NULL CONSTRAINT /*prefix*/DF_inventory_testproject_id DEFAULT ((0)),
	owner_id int NOT NULL,
	name VARCHAR(255) NOT NULL,
	ipaddress VARCHAR(255) NOT NULL,
	content TEXT,
	creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_inventory_creation_ts DEFAULT (getdate()),
	modification_ts datetime NULL,
	CONSTRAINT /*prefix*/PK_inventory PRIMARY KEY  CLUSTERED 
	(
		id
	)  ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_inventory_testproject_id ON  /*prefix*/inventory
(
		testproject_id
) ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/UIX_inventory ON  /*prefix*/inventory 
(
	name,testproject_id
) ON [PRIMARY];


--- 
CREATE TABLE /*prefix*/req_relations (
  id int IDENTITY(1,1) NOT NULL,
  source_id INT NOT NULL DEFAULT '0',
  destination_id  INT NOT NULL DEFAULT '0',
  relation_type INT NOT NULL DEFAULT '1',
	author_id int NOT NULL,
	creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_req_relations_creation_ts DEFAULT (getdate()),
	CONSTRAINT /*prefix*/PK_req_relations PRIMARY KEY  CLUSTERED 
	(
		id
	)  ON [PRIMARY]
) ON [PRIMARY];


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




-- Step 3 - table changes

-- testprojects
ALTER TABLE /*prefix*/testprojects ADD is_public tinyint NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/testprojects ADD options TEXT;


-- testplans
ALTER TABLE /*prefix*/testplans ADD is_public tinyint NOT NULL DEFAULT '1';

-- builds
ALTER TABLE /*prefix*/builds ADD author_id INT NULL DEFAULT NULL;
ALTER TABLE /*prefix*/builds ADD creation_ts datetime NOT NULL DEFAULT getdate();
ALTER TABLE /*prefix*/builds ADD release_date datetime NULL;

-- user_assignments
ALTER TABLE /*prefix*/user_assignments ADD build_id INT NULL DEFAULT NULL;

-- cfield_testprojects
ALTER TABLE /*prefix*/cfield_testprojects  ADD location tinyint NOT NULL DEFAULT '1';

-- milestones
ALTER TABLE /*prefix*/milestones ADD start_date DATETIME NULL;

-- req_spec
ALTER TABLE /*prefix*/req_specs ADD doc_id VARCHAR(64) NOT NULL DEFAULT 'RS_DOC_ID';

-- requirements
-- For MSSQL - NEED TO BE DONE MANUALLY
-- ALTER TABLE /*prefix*/requirements ALTER req_doc_id VARCHAR(64);

-- tcversions

-- testplan_tcversions
ALTER TABLE testplan_tcversions ADD author_id INT NULL DEFAULT NULL;
ALTER TABLE testplan_tcversions ADD creation_ts DATETIME NOT NULL DEFAULT GETDATE();

--- 
ALTER TABLE /*prefix*/cfield_design_values ALTER COLUMN value varchar(4000);
ALTER TABLE /*prefix*/cfield_execution_values ALTER COLUMN value varchar(4000);
ALTER TABLE /*prefix*/cfield_testplan_design_values ALTER COLUMN value varchar(4000);

-- For MSSQL - NEED TO BE DONE MANUALLY
-- ALTER TABLE /*prefix*/custom_fields ALTER COLUMN possible_values varchar(4000);
-- ALTER TABLE /*prefix*/custom_fields ALTER COLUMN default_value varchar(4000);