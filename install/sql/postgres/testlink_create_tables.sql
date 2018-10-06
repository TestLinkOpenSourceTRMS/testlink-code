-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- testlink_create_tables.sql
--
-- SQL script - create db tables for TL on Postgres   
-- 
-- IMPORTANT NOTE:
-- each NEW TABLE added here NEED TO BE DEFINED in object.class.php getDBTables()
--
-- ATTENTION: do not use a different naming convention, that one already in use.
-- 
-- Naming convention for column regarding date/time of creation or change
-- Right or wrong from TL 1.7 we have used
-- creation_ts, modification_ts
--
-- Then no other naming convention has to be used as: create_ts, modified_ts
--
--
-- CRITIC:
--        Because this file will be processed during installation doing text replaces
--        to add TABLE PREFIX NAME, any NEW DDL CODE added must be respect present
--        convention regarding case and spaces between DDL keywords.
--
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
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/roles_uidx1 ON /*prefix*/roles ("description");


--
-- Table structure for table "users"
--
--
CREATE TABLE /*prefix*/users(  
  "id" BIGSERIAL NOT NULL ,
  "login" VARCHAR(100) NOT NULL DEFAULT '',
  "password" VARCHAR(32) NOT NULL DEFAULT '',
  "role_id" SMALLINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/roles (id),
  "email" VARCHAR(100) NOT NULL DEFAULT '',
  "first" VARCHAR(50) NOT NULL DEFAULT '',
  "last" VARCHAR(50) NOT NULL DEFAULT '',
  "locale" VARCHAR(10) NOT NULL DEFAULT 'en_GB',
  "default_testproject_id" INTEGER NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "script_key" VARCHAR(32) NULL,
  "cookie_string" varchar(64) NOT NULL DEFAULT '', 
  "auth_method" VARCHAR(10) NULL DEFAULT '',
  "creation_ts" timestamp NOT NULL DEFAULT now(),
  "expiration_date" date DEFAULT NULL,
  PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX /*prefix*/users_uidx1 ON /*prefix*/users ("login");
CREATE UNIQUE INDEX /*prefix*/users_uidx2 ON /*prefix*/users ("cookie_string");

--
-- Table structure for table "tcversions"
--
CREATE TABLE /*prefix*/tcversions(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "tc_external_id" INT NULL,
  "version" INTEGER NOT NULL DEFAULT '1',
  "layout" INTEGER NOT NULL DEFAULT '1',
  "status" INTEGER NOT NULL DEFAULT '1',
  "summary" TEXT NULL DEFAULT NULL,
  "preconditions" TEXT NULL DEFAULT NULL,
  "importance" INT2 NOT NULL DEFAULT '2',
  "author_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "updater_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  "modification_ts" TIMESTAMP NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "is_open" INT2 NOT NULL DEFAULT '1',
  "execution_type" INT2 NOT NULL DEFAULT '1',
  "estimated_exec_duration" numeric(6,2) NULL,
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "tcsteps"
--
CREATE TABLE /*prefix*/tcsteps (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES /*prefix*/nodes_hierarchy (id),
  "step_number" INT NOT NULL DEFAULT '1',
  "actions" TEXT NULL DEFAULT NULL,
  "expected_results" TEXT NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
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
  "api_key" varchar(64) NOT NULL DEFAULT (MD5(RANDOM()::text) || MD5(RANDOM()::text)),
  PRIMARY KEY ("id")
); 
CREATE INDEX /*prefix*/testplans_testproject_id_active ON /*prefix*/testplans ("testproject_id","active");
CREATE UNIQUE INDEX /*prefix*/testplans_uidx1 ON /*prefix*/testplans ("api_key");


--
-- Table structure for table builds
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
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/builds_uidx1 ON /*prefix*/builds  ("testplan_id","name");
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
  "execution_duration" numeric(6,2) NULL,
  "notes" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE INDEX /*prefix*/executions_idx1 ON /*prefix*/executions ("testplan_id","tcversion_id","platform_id","build_id");
CREATE INDEX /*prefix*/executions_idx2 ON /*prefix*/executions ("execution_type");
CREATE INDEX /*prefix*/executions_idx3 ON /*prefix*/executions ("tcversion_id");

--
-- Table structure for table "execution_tcsteps"
--
CREATE TABLE /*prefix*/execution_tcsteps (
   "id" BIGSERIAL NOT NULL ,
   "execution_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/executions (id),
   "tcstep_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcsteps (id),
   "notes" TEXT NULL DEFAULT NULL,
   "status" CHAR(1) NULL DEFAULT NULL,
  PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX /*prefix*/execution_tcsteps_uidx1 ON  /*prefix*/execution_tcsteps ("execution_id","tcstep_id");

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
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/testplan_tcversions_uidx1 ON  /*prefix*/testplan_tcversions ("testplan_id","tcversion_id","platform_id");


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
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/custom_fields_uidx1 ON /*prefix*/custom_fields ("name");

--
-- Table structure for table "testprojects"
--
CREATE TABLE /*prefix*/testprojects(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id) ON DELETE CASCADE,
  "notes" TEXT NULL DEFAULT NULL,
  "color" VARCHAR(12) NOT NULL DEFAULT '#9BD',
  "active" INT2 NOT NULL DEFAULT '1',
  "option_reqs" INT2 NOT NULL DEFAULT '0',
  "option_priority" INT2 NOT NULL DEFAULT '0',
  "option_automation" INT2 NOT NULL DEFAULT '0',
  "options" TEXT,
  "prefix" varchar(16) NOT NULL,
  "tc_counter" int NOT NULL default '0',
  "is_public" INT2 NOT NULL DEFAULT '1',
  "issue_tracker_enabled" INT2 NOT NULL DEFAULT '0',
  "code_tracker_enabled" INT2 NOT NULL DEFAULT '0',
  "reqmgr_integration_enabled" INT2 NOT NULL DEFAULT '0',
  "api_key" varchar(64) NOT NULL DEFAULT (MD5(RANDOM()::text) || MD5(RANDOM()::text)),
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/testprojects_uidx1 ON /*prefix*/testprojects ("prefix");
CREATE UNIQUE INDEX /*prefix*/testprojects_uidx2 ON /*prefix*/testprojects ("api_key");
CREATE INDEX /*prefix*/testprojects_id_active ON /*prefix*/testprojects ("id","active");

--
-- Table structure for table cfield_testprojects
--

CREATE TABLE /*prefix*/cfield_testprojects(  
  "field_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id) ON DELETE CASCADE,
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  "display_order" INTEGER NOT NULL default '1',
  "active" INT2 NOT NULL default '1',
  "location" INT2 NOT NULL default '1',
  "required" INT2 NOT NULL default '0',
  "required_on_design" INT2 NOT NULL default '0',
  "required_on_execution" INT2 NOT NULL default '0',
  "monitorable" INT2 NOT NULL default '0',

  PRIMARY KEY ("field_id","testproject_id")
); 


--
-- Table structure for table cfield_design_values
--
CREATE TABLE /*prefix*/cfield_design_values(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id) ON DELETE CASCADE,
  "node_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id) ON DELETE CASCADE,
  "value" VARCHAR(4000) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","node_id")
); 
CREATE INDEX /*prefix*/IX_cfield_design_values ON /*prefix*/cfield_design_values ("node_id");


--
-- Table structure for table cfield_execution_values
--
CREATE TABLE /*prefix*/cfield_execution_values(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id) ON DELETE CASCADE,
  "execution_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/executions (id) ON DELETE CASCADE,
  "testplan_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id) ON DELETE CASCADE,
  "tcversion_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcversions (id) ON DELETE CASCADE,
  "value" VARCHAR(4000) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","execution_id","testplan_id","tcversion_id")
); 

--
-- Table structure for table cfield_testplan_design_values
--
CREATE TABLE /*prefix*/cfield_testplan_design_values(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id) ON DELETE CASCADE,
  "link_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplan_tcversions (id) ON DELETE CASCADE,
  "value" VARCHAR(4000) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","link_id")
); 
CREATE INDEX /*prefix*/IX_cfield_tplan_design_val ON /*prefix*/cfield_testplan_design_values ("link_id");

--
-- Table structure for table cfield_node_types
--
CREATE TABLE /*prefix*/cfield_node_types(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id) ON DELETE CASCADE,
  "node_type_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/node_types (id) ON DELETE CASCADE,
  PRIMARY KEY ("field_id","node_type_id")
); 
CREATE INDEX /*prefix*/cfield_node_types_idx_custom_fields_assign ON /*prefix*/cfield_node_types ("node_type_id");

--
-- Table structure for table assignment_status
--
CREATE TABLE /*prefix*/assignment_status(  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table assignment_types
--
CREATE TABLE /*prefix*/assignment_types(  
  "id" BIGSERIAL NOT NULL ,
  "fk_table" VARCHAR(30) NULL DEFAULT '',
  "description" VARCHAR(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY ("id")
); 


--
-- Table structure for table attachments
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
CREATE INDEX /*prefix*/attachments_idx1 ON /*prefix*/attachments ("fk_id");

--
-- Table structure for table "db_version"
--
CREATE TABLE /*prefix*/db_version(  
   "version" VARCHAR(50) NOT NULL DEFAULT 'unknown',
   "upgrade_ts" TIMESTAMP NOT NULL DEFAULT now(),
   "notes" TEXT NULL,
   PRIMARY KEY ("version")
); 


--
-- Table structure for table "execution_bugs"
--
CREATE TABLE /*prefix*/execution_bugs(  
  "execution_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/executions (id) ON DELETE CASCADE,
  "bug_id" VARCHAR(64) NOT NULL DEFAULT '0',
  "tcstep_id" BIGINT NOT NULL DEFAULT '0',
  PRIMARY KEY ("execution_id","bug_id","tcstep_id")
); 


--
-- Table structure for table "testcase_script_links"
--
CREATE TABLE /*prefix*/testcase_script_links(  
  "tcversion_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcversions (id) ON DELETE CASCADE,
  "project_key" VARCHAR(64) NOT NULL,
  "repository_name" VARCHAR(64) NOT NULL,
  "code_path" VARCHAR(255) NOT NULL,
  "branch_name" VARCHAR(64) NULL,
  "commit_id" VARCHAR(40) NULL,
  PRIMARY KEY ("tcversion_id","project_key","repository_name","code_path")
); 


--
-- Table structure for table "keywords"
--
CREATE TABLE /*prefix*/keywords(  
  "id" BIGSERIAL NOT NULL ,
  "keyword" VARCHAR(100) NOT NULL DEFAULT '',
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
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
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id) ON DELETE CASCADE,
  "target_date" DATE NOT NULL ,
  "start_date" DATE NULL ,
  "a" SMALLINT NOT NULL DEFAULT '0',
  "b" SMALLINT NOT NULL DEFAULT '0',
  "c" SMALLINT NOT NULL DEFAULT '0',
  "name" VARCHAR(100) NOT NULL DEFAULT 'undefined',
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/milestones_uidx1 ON /*prefix*/milestones ("name","testplan_id");
CREATE INDEX /*prefix*/milestones_testplan_id ON /*prefix*/milestones ("testplan_id");


--
-- Table structure for table object_keywords
--
CREATE TABLE /*prefix*/object_keywords(  
  "id" BIGSERIAL NOT NULL ,
  "fk_id" BIGINT NOT NULL DEFAULT '0',
  "fk_table" VARCHAR(30) NULL DEFAULT '',
  "keyword_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/keywords (id) ON DELETE CASCADE,
  PRIMARY KEY ("id")
); 


--
-- Table structure for table "req_specs"
--
-- TICKET 4661
CREATE TABLE /*prefix*/req_specs(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id) ON DELETE CASCADE,
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,  
  "doc_id" VARCHAR(64) NOT NULL,
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/req_specs_uidx1 ON /*prefix*/req_specs ("doc_id","testproject_id");
CREATE INDEX /*prefix*/req_specs_testproject_id ON /*prefix*/req_specs ("testproject_id");

--
-- Table structure for table "requirements"
--
CREATE TABLE /*prefix*/requirements (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "srs_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/req_specs (id) ON DELETE CASCADE,
  "req_doc_id" VARCHAR(64) NOT NULL,
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/requirements_idx1 ON /*prefix*/requirements ("srs_id","req_doc_id");

CREATE TABLE /*prefix*/req_versions(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "version" INTEGER NOT NULL DEFAULT '1',
  "revision" INTEGER NOT NULL DEFAULT '1',
  "scope" TEXT NULL DEFAULT NULL,
  "status" CHAR(1) NOT NULL DEFAULT 'V',
  "type" CHAR(1) NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "is_open" INT2 NOT NULL DEFAULT '1',
  "expected_coverage" INTEGER NOT NULL DEFAULT 1,
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  "log_message" TEXT NULL DEFAULT NULL,
  ---- PRIMARY KEY ("id","version")  <<<<--- NEED TO CHANGE in order to add simple FK on req_revisions
  PRIMARY KEY ("id")
); 

--
-- Table structure for table "req_coverage"
--
CREATE TABLE /*prefix*/req_coverage( 
  "id" BIGSERIAL NOT NULL , 
  "req_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/requirements (id) ON DELETE CASCADE,
  "req_version_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/req_versions (id) ON DELETE CASCADE,
  "testcase_id" INTEGER NOT NULL DEFAULT '0',
  "tcversion_id" INTEGER NOT NULL DEFAULT '0',
  "link_status" INT2 NOT NULL DEFAULT '1',
  "is_active" INT2 NOT NULL DEFAULT '1',
  "author_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "review_requester_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  "review_request_ts" TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/req_coverage_full_link ON /*prefix*/req_coverage ("req_id","req_version_id","testcase_id","tcversion_id");


--
-- Table structure for table "rights"
--
CREATE TABLE /*prefix*/rights(  
  "id" BIGSERIAL NOT NULL ,
  "description" VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/rights_uidx1 ON /*prefix*/rights ("description");


--
-- Table structure for table "risk_assignments"
--
CREATE TABLE /*prefix*/risk_assignments(  
  "id" BIGSERIAL NOT NULL ,
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id) ON DELETE CASCADE,
  "node_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "risk" INT2 NOT NULL DEFAULT '2',
  "importance" INT2 NOT NULL DEFAULT '2',
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/risk_assignments_uidx1 ON /*prefix*/risk_assignments ("testplan_id","node_id");


--
-- Table structure for table "role_rights"
--
CREATE TABLE /*prefix*/role_rights(  
  "role_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/roles (id) ON DELETE CASCADE,
  "right_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/rights (id) ON DELETE CASCADE,
  PRIMARY KEY ("role_id","right_id")
); 


--
-- Table structure for table "testcase_keywords"
--
CREATE TABLE /*prefix*/testcase_keywords( 
  "id" BIGSERIAL NOT NULL , 
  "testcase_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "tcversion_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcversions (id),
  "keyword_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/keywords (id) ON DELETE CASCADE,
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/idx01_testcase_keywords ON /*prefix*/testcase_keywords ("testcase_id","tcversion_id","keyword_id");


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
  "build_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/builds (id) ON DELETE CASCADE,
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
  "testplan_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id) ON DELETE CASCADE,
  "role_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/roles (id) ON DELETE CASCADE,
  PRIMARY KEY ("user_id","testplan_id")
); 


--
-- Table structure for table "user_testproject_roles"
--
CREATE TABLE /*prefix*/user_testproject_roles(  
  "user_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/users (id),
  "testproject_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  "role_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/roles (id) ON DELETE CASCADE,
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
  PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX /*prefix*/text_templates_uidx1 ON /*prefix*/text_templates (type,title);
COMMENT ON TABLE /*prefix*/text_templates IS 'Global Project Templates';


--
CREATE TABLE /*prefix*/user_group(
  id BIGSERIAL NOT NULL,
  title varchar(100) NOT NULL,
  description text,
  owner_id BIGINT NOT NULL REFERENCES  /*prefix*/users (id),
  testproject_id BIGINT NOT NULL REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX /*prefix*/user_group_uidx1 ON /*prefix*/user_group (title);

--
CREATE TABLE /*prefix*/user_group_assign(
  usergroup_id BIGINT NOT NULL REFERENCES  /*prefix*/user_group (id),
  user_id BIGINT NOT NULL REFERENCES  /*prefix*/users (id)
);


CREATE TABLE /*prefix*/platforms (
  id BIGSERIAL NOT NULL,
  name VARCHAR(100) NOT NULL,
  testproject_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  notes text NOT NULL,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX /*prefix*/platforms_uidx1 ON /*prefix*/platforms (testproject_id,name);

CREATE TABLE /*prefix*/testplan_platforms (
  id BIGSERIAL NOT NULL,
  testplan_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id) ON DELETE CASCADE,
  platform_id BIGINT NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX /*prefix*/testplan_platforms_uidx1 ON /*prefix*/testplan_platforms (testplan_id,platform_id);

CREATE TABLE /*prefix*/inventory (
	id BIGSERIAL NOT NULL,
	"testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
	"owner_id" BIGINT NOT NULL REFERENCES  /*prefix*/users (id),
	"name" VARCHAR(255) NOT NULL,
	ipaddress VARCHAR(255) NOT NULL,
	"content" TEXT NULL ,
	"creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
	"modification_ts" TIMESTAMP NULL,
	PRIMARY KEY (id)
);
CREATE INDEX /*prefix*/inventory_idx1 ON /*prefix*/inventory (testproject_id);
CREATE UNIQUE INDEX /*prefix*/inventory_uidx1 ON /*prefix*/inventory (name,testproject_id);


CREATE TABLE /*prefix*/req_relations (
	id BIGSERIAL NOT NULL,
  source_id INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/requirements (id) ON DELETE CASCADE,
  destination_id  INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/requirements (id) ON DELETE CASCADE,
  relation_type INT2 NOT NULL DEFAULT '1',
  author_id BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
	creation_ts TIMESTAMP NOT NULL DEFAULT now(),
	PRIMARY KEY (id)
);



--- BUGID 4056
CREATE TABLE /*prefix*/req_revisions(  
  "parent_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/req_versions (id) ON DELETE CASCADE,
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "revision" INTEGER NOT NULL DEFAULT '1',   
  "req_doc_id" VARCHAR(64) NULL,  --- fman - it's OK to allow a simple update query on code ?
  "name" VARCHAR(100) NULL DEFAULT NULL,
  "scope" TEXT NULL DEFAULT NULL,
  "status" CHAR(1) NOT NULL DEFAULT 'V',
  "type" CHAR(1) NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',   --- fman - Need To understand use i.e. just as memory ?
  "is_open" INT2 NOT NULL DEFAULT '1',  --- fman - Need To understand use i.e. just as memory ?
  "expected_coverage" INTEGER NOT NULL DEFAULT 1,
  "log_message" TEXT NULL DEFAULT NULL,
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/req_revisions_uidx1 ON /*prefix*/req_revisions ("parent_id","revision");



-- TICKET 4661
CREATE TABLE /*prefix*/req_specs_revisions (
  "parent_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/req_specs (id) ON DELETE CASCADE,
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "revision" INTEGER NOT NULL DEFAULT '1',
  "doc_id" VARCHAR(64) NULL,   /* it's OK to allow a simple update query on code */
  "name" VARCHAR(100) NULL,
  "scope" TEXT NULL DEFAULT NULL,
  "total_req" INTEGER NOT NULL DEFAULT '0',
  "status" INTEGER NOT NULL DEFAULT '1',
  "type" CHAR(1) NULL DEFAULT 'N',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  "log_message" TEXT NULL DEFAULT NULL,
  PRIMARY KEY  ("id")
);
CREATE UNIQUE INDEX /*prefix*/req_specs_revisions_uidx1 ON /*prefix*/req_revisions ("parent_id","revision");


CREATE TABLE /*prefix*/issuetrackers (
  "id" BIGSERIAL NOT NULL ,
  "name" VARCHAR(100) NOT NULL,
  "type" INTEGER NOT NULL DEFAULT '0',
  "cfg" TEXT,
  PRIMARY KEY  ("id")
);
CREATE UNIQUE INDEX /*prefix*/issuetrackers_uidx1 ON /*prefix*/issuetrackers ("name");


CREATE TABLE /*prefix*/testproject_issuetracker
(
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  "issuetracker_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/issuetrackers (id) ON DELETE CASCADE,
  PRIMARY KEY ("testproject_id")
);



CREATE TABLE /*prefix*/reqmgrsystems
(
  "id" BIGSERIAL NOT NULL ,
  "name" VARCHAR(100) NOT NULL,
  "type" INTEGER NOT NULL DEFAULT '0',
  "cfg" TEXT,
  PRIMARY KEY  ("id")
);
CREATE UNIQUE INDEX /*prefix*/reqmgrsystems_uidx1 ON /*prefix*/reqmgrsystems ("name");

CREATE TABLE /*prefix*/testproject_reqmgrsystem
(
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  "reqmgrsystem_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/reqmgrsystems (id) ON DELETE CASCADE,
  PRIMARY KEY ("testproject_id")
);


--
-- Table structure for table cfield_build_design_values
--
CREATE TABLE /*prefix*/cfield_build_design_values(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id) ON DELETE CASCADE,
  "node_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/builds (id) ON DELETE CASCADE,
  "value" VARCHAR(4000) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","node_id")
); 
CREATE INDEX /*prefix*/IX_cfield_build_design_values ON /*prefix*/cfield_build_design_values ("node_id");



--
-- Table structure for table testcase_relations
--
CREATE TABLE /*prefix*/testcase_relations (
  id BIGSERIAL NOT NULL,
  source_id INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id) ON DELETE CASCADE,
  destination_id  INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id) ON DELETE CASCADE,
  relation_type INT2 NOT NULL DEFAULT '1',
  link_status INT2 NOT NULL DEFAULT '1',
  author_id BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  creation_ts TIMESTAMP NOT NULL DEFAULT now(),
  PRIMARY KEY (id)
);


CREATE TABLE /*prefix*/req_monitor (
  req_id INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/requirements (id) ON DELETE CASCADE,
  user_id BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
  testproject_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  PRIMARY KEY (req_id,user_id,testproject_id)
);


CREATE TABLE /*prefix*/plugins (
   id BIGSERIAL NOT NULL,
   basename  VARCHAR(100) NOT NULL,
   enabled INT2 NOT NULL DEFAULT '0',
   author_id BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
   creation_ts TIMESTAMP NOT NULL DEFAULT now(),
   PRIMARY KEY (id)
);

CREATE TABLE /*prefix*/plugins_configuration (
   id BIGSERIAL NOT NULL,
   testproject_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
   config_key VARCHAR(255) NOT NULL,
   config_type INTEGER NOT NULL,
   config_value varchar(255) NOT NULL,
   author_id BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
   creation_ts TIMESTAMP NOT NULL DEFAULT now(),
   PRIMARY KEY (id)
);

--
--
--
CREATE TABLE /*prefix*/codetrackers (
  id BIGSERIAL NOT NULL ,
  name VARCHAR(100) NOT NULL,
  type INTEGER NOT NULL DEFAULT '0',
  cfg TEXT,
  PRIMARY KEY  ("id")
);
CREATE UNIQUE INDEX /*prefix*/codetrackers_uidx1 ON /*prefix*/codetrackers ("name");

--
--
--
CREATE TABLE /*prefix*/testproject_codetracker (
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  "codetracker_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/codetrackers (id) ON DELETE CASCADE,
  PRIMARY KEY ("testproject_id")
);


--
--
--
CREATE TABLE /*prefix*/testproject_codetracker (
  testproject_id int(10) unsigned NOT NULL,
  codetracker_id int(10) unsigned NOT NULL,
  PRIMARY KEY (testproject_id)
);



--
-- VIEWS
--
CREATE OR REPLACE VIEW /*prefix*/tcases_active AS 
(
  SELECT DISTINCT nhtcv.parent_id AS tcase_id, tcv.tc_external_id
  FROM /*prefix*/nodes_hierarchy nhtcv
  JOIN /*prefix*/tcversions tcv ON tcv.id = nhtcv.id
  WHERE tcv.active = 1
);

-- 
--
CREATE OR REPLACE VIEW /*prefix*/tcversions_last_active AS 
(
  SELECT tcv.id, tcv.tc_external_id, tcv.version, tcv.layout, tcv.status, 
       tcv.summary, tcv.preconditions, tcv.importance, tcv.author_id, tcv.creation_ts, 
       tcv.updater_id, tcv.modification_ts, tcv.active, tcv.is_open, tcv.execution_type, 
       ac.tcase_id
  FROM /*prefix*/tcversions tcv
  JOIN( 
    SELECT nhtcv.parent_id AS tcase_id, max(tcv.id) AS tcversion_id
    FROM /*prefix*/nodes_hierarchy nhtcv
    JOIN /*prefix*/tcversions tcv ON tcv.id = nhtcv.id
    WHERE tcv.active = 1
    GROUP BY nhtcv.parent_id, tcv.tc_external_id
    ) ac 
  ON tcv.id = ac.tcversion_id
);


--
CREATE OR REPLACE VIEW /*prefix*/latest_tcase_version_number AS 
( 
  SELECT NH_TC.id AS testcase_id,max(TCV.version) AS version 
  FROM /*prefix*/nodes_hierarchy NH_TC 
  JOIN /*prefix*/nodes_hierarchy NH_TCV 
  ON NH_TCV.parent_id = NH_TC.id
  JOIN /*prefix*/tcversions TCV 
  ON NH_TCV.id = TCV.id 
  GROUP BY testcase_id
);

--
-- @uses latest_tcase_version_number
--
CREATE OR REPLACE VIEW /*prefix*/latest_tcase_version_id AS 
(
  SELECT LTCVN.testcase_id AS testcase_id,
         LTCVN.version AS version,
         TCV.id AS tcversion_id
  FROM /*prefix*/latest_tcase_version_number LTCVN 
  JOIN /*prefix*/nodes_hierarchy NHTCV 
  ON NHTCV.parent_id = LTCVN.testcase_id
  JOIN /*prefix*/tcversions TCV 
  ON  TCV.id = NHTCV.id 
  AND TCV.version = LTCVN.version
);


--
-- @used_by latest_req_version_id
--
CREATE OR REPLACE VIEW /*prefix*/latest_req_version AS
( 
  SELECT RQ.id AS req_id,max(RQV.version) AS version 
  FROM /*prefix*/nodes_hierarchy NHRQV 
  JOIN /*prefix*/requirements RQ 
  ON RQ.id = NHRQV.parent_id 
  JOIN /*prefix*/req_versions RQV 
  ON RQV.id = NHRQV.id
  GROUP BY RQ.id
);


--
-- @uses latest_req_version
-- 
CREATE OR REPLACE VIEW /*prefix*/latest_req_version_id AS 
( 
  SELECT LRQVN.req_id AS req_id, LRQVN.version AS version,
         REQV.id AS req_version_id
  FROM /*prefix*/latest_req_version LRQVN JOIN 
       /*prefix*/nodes_hierarchy NHRQV
  ON NHRQV.parent_id = LRQVN.req_id 
  JOIN /*prefix*/req_versions REQV 
  ON REQV.id = NHRQV.id AND REQV.version = LRQVN.version
);


--
--
CREATE OR REPLACE VIEW /*prefix*/latest_rspec_revision AS 
(
  SELECT RSR.parent_id AS req_spec_id, RS.testproject_id AS testproject_id,
  MAX(RSR.revision) AS revision 
  FROM /*prefix*/req_specs_revisions RSR 
  JOIN /*prefix*/req_specs RS 
  ON RS.id = RSR.parent_id
  GROUP BY RSR.parent_id,RS.testproject_id
);

--
--
CREATE OR REPLACE VIEW /*prefix*/tcversions_without_keywords AS 
( 
  SELECT NHTCV.parent_id AS testcase_id, NHTCV.id AS id
  FROM /*prefix*/nodes_hierarchy NHTCV 
  WHERE NHTCV.node_type_id = 4 
  AND NOT(EXISTS(SELECT 1 FROM /*prefix*/testcase_keywords TCK 
                 WHERE TCK.tcversion_id = NHTCV.id ) )
);
