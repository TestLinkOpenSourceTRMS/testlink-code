--  -----------------------------------------------------------------------------------
-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- $Id: testlink_create_tables.sql,v 1.20 2008/01/02 18:53:13 franciscom Exp $
--
-- SQL script - create db tables for TL
-- Database Type: Microsoft SQL Server
-- 
-- Rev :
--      20071202 - franciscom - added tcversions.execution_type
--      20071010 - franciscom - ntext,nvarchar,nchar -> text,varchar,char
--                              open -> is_open
--      20070519 - franciscom - milestones table date -> target_date, because
--                              date is reserved word for Oracle
--
--       20070414 - franciscom - table requirements: added field node_order 
--
--       20070228 - franciscom -  BUGID 697 - priority table
--       20070228 - franciscom -  BUGID 697 - builds table
--       20070131 - franciscom - requirements -> req_doc_id(32), 
--
--       20070120 - franciscom - following BUGID 458 ( really a new feature request)
--                               two new fields on builds table
--                               active, open
--                               
--                               
--  -----------------------------------------------------------------------------------
CREATE TABLE [api_developer_keys] (  
	[id] [int] IDENTITY(1,1) NOT NULL,
  [developer_key] [VARCHAR] (32) NOT NULL,
  [user_id] [int] NOT NULL,
  CONSTRAINT [PK_api_developer_keys] PRIMARY KEY CLUSTERED 
  (
	 [id] ASC
  ) ON [PRIMARY]
) ON [PRIMARY] 
CREATE NONCLUSTERED INDEX [api_developer_keys_user_id] ON [api_developer_keys] 
(
	[user_id] ASC
) ON [PRIMARY]


CREATE TABLE [db_version](
[version] [varchar](50)  NOT NULL CONSTRAINT [DF_db_version_version]  DEFAULT (N'unknown'),
[upgrade_ts] [datetime] NOT NULL CONSTRAINT [DF_db_version_upgrade_ts]  DEFAULT (getdate()),
[notes] [text]  NULL
) ON [PRIMARY]


CREATE TABLE [assignment_status](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[description] [varchar](100)  NOT NULL CONSTRAINT [DF_assignment_status_description]  DEFAULT (N'unknown'),
 CONSTRAINT [PK_assignment_status] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [cfield_node_types](
	[field_id] [int] NOT NULL CONSTRAINT [DF_cfield_node_types_field_id]  DEFAULT ((0)),
	[node_type_id] [int] NOT NULL CONSTRAINT [DF_cfield_node_types_node_type_id]  DEFAULT ((0)),
 CONSTRAINT [PK_cfield_node_types] PRIMARY KEY CLUSTERED 
(
	[field_id] ASC,
	[node_type_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [idx_custom_fields_assign] ON [cfield_node_types] 
(
	[node_type_id] ASC
) ON [PRIMARY]

CREATE TABLE [testplan_tcversions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[tcversion_id] [int] NOT NULL CONSTRAINT [DF_testplan_tcversions_tcversion_id]  DEFAULT ((0)),
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_testplan_tcversions_testplan_id]  DEFAULT ((0)),
 CONSTRAINT [PK_testplan_tcversions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY],
 CONSTRAINT [IX_tp_tcversion] UNIQUE NONCLUSTERED 
(
	[tcversion_id] ASC,
	[testplan_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [cfield_testprojects](
	[field_id] [int] NOT NULL CONSTRAINT [DF_cfield_testprojects_field_id]  DEFAULT ((0)),
	[testproject_id] [int] NOT NULL CONSTRAINT [DF_cfield_testprojects_testproject_id]  DEFAULT ((0)),
	[display_order] [smallint] NOT NULL CONSTRAINT [DF_cfield_testprojects_display_order]  DEFAULT ((1)),
	[active] [tinyint] NOT NULL CONSTRAINT [DF_cfield_testprojects_active]  DEFAULT ((1)),
	[required_on_design] [tinyint] NOT NULL CONSTRAINT [DF_cfield_testprojects_required_on_design]  DEFAULT ((0)),
	[required_on_execution] [tinyint] NOT NULL CONSTRAINT [DF_cfield_testprojects_required_on_execution]  DEFAULT ((0)),
 CONSTRAINT [PK_cfield_testprojects] PRIMARY KEY CLUSTERED 
(
	[field_id] ASC,
	[testproject_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [object_keywords](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fk_id] [int] NOT NULL CONSTRAINT [DF_object_keywords_fk_id]  DEFAULT ((0)),
	[fk_table] [varchar](30)  NOT NULL,
	[keyword_id] [int] NOT NULL CONSTRAINT [DF_object_keywords_keyword_id]  DEFAULT ((0)),
 CONSTRAINT [PK_object_keywords] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [custom_fields](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [char](64)  NOT NULL default '',
	[label] [char](64)  NOT NULL default '',
	[type] [smallint] NOT NULL default '',
	[possible_values] [char](255)  NOT NULL default '',
	[default_value] [char](255)  NOT NULL default '',
	[valid_regexp] [char](255)  NOT NULL default '',
	[length_min] [int] NOT NULL default '',
	[length_max] [int] NOT NULL default '',
	[show_on_design] [tinyint] NOT NULL CONSTRAINT [DF_custom_fields_show_on_design]  DEFAULT ((1)),
	[enable_on_design] [tinyint] NOT NULL CONSTRAINT [DF_custom_fields_enable_on_design]  DEFAULT ((1)),
	[show_on_execution] [tinyint] NOT NULL CONSTRAINT [DF_custom_fields_show_on_execution]  DEFAULT ((0)),
	[enable_on_execution] [tinyint] NOT NULL CONSTRAINT [DF_custom_fields_enable_on_execution]  DEFAULT ((0)),
 CONSTRAINT [PK_custom_fields] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE UNIQUE NONCLUSTERED INDEX [IX_custom_fields_name] ON [custom_fields] 
(
	[name] ASC
) ON [PRIMARY]

CREATE TABLE [roles](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[description] [varchar](100)  NOT NULL,
	[notes] [text]  NULL,
 CONSTRAINT [PK_roles] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY],
 CONSTRAINT [IX_Description1] UNIQUE NONCLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE TABLE [execution_bugs](
	[execution_id] [int] NOT NULL CONSTRAINT [DF_execution_bugs_execution_id]  DEFAULT ((0)),
	[bug_id] [varchar](16)  NOT NULL CONSTRAINT [DF_execution_bugs_bug_id]  DEFAULT ((0)),
 CONSTRAINT [PK_execution_bugs] PRIMARY KEY CLUSTERED 
(
	[execution_id] ASC,
	[bug_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [user_assignments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[type] [int] NOT NULL CONSTRAINT [DF_user_assignments_type]  DEFAULT ((0)),
	[feature_id] [int] NOT NULL CONSTRAINT [DF_user_assignments_feature_id]  DEFAULT ((0)),
	[user_id] [int] NULL,
	[deadline_ts] [datetime] NULL,
	[assigner_id] [int] NULL DEFAULT ((0)),
	[creation_ts] [datetime] NOT NULL,
	[status] [int] NULL DEFAULT ((1)),
 CONSTRAINT [PK_user_assignments] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_user_assignments] ON [user_assignments] 
(
	[feature_id] ASC
) ON [PRIMARY]

CREATE TABLE [executions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[build_id] [int] NOT NULL CONSTRAINT [DF_executions_build_id]  DEFAULT ((0)),
	[tester_id] [int] NULL CONSTRAINT [DF_executions_tester_id]  DEFAULT (NULL),
	[execution_ts] [datetime] NULL CONSTRAINT [DF_executions_execution_ts]  DEFAULT (NULL),
	[status] [char](1)  NULL CONSTRAINT [DF_executions_status]  DEFAULT (NULL),
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_executions_testplan_id]  DEFAULT ((0)),
	[tcversion_id] [int] NOT NULL CONSTRAINT [DF_executions_tcversion_id]  DEFAULT ((0)),
	[execution_type] [tinyint] NOT NULL CONSTRAINT [DF_executions_execution_type]  DEFAULT ((1)),
	[notes] [text]  NULL CONSTRAINT [DF_executions_notes]  DEFAULT (NULL),
 CONSTRAINT [PK_executions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_executions_execution_type] ON [executions] 
(
	[execution_type] ASC
) ON [PRIMARY]



CREATE TABLE [risk_assignments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_risk_assignments_testplan_id]  DEFAULT ((0)),
	[node_id] [int] NOT NULL CONSTRAINT [DF_risk_assignments_node_id]  DEFAULT ((0)),
	[risk] [int] NOT NULL CONSTRAINT [DF_risk_assignments_risk]  DEFAULT ((2)),
	[importance] [char](1)  NOT NULL CONSTRAINT [DF_risk_assignments_importance]  DEFAULT (N'M'),
 CONSTRAINT [PK_risk_assignments] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY],
 CONSTRAINT [IX_tp_node_id] UNIQUE NONCLUSTERED 
(
	[testplan_id] ASC,
	[node_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [rights](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[description] [varchar](100)  NOT NULL,
 CONSTRAINT [PK_rights] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY],
 CONSTRAINT [IX_Description] UNIQUE NONCLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [builds](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_builds_testplan_id]  DEFAULT ((0)),
	[name] [varchar](100)  NOT NULL CONSTRAINT [DF_builds_name]  DEFAULT (N'undefined'),
	[notes] [text]  NULL,
	[active] [tinyint] NOT NULL CONSTRAINT [DF_builds_active]  DEFAULT ((1)),
	[is_open] [tinyint] NOT NULL CONSTRAINT [DF_builds_open]  DEFAULT ((1)),
 CONSTRAINT [PK_builds] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE UNIQUE NONCLUSTERED INDEX [IX_name] ON [builds] 
(
	[testplan_id] ASC,
	[name] ASC
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_testplan_id] ON [builds] 
(
	[testplan_id] ASC
) ON [PRIMARY]

CREATE TABLE [priorities](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_priorities_testplan_id]  DEFAULT ((0)),
	[risk] [char](1)  NOT NULL,
	[importance] [char](1)  NOT NULL,
	[priority] [char](1)  NOT NULL CONSTRAINT [DF_priorities_priority]  DEFAULT (N'b'),
 CONSTRAINT [PK_priorities] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY],
 CONSTRAINT [IX_testplan_id] UNIQUE NONCLUSTERED 
(
	[testplan_id] ASC,
	[risk] ASC,
	[importance] ASC,
	[priority] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [keywords](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[keyword] [varchar](100)  NOT NULL,
	[testproject_id] [int] NOT NULL CONSTRAINT [DF_keywords_testproject_id]  DEFAULT ((0)),
	[notes] [text]  NULL,
 CONSTRAINT [PK_keywords] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_keywords] ON [keywords] 
(
	[testproject_id] ASC
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_keywords_keyword] ON [keywords] 
(
	[keyword] ASC
) ON [PRIMARY]

CREATE TABLE [milestones](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_milestones_testplan_id]  DEFAULT ((0)),
	[target_date] [datetime] NOT NULL,
	[A] [tinyint] NOT NULL CONSTRAINT [DF_milestones_A]  DEFAULT ((0)),
	[B] [tinyint] NOT NULL CONSTRAINT [DF_milestones_B]  DEFAULT ((0)),
	[C] [tinyint] NOT NULL CONSTRAINT [DF_milestones_C]  DEFAULT ((0)),
	[name] [varchar](100)  NOT NULL CONSTRAINT [DF_milestones_name]  DEFAULT (N'undefined'),
 CONSTRAINT [PK_Milestones] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_Testplan] ON [milestones] 
(
	[testplan_id] ASC
) ON [PRIMARY]

CREATE TABLE [attachments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fk_id] [int] NOT NULL CONSTRAINT [DF_attachments_fk_id]  DEFAULT ((0)),
	[fk_table] [varchar](250)  NULL,
	[title] [varchar](250)  NULL,
	[description] [varchar](250)  NULL,
	[file_name] [varchar](250)  NOT NULL,
	[file_path] [varchar](250)  NOT NULL,
	[file_size] [int] NOT NULL CONSTRAINT [DF_attachments_file_size]  DEFAULT ((0)),
	[file_type] [varchar](250)  NOT NULL,
	[date_added] [datetime] NOT NULL,
	[content] [text]  NULL,
	[compression_type] [int] NOT NULL CONSTRAINT [DF_attachments_compression_type]  DEFAULT ((0)),
 CONSTRAINT [PK_attachments] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE TABLE [node_types](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[description] [varchar](100)  NOT NULL CONSTRAINT [DF_node_types_description]  DEFAULT (N'testproject'),
 CONSTRAINT [PK_node_types] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [nodes_hierarchy](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](100)  NULL,
	[parent_id] [int] NULL,
	[node_type_id] [int] NOT NULL CONSTRAINT [DF_nodes_hierarchy_node_type_id]  DEFAULT ((1)),
	[node_order] [int] NULL,
 CONSTRAINT [PK_nodes_hierarchy] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_pid_m_nodeorder] ON [nodes_hierarchy] 
(
	[parent_id] ASC,
	[node_order] ASC
) ON [PRIMARY]

CREATE TABLE [req_coverage](
	[req_id] [int] NOT NULL,
	[testcase_id] [int] NOT NULL
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_req_testcase] ON [req_coverage] 
(
	[req_id] ASC,
	[testcase_id] ASC
) ON [PRIMARY]

CREATE TABLE [req_specs](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[testproject_id] [int] NOT NULL,
	[title] [varchar](100)  NOT NULL,
	[scope] [text]  NULL,
	[total_req] [int] NOT NULL CONSTRAINT [DF_req_specs_total_req]  DEFAULT ((0)),
	[type] [char](1)  NOT NULL CONSTRAINT [DF_req_specs_type]  DEFAULT (N'n'),
	[author_id] [int] NULL,
	[creation_ts] [datetime] NOT NULL CONSTRAINT [DF_req_specs_creation_ts]  DEFAULT (getdate()),
	[modifier_id] [int] NULL,
	[modification_ts] [datetime] NULL,
 CONSTRAINT [PK_req_specs] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_testproject_id] ON [req_specs] 
(
	[testproject_id] ASC
) ON [PRIMARY]

CREATE TABLE [requirements](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[srs_id] [int] NOT NULL,
	[req_doc_id] [varchar](32)  NULL,
	[title] [varchar](100)  NOT NULL,
	[scope] [text]  NULL,
	[status] [char](1)  NOT NULL CONSTRAINT [DF_requirements_status]  DEFAULT (N'n'),
	[type] [char](1)  NULL,
	[node_order] [int] NOT NULL DEFAULT ((1)),
	[author_id] [int] NULL,
	[creation_ts] [datetime] NULL CONSTRAINT [DF_requirements_creation_ts]  DEFAULT (getdate()),
	[modifier_id] [int] NULL,
	[modification_ts] [datetime] NULL,
 CONSTRAINT [PK_requirements] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_requirements] ON [requirements] 
(
	[srs_id] ASC,
	[status] ASC
) ON [PRIMARY]

CREATE TABLE [role_rights](
	[role_id] [int] NOT NULL CONSTRAINT [DF_role_rights_role_id]  DEFAULT ((0)),
	[right_id] [int] NOT NULL CONSTRAINT [DF_role_rights_right_id]  DEFAULT ((0)),
 CONSTRAINT [PK_role_rights] PRIMARY KEY CLUSTERED 
(
	[role_id] ASC,
	[right_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [testcase_keywords](
	[testcase_id] [int] NOT NULL CONSTRAINT [DF_testcase_keywords_testcase_id]  DEFAULT ((0)),
	[keyword_id] [int] NOT NULL CONSTRAINT [DF_testcase_keywords_keyword_id]  DEFAULT ((0)),
 CONSTRAINT [PK_testcase_keywords] PRIMARY KEY CLUSTERED 
(
	[testcase_id] ASC,
	[keyword_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_testcase_keywords] ON [testcase_keywords] 
(
	[testcase_id] ASC
) ON [PRIMARY]

CREATE TABLE [tcversions](
	[id] [int] NOT NULL,
	[version] [smallint] NOT NULL CONSTRAINT [DF_tcversions_version]  DEFAULT ((1)),
	[summary] [text]  NULL,
	[steps] [text]  NULL,
	[expected_results] [text]  NOT NULL,
	[importance] [char](1)  NOT NULL CONSTRAINT [DF_tcversions_importance]  DEFAULT (N'M'),
	[author_id] [int] NULL,
	[creation_ts] [datetime] NOT NULL CONSTRAINT [DF_tcversions_creation_ts]  DEFAULT (getdate()),
	[updater_id] [int] NULL,
	[modification_ts] [datetime] NULL,
	[active] [tinyint] NOT NULL CONSTRAINT [DF_tcversions_active]  DEFAULT ((1)),
	[is_open] [tinyint] NOT NULL CONSTRAINT [DF_tcversions_open]  DEFAULT ((1)),
	[execution_type] [tinyint] NOT NULL CONSTRAINT [DF_tcversions_execution_type]  DEFAULT ((1)),
 CONSTRAINT [PK_tcversions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE TABLE [testplans](
	[id] [int] NOT NULL,
	[testproject_id] [int] NOT NULL CONSTRAINT [DF_testplans_testproject_id]  DEFAULT ((0)),
	[notes] [text]  NULL,
	[active] [tinyint] NOT NULL CONSTRAINT [DF_testplans_active]  DEFAULT ((1)),
	[is_open] [tinyint] NOT NULL CONSTRAINT [DF_testplans_open]  DEFAULT ((1)),
 CONSTRAINT [PK_testplans] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_testproject_id_active] ON [testplans] 
(
	[testproject_id] ASC,
	[active] ASC
) ON [PRIMARY]

CREATE TABLE [testprojects](
	[id] [int] NOT NULL,
	[notes] [text]  NULL,
	[color] [varchar](12)  NOT NULL CONSTRAINT [DF_testprojects_color]  DEFAULT (N'#9BD'),
	[active] [tinyint] NOT NULL CONSTRAINT [DF_testprojects_active]  DEFAULT ((1)),
	[option_reqs] [tinyint] NOT NULL CONSTRAINT [DF_testprojects_option_reqs]  DEFAULT ((0)),
	[option_priority] [tinyint] NOT NULL CONSTRAINT [DF_testprojects_option_priority]  DEFAULT ((1)),
 CONSTRAINT [PK_testprojects] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_id_active] ON [testprojects] 
(
	[id] ASC,
	[active] ASC
) ON [PRIMARY]

CREATE TABLE [testsuites](
	[id] [int] NOT NULL,
	[details] [text]  NULL,
 CONSTRAINT [PK_testsuites] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE TABLE [user_testproject_roles](
	[user_id] [int] NOT NULL CONSTRAINT [DF_user_testproject_roles_user_id]  DEFAULT ((0)),
	[testproject_id] [int] NOT NULL CONSTRAINT [DF_user_testproject_roles_testproject_id]  DEFAULT ((0)),
	[role_id] [int] NOT NULL CONSTRAINT [DF_user_testproject_roles_role_id]  DEFAULT ((0)),
 CONSTRAINT [PK_user_testproject_roles] PRIMARY KEY CLUSTERED 
(
	[user_id] ASC,
	[testproject_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [user_testplan_roles](
	[user_id] [int] NOT NULL,
	[testplan_id] [int] NOT NULL,
	[role_id] [int] NOT NULL,
 CONSTRAINT [PK_user_testplan_roles] PRIMARY KEY CLUSTERED 
(
	[user_id] ASC,
	[testplan_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[login] [varchar](30)  NOT NULL,
	[password] [varchar](32)  NOT NULL,
	[role_id] [int] NOT NULL CONSTRAINT [DF_users_role_id]  DEFAULT ((0)),
	[email] [varchar](100)  NOT NULL,
	[first] [varchar](30)  NOT NULL,
	[last] [varchar](30)  NOT NULL,
	[locale] [varchar](10)  NOT NULL CONSTRAINT [DF_users_locale]  DEFAULT (N'en_US'),
	[default_testproject_id] [int] NULL,
	[active] [tinyint] NOT NULL CONSTRAINT [DF_users_active]  DEFAULT ((1)),
 CONSTRAINT [PK_users] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [IX_users_login] ON [users] 
(
	[login] ASC
) ON [PRIMARY]

CREATE TABLE [cfield_design_values](
	[field_id] [int] NOT NULL,
	[node_id] [int] NOT NULL CONSTRAINT [DF_cfield_design_values_node_id]  DEFAULT ((0)),
	[value] [varchar](255)  NOT NULL CONSTRAINT [DF_cfield_design_values_value]  DEFAULT ((0)),
 CONSTRAINT [PK_cfield_design_values] PRIMARY KEY CLUSTERED 
(
	[field_id] ASC,
	[node_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE NONCLUSTERED INDEX [dx_cfield_design_values] ON [cfield_design_values] 
(
	[node_id] ASC
) ON [PRIMARY]

CREATE TABLE [assignment_types](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fk_table] [varchar](30)  NOT NULL,
	[description] [varchar](100)  NOT NULL CONSTRAINT [DF_assignment_types_description]  DEFAULT (N'unknown'),
 CONSTRAINT [PK_assignment_types] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [cfield_execution_values](
	[field_id] [int] NOT NULL CONSTRAINT [DF_cfield_execution_values_field_id]  DEFAULT ((0)),
	[execution_id] [int] NOT NULL CONSTRAINT [DF_cfield_execution_values_execution_id]  DEFAULT ((0)),
	[testplan_id] [int] NOT NULL CONSTRAINT [DF_cfield_execution_values_testplan_id]  DEFAULT ((0)),
	[tcversion_id] [int] NOT NULL CONSTRAINT [DF_cfield_execution_values_tcversion_id]  DEFAULT ((0)),
	[value] [varchar](255)  NOT NULL,
 CONSTRAINT [PK_cfield_execution_values] PRIMARY KEY CLUSTERED 
(
	[field_id] ASC,
	[execution_id] ASC,
	[testplan_id] ASC,
	[tcversion_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
