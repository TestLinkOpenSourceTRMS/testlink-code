-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- $Id: testlink_create_tables.sql,v 1.43 2009/11/19 13:50:13 franciscom Exp $
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
--      20091119 - franciscom - req_specs added doc_id field
--      20091010 - franciscom - added testplan_platforms,platforms
--                              platform_id to tables
--      20090910 - franciscom - tcversions.preconditions
--                              milestones.start_date
--      20090717 - franciscom - added cfield_testprojects.location field
--      20090611 - franciscom - builds.closed_on_date 
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
CREATE TABLE /*prefix*/node_types (  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT 'testproject',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "nodes_hierarchy"
--
CREATE TABLE /*prefix*/nodes_hierarchy(  
  "id" BIGSERIAL NOT NULL ,
  "name" VARCHAR(100) NULL DEFAULT NULL,
  "parent_id" BIGINT NULL DEFAULT NULL,
  "node_type_id" BIGINT NOT NULL DEFAULT '1' REFERENCES  /*prefix*/node_types (id),
  "node_order" BIGINT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX /*prefix*/nodes_hierarchy_pid_m_nodeorder ON /*prefix*/nodes_hierarchy ("parent_id","node_order");

--
--
--
CREATE TABLE /*prefix*/transactions(
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
CREATE TABLE /*prefix*/events(
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
CREATE INDEX /*prefix*/events_transaction_id ON /*prefix*/events ("transaction_id");
CREATE INDEX /*prefix*/events_fired_at ON /*prefix*/events ("fired_at");


--
-- Table structure for table "roles"
--
CREATE TABLE /*prefix*/roles(  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT '',
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id"),
  UNIQUE ("description")
); 


--
-- Table structure for table "users"
--
CREATE TABLE /*prefix*/users(  
  "id" BIGSERIAL NOT NULL ,
  "login" VARCHAR(30) NOT NULL DEFAULT '',
  "password" VARCHAR(32) NOT NULL DEFAULT '',
  "role_id" SMALLINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/roles (id),
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
CREATE TABLE /*prefix*/tcversions(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "tc_external_id" INT NULL,
  "version" INTEGER NOT NULL DEFAULT '1',
  "summary" TEXT NULL DEFAULT NULL,
  "preconditions" TEXT NULL DEFAULT NULL,
  "steps" TEXT NULL DEFAULT NULL,
  "expected_results" TEXT NULL DEFAULT NULL,
  "importance" INT2 NOT NULL DEFAULT '2',
  "author_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "updater_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  "modification_ts" TIMESTAMP NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "is_open" INT2 NOT NULL DEFAULT '1',
  "execution_type" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "testplans"
--
CREATE TABLE /*prefix*/testplans(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "testproject_id" BIGINT NOT NULL DEFAULT '0',
  "notes" TEXT NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "is_open" INT2 NOT NULL DEFAULT '1',
  "is_public" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 
CREATE INDEX /*prefix*/testplans_testproject_id_active ON /*prefix*/testplans ("testproject_id","active");


--
-- Table structure for table `builds`
--
CREATE TABLE /*prefix*/builds(  
  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  "name" VARCHAR(100) NOT NULL DEFAULT 'undefined',
  "notes" TEXT NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "is_open" INT2 NOT NULL DEFAULT '1',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "release_date" DATE NULL,
  "closed_on_date" DATE NULL,
  PRIMARY KEY ("id"),
  UNIQUE ("testplan_id","name")
); 
CREATE INDEX /*prefix*/builds_testplan_id ON /*prefix*/builds ("testplan_id");

--
-- Table structure for table "executions"
--
CREATE TABLE /*prefix*/executions(  
  "id" BIGSERIAL NOT NULL ,
  "build_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/builds (id),
  "tester_id" BIGINT NULL DEFAULT NULL,
  "execution_ts" TIMESTAMP NULL,
  "status" CHAR(1) NULL DEFAULT NULL,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  "tcversion_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcversions (id),
  "tcversion_number" INTEGER NOT NULL DEFAULT '1',
  "platform_id" BIGINT NOT NULL DEFAULT '0',
  "execution_type" INT2 NOT NULL DEFAULT '1',
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX /*prefix*/executions_idx1 ON /*prefix*/executions ("testplan_id","tcversion_id");
CREATE INDEX /*prefix*/executions_idx2 ON /*prefix*/executions ("execution_type");

--
-- Table structure for table "testplan_tcversions"
--
CREATE TABLE /*prefix*/testplan_tcversions(  
  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  "tcversion_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcversions (id),  
  "platform_id" BIGINT NOT NULL DEFAULT '0',
  "node_order" BIGINT NOT NULL DEFAULT 1,
  "urgency" INT2 NOT NULL DEFAULT '2',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  PRIMARY KEY ("id"),
  UNIQUE ("testplan_id","tcversion_id","platform_id")
); 


--
-- Table structure for table "custom_fields"
--
CREATE TABLE /*prefix*/custom_fields(  
  "id" SERIAL NOT NULL ,
  "name" VARCHAR(64) NOT NULL DEFAULT '',
  "label" VARCHAR(64) NOT NULL DEFAULT '',
  "type" SMALLINT NOT NULL DEFAULT '0',
  "possible_values" VARCHAR(4000) NOT NULL DEFAULT '',
  "default_value" VARCHAR(4000) NOT NULL DEFAULT '',
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
CREATE INDEX /*prefix*/custom_fields_idx_custom_fields_name ON /*prefix*/custom_fields ("name");

--
-- Table structure for table "testprojects"
--
CREATE TABLE /*prefix*/testprojects(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
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
CREATE INDEX /*prefix*/testprojects_id_active ON /*prefix*/testprojects ("id","active");

--
-- Table structure for table `cfield_testprojects`
--

CREATE TABLE /*prefix*/cfield_testprojects(  
  "field_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id),
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id),
  "display_order" INTEGER NOT NULL default '1',
  "active" INT2 NOT NULL default '1',
  "location" INT2 NOT NULL default '1',
  "required_on_design" INT2 NOT NULL default '0',
  "required_on_execution" INT2 NOT NULL default '0',

  PRIMARY KEY ("field_id","testproject_id")
); 


--
-- Table structure for table `cfield_design_values`
--
CREATE TABLE /*prefix*/cfield_design_values(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id),
  "node_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "value" VARCHAR(4000) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","node_id")
); 
CREATE INDEX /*prefix*/IX_cfield_design_values ON /*prefix*/cfield_design_values ("node_id");


--
-- Table structure for table `cfield_execution_values`
--
CREATE TABLE /*prefix*/cfield_execution_values(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id),
  "execution_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/executions (id),
  "testplan_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  "tcversion_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcversions (id),
  "value" VARCHAR(4000) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","execution_id","testplan_id","tcversion_id")
); 

--
-- Table structure for table cfield_testplan_design_values
--
CREATE TABLE /*prefix*/cfield_testplan_design_values(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id),
  "link_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplan_tcversions (id),
  "value" VARCHAR(4000) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","link_id")
); 
CREATE INDEX /*prefix*/IX_cfield_tplan_design_val ON /*prefix*/cfield_testplan_design_values ("link_id");

--
-- Table structure for table `cfield_node_types`
--
CREATE TABLE /*prefix*/cfield_node_types(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id),
  "node_type_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/node_types (id),
  PRIMARY KEY ("field_id","node_type_id")
); 
CREATE INDEX /*prefix*/cfield_node_types_idx_custom_fields_assign ON /*prefix*/cfield_node_types ("node_type_id");



-- ################################################################################ --
--
-- Table structure for table `assignment_status`
--
CREATE TABLE /*prefix*/assignment_status(  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table `assignment_types`
--
CREATE TABLE /*prefix*/assignment_types(  
  "id" BIGSERIAL NOT NULL ,
  "fk_table" VARCHAR(30) NULL DEFAULT '',
  "description" VARCHAR(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table `attachments`
--
CREATE TABLE /*prefix*/attachments(  "id" BIGSERIAL NOT NULL ,
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
CREATE TABLE /*prefix*/db_version(  
   "version" VARCHAR(50) NOT NULL DEFAULT 'unknown',
   "upgrade_ts" TIMESTAMP NOT NULL DEFAULT now(),
   "notes" TEXT NULL
); 




--
-- Table structure for table "execution_bugs"
--
CREATE TABLE /*prefix*/execution_bugs(  
  "execution_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/executions (id),
  "bug_id" VARCHAR(16) NOT NULL DEFAULT '0',
  PRIMARY KEY ("execution_id","bug_id")
); 


--
-- Table structure for table "keywords"
--
CREATE TABLE /*prefix*/keywords(  
  "id" BIGSERIAL NOT NULL ,
  "keyword" VARCHAR(100) NOT NULL DEFAULT '',
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id),
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX /*prefix*/keywords_testproject_id ON /*prefix*/keywords ("testproject_id");
CREATE INDEX /*prefix*/keywords_keyword ON /*prefix*/keywords ("keyword");


--
-- Table structure for table "milestones"
--
CREATE TABLE /*prefix*/milestones(  
  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  "target_date" DATE NOT NULL ,
  "start_date" DATE NULL ,
  "a" SMALLINT NOT NULL DEFAULT '0',
  "b" SMALLINT NOT NULL DEFAULT '0',
  "c" SMALLINT NOT NULL DEFAULT '0',
  "name" VARCHAR(100) NOT NULL DEFAULT 'undefined',
  PRIMARY KEY ("id"),
  UNIQUE ("name","testplan_id")

); 
CREATE INDEX /*prefix*/milestones_testplan_id ON /*prefix*/milestones ("testplan_id");



--
-- Table structure for table `object_keywords`
--
CREATE TABLE /*prefix*/object_keywords(  
  "id" BIGSERIAL NOT NULL ,
  "fk_id" BIGINT NOT NULL DEFAULT '0',
  "fk_table" VARCHAR(30) NULL DEFAULT '',
  "keyword_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/keywords (id),
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "req_specs"
--
CREATE TABLE /*prefix*/req_specs(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id),  
  "doc_id" VARCHAR(32) NULL DEFAULT NULL,
  "title" VARCHAR(100) NOT NULL DEFAULT '',
  "scope" TEXT NULL DEFAULT NULL,
  "total_req" INTEGER NOT NULL DEFAULT '0',
  "type" CHAR(1) NULL DEFAULT 'N',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  PRIMARY KEY ("id"),
  UNIQUE ("doc_id","testproject_id")
); 
CREATE INDEX /*prefix*/req_specs_testproject_id ON /*prefix*/req_specs ("testproject_id");

--
-- Table structure for table "req_suites" - NEW - 
--
CREATE TABLE /*prefix*/req_suites(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "details" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 



--
-- Table structure for table "requirements"
--
CREATE TABLE /*prefix*/requirements(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "srs_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/req_specs (id),
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
CREATE INDEX /*prefix*/requirements_srs_id ON /*prefix*/requirements("srs_id","status");
CREATE INDEX /*prefix*/requirements_req_doc_id ON /*prefix*/requirements ("srs_id","req_doc_id");


--
-- Table structure for table "req_coverage"
--
CREATE TABLE /*prefix*/req_coverage(  
  "req_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/requirements (id),
  "testcase_id" INTEGER NOT NULL DEFAULT '0'
); 
CREATE INDEX /*prefix*/req_coverage_req_testcase ON /*prefix*/req_coverage ("req_id","testcase_id");


--
-- Table structure for table "rights"
--
CREATE TABLE /*prefix*/rights(  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY ("id"),
  UNIQUE ("description")
); 


--
-- Table structure for table "risk_assignments"
--
CREATE TABLE /*prefix*/risk_assignments(  
  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  "node_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "risk" CHAR(1) NOT NULL DEFAULT '2',
  "importance" CHAR(1) NOT NULL DEFAULT 'M',
  PRIMARY KEY ("id"),
  UNIQUE ("testplan_id","node_id")
); 


--
-- Table structure for table "role_rights"
--
CREATE TABLE /*prefix*/role_rights(  
  "role_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/roles (id),
  "right_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/rights (id),
  PRIMARY KEY ("role_id","right_id")
); 


--
-- Table structure for table "testcase_keywords"
--
CREATE TABLE /*prefix*/testcase_keywords(  
  "testcase_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "keyword_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/keywords (id),
  PRIMARY KEY ("testcase_id","keyword_id")
); 


--
-- Table structure for table "testsuites"
--
CREATE TABLE /*prefix*/testsuites(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "details" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "user_assignments"
--
CREATE TABLE /*prefix*/user_assignments(  
  "id" BIGSERIAL NOT NULL ,
  "type" BIGINT NOT NULL DEFAULT '0',
  "feature_id" BIGINT NOT NULL DEFAULT '0',
  "user_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  "deadline_ts" TIMESTAMP NOT NULL DEFAULT (now() + '10 days'::interval),
  "assigner_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "status" INTEGER NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 
CREATE INDEX /*prefix*/user_assignments_feature_id ON /*prefix*/user_assignments ("feature_id");


--
-- Table structure for table "user_testplan_roles"
--
CREATE TABLE /*prefix*/user_testplan_roles(  
  "user_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/users (id),
  "testplan_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  "role_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/roles (id),
  PRIMARY KEY ("user_id","testplan_id")
); 


--
-- Table structure for table "user_testproject_roles"
--
CREATE TABLE /*prefix*/user_testproject_roles(  
  "user_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/users (id),
  "testproject_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id),
  "role_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/roles (id),
  PRIMARY KEY ("user_id","testproject_id")
); 

--
CREATE TABLE /*prefix*/text_templates(
  id BIGSERIAL NOT NULL,
  type INT NOT NULL,
  title varchar(100) NOT NULL,
  template_data text,
  author_id BIGINT default NULL REFERENCES  /*prefix*/users (id),
  creation_ts TIMESTAMP NOT NULL default now(),
  is_public INT2 NOT NULL default '0',
  PRIMARY KEY ("id"),
  UNIQUE (type,title)
);
COMMENT ON TABLE /*prefix*/text_templates IS 'Global Project Templates';


--
CREATE TABLE /*prefix*/user_group(
  id BIGSERIAL NOT NULL,
  title varchar(100) NOT NULL,
  description text,
  owner_id BIGINT NOT NULL REFERENCES  /*prefix*/users (id),
  testproject_id BIGINT NOT NULL REFERENCES  /*prefix*/testprojects (id),
  PRIMARY KEY ("id"),
  UNIQUE (title)
);

--
CREATE TABLE /*prefix*/user_group_assign(
  usergroup_id BIGINT NOT NULL REFERENCES  /*prefix*/user_group (id),
  user_id BIGINT NOT NULL REFERENCES  /*prefix*/users (id)
);


CREATE TABLE /*prefix*/platforms (
  id BIGSERIAL NOT NULL,
  name VARCHAR(100) NOT NULL,
  testproject_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id),
  notes text NOT NULL,
  PRIMARY KEY (id),
  UNIQUE (testproject_id,name)
);

CREATE TABLE /*prefix*/testplan_platforms (
  id BIGSERIAL NOT NULL,
  testplan_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  platform_id BIGINT NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE (testplan_id,platform_id)
);
