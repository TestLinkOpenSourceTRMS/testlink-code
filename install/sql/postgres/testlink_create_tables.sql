-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- $Id: testlink_create_tables.sql,v 1.37 2009/06/08 20:12:31 franciscom Exp $
--
-- SQL script - create db tables for TL on Postgres   
-- 
-- ATTENTION: do not use a different naming convention, that one already in use.
-- 
-- CRITIC:
--        Because this file will be processed during installation doing text replaces
--        to add TABLE PREFIX NAME, any NEW DDL CODE added must be respect present
--        convention regarding case and spaces between DDL keywords.
--
-- 
-- Rev :
--      20090512 - franciscom - BUGID - is_public attribute for testprojects and testplans
--      20090507 - franciscom - BUGID  new builds structure
--      20090411 - franciscom - BUGID 2369 - testplan_tcversions
--
--      20090315 - franciscom - req_spec, requirements id can not be big serial
--                              because are nodes on nodes_hierarchy.
--
--      20090204 - franciscom - object_keywords - bad type for ID column
--      20090103 - franciscom - milestones table - added new unique index
--                              custom_fields - added missing unique constraint
--      20081018 - franciscom - new indexes (suggested by schlundus) on events table 
--      20080831 - franciscom - BUGID 1650 (REQ)
--                 custom_fields.show_on_testplan_design
--                 custom_fields.enable_on_testplan_design
--                 new table cfield_testplan_design_values 
--
--      20080709 - franciscom - Added Foreing Keys (REFERENCES)
--      20080102 - franciscom - added changes for API feature (DB 1.2)
--                              added notes fields on db_version
--
--      20071202 - franciscom - added tcversions.execution_type
--      20071010 - franciscom - open -> is_open due to MSSQL reserved word problem
--      20070519 - franciscom - milestones table date -> target_date, because
--                              date is reserved word for Oracle
--
--      20070414 - franciscom - table requirements: added field node_order 
--      20070204 - franciscom - changes in tables priorities, risk_assignments 
--      20070131 - franciscom - requirements -> req_doc_id(32), 
--
--      20070120 - franciscom - following BUGID 458 ( really a new feature request)
--                              two new fields on builds table
--                              active, open
--
--      20070117 - franciscom - create_ts -> creation_ts
--
--      20070116 - franciscom - fixed BUGID 545
--
--      20070113 - franciscom - table cfield_testprojects added fields
--                              required_on_design,required_on_execution
--      20060515 - franciscom - creation
--

--
-- Table structure for table "node_types"
--
CREATE TABLE "node_types" (  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT 'testproject',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "nodes_hierarchy"
--
CREATE TABLE "nodes_hierarchy" (  
  "id" BIGSERIAL NOT NULL ,
  "name" VARCHAR(100) NULL DEFAULT NULL,
  "parent_id" BIGINT NULL DEFAULT NULL,
  "node_type_id" BIGINT NOT NULL DEFAULT '1' REFERENCES node_types (id),
  "node_order" BIGINT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "nodes_hierarchy_pid_m_nodeorder" ON "nodes_hierarchy" ("parent_id","node_order");

--
--
--
CREATE TABLE "transactions" (
  "id" BIGSERIAL NOT NULL,
  "entry_point" varchar(45) NOT NULL default '',
  "start_time" INT NOT NULL default '0',
  "end_time" INT NOT NULL default '0',
  "user_id" BIGINT DEFAULT 0,
  "session_id" varchar(45) default NULL,
  PRIMARY KEY ("id")
);

--
--
--
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


--
-- Table structure for table "roles"
--
CREATE TABLE "roles" (  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT '',
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id"),
  UNIQUE ("description")
); 


--
-- Table structure for table "users"
--
CREATE TABLE "users" (  
  "id" BIGSERIAL NOT NULL ,
  "login" VARCHAR(30) NOT NULL DEFAULT '',
  "password" VARCHAR(32) NOT NULL DEFAULT '',
  "role_id" SMALLINT NOT NULL DEFAULT '0' REFERENCES roles (id),
  "email" VARCHAR(100) NOT NULL DEFAULT '',
  "first" VARCHAR(30) NOT NULL DEFAULT '',
  "last" VARCHAR(30) NOT NULL DEFAULT '',
  "locale" VARCHAR(10) NOT NULL DEFAULT 'en_GB',
  "default_testproject_id" INTEGER NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "script_key" VARCHAR(32) NULL,
  PRIMARY KEY ("id"),
  UNIQUE ("login")
);


--
-- Table structure for table "tcversions"
--
CREATE TABLE "tcversions" (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "tc_external_id" INT NULL,
  "version" INTEGER NOT NULL DEFAULT '1',
  "summary" TEXT NULL DEFAULT NULL,
  "steps" TEXT NULL DEFAULT NULL,
  "expected_results" TEXT NULL DEFAULT NULL,
  "importance" INT2 NOT NULL DEFAULT '2',
  "author_id" BIGINT NULL DEFAULT NULL REFERENCES users (id),
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "updater_id" BIGINT NULL DEFAULT NULL REFERENCES users (id),
  "modification_ts" TIMESTAMP NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "is_open" INT2 NOT NULL DEFAULT '1',
  "execution_type" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "testplans"
--
CREATE TABLE "testplans" (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "testproject_id" BIGINT NOT NULL DEFAULT '0',
  "notes" TEXT NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "is_open" INT2 NOT NULL DEFAULT '1',
  "is_public" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 
CREATE INDEX "testplans_testproject_id_active" ON "testplans" ("testproject_id","active");


--
-- Table structure for table `builds`
--
CREATE TABLE "builds" (  
  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES testplans (id),
  "name" VARCHAR(100) NOT NULL DEFAULT 'undefined',
  "notes" TEXT NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "is_open" INT2 NOT NULL DEFAULT '1',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "release_date" DATE NULL,
  PRIMARY KEY ("id"),
  UNIQUE ("testplan_id","name")
); 
CREATE INDEX "builds_testplan_id" ON "builds" ("testplan_id");

--
-- Table structure for table "executions"
--
CREATE TABLE "executions" (  
  "id" BIGSERIAL NOT NULL ,
  "build_id" INTEGER NOT NULL DEFAULT '0' REFERENCES builds (id),
  "tester_id" BIGINT NULL DEFAULT NULL,
  "execution_ts" TIMESTAMP NULL,
  "status" CHAR(1) NULL DEFAULT NULL,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES testplans (id),
  "tcversion_id" BIGINT NOT NULL DEFAULT '0' REFERENCES tcversions (id),
  "tcversion_number" INTEGER NOT NULL DEFAULT '1',
  "execution_type" INT2 NOT NULL DEFAULT '1',
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "executions_idx1" ON "executions" ("testplan_id","tcversion_id");
CREATE INDEX "executions_idx2" ON "executions" ("execution_type");

--
-- Table structure for table "testplan_tcversions"
--
CREATE TABLE "testplan_tcversions" (  
  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES testplans (id),
  "tcversion_id" BIGINT NOT NULL DEFAULT '0' REFERENCES tcversions (id),  
  "node_order" BIGINT NOT NULL DEFAULT 1,
  "urgency" INT2 NOT NULL DEFAULT '2',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  PRIMARY KEY ("id"),
  UNIQUE ("testplan_id","tcversion_id")
); 


--
-- Table structure for table "custom_fields"
--
CREATE TABLE "custom_fields" (  
  "id" SERIAL NOT NULL ,
  "name" VARCHAR(64) NOT NULL DEFAULT '',
  "label" VARCHAR(64) NOT NULL DEFAULT '',
  "type" SMALLINT NOT NULL DEFAULT '0',
  "possible_values" VARCHAR(255) NOT NULL DEFAULT '',
  "default_value" VARCHAR(255) NOT NULL DEFAULT '',
  "valid_regexp" VARCHAR(255) NOT NULL DEFAULT '',
  "length_min" INTEGER NOT NULL DEFAULT '0',
  "length_max" INTEGER NOT NULL DEFAULT '0',
  "show_on_design" SMALLINT NOT NULL DEFAULT '1',
  "enable_on_design" SMALLINT NOT NULL DEFAULT '1',
  "show_on_execution" SMALLINT NOT NULL DEFAULT '0',
  "enable_on_execution" SMALLINT NOT NULL DEFAULT '0',
  "show_on_testplan_design" SMALLINT NOT NULL DEFAULT '0',
  "enable_on_testplan_design" SMALLINT NOT NULL DEFAULT '0',
  PRIMARY KEY ("id"),
  UNIQUE ("name")
); 
CREATE INDEX "custom_fields_idx_custom_fields_name" ON "custom_fields" ("name");

--
-- Table structure for table "testprojects"
--
CREATE TABLE "testprojects" (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "notes" TEXT NULL DEFAULT NULL,
  "color" VARCHAR(12) NOT NULL DEFAULT '#9BD',
  "active" INT2 NOT NULL DEFAULT '1',
  "option_reqs" INT2 NOT NULL DEFAULT '0',
  "option_priority" INT2 NOT NULL DEFAULT '0',
  "option_automation" INT2 NOT NULL DEFAULT '0',
  "prefix" varchar(16) NOT NULL,
  "tc_counter" int NOT NULL default '0',
  "is_public" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id"),
  UNIQUE ("prefix")
); 
CREATE INDEX "testprojects_id_active" ON "testprojects" ("id","active");

--
-- Table structure for table `cfield_testprojects`
--

CREATE TABLE "cfield_testprojects" (  
  "field_id" BIGINT NOT NULL DEFAULT '0' REFERENCES custom_fields (id),
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES testprojects (id),
  "display_order" INTEGER NOT NULL default '1',
  "active" INT2 NOT NULL default '1',
  "required_on_design" INT2 NOT NULL default '0',
  "required_on_execution" INT2 NOT NULL default '0',

  PRIMARY KEY ("field_id","testproject_id")
); 


--
-- Table structure for table `cfield_design_values`
--
CREATE TABLE "cfield_design_values" (  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES custom_fields (id),
  "node_id" INTEGER NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "value" VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","node_id")
); 
CREATE INDEX "idx_cfield_design_values" ON "cfield_design_values" ("node_id");


--
-- Table structure for table `cfield_execution_values`
--
CREATE TABLE "cfield_execution_values" (  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES custom_fields (id),
  "execution_id" INTEGER NOT NULL DEFAULT '0' REFERENCES executions (id),
  "testplan_id" INTEGER NOT NULL DEFAULT '0' REFERENCES testplans (id),
  "tcversion_id" INTEGER NOT NULL DEFAULT '0' REFERENCES tcversions (id),
  "value" VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","execution_id","testplan_id","tcversion_id")
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

--
-- Table structure for table `cfield_node_types`
--
CREATE TABLE "cfield_node_types" (  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES custom_fields (id),
  "node_type_id" INTEGER NOT NULL DEFAULT '0' REFERENCES node_types (id),
  PRIMARY KEY ("field_id","node_type_id")
); 
CREATE INDEX "cfield_node_types_idx_custom_fields_assign" ON "cfield_node_types" ("node_type_id");



-- ################################################################################ --
--
-- Table structure for table `assignment_status`
--
CREATE TABLE "assignment_status" (  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table `assignment_types`
--
CREATE TABLE "assignment_types" (  "id" BIGSERIAL NOT NULL ,
  "fk_table" VARCHAR(30) NULL DEFAULT '',
  "description" VARCHAR(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table `attachments`
--
CREATE TABLE "attachments" (  "id" BIGSERIAL NOT NULL ,
  "fk_id" BIGINT NOT NULL DEFAULT '0',
  "fk_table" VARCHAR(250) NULL DEFAULT '',
  "title" VARCHAR(250) NULL DEFAULT '',
  "description" VARCHAR(250) NULL DEFAULT '',
  "file_name" VARCHAR(250) NOT NULL DEFAULT '',
  "file_path" VARCHAR(250) NULL DEFAULT '',
  "file_size" INTEGER NOT NULL DEFAULT '0',
  "file_type" VARCHAR(250) NOT NULL DEFAULT '',
  "date_added" TIMESTAMP NOT NULL DEFAULT now(),
  "content" BYTEA NULL DEFAULT NULL,
  "compression_type" INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY ("id")
); 

--
-- Table structure for table "db_version"
--
CREATE TABLE "db_version" (  
   "version" VARCHAR(50) NOT NULL DEFAULT 'unknown',
   "upgrade_ts" TIMESTAMP NOT NULL DEFAULT now(),
   "notes" TEXT NULL
); 




--
-- Table structure for table "execution_bugs"
--
CREATE TABLE "execution_bugs" (  
  "execution_id" BIGINT NOT NULL DEFAULT '0' REFERENCES executions (id),
  "bug_id" VARCHAR(16) NOT NULL DEFAULT '0',
  PRIMARY KEY ("execution_id","bug_id")
); 


--
-- Table structure for table "keywords"
--
CREATE TABLE "keywords" (  
  "id" BIGSERIAL NOT NULL ,
  "keyword" VARCHAR(100) NOT NULL DEFAULT '',
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES testprojects (id),
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "keywords_testproject_id" ON "keywords" ("testproject_id");
CREATE INDEX "keywords_keyword" ON "keywords" ("keyword");


--
-- Table structure for table "milestones"
--
CREATE TABLE "milestones" (  
  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES testplans (id),
  "target_date" DATE NOT NULL ,
  "a" SMALLINT NOT NULL DEFAULT '0',
  "b" SMALLINT NOT NULL DEFAULT '0',
  "c" SMALLINT NOT NULL DEFAULT '0',
  "name" VARCHAR(100) NOT NULL DEFAULT 'undefined',
  PRIMARY KEY ("id"),
  UNIQUE ("name","testplan_id")

); 
CREATE INDEX "milestones_testplan_id" ON "milestones" ("testplan_id");



--
-- Table structure for table `object_keywords`
--
CREATE TABLE "object_keywords" (  
  "id" BIGSERIAL NOT NULL ,
  "fk_id" BIGINT NOT NULL DEFAULT '0',
  "fk_table" VARCHAR(30) NULL DEFAULT '',
  "keyword_id" BIGINT NOT NULL DEFAULT '0' REFERENCES keywords (id),
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "req_specs"
--
CREATE TABLE "req_specs" (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES testprojects (id),
  "title" VARCHAR(100) NOT NULL DEFAULT '',
  "scope" TEXT NULL DEFAULT NULL,
  "total_req" INTEGER NOT NULL DEFAULT '0',
  "type" CHAR(1) NULL DEFAULT 'N',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "req_specs_testproject_id" ON "req_specs" ("testproject_id");

--
-- Table structure for table "req_suites" - NEW - 
--
CREATE TABLE "req_suites" (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "details" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 



--
-- Table structure for table "requirements"
--
CREATE TABLE "requirements" (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "srs_id" BIGINT NOT NULL DEFAULT '0' REFERENCES req_specs (id),
  "req_doc_id" VARCHAR(32) NULL DEFAULT NULL,
  "title" VARCHAR(100) NOT NULL DEFAULT '',
  "scope" TEXT NULL DEFAULT NULL,
  "status" CHAR(1) NOT NULL DEFAULT 'V',
  "type" CHAR(1) NULL DEFAULT NULL,
  "node_order" BIGINT NOT NULL DEFAULT 0,
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "requirements_srs_id" ON "requirements" ("srs_id","status");
CREATE INDEX "requirements_req_doc_id" ON "requirements" ("srs_id","req_doc_id");


--
-- Table structure for table "req_coverage"
--
CREATE TABLE "req_coverage" (  
  "req_id" INTEGER NOT NULL DEFAULT '0' REFERENCES requirements (id),
  "testcase_id" INTEGER NOT NULL DEFAULT '0'
); 
CREATE INDEX "req_coverage_req_testcase" ON "req_coverage" ("req_id","testcase_id");


--
-- Table structure for table "rights"
--
CREATE TABLE "rights" (  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY ("id"),
  UNIQUE ("description")
); 


--
-- Table structure for table "risk_assignments"
--
CREATE TABLE "risk_assignments" (  
  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES testplans (id),
  "node_id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "risk" CHAR(1) NOT NULL DEFAULT '2',
  "importance" CHAR(1) NOT NULL DEFAULT 'M',
  PRIMARY KEY ("id"),
  UNIQUE ("testplan_id","node_id")
); 


--
-- Table structure for table "role_rights"
--
CREATE TABLE "role_rights" (  
  "role_id" INTEGER NOT NULL DEFAULT '0' REFERENCES roles (id),
  "right_id" INTEGER NOT NULL DEFAULT '0' REFERENCES rights (id),
  PRIMARY KEY ("role_id","right_id")
); 


--
-- Table structure for table "testcase_keywords"
--
CREATE TABLE "testcase_keywords" (  
  "testcase_id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "keyword_id" BIGINT NOT NULL DEFAULT '0' REFERENCES keywords (id),
  PRIMARY KEY ("testcase_id","keyword_id")
); 


--
-- Table structure for table "testsuites"
--
CREATE TABLE "testsuites" (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "details" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "user_assignments"
--
CREATE TABLE "user_assignments" (  
  "id" BIGSERIAL NOT NULL ,
  "type" BIGINT NOT NULL DEFAULT '0',
  "feature_id" BIGINT NOT NULL DEFAULT '0',
  "user_id" BIGINT NULL DEFAULT NULL REFERENCES users (id),
  "deadline_ts" TIMESTAMP NOT NULL DEFAULT (now() + '10 days'::interval),
  "assigner_id" BIGINT NULL DEFAULT NULL REFERENCES users (id),
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "status" INTEGER NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 
CREATE INDEX "feature_id" ON "user_assignments" ("feature_id");


--
-- Table structure for table "user_testplan_roles"
--
CREATE TABLE "user_testplan_roles" (  
  "user_id" INTEGER NOT NULL DEFAULT '0' REFERENCES users (id),
  "testplan_id" INTEGER NOT NULL DEFAULT '0' REFERENCES testplans (id),
  "role_id" INTEGER NOT NULL DEFAULT '0' REFERENCES roles (id),
  PRIMARY KEY ("user_id","testplan_id")
); 


--
-- Table structure for table "user_testproject_roles"
--
CREATE TABLE "user_testproject_roles" (  
  "user_id" INTEGER NOT NULL DEFAULT '0' REFERENCES users (id),
  "testproject_id" INTEGER NOT NULL DEFAULT '0' REFERENCES testprojects (id),
  "role_id" INTEGER NOT NULL DEFAULT '0' REFERENCES roles (id),
  PRIMARY KEY ("user_id","testproject_id")
); 

--
CREATE TABLE "text_templates" (
  id BIGSERIAL NOT NULL,
  type INT NOT NULL,
  title varchar(100) NOT NULL,
  template_data text,
  author_id BIGINT default NULL REFERENCES users (id),
  create_ts TIMESTAMP NOT NULL default now(),
  is_public INT2 NOT NULL default '0',
  PRIMARY KEY ("id"),
  UNIQUE (type,title)
);
COMMENT ON TABLE text_templates IS 'Global Project Templates';


--
CREATE TABLE "user_group" (
  id BIGSERIAL NOT NULL,
  title varchar(100) NOT NULL,
  description text,
  owner_id BIGINT NOT NULL REFERENCES users (id),
  testproject_id BIGINT NOT NULL REFERENCES testprojects (id),
  PRIMARY KEY ("id"),
  UNIQUE (title)
);

--
CREATE TABLE "user_group_assign" (
  usergroup_id BIGINT NOT NULL REFERENCES user_group (id),
  user_id BIGINT NOT NULL REFERENCES users (id)
);