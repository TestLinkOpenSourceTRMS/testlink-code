-- $Revision: 1.12 $
-- $Date: 2009/08/30 00:56:06 $
-- $Author: havlat $
-- $RCSfile: db_schema_update.sql,v $
-- DB: Postgres
--
-- Important Warning: 
-- This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
-- It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
-- USE SEMICOLONS ONLY to signal END of SQL Statements.
--
--
-- 20090123 - havlatm - BUG 2013 (remove right ID=19 before add, it was there a minute in 1.7)
-- 20081109 - franciscom - added new right events_mgt
-- 20081018 - franciscom - new indexes (suggested by schlundus) on events table 
-- 20081003 - franciscom - added  CREATE TABLE cfield_testplan_design_values
-- 20080927 - franciscom - fix bug when migration tcversions.importance


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

CREATE TABLE "events" (
  "id" BIGSERIAL NOT NULL,
  "transaction_id" BIGINT NOT NULL default '0',
  "log_level" SMALLINT NOT NULL default '0',
  "source" varchar(45) NULL,
  "description" text NOT NULL,
  "fired_at" INT NOT NULL default '0',
  "activity" varchar(45) NULL,
  "object_id" BIGINT NULL,
  "object_type" varchar(45) NULL,
  PRIMARY KEY  ("id")
);
CREATE INDEX "events_transaction_id" ON "events" ("transaction_id");
CREATE INDEX "events_fired_at" ON "events" ("fired_at");


CREATE TABLE  "transactions" (
  "id" BIGSERIAL NOT NULL,
  "entry_point" varchar(45) NOT NULL default '',
  "start_time" INT NOT NULL default '0',
  "end_time" INT NOT NULL default '0',
  "user_id" BIGINT DEFAULT 0,
  "session_id" varchar(45) default NULL,
  PRIMARY KEY ("id")
);


CREATE TABLE text_templates (
  "id" BIGSERIAL NOT NULL,
  type INT NOT NULL,
  title varchar(100) NOT NULL,
  template_data text,
  author_id BIGINT default NULL,
  create_ts TIMESTAMP NOT NULL default now(),
  is_public INT2 NOT NULL default '0',
  PRIMARY KEY ("id"),
  UNIQUE (type,title)
);
COMMENT ON TABLE text_templates IS 'Global Project Templates';


CREATE TABLE user_group (
  "id" BIGSERIAL NOT NULL,
  title varchar(100) NOT NULL,
  description text,
  owner_id BIGINT NOT NULL,
  testproject_id BIGINT NOT NULL,
  UNIQUE (title)
);


CREATE TABLE user_group_assign (
  usergroup_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL
);


--
-- Table structure for table cfield_testplan_design_values
--
CREATE TABLE "cfield_testplan_design_values" (  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES custom_fields (id),
  "link_id" INTEGER NOT NULL DEFAULT '0' REFERENCES testplan_tcversions (id),
  "value" VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","link_id")
); 
CREATE INDEX "idx_cfield_tplan_design_val" ON "cfield_testplan_design_values" ("link_id");


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

BEGIN;
ALTER TABLE tcversions ALTER COLUMN importance DROP DEFAULT;
ALTER TABLE tcversions ALTER COLUMN importance TYPE int2 USING CAST(importance AS int2);
ALTER TABLE tcversions ALTER COLUMN importance SET DEFAULT 2;
COMMIT;
 
BEGIN;
ALTER TABLE tcversions ADD COLUMN tc_external_id numeric(10) NOT NULL DEFAULT 0;
ALTER TABLE tcversions ALTER COLUMN tc_external_id DROP DEFAULT;
COMMIT;
 
ALTER TABLE tcversions ADD COLUMN execution_type INT2 NOT NULL default 1;

-- testprojects
ALTER TABLE testprojects ADD COLUMN prefix varchar(30) NULL;
ALTER TABLE testprojects ADD COLUMN tc_counter INT NULL default 0;
ALTER TABLE testprojects ADD COLUMN option_automation INT2 NOT NULL default 0;
COMMENT ON TABLE testprojects IS 'Updated to TL 1.8.0 Development - DB 1.2';


-- user
ALTER TABLE users ADD COLUMN script_key varchar(32) NULL;
COMMENT ON TABLE users IS 'Updated to TL 1.8.0 Development - DB 1.2';

-- executions
ALTER TABLE executions ADD COLUMN tcversion_number INT NOT NULL default '1';
ALTER TABLE executions ADD COLUMN execution_type INT2 NOT NULL default '1';
COMMENT ON COLUMN executions.execution_type IS '1 -> manual, 2 -> automated'; 
COMMENT ON COLUMN executions.tcversion_number IS 'test case version used for this execution';
COMMENT ON TABLE executions  IS 'Updated to TL 1.8.0 Development - DB 1.2';

-- testplan_tcversions
ALTER TABLE testplan_tcversions ADD COLUMN urgency INT2 NOT NULL default '2';
ALTER TABLE testplan_tcversions ADD COLUMN node_order INT NOT NULL default '1';
COMMENT ON COLUMN testplan_tcversions.node_order IS 'order in execution tree'; 
COMMENT ON TABLE testplan_tcversions IS 'Updated to TL 1.8.0 Development - DB 1.2';

-- custom_fields
ALTER TABLE custom_fields ADD COLUMN show_on_testplan_design SMALLINT NOT NULL DEFAULT '0';
ALTER TABLE custom_fields ADD COLUMN enable_on_testplan_design SMALLINT NOT NULL DEFAULT '0';
COMMENT ON TABLE custom_fields IS 'Updated to TL 1.8 RC3  - DB 1.2';



-- db_version
ALTER TABLE db_version ADD COLUMN notes  text;
COMMENT ON TABLE db_version IS 'Updated to TL 1.8.0 Development - DB 1.2';

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
