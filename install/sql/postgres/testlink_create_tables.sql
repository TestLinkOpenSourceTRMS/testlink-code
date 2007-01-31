-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- $Id: testlink_create_tables.sql,v 1.9 2007/01/31 14:15:20 franciscom Exp $
--
-- SQL script - create db tables for TL on Postgres   
-- 
--
-- 
-- Rev :
--       20070131 - franciscom - requirements -> req_doc_id(32), 
--
--       20070120 - franciscom - following BUGID 458 ( really a new feature request)
--                               two new fields on builds table
--                               active, open
--
--       20070117 - franciscom - create_ts -> creation_ts
--
--       20070116 - franciscom - fixed BUGID 545
--
--       20070113 - franciscom - table cfield_testprojects added fields
--                               required_on_design,required_on_execution
--       20060515 - franciscom - creation
--

--
-- Table structure for table `assignment_status`
--
CREATE TABLE "assignment_status" (  "id" BIGSERIAL NOT NULL ,
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
-- Table structure for table `builds`
--
CREATE TABLE "builds" (  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0',
  "name" VARCHAR(100) NOT NULL DEFAULT 'undefined',
  "notes" TEXT NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "open" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id"),
  UNIQUE ("testplan_id","name")
); 
CREATE INDEX "builds_testplan_id" ON "builds" ("testplan_id");


--
-- Table structure for table `cfield_design_values`
--
CREATE TABLE "cfield_design_values" (  "field_id" INTEGER NOT NULL DEFAULT '0',
  "node_id" INTEGER NOT NULL DEFAULT '0',
  "value" VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","node_id")
); 
CREATE INDEX "idx_cfield_design_values" ON "cfield_design_values" ("node_id");


--
-- Table structure for table `cfield_execution_values`
--
CREATE TABLE "cfield_execution_values" (  "field_id" INTEGER NOT NULL DEFAULT '0',
  "execution_id" INTEGER NOT NULL DEFAULT '0',
  "testplan_id" INTEGER NOT NULL DEFAULT '0',
  "tcversion_id" INTEGER NOT NULL DEFAULT '0',
  "value" VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","execution_id","testplan_id","tcversion_id")
); 


--
-- Table structure for table `cfield_node_types`
--
CREATE TABLE "cfield_node_types" (  "field_id" INTEGER NOT NULL DEFAULT '0',
  "node_type_id" INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY ("field_id","node_type_id")
); 
CREATE INDEX "cfield_node_types_idx_custom_fields_assign" ON "cfield_node_types" ("node_type_id");


--
-- Table structure for table `cfield_testprojects`
--
CREATE TABLE "cfield_testprojects" (  "field_id" BIGINT NOT NULL DEFAULT '0',
  "testproject_id" BIGINT NOT NULL DEFAULT '0',
  "display_order" INTEGER NOT NULL default '1',
  "active" INT2 NOT NULL default '1',
  "required_on_design" INT2 NOT NULL default '0',
  "required_on_execution" INT2 NOT NULL default '0',

  PRIMARY KEY ("field_id","testproject_id")
); 


--
-- Table structure for table "custom_fields"
--
CREATE TABLE "custom_fields" (  "id" SERIAL NOT NULL ,
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
  PRIMARY KEY ("id")
); 
CREATE INDEX "custom_fields_idx_custom_fields_name" ON "custom_fields" ("name");


--
-- Table structure for table "db_version"
--
CREATE TABLE "db_version" (  "version" VARCHAR(50) NOT NULL DEFAULT 'unknown',
  "upgrade_ts" TIMESTAMP NOT NULL DEFAULT now() 
); 




--
-- Table structure for table "execution_bugs"
--
CREATE TABLE "execution_bugs" (  "execution_id" BIGINT NOT NULL DEFAULT '0',
  "bug_id" VARCHAR(16) NOT NULL DEFAULT '0',
  PRIMARY KEY ("execution_id","bug_id")
); 


--
-- Table structure for table "executions"
--
CREATE TABLE "executions" (  "id" BIGSERIAL NOT NULL ,
  "build_id" INTEGER NOT NULL DEFAULT '0',
  "tester_id" BIGINT NULL DEFAULT NULL,
  "execution_ts" TIMESTAMP NULL,
  "status" CHAR(1) NULL DEFAULT NULL,
  "testplan_id" BIGINT NOT NULL DEFAULT '0',
  "tcversion_id" BIGINT NOT NULL DEFAULT '0',
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "keywords"
--
CREATE TABLE "keywords" (  "id" BIGSERIAL NOT NULL ,
  "keyword" VARCHAR(100) NOT NULL DEFAULT '',
  "testproject_id" BIGINT NOT NULL DEFAULT '0',
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "keywords_testproject_id" ON "keywords" ("testproject_id");
CREATE INDEX "keywords_keyword" ON "keywords" ("keyword");


--
-- Table structure for table "milestones"
--
CREATE TABLE "milestones" (  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0',
  "date" DATE NOT NULL ,
  "a" SMALLINT NOT NULL DEFAULT '0',
  "b" SMALLINT NOT NULL DEFAULT '0',
  "c" SMALLINT NOT NULL DEFAULT '0',
  "name" VARCHAR(100) NOT NULL DEFAULT 'undefined',
  PRIMARY KEY ("id")
); 
CREATE INDEX "milestones_testplan_id" ON "milestones" ("testplan_id");


--
-- Table structure for table "node_types"
--
CREATE TABLE "node_types" (  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT 'testproject',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "nodes_hierarchy"
--
CREATE TABLE "nodes_hierarchy" (  "id" BIGSERIAL NOT NULL ,
  "name" VARCHAR(100) NULL DEFAULT NULL,
  "parent_id" BIGINT NULL DEFAULT NULL,
  "node_type_id" BIGINT NOT NULL DEFAULT '1',
  "node_order" BIGINT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "nodes_hierarchy_pid_m_nodeorder" ON "nodes_hierarchy" ("parent_id","node_order");

--
-- Table structure for table `object_keywords`
--


CREATE TABLE "object_keywords" (  "id" BIGINT NOT NULL ,
  "fk_id" BIGINT NOT NULL DEFAULT '0',
  "fk_table" VARCHAR(30) NULL DEFAULT '',
  "keyword_id" BIGINT NOT NULL DEFAULT '0',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "priorities"
--
CREATE TABLE "priorities" (  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0',
  "risk_importance" CHAR(2) NOT NULL DEFAULT '',
  "priority" CHAR(1) NOT NULL DEFAULT 'b',
  PRIMARY KEY ("id")
); 
CREATE INDEX "priorities_testplan_id" ON "priorities" ("testplan_id");


--
-- Table structure for table "req_coverage"
--
CREATE TABLE "req_coverage" (  "req_id" INTEGER NOT NULL DEFAULT '0',
  "testcase_id" INTEGER NOT NULL DEFAULT '0'
); 
CREATE INDEX "req_coverage_req_testcase" ON "req_coverage" ("req_id","testcase_id");


--
-- Table structure for table "req_specs"
--
CREATE TABLE "req_specs" (  "id" BIGSERIAL NOT NULL ,
  "testproject_id" BIGINT NOT NULL DEFAULT '0',
  "title" VARCHAR(100) NOT NULL DEFAULT '',
  "scope" TEXT NULL DEFAULT NULL,
  "total_req" INTEGER NOT NULL DEFAULT '0',
  "type" CHAR(1) NULL DEFAULT 'n',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "req_specs_testproject_id" ON "req_specs" ("testproject_id");


--
-- Table structure for table "requirements"
--
CREATE TABLE "requirements" (  "id" BIGSERIAL NOT NULL ,
  "srs_id" BIGINT NOT NULL DEFAULT '0',
  "req_doc_id" VARCHAR(32) NULL DEFAULT NULL,
  "title" VARCHAR(100) NOT NULL DEFAULT '',
  "scope" TEXT NULL DEFAULT NULL,
  "status" CHAR(1) NOT NULL DEFAULT 'v',
  "type" CHAR(1) NULL DEFAULT NULL,
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX "requirements_srs_id" ON "requirements" ("srs_id","status");
CREATE INDEX "requirements_req_doc_id" ON "requirements" ("srs_id","req_doc_id");


--
-- Table structure for table "rights"
--
CREATE TABLE "rights" (  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY ("id"),
  UNIQUE ("description")
); 


--
-- Table structure for table "risk_assignments"
--
CREATE TABLE "risk_assignments" (  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0',
  "node_id" BIGINT NOT NULL DEFAULT '0',
  "risk" INTEGER NOT NULL DEFAULT '2',
  "importance" CHAR(1) NOT NULL DEFAULT 'M',
  PRIMARY KEY ("id"),
  UNIQUE ("testplan_id","node_id")
); 


--
-- Table structure for table "role_rights"
--
CREATE TABLE "role_rights" (  "role_id" INTEGER NOT NULL DEFAULT '0',
  "right_id" INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY ("role_id","right_id")
); 




--
-- Table structure for table "roles"
--
CREATE TABLE "roles" (  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT '',
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id"),
  UNIQUE ("description")
); 


--
-- Table structure for table "tcversions"
--
CREATE TABLE "tcversions" (  "id" BIGINT NOT NULL DEFAULT '0',
  "version" INTEGER NOT NULL DEFAULT '1',
  "summary" TEXT NULL DEFAULT NULL,
  "steps" TEXT NULL DEFAULT NULL,
  "expected_results" TEXT NULL DEFAULT NULL,
  "importance" CHAR(1) NOT NULL DEFAULT 'M',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "updater_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "open" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 



--
-- Table structure for table "testcase_keywords"
--
CREATE TABLE "testcase_keywords" (  "testcase_id" BIGINT NOT NULL DEFAULT '0',
  "keyword_id" BIGINT NOT NULL DEFAULT '0',
  PRIMARY KEY ("testcase_id","keyword_id")
); 


--
-- Table structure for table "testplan_tcversions"
--
CREATE TABLE "testplan_tcversions" (  "id" BIGSERIAL NOT NULL ,
 "testplan_id" BIGINT NOT NULL DEFAULT '0',
 "tcversion_id" BIGINT NOT NULL DEFAULT '0',  
  PRIMARY KEY ("id"),
  UNIQUE ("testplan_id","tcversion_id")
); 


--
-- Table structure for table "testplans"
--
CREATE TABLE "testplans" (  "id" BIGINT NOT NULL DEFAULT '0',
  "testproject_id" BIGINT NOT NULL DEFAULT '0',
  "notes" TEXT NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "open" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 
CREATE INDEX "testplans_testproject_id_active" ON "testplans" ("testproject_id","active");



--
-- Table structure for table "testprojects"
--
CREATE TABLE "testprojects" (  "id" BIGINT NOT NULL DEFAULT '0',
  "notes" TEXT NULL DEFAULT NULL,
  "color" VARCHAR(12) NOT NULL DEFAULT '#9BD',
  "active" INT2 NOT NULL DEFAULT '1',
  "option_reqs" INT2 NOT NULL DEFAULT '0',
  "option_priority" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 
CREATE INDEX "testprojects_id_active" ON "testprojects" ("id","active");


--
-- Table structure for table "testsuites"
--
CREATE TABLE "testsuites" (  "id" BIGINT NOT NULL DEFAULT '0',
  "details" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "user_assignments"
--
-- 20070117 - franciscom
-- 20070116 - franciscom
CREATE TABLE "user_assignments" (  "id" BIGSERIAL NOT NULL ,
  "type" BIGINT NOT NULL DEFAULT '0',
  "feature_id" BIGINT NOT NULL DEFAULT '0',
  "user_id" BIGINT NULL DEFAULT NULL,
  "deadline_ts" TIMESTAMP NOT NULL DEFAULT (now() + '10 days'::interval),
  "assigner_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "status" INTEGER NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 
CREATE INDEX feature_id ON user_assignments ("feature_id");





--
-- Table structure for table "user_testplan_roles"
--
CREATE TABLE "user_testplan_roles" (  "user_id" INTEGER NOT NULL DEFAULT '0',
  "testplan_id" INTEGER NOT NULL DEFAULT '0',
  "role_id" INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY ("user_id","testplan_id")
); 


--
-- Table structure for table "user_testproject_roles"
--
CREATE TABLE "user_testproject_roles" (  "user_id" INTEGER NOT NULL DEFAULT '0',
  "testproject_id" INTEGER NOT NULL DEFAULT '0',
  "role_id" INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY ("user_id","testproject_id")
); 


--
-- Table structure for table "users"
--
CREATE TABLE "users" (  "id" BIGSERIAL NOT NULL ,
  "login" VARCHAR(30) NOT NULL DEFAULT '',
  "password" VARCHAR(32) NOT NULL DEFAULT '',
  "role_id" SMALLINT NOT NULL DEFAULT '0',
  "email" VARCHAR(100) NOT NULL DEFAULT '',
  "first" VARCHAR(30) NOT NULL DEFAULT '',
  "last" VARCHAR(30) NOT NULL DEFAULT '',
  "locale" VARCHAR(10) NOT NULL DEFAULT 'en_GB',
  "default_testproject_id" INTEGER NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id"),
  UNIQUE ("login")
);
