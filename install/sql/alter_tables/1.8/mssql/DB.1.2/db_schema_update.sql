-- $Revision: 1.6 $
-- $Date: 2009/04/15 12:51:01 $
-- $Author: havlat $
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
--      20090123 - havlatm - BUG 2013 (remove right ID=19 before add, it was there a minute in 1.7)
--      20081109 - franciscom - added new right events_mgt
--
-- DO NOT USE YET NEED TO BE COMPLETED 
--
-- Step 1 - Drops if needed
DROP TABLE IF EXISTS priorities;
DROP TABLE IF EXISTS risk_assignments;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS text_templates;
DROP TABLE IF EXISTS test_urgency;
DROP TABLE IF EXISTS user_group;
DROP TABLE IF EXISTS user_group_assign;


-- Step 2 - new tables
--
CREATE TABLE [transactions] (
	[id] [int] IDENTITY(1,1) NOT NULL,
  [entry_point] varchar(45) NOT NULL CONSTRAINT [DF_transactions_entry_point] default(N''),
  [start_time] INT NOT NULL CONSTRAINT [DF_transactions_start_time] DEFAULT ((0)),
  [end_time] INT NOT NULL CONSTRAINT [DF_transactions_end_time] DEFAULT ((0)),
  [user_id] INT CONSTRAINT [DF_transactions_user_id] DEFAULT ((0)),
  [session_id] varchar(45) NULL,
  CONSTRAINT [PK_transactions] PRIMARY KEY CLUSTERED 
  (
	  [id] ASC
  ) ON [PRIMARY]
) ON [PRIMARY]

--
CREATE TABLE [events] (
	[id] [int] IDENTITY(1,1) NOT NULL,
  [transaction_id] INT NOT NULL CONSTRAINT [DF_events_transaction_id] DEFAULT ((0)),
  [log_level] SMALLINT NOT NULL CONSTRAINT [DF_events_log_level] DEFAULT ((0)),
  [source] varchar(45) NULL,
  [description] text NOT NULL,
  [fired_at] INT NOT NULL CONSTRAINT [DF_fired_at] DEFAULT ((0)),
  [activity] varchar(45) NULL,
  [object_id] INT NULL,
  [object_type] varchar(45) NULL,
  CONSTRAINT [PK_events] PRIMARY KEY CLUSTERED 
  (
	  [id] ASC
  ) ON [PRIMARY]
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [idx_transaction_id] ON [events] 
(
	[transaction_id] ASC
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [idx_fired_at] ON [events] 
(
	[fired_at] ASC
) ON [PRIMARY]

--
CREATE TABLE [text_templates] (
  [id] [int] IDENTITY(1,1) NOT NULL,
  [type] [smallint] NOT NULL,
  [title] [varchar] (100) NOT NULL,
  [template_data] [text],
  [author_id] [int] default NULL,
	[creation_ts] [datetime] NOT NULL CONSTRAINT [DF_text_templates_creation_ts]  DEFAULT (getdate()),
	[is_public] [tinyint] NOT NULL CONSTRAINT [DF_text_templates_is_public]  DEFAULT ((0)),
	CONSTRAINT [PK_text_templates] PRIMARY KEY  CLUSTERED 
	(
		[id]
	)  ON [PRIMARY],
	CONSTRAINT [IX_text_templates] UNIQUE  NONCLUSTERED 
	(
		[type],
		[title]
	)  ON [PRIMARY] 
) ON [PRIMARY]

--
CREATE TABLE [user_group] (
  [id] [int] IDENTITY(1,1) NOT NULL,
  [title] [varchar] (100) NOT NULL,
  [description] [text],
	CONSTRAINT [PK_user_group] PRIMARY KEY  CLUSTERED 
	(
		[id]
	)  ON [PRIMARY], 
	CONSTRAINT [IX_user_group_title] UNIQUE  NONCLUSTERED 
	(
		[title]
	)  ON [PRIMARY] 
) ON [PRIMARY]

--
CREATE TABLE [user_group_assign] (
  [usergroup_id] [int] NOT NULL,
  [user_id] [int] NOT NULL,
) ON [PRIMARY]


--
-- Table structure for table cfield_testplan_design_values
--
CREATE TABLE [cfield_testplan_design_values](
	[field_id] [int] NOT NULL,
	[link_id] [int] NOT NULL CONSTRAINT [DF_cfield_testplan_design_values_node_id]  DEFAULT ((0)),
	[value] [varchar](255)  NOT NULL CONSTRAINT [DF_cfield_testplan_design_values_value]  DEFAULT (''),
 CONSTRAINT [PK_cfield_testplan_design_values] PRIMARY KEY CLUSTERED 
(
	[field_id] ASC,
	[link_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [idx_cfield_testplan_design_values] ON [cfield_testplan_design_values] 
(
	[link_id] ASC
) ON [PRIMARY]


-- Step 3 - table changes
-- tcversions
UPDATE tcversions
SET importance='2'
WHERE importance IN('M','m');

UPDATE tcversions
SET importance='3'
WHERE importance IN('H','h');

UPDATE tcversions
SET importance='1'
WHERE importance IN('L','l');

ALTER TABLE tcversions DROP CONSTRAINT DF_tcversions_importance;
ALTER TABLE tcversions ALTER COLUMN importance INT NOT NULL;
ALTER TABLE tcversions ADD CONSTRAINT	DF_tcversions_importance DEFAULT '1' FOR importance;

ALTER TABLE tcversions ADD execution_type INT NOT NULL DEFAULT  '1';

-- testprojects
ALTER TABLE testprojects ADD prefix varchar(30) NULL;
ALTER TABLE testprojects ADD tc_counter INT NULL default '0';
ALTER TABLE testprojects ADD option_automation INT NOT NULL default '0';

-- user
ALTER TABLE users ADD script_key varchar(32) NULL;

-- executions
ALTER TABLE executions ADD tcversion_number INT NOT NULL default '1';
ALTER TABLE executions ADD execution_type INT NOT NULL default '1';

-- testplan_tcversions
ALTER TABLE testplan_tcversions ADD urgency INT NOT NULL default '2',
ALTER TABLE testplan_tcversions ADD node_order INT NOT NULL default '1';

-- custom_fields
ALTER TABLE custom_fields ADD show_on_testplan_design tinyint NOT NULL DEFAULT '0',
ALTER TABLE custom_fields ADD enable_on_testplan_design tinyint NOT NULL DEFAULT '0';

-- db_version
ALTER TABLE db_version ADD notes TEXT NULL;

-- data update
DELETE FROM rights WHERE id=19;
INSERT INTO rights (id,description) VALUES (19,'system_configuration');
INSERT INTO rights (id,description) VALUES (20,'mgt_view_events');
INSERT INTO rights (id,description) VALUES (21,'mgt_view_usergroups');
INSERT INTO rights (id,description) VALUES (22,'events_mgt');

DELETE FROM role_rights WHERE right_id=19;
INSERT INTO role_rights (role_id,right_id) VALUES (8,19);
INSERT INTO role_rights (role_id,right_id) VALUES (8,20);
INSERT INTO role_rights (role_id,right_id) VALUES (8,21);
INSERT INTO role_rights (role_id,right_id) VALUES (8,22);
