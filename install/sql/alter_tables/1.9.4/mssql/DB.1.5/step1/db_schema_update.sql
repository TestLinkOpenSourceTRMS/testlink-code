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

/* users - EXECUTE ONE LINE AT A TIME */
ALTER TABLE /*prefix*/users ADD cookie_string varchar(64) NOT NULL DEFAULT '';
UPDATE /*prefix*/users SET cookie_string=HashBytes('MD5',CAST(RAND() AS CHAR)) + HashBytes('MD5',login);
CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/users_cookie_string ON  /*prefix*/users 
(
	cookie_string
) ON [PRIMARY];

/* FROM THIS POINT YOU CAN RUN EVERYTHING AS A SINGLE BATCH */
/* update some config data */
SET IDENTITY_INSERT /*prefix*/node_types ON;
INSERT INTO /*prefix*/node_types ("id","description") VALUES (11,'requirement_spec_revision');
SET IDENTITY_INSERT /*prefix*/node_types OFF;


-- TICKET 4661
CREATE TABLE /*prefix*/req_specs_revisions (
  	parent_id int NOT NULL,
	id int NOT NULL,
  	revision INTEGER NOT NULL DEFAULT '1',
	doc_id VARCHAR(64) NOT NULL,
	name varchar(100) NULL,
	scope nvarchar(max)   NULL,
	total_req int NOT NULL CONSTRAINT /*prefix*/DF_req_specs_revisions_total_req DEFAULT ((0)),
	type char(1)  NOT NULL CONSTRAINT /*prefix*/DF_req_specs_revisions_type DEFAULT (N'n'),
	status int NULL DEFAULT ((1)),
	author_id int NULL,
	creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_req_specs_revisions_creation_ts DEFAULT (getdate()),
	modifier_id int NULL,
	modification_ts datetime NULL,
  	log_message nvarchar(max)  NULL DEFAULT NULL,
 	CONSTRAINT /*prefix*/PK_req_specs_revisions PRIMARY KEY CLUSTERED 
	(
		id ASC
	) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];


CREATE TABLE /*prefix*/issuetrackers
(
  id int IDENTITY(1,1) NOT NULL,
  name VARCHAR(100) NOT NULL,
  type int NOT NULL CONSTRAINT /*prefix*/DF_issuetrackers_type DEFAULT ((0)),
  cfg nvarchar(max)  NULL,
  CONSTRAINT /*prefix*/PK_issuetrackers PRIMARY KEY  CLUSTERED 
	(
		id
	)  ON [PRIMARY],
    CONSTRAINT /*prefix*/UIX_issuetrackers UNIQUE NONCLUSTERED 
   ( 
	name ASC
   ) ON [PRIMARY]	
) ON [PRIMARY];


CREATE TABLE /*prefix*/testproject_issuetracker
(
  testproject_id int NOT NULL,
  issuetracker_id int NOT NULL,
    CONSTRAINT /*prefix*/UIX_testproject_issuetracker UNIQUE NONCLUSTERED 
   ( 
	testproject_id ASC
   ) ON [PRIMARY]	  
)ON [PRIMARY];


/* testprojects */
ALTER TABLE /*prefix*/testprojects ADD issue_tracker_enabled tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_issue_tracker_enabled DEFAULT ((0));


/* Create Req Spec Revision Nodes */
INSERT INTO /*prefix*/nodes_hierarchy 
(parent_id,name,node_type_id)
SELECT RSP.id,NHRSP.name,11
FROM /*prefix*/req_specs RSP JOIN /*prefix*/nodes_hierarchy NHRSP ON NHRSP.id = RSP.id;

/* Populate Req Spec Revisions Table */
INSERT INTO /*prefix*/req_specs_revisions 
(parent_id,doc_id,scope,total_req,type,author_id,creation_ts,id,name)
SELECT RSP.id,RSP.doc_id,RSP.scope,RSP.total_req,RSP.type,RSP.author_id,RSP.creation_ts,
NHRSPREV.id,NHRSPREV.name
FROM /*prefix*/req_specs RSP JOIN /*prefix*/nodes_hierarchy NHRSPREV
ON NHRSPREV.parent_id = RSP.id AND NHRSPREV.node_type_id=11; 

/* Drop Columns from Req Specs Table */
ALTER TABLE /*prefix*/req_specs DROP COLUMN scope;
ALTER TABLE /*prefix*/req_specs DROP COLUMN total_req;
ALTER TABLE /*prefix*/req_specs DROP COLUMN type;
ALTER TABLE /*prefix*/req_specs DROP COLUMN author_id;
ALTER TABLE /*prefix*/req_specs DROP COLUMN creation_ts;
ALTER TABLE /*prefix*/req_specs DROP COLUMN modifier_id;
ALTER TABLE /*prefix*/req_specs DROP COLUMN modification_ts;

/* new rights */
SET IDENTITY_INSERT /*prefix*/rights ON;
INSERT INTO /*prefix*/rights  (id,description) VALUES (28,'req_tcase_link_management');
INSERT INTO /*prefix*/rights  (id,description) VALUES (29,'keyword_assignment');
INSERT INTO /*prefix*/rights  (id,description) VALUES (30,'mgt_unfreeze_req');
INSERT INTO /*prefix*/rights  (id,description) VALUES (31,'issuetracker_management');
INSERT INTO /*prefix*/rights  (id,description) VALUES (32,'issuetracker_view');
SET IDENTITY_INSERT /*prefix*/rights OFF;


/* update rights on admin role */
SET IDENTITY_INSERT /*prefix*/role_rights ON;
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,30);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,31);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,32);
SET IDENTITY_INSERT /*prefix*/role_rights OFF;
/* ----- END ----- */