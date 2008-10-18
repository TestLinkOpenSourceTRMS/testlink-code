# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
# $Id: testlink_create_tables.sql,v 1.45 2008/10/18 17:48:39 franciscom Exp $
#
# SQL script - create db tables for TL - MySQL  
#
# ATTENTION: do not use a different naming convention, that one already in use.
#
# Rev :
#
# 20081018 - franciscom - renamed indexes on events table according to dev standards
# 20080810 - franciscom - BUGID 1650 (REQ)
#                         custom_fields.show_on_testplan_design
#                         custom_fields.enable_on_testplan_design
#                         new table cfield_testplan_design_values 
#
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
#
#	20080114 - mht - changes for priorities (add 2 + delete 1 table)
#			 add table for templates
#			 add table for usergroups
#
# 20080112 - franciscom - tcversions.tc_external_id
#                         testprojects.prefix
#                         testprojects.tc_counter
#
# 20080102 - franciscom - added changes for API feature (DB 1.2)
#                         added notes fields on db_version
#
# 20071202 - franciscom - added tcversions.execution_type
# 20071010 - franciscom - open -> is_open due to MSSQL reserved word problem
#
# 20070519 - franciscom - milestones table date -> target_date, because
#                         date is reserved word for Oracle
#
# 20070414 - franciscom - table requirements: added field node_order 
# 20070204 - franciscom - changes in tables priorities, risk_assignments 
#
# 20070131 - franciscom - requirements -> req_doc_id(32), 
#
# 20070120 - franciscom - following BUGID 458 ( really a new feature request)
#                         two new fields on builds table
#                         active, open
# 
# 20070113 - franciscom - table cfield_testprojects added fields
#                         required_on_design,required_on_execution
# 
# 20070106 - franciscom - again, and again  'en_GB' as default NOT en_US
#
# 20061228 - franciscom - added field active on table cfield_testprojects
#
# 20061224 - franciscom - changes to custom field related tables
#
# 20061220 - franciscom - added new indexes to solve performance problems
#                         executions, user_assignment, testplan_tcversions
#                         changed column order on index on testplan_tcversions
#
# 20061009 - franciscom - changes to index names for rights and roles tables
#                         added UNIQUE to req_doc_id KEY in table requirements
#
# 20060908 - franciscom - changes to user_assignments
#                         new tables assignment_types, assignment_status
#
# 20060815 - franciscom - changes to user_assignments
#                                    risk_assignments
#                         added object_keywords
#
# 20060715 - schlundus - changes to milestones table.
#  
# 20060711 - franciscom - added index pid_m_nodeorder on nodes_hierarchy
#	to improve performance
#
#
# 20060424 - franciscom - redoing asiel changes on users table due to wrong name
# 20060312 - franciscom
# changed bud_id column type to varchar(16) as requested by Asiel
# to avoid problems with JIRA bug tracking system.
#
# added name to nodes_hierarchy table to improve performance in
# tree operations
# 
# changed some int(11) to int(10)
# --------------------------------------------------------
#
#
CREATE TABLE `builds` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default 'undefined',
  `notes` text,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`testplan_id`,`name`),
  KEY `testplan_id` (`testplan_id`)
) DEFAULT CHARSET=utf8 COMMENT='Available builds';

CREATE TABLE `db_version` (
  `version` varchar(50) NOT NULL default 'unknown',
  `upgrade_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `notes` text
) DEFAULT CHARSET=utf8;


CREATE TABLE `events` (
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
  KEY `transaction_id` (`transaction_id`),
  KEY `fired_at` (`fired_at`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `execution_bugs` (
  `execution_id` int(10) unsigned NOT NULL default '0',
  `bug_id` varchar(16) NOT NULL default '0',
  PRIMARY KEY  (`execution_id`,`bug_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE `executions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `build_id` int(10) NOT NULL default '0',
  `tester_id` int(10) unsigned default NULL,
  `execution_ts` datetime default NULL,
  `status` char(1) default NULL,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `tcversion_id` int(10) unsigned NOT NULL default '0',
  `tcversion_number` smallint(5) unsigned NOT NULL default '1',
  `execution_type` tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated',
  `notes` text,
  PRIMARY KEY  (`id`),
  KEY `testplan_id_tcversion_id`(`testplan_id`,`tcversion_id`),
  KEY `execution_type`(`execution_type`)

) DEFAULT CHARSET=utf8;

CREATE TABLE `keywords` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keyword` varchar(100) NOT NULL default '',
  `testproject_id` int(10) unsigned NOT NULL default '0',
  `notes` text,
  PRIMARY KEY  (`id`),
  KEY `testproject_id` (`testproject_id`),
  KEY `keyword` (`keyword`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `milestones` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `target_date` date NOT NULL default '0000-00-00',
  `A` tinyint(3) unsigned NOT NULL default '0',
  `B` tinyint(3) unsigned NOT NULL default '0',
  `C` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default 'undefined',
  PRIMARY KEY  (`id`),
  KEY `testplan_id` (`testplan_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE `node_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default 'testproject',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `nodes_hierarchy` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `parent_id` int(10) unsigned default NULL,
  `node_type_id` int(10) unsigned NOT NULL default '1',
  `node_order` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `pid_m_nodeorder` (`parent_id`,`node_order`)
) DEFAULT CHARSET=utf8;


CREATE TABLE `req_coverage` (
  `req_id` int(10) NOT NULL,
  `testcase_id` int(10) NOT NULL,
  KEY `req_testcase` (`req_id`,`testcase_id`)
) DEFAULT CHARSET=utf8 COMMENT='relation test case ** requirements';

CREATE TABLE `req_specs` (
  `id` int(10) unsigned NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `scope` text,
  `total_req` int(10) NOT NULL default '0',
  `type` char(1) default 'n',
  `author_id` int(10) unsigned default NULL,
  `creation_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `modifier_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `testproject_id` (`testproject_id`)
) DEFAULT CHARSET=utf8 COMMENT='Dev. Documents (e.g. System Requirements Specification)';

/*   `node_order` int(10) unsigned NOT NULL default '1', */
CREATE TABLE `requirements` (
  `id` int(10) unsigned NOT NULL,
  `srs_id` int(10) unsigned NOT NULL,
  `req_doc_id` varchar(32) default NULL,
  `title` varchar(100) NOT NULL,
  `scope` text,
  `status` char(1) NOT NULL default 'V',
  `type` char(1) default NULL,
  `author_id` int(10) unsigned default NULL,
  `creation_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `modifier_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `srs_id` (`srs_id`,`status`),
  UNIQUE KEY `req_doc_id` (`srs_id`,`req_doc_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `rights` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `rights_descr` (`description`)
) DEFAULT CHARSET=utf8;


CREATE TABLE `risk_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `node_id` int(10) unsigned NOT NULL default '0',
  `risk` char(1) NOT NULL default '2',
  `importance` char(1) NOT NULL default 'M',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tp_node_id` (`testplan_id`,`node_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `user_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) unsigned NOT NULL default '1',
  `feature_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned default '0',
  `deadline_ts` datetime NULL,
  `assigner_id`  int(10) unsigned default '0',
  `creation_ts`  datetime NOT NULL default '0000-00-00 00:00:00',
  `status` int(10) unsigned default '1',
  PRIMARY KEY  (`id`),
  KEY `feature_id` (`feature_id`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `role_rights` (
  `role_id` int(10) NOT NULL default '0',
  `right_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`role_id`,`right_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `notes` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `roles_descr` (`description`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `testcase_keywords` (
  `testcase_id` int(10) unsigned NOT NULL default '0',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`testcase_id`,`keyword_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `tcversions` (
  `id` int(10) unsigned NOT NULL,
  `tc_external_id` int(10) unsigned NULL,
  `version` smallint(5) unsigned NOT NULL default '1',
  `summary` text,
  `steps` text,
  `expected_results` text,
  `importance` smallint(5) unsigned NOT NULL default '2',
  `author_id` int(10) unsigned default NULL,
  `creation_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `updater_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  `execution_type` tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE `testplan_tcversions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `tcversion_id` int(10) unsigned NOT NULL default '0',
  `node_order` int(10) unsigned NOT NULL default '1',
  `urgency` smallint(5) NOT NULL default '2',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tp_tcversion` (`testplan_id`,`tcversion_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE `testplans` (
  `id` int(10) unsigned NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL default '0',
  `notes` text,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `testproject_id_active` (`testproject_id`,`active`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `testprojects` (
  `id` int(10) unsigned NOT NULL,
  `notes` text,
  `color` varchar(12) NOT NULL default '#9BD',
  `active` tinyint(1) NOT NULL default '1',
  `option_reqs` tinyint(1) NOT NULL default '0',
  `option_priority` tinyint(1) NOT NULL default '0',
  `option_automation` tinyint(1) NOT NULL default '0',  
  `prefix` varchar(16) NOT NULL,
  `tc_counter` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_active` (`id`,`active`),
  UNIQUE KEY `prefix` (`prefix`)
  
) DEFAULT CHARSET=utf8;

CREATE TABLE `testsuites` (
  `id` int(10) unsigned NOT NULL,
  `details` text,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE  `transactions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `entry_point` varchar(45) NOT NULL default '',
  `start_time` int(10) unsigned NOT NULL default '0',
  `end_time` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `session_id` varchar(45) default NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
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
  UNIQUE KEY `login` (`login`)
) DEFAULT CHARSET=utf8 COMMENT='User information';

CREATE TABLE `cfield_node_types` (
  `field_id` int(10) NOT NULL default '0',
  `node_type_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`field_id`,`node_type_id`),
  KEY `idx_custom_fields_assign` (`node_type_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `cfield_testprojects` (
  `field_id` int(10) unsigned NOT NULL default '0',
  `testproject_id` int(10) unsigned NOT NULL default '0',
  `display_order` smallint(5) unsigned NOT NULL default '1',
  `active` tinyint(1) NOT NULL default '1',
  `required_on_design` tinyint(1) NOT NULL default '0',
  `required_on_execution` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`field_id`,`testproject_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `cfield_design_values` (
  `field_id` int(10) NOT NULL default '0',
  `node_id` int(10) NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`node_id`),
  KEY `idx_cfield_design_values` (`node_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `cfield_execution_values` (
  `field_id`     int(10) NOT NULL default '0',
  `execution_id` int(10) NOT NULL default '0',
  `testplan_id` int(10) NOT NULL default '0',
  `tcversion_id` int(10) NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`execution_id`,`testplan_id`,`tcversion_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `cfield_testplan_design_values` (
  `field_id` int(10) NOT NULL default '0',
  `link_id` int(10) NOT NULL default '0' COMMENT 'point to testplan_tcversion id',   
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`link_id`),
  KEY `idx_cfield_tplan_design_val` (`link_id`)
) DEFAULT CHARSET=utf8;


# 20080809 - franciscom - new fields to display custom fields in new areas
#                         test case linking to testplan (test plan design)
CREATE TABLE `custom_fields` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `label` varchar(64) NOT NULL default '' COMMENT 'label to display on user interface' ,
  `type` smallint(6) NOT NULL default '0',
  `possible_values` varchar(255) NOT NULL default '',
  `default_value` varchar(255) NOT NULL default '',
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
  UNIQUE KEY `idx_custom_fields_name` (`name`)
) DEFAULT CHARSET=utf8;


CREATE TABLE `user_testproject_roles` (
  `user_id` int(10) NOT NULL default '0',
  `testproject_id` int(10) NOT NULL default '0',
  `role_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`testproject_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `user_testplan_roles` (
  `user_id` int(10) NOT NULL default '0',
  `testplan_id` int(10) NOT NULL default '0',
  `role_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`testplan_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `attachments` (
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

CREATE TABLE `object_keywords` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_id` int(10) unsigned NOT NULL default '0',
  `fk_table` varchar(30) default '',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8; 


CREATE TABLE `assignment_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_table` varchar(30) default '',
  `description` varchar(100) NOT NULL default 'unknown',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `assignment_status` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default 'unknown',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

/* mht - 0000774: Global Project Template */
CREATE TABLE `text_templates` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` smallint(5) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `template_data` text,
  `author_id` int(10) unsigned default NULL,
  `creation_ts` datetime NOT NULL default '1900-00-00 01:00:00',
  `is_public` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_text_templates` (`type`,`title`)
) DEFAULT CHARSET=utf8 COMMENT='Global Project Templates';


/* mht - group users for large companies */
CREATE TABLE `user_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY (`title`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `user_group_assign` (
  `usergroup_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `idx_user_group_assign` (`usergroup_id`,`user_id`)
) DEFAULT CHARSET=utf8;
