--  -----------------------------------------------------------------------------------
-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- @filesource testlink_create_tables.sql
--
-- SQL script - create db tables for TL
-- Database Type: Microsoft SQL Server
--
-- ATTENTION: do not use a different naming convention, that one already in use.
--            TEXTIMAGE Option can be used only tables that have fields of type:
--            varchar(MAXSIZEALLOWED), nvarchar(MAXSIZEALLOWED), varbinary(MAXSIZEALLOWED), xml 
-- 
--        Find issue with custom_fields table if two fields were char(4000)
--        changed to varchar(4000) everything goes OK
--            http://www.mssqltips.com/sqlservertip/2242/row-sizes-exceeding-8060-bytes-in-sql-2005/
-- 
-- ATTENTION: 
-- 
-- @internal revisions
--                          
--  -----------------------------------------------------------------------------------
--
CREATE TABLE /*prefix*/transactions (
  id int IDENTITY(1,1) NOT NULL,
  entry_point varchar(45) NOT NULL CONSTRAINT /*prefix*/DF_transactions_entry_point default(N''),
  start_time INT NOT NULL CONSTRAINT /*prefix*/DF_transactions_start_time DEFAULT ((0)),
  end_time INT NOT NULL CONSTRAINT /*prefix*/DF_transactions_end_time DEFAULT ((0)),
  user_id INT CONSTRAINT /*prefix*/DF_transactions_user_id DEFAULT ((0)),
  session_id varchar(45) NULL,
  CONSTRAINT /*prefix*/PK_transactions PRIMARY KEY CLUSTERED 
  (
    id ASC
  ) ON [PRIMARY]
) ON [PRIMARY];
--
--
--
CREATE TABLE /*prefix*/events (
  id int IDENTITY(1,1) NOT NULL,
  transaction_id INT NOT NULL CONSTRAINT /*prefix*/DF_events_transaction_id DEFAULT ((0)),
  log_level SMALLINT NOT NULL CONSTRAINT /*prefix*/DF_events_log_level DEFAULT ((0)),
  source varchar(45) NULL,
  description nvarchar(max)  NOT NULL,
  fired_at INT NOT NULL CONSTRAINT /*prefix*/DF_fired_at DEFAULT ((0)),
  activity varchar(45) NULL,
  object_id INT NULL,
  object_type varchar(45) NULL,
  CONSTRAINT /*prefix*/PK_events PRIMARY KEY CLUSTERED 
  (
    id ASC
  ) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_transaction_id ON  /*prefix*/events
(
  transaction_id ASC
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_fired_at ON  /*prefix*/events
(
  fired_at ASC
) ON [PRIMARY];


CREATE TABLE /*prefix*/db_version (
version varchar(50)  NOT NULL CONSTRAINT /*prefix*/DF_db_version_version DEFAULT (N'unknown'),
upgrade_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_db_version_upgrade_ts DEFAULT (getdate()),
notes nvarchar(max)   NULL,
CONSTRAINT /*prefix*/PK_db_version PRIMARY KEY CLUSTERED 
(
  version ASC
) ON [PRIMARY]
) ON [PRIMARY];


CREATE TABLE /*prefix*/assignment_status (
  id int IDENTITY(1,1) NOT NULL,
  description varchar(100)  NOT NULL CONSTRAINT /*prefix*/DF_assignment_status_description DEFAULT (N'unknown'),
 CONSTRAINT /*prefix*/PK_assignment_status PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/cfield_node_types (
  field_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_node_types_field_id DEFAULT ((0)),
  node_type_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_node_types_node_type_id DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_cfield_node_types PRIMARY KEY CLUSTERED 
(
  field_id ASC,
  node_type_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_custom_fields_assign ON  /*prefix*/cfield_node_types
(
  node_type_id ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/testplan_tcversions (
  id int IDENTITY(1,1) NOT NULL,
  tcversion_id int NOT NULL CONSTRAINT /*prefix*/DF_testplan_tcversions_tcversion_id DEFAULT ((0)),
  testplan_id int NOT NULL CONSTRAINT /*prefix*/DF_testplan_tcversions_testplan_id DEFAULT ((0)),
  platform_id int NOT NULL CONSTRAINT /*prefix*/DF_testplan_tcversions_platform_id DEFAULT ((0)),
  node_order int NOT NULL CONSTRAINT /*prefix*/DF_testplan_tcversions_node_order DEFAULT ((1)),
  urgency tinyint NOT NULL CONSTRAINT /*prefix*/DF_testplan_tcversions_urgency DEFAULT ((2)),
  author_id int NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_testplan_tcversions_creation_ts DEFAULT (getdate()),
 CONSTRAINT /*prefix*/PK_testplan_tcversions PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY],
CONSTRAINT /*prefix*/IX_tplan_tcversion UNIQUE NONCLUSTERED 
(
  tcversion_id ASC,
  testplan_id ASC,
  platform_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/cfield_testprojects (
  field_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_field_id DEFAULT ((0)),
  testproject_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_testproject_id DEFAULT ((0)),
  display_order smallint NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_display_order DEFAULT ((1)),
  active tinyint NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_active DEFAULT ((1)),
  location tinyint NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_location DEFAULT ((1)),
  required tinyint NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_required DEFAULT ((0)),
  required_on_design tinyint NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_required_on_design DEFAULT ((0)),
  required_on_execution tinyint NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_required_on_execution DEFAULT ((0)),
  monitorable tinyint NOT NULL CONSTRAINT /*prefix*/DF_cfield_testprojects_monitorable DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_cfield_testprojects PRIMARY KEY CLUSTERED 
 (
  field_id ASC,
  testproject_id ASC
 ) ON [PRIMARY]
) ON [PRIMARY];


CREATE TABLE /*prefix*/object_keywords (
  id int IDENTITY(1,1) NOT NULL,
  fk_id int NOT NULL CONSTRAINT /*prefix*/DF_object_keywords_fk_id DEFAULT ((0)),
  fk_table varchar(30)  NOT NULL,
  keyword_id int NOT NULL CONSTRAINT /*prefix*/DF_object_keywords_keyword_id DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_object_keywords PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/custom_fields (
  id int IDENTITY(1,1) NOT NULL,
  name varchar(64)  NOT NULL default '',
  label varchar(64)  NOT NULL default '',
  type smallint NOT NULL default '',
  possible_values varchar(4000)  NOT NULL default '',
  default_value varchar(4000)  NOT NULL default '',
  valid_regexp char(255)  NOT NULL default '',
  length_min int NOT NULL default '',
  length_max int NOT NULL default '',
  show_on_design tinyint NOT NULL CONSTRAINT /*prefix*/DF_custom_fields_show_on_design DEFAULT ((1)),
  enable_on_design tinyint NOT NULL CONSTRAINT /*prefix*/DF_custom_fields_enable_on_design DEFAULT ((1)),
  show_on_execution tinyint NOT NULL CONSTRAINT /*prefix*/DF_custom_fields_show_on_execution DEFAULT ((0)),
  enable_on_execution tinyint NOT NULL CONSTRAINT /*prefix*/DF_custom_fields_enable_on_execution DEFAULT ((0)),
  show_on_testplan_design tinyint NOT NULL CONSTRAINT /*prefix*/DF_custom_fields_show_on_testplan_design DEFAULT ((0)),
  enable_on_testplan_design tinyint NOT NULL CONSTRAINT /*prefix*/DF_custom_fields_enable_on_testplan_design DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_custom_fields PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/IX_custom_fields_name ON  /*prefix*/custom_fields 
(
  name ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/roles (
  id int IDENTITY(1,1) NOT NULL,
  description varchar(100)  NOT NULL,
  notes nvarchar(max)   NULL,
 CONSTRAINT /*prefix*/PK_roles PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY],
 CONSTRAINT /*prefix*/IX_description1 UNIQUE NONCLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE TABLE /*prefix*/execution_bugs (
  execution_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_bugs_execution_id DEFAULT ((0)),
  bug_id varchar(64)  NOT NULL CONSTRAINT /*prefix*/DF_execution_bugs_bug_id DEFAULT ((0)),
  tcstep_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_bugs_tcstep_id DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_execution_bugs PRIMARY KEY CLUSTERED 
(
  execution_id ASC,
  bug_id ASC,
  tcstep_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/user_assignments (
  id int IDENTITY(1,1) NOT NULL,
  type int NOT NULL CONSTRAINT /*prefix*/DF_user_assignments_type DEFAULT ((0)),
  feature_id int NOT NULL CONSTRAINT /*prefix*/DF_user_assignments_feature_id DEFAULT ((0)),
  user_id int NULL,
  build_id int NULL,
  deadline_ts datetime NULL,
  assigner_id int NULL DEFAULT ((0)),
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_user_assignments_creation_ts DEFAULT (getdate()),
  status int NULL DEFAULT ((1)),
 CONSTRAINT /*prefix*/PK_user_assignments PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_user_assignments ON  /*prefix*/user_assignments 
(
  feature_id ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/executions (
  id int IDENTITY(1,1) NOT NULL,
  build_id int NOT NULL CONSTRAINT /*prefix*/DF_executions_build_id DEFAULT ((0)),
  tester_id int NULL CONSTRAINT /*prefix*/DF_executions_tester_id DEFAULT (NULL),
  execution_ts datetime NULL CONSTRAINT /*prefix*/DF_executions_execution_ts DEFAULT (NULL),
  status char(1)  NULL CONSTRAINT /*prefix*/DF_executions_status DEFAULT (NULL),
  testplan_id int NOT NULL CONSTRAINT /*prefix*/DF_executions_testplan_id DEFAULT ((0)),
  tcversion_id int NOT NULL CONSTRAINT /*prefix*/DF_executions_tcversion_id DEFAULT ((0)),
  tcversion_number smallint NOT NULL CONSTRAINT /*prefix*/DF_executions_tcversion_number DEFAULT ((1)),
  platform_id int NOT NULL CONSTRAINT /*prefix*/DF_executions_platform_id DEFAULT ((0)),
  execution_type tinyint NOT NULL CONSTRAINT /*prefix*/DF_executions_execution_type DEFAULT ((1)),
  execution_duration decimal(6,2) NULL,
  notes nvarchar(max)   NULL CONSTRAINT /*prefix*/DF_executions_notes DEFAULT (NULL),
 CONSTRAINT /*prefix*/PK_executions PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/executions_IX2 ON  /*prefix*/executions 
(
  execution_type ASC
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/executions_IX1 ON  /*prefix*/executions 
(
  testplan_id,tcversion_id,platform_id,build_id
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/executions_IX3 ON  /*prefix*/executions 
(
  tcversion_id
) ON [PRIMARY];

--
-- Table structure for table "execution_tcsteps"
--
CREATE TABLE /*prefix*/execution_tcsteps (
   id int IDENTITY(1,1) NOT NULL,
   execution_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_execution_id DEFAULT ((0)),  
   tcstep_id int NOT NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_tcstep_id DEFAULT ((0)),
   notes nvarchar(max)   NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_notes DEFAULT (NULL),
   status char(1)  NULL CONSTRAINT /*prefix*/DF_execution_tcsteps_status DEFAULT (NULL),
  CONSTRAINT /*prefix*/PK_execution_tcsteps PRIMARY KEY CLUSTERED 
  ( 
    id ASC
  ) ON [PRIMARY],

  CONSTRAINT /*prefix*/UIX_execution_tcsteps UNIQUE NONCLUSTERED 
  ( 
  execution_id,tcstep_id ASC
  ) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];





CREATE TABLE /*prefix*/risk_assignments (
  id int IDENTITY(1,1) NOT NULL,
  testplan_id int NOT NULL CONSTRAINT /*prefix*/DF_risk_assignments_testplan_id DEFAULT ((0)),
  node_id int NOT NULL CONSTRAINT /*prefix*/DF_risk_assignments_node_id DEFAULT ((0)),
  risk int NOT NULL CONSTRAINT /*prefix*/DF_risk_assignments_risk DEFAULT ((2)),
  importance char(1)  NOT NULL CONSTRAINT /*prefix*/DF_risk_assignments_importance DEFAULT (N'M'),
 CONSTRAINT /*prefix*/PK_risk_assignments PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY],
 CONSTRAINT /*prefix*/IX_tplan_node_id UNIQUE NONCLUSTERED 
(
  testplan_id ASC,
  node_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/rights (
  id int IDENTITY(1,1) NOT NULL,
  description varchar(100)  NOT NULL,
 CONSTRAINT /*prefix*/PK_rights PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY],
 CONSTRAINT /*prefix*/IX_description UNIQUE NONCLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/builds (
  id int IDENTITY(1,1) NOT NULL,
  testplan_id int NOT NULL CONSTRAINT /*prefix*/DF_builds_testplan_id DEFAULT ((0)),
  name varchar(100)  NOT NULL CONSTRAINT /*prefix*/DF_builds_name DEFAULT (N'undefined'),
  notes nvarchar(max)   NULL,
  active tinyint NOT NULL CONSTRAINT /*prefix*/DF_builds_active DEFAULT ((1)),
  is_open tinyint NOT NULL CONSTRAINT /*prefix*/DF_builds_open DEFAULT ((1)),
  author_id int NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_builds_creation_ts DEFAULT (getdate()),
  release_date datetime NULL,
  closed_on_date datetime NULL,
 CONSTRAINT /*prefix*/PK_builds PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/IX_name ON  /*prefix*/builds 
(
  testplan_id ASC,
  name ASC
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_testplan_id ON  /*prefix*/builds 
(
  testplan_id ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/keywords (
  id int IDENTITY(1,1) NOT NULL,
  keyword varchar(100)  NOT NULL,
  testproject_id int NOT NULL CONSTRAINT /*prefix*/DF_keywords_testproject_id DEFAULT ((0)),
  notes nvarchar(max)   NULL,
 CONSTRAINT /*prefix*/PK_keywords PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_keywords ON  /*prefix*/keywords 
(
  testproject_id ASC
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_keywords_keyword ON  /*prefix*/keywords 
(
  keyword ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/milestones (
  id int IDENTITY(1,1) NOT NULL,
  testplan_id int NOT NULL CONSTRAINT /*prefix*/DF_milestones_testplan_id DEFAULT ((0)),
  target_date datetime NOT NULL,
  start_date datetime NULL,
  a tinyint NOT NULL CONSTRAINT /*prefix*/DF_milestones_A DEFAULT ((0)),
  b tinyint NOT NULL CONSTRAINT /*prefix*/DF_milestones_B DEFAULT ((0)),
  c tinyint NOT NULL CONSTRAINT /*prefix*/DF_milestones_C DEFAULT ((0)),
  name varchar(100)  NOT NULL CONSTRAINT /*prefix*/DF_milestones_name DEFAULT (N'undefined'),
 CONSTRAINT /*prefix*/PK_Milestones PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_Testplan ON  /*prefix*/milestones 
(
  testplan_id ASC
) ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/IX_name_testplan_id ON  /*prefix*/milestones 
(
  name ASC,
  testplan_id ASC
) ON [PRIMARY];


CREATE TABLE /*prefix*/attachments (
  id int IDENTITY(1,1) NOT NULL,
  fk_id int NOT NULL CONSTRAINT /*prefix*/DF_attachments_fk_id DEFAULT ((0)),
  fk_table varchar(250)  NULL,
  title varchar(250)  NULL,
  description varchar(250)  NULL,
  file_name varchar(250)  NOT NULL,
  file_path varchar(250)  NOT NULL,
  file_size int NOT NULL CONSTRAINT /*prefix*/DF_attachments_file_size DEFAULT ((0)),
  file_type varchar(250)  NOT NULL,
  date_added datetime NOT NULL,
  content nvarchar(max)   NULL,
  compression_type int NOT NULL CONSTRAINT /*prefix*/DF_attachments_compression_type DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_attachments PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/attachments_IX1 ON  /*prefix*/attachments 
(
  fk_id ASC
) ON [PRIMARY];



CREATE TABLE /*prefix*/node_types (
  id int IDENTITY(1,1) NOT NULL,
  description varchar(100)  NOT NULL CONSTRAINT /*prefix*/DF_node_types_description DEFAULT (N'testproject'),
 CONSTRAINT /*prefix*/PK_node_types PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/nodes_hierarchy (
  id int IDENTITY(1,1) NOT NULL,
  name varchar(100)  NULL,
  parent_id int NULL,
  node_type_id int NOT NULL CONSTRAINT /*prefix*/DF_nodes_hierarchy_node_type_id DEFAULT ((1)),
  node_order int NULL,
 CONSTRAINT /*prefix*/PK_nodes_hierarchy PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_pid_m_nodeorder ON  /*prefix*/nodes_hierarchy 
(
  parent_id ASC,
  node_order ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/req_coverage (
  req_id int NOT NULL,
  testcase_id int NOT NULL,
  author_id int NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_req_coverage_creation_ts DEFAULT (getdate()),
  review_requester_id int NULL,
  review_request_ts datetime NULL 
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_req_testcase ON  /*prefix*/req_coverage 
(
  req_id ASC,
  testcase_id ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/req_specs (
  id int NOT NULL,
  testproject_id int NOT NULL,
  doc_id VARCHAR(64) NOT NULL,
   CONSTRAINT /*prefix*/PK_req_specs PRIMARY KEY CLUSTERED 
  (
    id ASC
  ) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_req_specs_testproject_id ON  /*prefix*/req_specs 
(
  testproject_id ASC
) ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/UIX_req_specs ON  /*prefix*/req_specs 
(
  doc_id,testproject_id
) ON [PRIMARY];


CREATE TABLE /*prefix*/requirements (
  id int NOT NULL,
  srs_id int NOT NULL,
  req_doc_id varchar(64)  NOT NULL,
  CONSTRAINT /*prefix*/PK_requirements PRIMARY KEY CLUSTERED 
  (
    id ASC
  ) ON [PRIMARY]
) ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/IX_requirements ON  /*prefix*/requirements 
(
  srs_id,req_doc_id
) ON [PRIMARY];


--- Test Link 1.9
CREATE TABLE /*prefix*/req_versions(  
  id int NOT NULL,
  version INTEGER NOT NULL DEFAULT '1',
  revision INTEGER NOT NULL DEFAULT '1',
  scope nvarchar(max)  NULL DEFAULT NULL,
  status CHAR(1) NOT NULL DEFAULT 'V',
  type CHAR(1) NULL DEFAULT NULL,
  active INT NOT NULL DEFAULT '1',
  is_open INT NOT NULL DEFAULT '1',
  expected_coverage INT NOT NULL DEFAULT 1,
  author_id  INT NULL DEFAULT NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_req_versions_creation_ts DEFAULT (getdate()),
  modifier_id INT NULL DEFAULT NULL,
  modification_ts datetime NULL,
  log_message nvarchar(max)  NULL DEFAULT NULL,
  CONSTRAINT /*prefix*/PK_req_versions PRIMARY KEY CLUSTERED 
  (
    id
  ) ON [PRIMARY]
) ON [PRIMARY];

--- Test Link 1.9.1
CREATE TABLE /*prefix*/req_revisions(
  parent_id int NOT NULL,
  id int NOT NULL,
  revision INTEGER NOT NULL DEFAULT '1',
  req_doc_id varchar(64) NULL,
  name varchar(100) NULL,
  scope nvarchar(max)  NULL DEFAULT NULL,
  status CHAR(1) NOT NULL DEFAULT 'V',
  type CHAR(1) NULL DEFAULT NULL,
  active INT NOT NULL DEFAULT '1',
  is_open INT NOT NULL DEFAULT '1',
  expected_coverage INT NOT NULL DEFAULT 1,
  log_message nvarchar(max)  NULL DEFAULT NULL,
  author_id INT NULL DEFAULT NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_req_revisions_creation_ts DEFAULT (getdate()),
  modifier_id INT NULL DEFAULT NULL,
  modification_ts datetime NULL,
  CONSTRAINT /*prefix*/PK_req_revisions PRIMARY KEY CLUSTERED
  (
      id
  ) ON [PRIMARY]
) ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/IX1_req_revisions ON /*prefix*/req_revisions
(
    parent_id,revision
) ON [PRIMARY];


CREATE TABLE /*prefix*/role_rights (
  role_id int NOT NULL CONSTRAINT /*prefix*/DF_role_rights_role_id DEFAULT ((0)),
  right_id int NOT NULL CONSTRAINT /*prefix*/DF_role_rights_right_id DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_role_rights PRIMARY KEY CLUSTERED 
(
  role_id ASC,
  right_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/testcase_keywords (
  testcase_id int NOT NULL CONSTRAINT /*prefix*/DF_testcase_keywords_testcase_id DEFAULT ((0)),
  keyword_id int NOT NULL CONSTRAINT /*prefix*/DF_testcase_keywords_keyword_id DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_testcase_keywords PRIMARY KEY CLUSTERED 
(
  testcase_id ASC,
  keyword_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_testcase_keywords ON  /*prefix*/testcase_keywords 
(
  testcase_id ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/tcversions (
  id int NOT NULL,
  tc_external_id int NULL,
  version smallint NOT NULL CONSTRAINT /*prefix*/DF_tcversions_version DEFAULT ((1)),
  layout INT NOT NULL DEFAULT '1',
  status int NOT NULL DEFAULT ((1)),
  summary nvarchar(max)   NULL,
  preconditions nvarchar(max)  NULL,
  importance tinyint NOT NULL CONSTRAINT /*prefix*/DF_tcversions_importance DEFAULT ((2)),
  author_id int NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_tcversions_creation_ts DEFAULT (getdate()),
  updater_id int NULL,
  modification_ts datetime NULL,
  active tinyint NOT NULL CONSTRAINT /*prefix*/DF_tcversions_active DEFAULT ((1)),
  is_open tinyint NOT NULL CONSTRAINT /*prefix*/DF_tcversions_open DEFAULT ((1)),
  execution_type tinyint NOT NULL CONSTRAINT /*prefix*/DF_tcversions_execution_type DEFAULT ((1)),
  estimated_exec_duration decimal(6,2) NULL,
 CONSTRAINT /*prefix*/PK_tcversions PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];


--
-- Test Link 1.9 - 
--
CREATE TABLE /*prefix*/tcsteps (  
  id int NOT NULL,
  step_number INT NOT NULL DEFAULT '1',
  actions nvarchar(max)  NULL DEFAULT NULL,
  expected_results nvarchar(max)  NULL DEFAULT NULL,
  active INT NOT NULL DEFAULT '1',
  execution_type INT NOT NULL DEFAULT '1',
  CONSTRAINT /*prefix*/PK_tcsteps PRIMARY KEY CLUSTERED 
  (
  id ASC
  ) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];


CREATE TABLE /*prefix*/testplans (
  id int NOT NULL,
  testproject_id int NOT NULL CONSTRAINT /*prefix*/DF_testplans_testproject_id DEFAULT ((0)),
  notes nvarchar(max)   NULL,
  active tinyint NOT NULL CONSTRAINT /*prefix*/DF_testplans_active DEFAULT ((1)),
  is_open tinyint NOT NULL CONSTRAINT /*prefix*/DF_testplans_is_open DEFAULT ((1)),
  is_public tinyint NOT NULL CONSTRAINT /*prefix*/DF_testplans_is_public DEFAULT ((1)),
  api_key varchar(64) NOT NULL DEFAULT (HashBytes('MD5',CAST(RAND() AS CHAR)) + HashBytes('MD5',CAST(RAND() AS CHAR))),
 CONSTRAINT /*prefix*/PK_testplans PRIMARY KEY CLUSTERED 
 (
  id ASC
 ) ON [PRIMARY],
 CONSTRAINT /*prefix*/IX_testplans_api_key UNIQUE NONCLUSTERED 
 (
    api_key ASC
 ) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_testproject_id_active ON  /*prefix*/testplans 
(
  testproject_id ASC,
  active ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/testprojects (
  id int NOT NULL,
  notes nvarchar(max)   NULL,
  color varchar(12)  NOT NULL CONSTRAINT /*prefix*/DF_testprojects_color DEFAULT (N'#9BD'),
  active tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_active DEFAULT ((1)),
  is_public tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_is_public DEFAULT ((1)),
  options nvarchar(max) ,
  option_reqs tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_option_reqs DEFAULT ((0)),
  option_priority tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_option_priority DEFAULT ((0)),
  option_automation tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_option_automation DEFAULT ((0)),
  prefix varchar(16) NOT NULL,
  tc_counter int NOT NULL CONSTRAINT /*prefix*/DF_testprojects_tc_counter DEFAULT ((0)),
  issue_tracker_enabled tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_issue_tracker_enabled DEFAULT ((0)),  
  reqmgr_integration_enabled tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_reqmgr_integration_enabled DEFAULT ((0)),  
  api_key varchar(64) NOT NULL DEFAULT (HashBytes('MD5',CAST(RAND() AS CHAR)) + HashBytes('MD5',CAST(RAND() AS CHAR))),
  CONSTRAINT /*prefix*/PK_testprojects PRIMARY KEY CLUSTERED 
  (
   id ASC
  ) ON [PRIMARY],
  CONSTRAINT /*prefix*/IX_testprojects_prefix UNIQUE  NONCLUSTERED 
  (
    prefix
  )  ON [PRIMARY],
  CONSTRAINT /*prefix*/IX_testprojects_api_key UNIQUE NONCLUSTERED 
  (
    api_key ASC
  ) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_id_active ON  /*prefix*/testprojects 
(
  id ASC,
  active ASC
) ON [PRIMARY];

CREATE TABLE /*prefix*/testsuites (
  id int NOT NULL,
  details nvarchar(max)   NULL,
 CONSTRAINT /*prefix*/PK_testsuites PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE TABLE /*prefix*/user_testproject_roles (
  user_id int NOT NULL CONSTRAINT /*prefix*/DF_user_testproject_roles_user_id DEFAULT ((0)),
  testproject_id int NOT NULL CONSTRAINT /*prefix*/DF_user_testproject_roles_testproject_id DEFAULT ((0)),
  role_id int NOT NULL CONSTRAINT /*prefix*/DF_user_testproject_roles_role_id DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_user_testproject_roles PRIMARY KEY CLUSTERED 
(
  user_id ASC,
  testproject_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/user_testplan_roles (
  user_id int NOT NULL,
  testplan_id int NOT NULL,
  role_id int NOT NULL,
 CONSTRAINT /*prefix*/PK_user_testplan_roles PRIMARY KEY CLUSTERED 
(
  user_id ASC,
  testplan_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/users (
  id int IDENTITY(1,1) NOT NULL,
  login varchar(100)  NOT NULL,
  password varchar(32)  NOT NULL,
  role_id int NOT NULL CONSTRAINT /*prefix*/DF_users_role_id DEFAULT ((0)),
  email varchar(100)  NOT NULL,
  first varchar(50)  NOT NULL,
  last varchar(50)  NOT NULL,
  locale varchar(10)  NOT NULL CONSTRAINT /*prefix*/DF_users_locale DEFAULT (N'en_US'),
  default_testproject_id int NULL,
  active tinyint NOT NULL CONSTRAINT /*prefix*/DF_users_active DEFAULT ((1)),
  script_key varchar (32) NULL,
  cookie_string varchar (64) NOT NULL CONSTRAINT /*prefix*/DF_cookie_string DEFAULT (N''),
  auth_method varchar (10) NULL,
 CONSTRAINT /*prefix*/PK_users PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/IX_users_login ON  /*prefix*/users 
(
  login ASC
) ON [PRIMARY];
CREATE UNIQUE NONCLUSTERED INDEX /*prefix*/IX_users_cookie_string ON  /*prefix*/users 
(
  cookie_string ASC
) ON [PRIMARY];



CREATE TABLE /*prefix*/cfield_design_values (
  field_id int NOT NULL,
  node_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_design_values_node_id DEFAULT ((0)),
  value varchar(4000)  NOT NULL CONSTRAINT /*prefix*/DF_cfield_design_values_value DEFAULT ((0)),
 CONSTRAINT /*prefix*/PK_cfield_design_values PRIMARY KEY CLUSTERED 
(
  field_id ASC,
  node_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_cfield_design_values ON  /*prefix*/cfield_design_values 
(
  node_id ASC
) ON [PRIMARY];

--
CREATE TABLE /*prefix*/cfield_build_design_values (
  field_id int NOT NULL,
  node_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_design_build_values_node_id DEFAULT ((0)),
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


-- 
CREATE TABLE /*prefix*/cfield_testplan_design_values (
  field_id int NOT NULL,
  link_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_testplan_design_values_node_id DEFAULT ((0)),
  value varchar(4000)  NOT NULL CONSTRAINT /*prefix*/DF_cfield_testplan_design_values_value DEFAULT (''),
 CONSTRAINT /*prefix*/PK_cfield_testplan_design_values PRIMARY KEY CLUSTERED 
(
  field_id ASC,
  link_id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX /*prefix*/IX_cfield_testplan_design_values ON  /*prefix*/cfield_testplan_design_values 
(
  link_id ASC
) ON [PRIMARY]; 
--

CREATE TABLE /*prefix*/assignment_types (
  id int IDENTITY(1,1) NOT NULL,
  fk_table varchar(30)  NOT NULL,
  description varchar(100)  NOT NULL CONSTRAINT /*prefix*/DF_assignment_types_description DEFAULT (N'unknown'),
 CONSTRAINT /*prefix*/PK_assignment_types PRIMARY KEY CLUSTERED 
(
  id ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/cfield_execution_values (
  field_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_execution_values_field_id DEFAULT ((0)),
  execution_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_execution_values_execution_id DEFAULT ((0)),
  testplan_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_execution_values_testplan_id DEFAULT ((0)),
  tcversion_id int NOT NULL CONSTRAINT /*prefix*/DF_cfield_execution_values_tcversion_id DEFAULT ((0)),
  value varchar(4000)  NOT NULL,
 CONSTRAINT /*prefix*/PK_cfield_execution_values PRIMARY KEY CLUSTERED 
(
  field_id ASC,
  execution_id ASC,
  testplan_id ASC,
  tcversion_id ASC
) ON [PRIMARY]
) ON [PRIMARY];


CREATE TABLE /*prefix*/text_templates (
  id int IDENTITY(1,1) NOT NULL,
  type smallint NOT NULL,
  title varchar (100) NOT NULL,
  template_data nvarchar(max) ,
  author_id int DEFAULT NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_text_templates_creation_ts DEFAULT (getdate()),
  is_public tinyint NOT NULL CONSTRAINT /*prefix*/DF_text_templates_is_public DEFAULT ((0)),
  CONSTRAINT /*prefix*/PK_text_templates PRIMARY KEY  CLUSTERED 
  (
    id
  )  ON [PRIMARY],
  CONSTRAINT /*prefix*/IX_text_templates UNIQUE  NONCLUSTERED 
  (
    type,
    title
  )  ON [PRIMARY] 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE TABLE /*prefix*/user_group (
  id int IDENTITY(1,1) NOT NULL,
  title varchar (100) NOT NULL,
  description nvarchar(max) ,
  CONSTRAINT /*prefix*/PK_user_group PRIMARY KEY  CLUSTERED 
  (
    id
  )  ON [PRIMARY], 
  CONSTRAINT /*prefix*/IX_user_group_title UNIQUE  NONCLUSTERED 
  (
    title
  )  ON [PRIMARY] 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE TABLE /*prefix*/user_group_assign (
  usergroup_id int NOT NULL,
  user_id int NOT NULL,
) ON [PRIMARY];

--- 1.9 tables
CREATE TABLE /*prefix*/platforms (
  id int IDENTITY(1,1) NOT NULL,
  name VARCHAR(100) NOT NULL,
  testproject_id int NOT NULL DEFAULT '0',
  notes nvarchar(max)  NOT NULL,
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
  content nvarchar(max) ,
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


CREATE TABLE /*prefix*/reqmgrsystems
(
  id int IDENTITY(1,1) NOT NULL,
  name VARCHAR(100) NOT NULL,
  type int NOT NULL CONSTRAINT /*prefix*/DF_reqmgrsystems_type DEFAULT ((0)),
  cfg nvarchar(max)  NULL,
  CONSTRAINT /*prefix*/PK_reqmgrsystems PRIMARY KEY  CLUSTERED 
  (
    id
  )  ON [PRIMARY],
    CONSTRAINT /*prefix*/UIX_reqmgrsystems UNIQUE NONCLUSTERED 
   ( 
  name ASC
   ) ON [PRIMARY]  
) ON [PRIMARY];


CREATE TABLE /*prefix*/testproject_reqmgrsystem
(
  testproject_id int NOT NULL,
  reqmgrsystem_id int NOT NULL,
  CONSTRAINT /*prefix*/UIX_testproject_reqmgrsystem UNIQUE NONCLUSTERED 
  ( 
    testproject_id ASC
  ) ON [PRIMARY]    
)ON [PRIMARY];

--- 
CREATE TABLE /*prefix*/testcase_relations (
  id int IDENTITY(1,1) NOT NULL,
  source_id INT NOT NULL DEFAULT '0',
  destination_id  INT NOT NULL DEFAULT '0',
  relation_type INT NOT NULL DEFAULT '1',
  author_id int NOT NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_testcase_relations_creation_ts DEFAULT (getdate()),
  CONSTRAINT /*prefix*/PK_testcase_relations PRIMARY KEY  CLUSTERED 
  (
    id
  )  ON [PRIMARY]
) ON [PRIMARY];

--- 
CREATE TABLE /*prefix*/req_monitor (
  req_id INT NOT NULL DEFAULT '0',
  user_id  INT NOT NULL DEFAULT '0',
  testproject_id INT NOT NULL DEFAULT '0',
  CONSTRAINT /*prefix*/PK_req_monitor PRIMARY KEY  CLUSTERED 
  (
    req_id,user_id,testproject_id
  )  ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/plugins (
  plugin_id int NOT NULL IDENTITY(1,1) CONSTRAINT /*prefix*/DF_plugins_plugin_id DEFAULT ((0)),
  basename VARCHAR(100) NOT NULL,
  enabled tinyint NOT NULL CONSTRAINT /*prefix*/DF_plugins_enabled DEFAULT ((0)),
  author_id int NOT NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_plugins_creation_ts DEFAULT (getdate()),
 CONSTRAINT /*prefix*/PK_plugins PRIMARY KEY CLUSTERED
 (
  plugin_id ASC
 ) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/plugins_configuration (
  plugin_config_id int IDENTITY(1,1) NOT NULL CONSTRAINT /*prefix*/DF_plugins_configuration_plugin_config_id DEFAULT ((0)),
  testproject_id int NOT NULL CONSTRAINT /*prefix*/DF_plugins_configuration__testproject_id DEFAULT ((0)),
  config_key VARCHAR(255) NOT NULL,
  config_type int NOT NULL,
  config_value VARCHAR(255) NOT NULL,
  author_id int NOT NULL,
  creation_ts datetime NOT NULL CONSTRAINT /*prefix*/DF_plugins_configuration__creation_ts DEFAULT (getdate()),
 CONSTRAINT /*prefix*/PK_plugins_configuration PRIMARY KEY CLUSTERED
 (
  plugin_config_id ASC
 ) ON [PRIMARY]
) ON [PRIMARY];
