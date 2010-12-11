# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
# ---------------------------------------------------------------------------------------
# $Id: testlink_create_tables.sql,v 1.78.2.2 2010/12/11 17:25:44 franciscom Exp $
#
# SQL script - create all DB tables for MySQL
# tables are in alphabetic order  
#
# ATTENTION: do not use a different naming convention, that one already in use.
#
# IMPORTANT NOTE:
# each NEW TABLE added here NEED TO BE DEFINED in object.class.php getDBTables()
#
# IMPORTANT NOTE - DATETIME or TIMESTAMP
# Extracted from MySQL Manual
#
# The TIMESTAMP column type provides a type that you can use to automatically 
# mark INSERT or UPDATE operations with the current date and time. 
# If you have multiple TIMESTAMP columns in a table, only the first one is updated automatically.
#
# Knowing this is clear that we can use in interchangable way DATETIME or TIMESTAMP
#
# Naming convention for column regarding date/time of creation or change
#
# Right or wrong from TL 1.7 we have used
#
# creation_ts
# modification_ts
#
# Then no other naming convention has to be used as:
# create_ts, modified_ts
#
# CRITIC:
# Because this file will be processed during installation doing text replaces
# to add TABLE PREFIX NAME, any NEW DDL CODE added must be respect present
# convention regarding case and spaces between DDL keywords.
# 
# ---------------------------------------------------------------------------------------
# Revisions:
#
# 20101211 - franciscom - BUGID 4056: Requirement Revisioning
#            req_versions removed version from index to allow easy creation of FK
#            (in future) from req_revisions
#
# 20101204 - franciscom - BUGID 4070 executions index
# 20100705 - asimon - added new column build_id to user_assignments
# 20100308 - Julian - req_relations table added
# 20100124 - franciscom - is_open,active added to req_versions table
# 20100113 - franciscom - doc_id increased to 64 and setted NOT NULL
# 20100106 - franciscom - Test Case Step feature
# 
# 20091228 - franciscom - changes to requirements table and new table req_versions
#                         to implement requirement versioning
#                         req_doc_id and doc_id => changed to NOT NULL
#                         
# 20091221 - havlatm - infrastructure table added.
#                      tcversions.layout added 
#                      testproject.options added
# 20091220 - franciscom - fields removed form req_spec and requirements "title"
# 20091119 - franciscom - requirements table - new field expected_coverage
# 20091119 - franciscom - req_specs added doc_id field
# 20090919 - franciscom - custom field values increased to 4000
# 20090910 - franciscom - added milestones.start_date
# 20090831 - franciscom - added preconditions
# 20090806 - franciscom - added testplan_platforms,platforms,platform_id to tables
# 20090717 - franciscom - added cfield_testprojects.location field
# 20090512 - franciscom - BUGID - builds release_date
#                         BUGID - is_public attribute for testprojects and testplans
# 20090411 - franciscom - BUGID 2369 - testplan_tcversions
# 20090103 - franciscom - changed case of unique fields in UPPER CASE (milestones table A,B,C)
# 20090103 - franciscom - milestones table - added new unique index
# 20081018 - franciscom - renamed indexes on events table according to dev standards
# 20080810 - franciscom - BUGID 1650 (REQ)
#                         custom_fields.show_on_testplan_design
#                         custom_fields.enable_on_testplan_design
#                         new table cfield_testplan_design_values 
# 20080720 - franciscom - fixed bug on text_templates definition
# 20080703 - franciscom - removed MyISAM on create table
# 20080701 - havlatm - redefine test prioritization fields
# 20080628 - franciscom - create_ts -> creation_ts
# 20080528 - franciscom - BUGID 1504 - added executions.tcversion_number
# 20080331 - franciscom - testplan_tcversions added node_order
# 20080226 - franciscom - removed autoincrement id on req_spec, requirements
# 20080119 - franciscom - testprojects.option_automation
#	20080117 - schlundus - added table for events and transactions
# 20080117 - franciscom - prefix size increased (16)
# 20080114 - franciscom - usergroup_id -> id
#	20080114 - mht - changes for priorities (add 2 + delete 1 table)
#			 add table for templates
#			 add table for usergroups
# 20080112 - franciscom - tcversions.tc_external_id,testprojects.prefix
#                         testprojects.tc_counter
# 20080102 - franciscom - added changes for API feature (DB 1.2)
#                         added notes fields on db_version
# 20071202 - franciscom - added tcversions.execution_type
# 20071010 - franciscom - open -> is_open due to MSSQL reserved word problem
# 20070519 - franciscom - milestones table date -> target_date, because
#                         date is reserved word for Oracle
# 20070414 - franciscom - table requirements: added field node_order 
# 20070204 - franciscom - changes in tables priorities, risk_assignments 
# 20070131 - franciscom - requirements -> req_doc_id(32), 
# 20070120 - franciscom - following BUGID 458 ( really a new feature request)
#                         two new fields on builds table: active, open
# 20070113 - franciscom - table cfield_testprojects added fields
#                         required_on_design,required_on_execution
# 20070106 - franciscom - again, and again  'en_GB' as default NOT en_US
# 20061228 - franciscom - added field active on table cfield_testprojects
# 20061224 - franciscom - changes to custom field related tables
# 20061220 - franciscom - added new indexes to solve performance problems
#                         executions, user_assignment, testplan_tcversions
#                         changed column order on index on testplan_tcversions
# 20061009 - franciscom - changes to index names for rights and roles tables
#                         added UNIQUE to req_doc_id KEY in table requirements
# 20060908 - franciscom - changes to user_assignments
#                         new tables assignment_types, assignment_status
# 20060815 - franciscom - changes to user_assignments, risk_assignments, added object_keywords
# 20060715 - schlundus - changes to milestones table.
# 20060711 - franciscom - added index pid_m_nodeorder on nodes_hierarchy
#						  to improve performance
# 20060424 - franciscom - redoing asiel changes on users table due to wrong name
# 20060312 - franciscom - changed bud_id column type to varchar(16) as requested by Asiel
# 						  to avoid problems with JIRA bug tracking system.
# 						  added name to nodes_hierarchy table to improve performance in
# 						  tree operations changed some int(11) to int(10)
#
# ---------------------------------------------------------------------------------------


CREATE TABLE /*prefix*/assignment_types (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_table` varchar(30) default '',
  `description` varchar(100) NOT NULL default 'unknown',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/assignment_status (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default 'unknown',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/attachments (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_id` int(10) unsigned NOT NULL default '0',
  `fk_table` varchar(250) default '',
  `title` varchar(250) default '',
  `description` varchar(250) default '',
  `file_name` varchar(250) NOT NULL default '',
  `file_path` varchar(250) default '',
  `file_size` int(11) NOT NULL default '0',
  `file_type` varchar(250) NOT NULL default '',
  `date_added` datetime NOT NULL default '0000-00-00 00:00:00',
  `content` longblob,
  `compression_type` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8; 


CREATE TABLE /*prefix*/builds (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default 'undefined',
  `notes` text,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  `author_id` int(10) unsigned default NULL,
  `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `release_date` date NULL,
  `closed_on_date` date NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/name (`testplan_id`,`name`),
  KEY /*prefix*/testplan_id (`testplan_id`)
) DEFAULT CHARSET=utf8 COMMENT='Available builds';


CREATE TABLE /*prefix*/cfield_design_values (
  `field_id` int(10) NOT NULL default '0',
  `node_id` int(10) NOT NULL default '0',
  `value` varchar(4000) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`node_id`),
  KEY /*prefix*/idx_cfield_design_values (`node_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/cfield_execution_values (
  `field_id`     int(10) NOT NULL default '0',
  `execution_id` int(10) NOT NULL default '0',
  `testplan_id` int(10) NOT NULL default '0',
  `tcversion_id` int(10) NOT NULL default '0',
  `value` varchar(4000) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`execution_id`,`testplan_id`,`tcversion_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/cfield_node_types (
  `field_id` int(10) NOT NULL default '0',
  `node_type_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`field_id`,`node_type_id`),
  KEY /*prefix*/idx_custom_fields_assign (`node_type_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/cfield_testprojects (
  `field_id` int(10) unsigned NOT NULL default '0',
  `testproject_id` int(10) unsigned NOT NULL default '0',
  `display_order` smallint(5) unsigned NOT NULL default '1',
  `location` smallint(5) unsigned NOT NULL default '1',
  `active` tinyint(1) NOT NULL default '1',
  `required_on_design` tinyint(1) NOT NULL default '0',
  `required_on_execution` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`field_id`,`testproject_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/cfield_testplan_design_values (
  `field_id` int(10) NOT NULL default '0',
  `link_id` int(10) NOT NULL default '0' COMMENT 'point to testplan_tcversion id',   
  `value` varchar(4000) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`link_id`),
  KEY /*prefix*/idx_cfield_tplan_design_val (`link_id`)
) DEFAULT CHARSET=utf8;


# 20080809 - franciscom - new fields to display custom fields in new areas
#                         test case linking to testplan (test plan design)
CREATE TABLE /*prefix*/custom_fields (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `label` varchar(64) NOT NULL default '' COMMENT 'label to display on user interface' ,
  `type` smallint(6) NOT NULL default '0',
  `possible_values` varchar(4000) NOT NULL default '',
  `default_value` varchar(4000) NOT NULL default '',
  `valid_regexp` varchar(255) NOT NULL default '',
  `length_min` int(10) NOT NULL default '0',
  `length_max` int(10) NOT NULL default '0',
  `show_on_design` tinyint(3) unsigned NOT NULL default '1' COMMENT '1=> show it during specification design',
  `enable_on_design` tinyint(3) unsigned NOT NULL default '1' COMMENT '1=> user can write/manage it during specification design',
  `show_on_execution` tinyint(3) unsigned NOT NULL default '0' COMMENT '1=> show it during test case execution',
  `enable_on_execution` tinyint(3) unsigned NOT NULL default '0' COMMENT '1=> user can write/manage it during test case execution',
  `show_on_testplan_design` tinyint(3) unsigned NOT NULL default '0' ,
  `enable_on_testplan_design` tinyint(3) unsigned NOT NULL default '0' ,
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/idx_custom_fields_name (`name`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/db_version (
  `version` varchar(50) NOT NULL default 'unknown',
  `upgrade_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `notes` text
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/events (
  `id` int(10) unsigned NOT NULL auto_increment,
  `transaction_id` int(10) unsigned NOT NULL default '0',
  `log_level` smallint(5) unsigned NOT NULL default '0',
  `source` varchar(45) default NULL,
  `description` text NOT NULL,
  `fired_at` int(10) unsigned NOT NULL default '0',
  `activity` varchar(45) default NULL,
  `object_id` int(10) unsigned default NULL,
  `object_type` varchar(45) default NULL,
  PRIMARY KEY  (`id`),
  KEY /*prefix*/transaction_id (`transaction_id`),
  KEY /*prefix*/fired_at (`fired_at`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/execution_bugs (
  `execution_id` int(10) unsigned NOT NULL default '0',
  `bug_id` varchar(16) NOT NULL default '0',
  PRIMARY KEY  (`execution_id`,`bug_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/executions (
  id int(10) unsigned NOT NULL auto_increment,
  build_id int(10) NOT NULL default '0',
  tester_id int(10) unsigned default NULL,
  execution_ts datetime default NULL,
  status char(1) default NULL,
  testplan_id int(10) unsigned NOT NULL default '0',
  tcversion_id int(10) unsigned NOT NULL default '0',
  tcversion_number smallint(5) unsigned NOT NULL default '1',
  platform_id int(10) unsigned NOT NULL default '0',
  execution_type tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated',
  notes text,
  PRIMARY KEY  (id),
  KEY /*prefix*/executions_idx1(testplan_id,tcversion_id,platform_id,build_id),
  KEY /*prefix*/executions_idx2(execution_type)

) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/inventory (
  id int(10) unsigned NOT NULL auto_increment,
	`testproject_id` INT( 10 ) UNSIGNED NOT NULL ,
	`owner_id` INT(10) UNSIGNED NOT NULL ,
	`name` VARCHAR(255) NOT NULL ,
	`ipaddress` VARCHAR(255)  NOT NULL ,
	`content` TEXT NULL ,
	`creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modification_ts` TIMESTAMP NOT NULL,
	PRIMARY KEY (`id`),
	KEY /*prefix*/inventory_idx1 (`testproject_id`)
) DEFAULT CHARSET=utf8; 


CREATE TABLE /*prefix*/keywords (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keyword` varchar(100) NOT NULL default '',
  `testproject_id` int(10) unsigned NOT NULL default '0',
  `notes` text,
  PRIMARY KEY  (`id`),
  KEY /*prefix*/testproject_id (`testproject_id`),
  KEY /*prefix*/keyword (`keyword`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/milestones (
  id int(10) unsigned NOT NULL auto_increment,
  testplan_id int(10) unsigned NOT NULL default '0',
  target_date date NULL,
  start_date date NOT NULL default '0000-00-00',
  a tinyint(3) unsigned NOT NULL default '0',
  b tinyint(3) unsigned NOT NULL default '0',
  c tinyint(3) unsigned NOT NULL default '0',
  name varchar(100) NOT NULL default 'undefined',
  PRIMARY KEY  (id),
  KEY /*prefix*/testplan_id (`testplan_id`),
  UNIQUE KEY /*prefix*/name_testplan_id (`name`,`testplan_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/node_types (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default 'testproject',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/nodes_hierarchy (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `parent_id` int(10) unsigned default NULL,
  `node_type_id` int(10) unsigned NOT NULL default '1',
  `node_order` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY /*prefix*/pid_m_nodeorder (`parent_id`,`node_order`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/platforms (
  id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  testproject_id int(10) UNSIGNED NOT NULL,
  notes text NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY /*prefix*/idx_platforms (testproject_id,name)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/req_coverage (
  `req_id` int(10) NOT NULL,
  `testcase_id` int(10) NOT NULL,
  KEY /*prefix*/req_testcase (`req_id`,`testcase_id`)
) DEFAULT CHARSET=utf8 COMMENT='relation test case ** requirements';


CREATE TABLE /*prefix*/req_specs (
  `id` int(10) unsigned NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL,
  `doc_id` varchar(64) NOT NULL,
  `scope` text,
  `total_req` int(10) NOT NULL default '0',
  `type` char(1) default 'n',
  `author_id` int(10) unsigned default NULL,
   creation_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY /*prefix*/testproject_id (`testproject_id`),
  UNIQUE KEY /*prefix*/req_spec_uk1(`doc_id`,`testproject_id`)
) DEFAULT CHARSET=utf8 COMMENT='Dev. Documents (e.g. System Requirements Specification)';

CREATE TABLE /*prefix*/requirements (
  `id` int(10) unsigned NOT NULL,
  `srs_id` int(10) unsigned NOT NULL,
  `req_doc_id` varchar(64) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/requirements_req_doc_id (`srs_id`,`req_doc_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/req_versions (
  `id` int(10) unsigned NOT NULL,
  `version` smallint(5) unsigned NOT NULL default '1',
  `revision` smallint(5) unsigned NOT NULL default '1', 
  `scope` text,
  `status` char(1) NOT NULL default 'V',
  `type` char(1) default NULL,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  `expected_coverage` int(10) NOT NULL default '1',
  `author_id` int(10) unsigned default NULL,
  `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `log_message` text,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/req_relations (
  `id` int(10) unsigned NOT NULL auto_increment,
  `source_id` int(10) unsigned NOT NULL,
  `destination_id` int(10) unsigned NOT NULL,
  `relation_type` smallint(5) unsigned NOT NULL default '1',
  `author_id` int(10) unsigned default NULL,
  `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/rights (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/rights_descr (`description`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/risk_assignments (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `node_id` int(10) unsigned NOT NULL default '0',
  `risk` char(1) NOT NULL default '2',
  `importance` char(1) NOT NULL default 'M',
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/risk_assignments_tplan_node_id (`testplan_id`,`node_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/role_rights (
  `role_id` int(10) NOT NULL default '0',
  `right_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`role_id`,`right_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/roles (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `notes` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/role_rights_roles_descr (`description`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/testcase_keywords (
  `testcase_id` int(10) unsigned NOT NULL default '0',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`testcase_id`,`keyword_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/tcversions (
  `id` int(10) unsigned NOT NULL,
  `tc_external_id` int(10) unsigned NULL,
  `version` smallint(5) unsigned NOT NULL default '1',
  `layout` smallint(5) unsigned NOT NULL default '1',
  `status` smallint(5) unsigned NOT NULL default '1',
  `summary` text,
  `preconditions` text,
  `importance` smallint(5) unsigned NOT NULL default '2',
  `author_id` int(10) unsigned default NULL,
  `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updater_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  `execution_type` tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/tcsteps (  
  id int(10) unsigned NOT NULL,
  step_number INT NOT NULL DEFAULT '1',
  actions TEXT,
  expected_results TEXT,
  active tinyint(1) NOT NULL default '1',
  execution_type tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated',
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/testplan_tcversions (
  id int(10) unsigned NOT NULL auto_increment,
  testplan_id int(10) unsigned NOT NULL default '0',
  tcversion_id int(10) unsigned NOT NULL default '0',
  node_order int(10) unsigned NOT NULL default '1',
  urgency smallint(5) NOT NULL default '2',
  platform_id int(10) unsigned NOT NULL default '0',
  author_id int(10) unsigned default NULL,
  creation_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY /*prefix*/testplan_tcversions_tplan_tcversion (testplan_id,tcversion_id,platform_id)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/testplans (
  `id` int(10) unsigned NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL default '0',
  `notes` text,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  `is_public` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY /*prefix*/testplans_testproject_id_active (`testproject_id`,`active`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/testplan_platforms (
  id int(10) unsigned NOT NULL auto_increment,
  testplan_id int(10) unsigned NOT NULL,
  platform_id int(10) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY /*prefix*/idx_testplan_platforms(testplan_id,platform_id)
) DEFAULT CHARSET=utf8 COMMENT='Connects a testplan with platforms';


CREATE TABLE /*prefix*/testprojects (
  `id` int(10) unsigned NOT NULL,
  `notes` text,
  `color` varchar(12) NOT NULL default '#9BD',
  `active` tinyint(1) NOT NULL default '1',
  `option_reqs` tinyint(1) NOT NULL default '0',
  `option_priority` tinyint(1) NOT NULL default '0',
  `option_automation` tinyint(1) NOT NULL default '0',  
  `options` text,
  `prefix` varchar(16) NOT NULL,
  `tc_counter` int(10) unsigned NOT NULL default '0',
  `is_public` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY /*prefix*/testprojects_id_active (`id`,`active`),
  UNIQUE KEY /*prefix*/testprojects_prefix (`prefix`)
  
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/testsuites (
  `id` int(10) unsigned NOT NULL,
  `details` text,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/transactions (
  `id` int(10) unsigned NOT NULL auto_increment,
  `entry_point` varchar(45) NOT NULL default '',
  `start_time` int(10) unsigned NOT NULL default '0',
  `end_time` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `session_id` varchar(45) default NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/user_assignments (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) unsigned NOT NULL default '1',
  `feature_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned default '0',
  `build_id` int(10) unsigned default '0',
  `deadline_ts` datetime NULL,
  `assigner_id`  int(10) unsigned default '0',
  `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(10) unsigned default '1',
  PRIMARY KEY  (`id`),
  KEY /*prefix*/user_assignments_feature_id (`feature_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/users (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(30) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `role_id` int(10) unsigned NOT NULL default '0',
  `email` varchar(100) NOT NULL default '',
  `first` varchar(30) NOT NULL default '',
  `last` varchar(30) NOT NULL default '',
  `locale` varchar(10) NOT NULL default 'en_GB',
  `default_testproject_id` int(10) default NULL,
  `active` tinyint(1) NOT NULL default '1',
  `script_key` varchar(32) NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/users_login (`login`)
) DEFAULT CHARSET=utf8 COMMENT='User information';


CREATE TABLE /*prefix*/user_testproject_roles (
  `user_id` int(10) NOT NULL default '0',
  `testproject_id` int(10) NOT NULL default '0',
  `role_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`testproject_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/user_testplan_roles (
  `user_id` int(10) NOT NULL default '0',
  `testplan_id` int(10) NOT NULL default '0',
  `role_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`testplan_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/object_keywords (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_id` int(10) unsigned NOT NULL default '0',
  `fk_table` varchar(30) default '',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8; 


# not used - group users for large companies 
CREATE TABLE /*prefix*/user_group (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/idx_user_group (`title`)
) DEFAULT CHARSET=utf8;


# not used - group users for large companies 
CREATE TABLE /*prefix*/user_group_assign (
  `usergroup_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  UNIQUE KEY /*prefix*/idx_user_group_assign (`usergroup_id`,`user_id`)
) DEFAULT CHARSET=utf8;




# ----------------------------------------------------------------------------------
# BUGID 4056
# ----------------------------------------------------------------------------------
CREATE TABLE /*prefix*/req_revisions (
  `parent_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `revision` smallint(5) unsigned NOT NULL default '1',
  `req_doc_id` varchar(64) NULL,   /* it's OK to allow a simple update query on code */
  `name` varchar(100) NULL,
  `scope` text,
  `status` char(1) NOT NULL default 'V',
  `type` char(1) default NULL,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  `expected_coverage` int(10) NOT NULL default '1',
  `log_message` text,
  `author_id` int(10) unsigned default NULL,
  `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/req_revisions_uidx1 (`parent_id`,`revision`)
) DEFAULT CHARSET=utf8;
