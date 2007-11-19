-- phpMyAdmin SQL Dump
-- version 2.10.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Nov 07, 2007 at 04:13 PM
-- Server version: 5.0.41
-- PHP Version: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `testlink_development`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `api_developer_keys`
-- 

DROP TABLE IF EXISTS `api_developer_keys`;
CREATE TABLE `api_developer_keys` (
  `id` int(11) NOT NULL auto_increment,
  `developer_key` varchar(32) character set latin1 NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- 
-- Dumping data for table `api_developer_keys`
-- 

INSERT INTO `api_developer_keys` (`id`, `developer_key`, `user_id`) VALUES 
(5, '2f64aaa8d0ac693d0d7c934fe20c68b6', 2),
(1, 'validTestDevKey', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `assignment_status`
-- 

DROP TABLE IF EXISTS `assignment_status`;
CREATE TABLE `assignment_status` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default 'unknown',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- 
-- Dumping data for table `assignment_status`
-- 

INSERT INTO `assignment_status` (`id`, `description`) VALUES 
(1, 'open'),
(2, 'closed'),
(3, 'completed'),
(4, 'todo_urgent'),
(5, 'todo');

-- --------------------------------------------------------

-- 
-- Table structure for table `assignment_types`
-- 

DROP TABLE IF EXISTS `assignment_types`;
CREATE TABLE `assignment_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_table` varchar(30) default '',
  `description` varchar(100) NOT NULL default 'unknown',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `assignment_types`
-- 

INSERT INTO `assignment_types` (`id`, `fk_table`, `description`) VALUES 
(1, 'testplan_tcversions', 'testcase_execution'),
(2, 'tcversions', 'testcase_review');

-- --------------------------------------------------------

-- 
-- Table structure for table `attachments`
-- 

DROP TABLE IF EXISTS `attachments`;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `attachments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `builds`
-- 

DROP TABLE IF EXISTS `builds`;
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Available builds' AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `builds`
-- 

INSERT INTO `builds` (`id`, `testplan_id`, `name`, `notes`, `active`, `is_open`) VALUES 
(1, 2, 'A build for the test plan', '<p>Have to build some time</p>', 1, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `cfield_design_values`
-- 

DROP TABLE IF EXISTS `cfield_design_values`;
CREATE TABLE `cfield_design_values` (
  `field_id` int(10) NOT NULL default '0',
  `node_id` int(10) NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`node_id`),
  KEY `idx_cfield_design_values` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `cfield_design_values`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `cfield_execution_values`
-- 

DROP TABLE IF EXISTS `cfield_execution_values`;
CREATE TABLE `cfield_execution_values` (
  `field_id` int(10) NOT NULL default '0',
  `execution_id` int(10) NOT NULL default '0',
  `testplan_id` int(10) NOT NULL default '0',
  `tcversion_id` int(10) NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`execution_id`,`testplan_id`,`tcversion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `cfield_execution_values`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `cfield_node_types`
-- 

DROP TABLE IF EXISTS `cfield_node_types`;
CREATE TABLE `cfield_node_types` (
  `field_id` int(10) NOT NULL default '0',
  `node_type_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`field_id`,`node_type_id`),
  KEY `idx_custom_fields_assign` (`node_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `cfield_node_types`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `cfield_testprojects`
-- 

DROP TABLE IF EXISTS `cfield_testprojects`;
CREATE TABLE `cfield_testprojects` (
  `field_id` int(10) unsigned NOT NULL default '0',
  `testproject_id` int(10) unsigned NOT NULL default '0',
  `display_order` smallint(5) unsigned NOT NULL default '1',
  `active` tinyint(1) NOT NULL default '1',
  `required_on_design` tinyint(1) NOT NULL default '0',
  `required_on_execution` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`field_id`,`testproject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `cfield_testprojects`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `custom_fields`
-- 

DROP TABLE IF EXISTS `custom_fields`;
CREATE TABLE `custom_fields` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `label` varchar(64) NOT NULL default '' COMMENT 'label to display on user interface',
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
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_custom_fields_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `custom_fields`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `db_version`
-- 

DROP TABLE IF EXISTS `db_version`;
CREATE TABLE `db_version` (
  `version` varchar(50) NOT NULL default 'unknown',
  `upgrade_ts` datetime NOT NULL default '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `db_version`
-- 

INSERT INTO `db_version` (`version`, `upgrade_ts`) VALUES 
('DB 1.1', '2007-11-01 14:06:11');

-- --------------------------------------------------------

-- 
-- Table structure for table `executions`
-- 

DROP TABLE IF EXISTS `executions`;
CREATE TABLE `executions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `build_id` int(10) NOT NULL default '0',
  `tester_id` int(10) unsigned default NULL,
  `execution_ts` datetime default NULL,
  `status` char(1) default NULL,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `tcversion_id` int(10) unsigned NOT NULL default '0',
  `notes` text,
  `automated` tinyint(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `testplan_id_tcversion_id` (`testplan_id`,`tcversion_id`),
  KEY `automated` (`automated`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `executions`
-- 

INSERT INTO `executions` (`id`, `build_id`, `tester_id`, `execution_ts`, `status`, `testplan_id`, `tcversion_id`, `notes`, `automated`) VALUES 
(1, 1, 1, '2007-11-07 15:00:29', 'f', 2, 8, '', NULL),
(2, 1, 1, '2007-11-07 15:00:36', 'p', 2, 7, '', NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `execution_bugs`
-- 

DROP TABLE IF EXISTS `execution_bugs`;
CREATE TABLE `execution_bugs` (
  `execution_id` int(10) unsigned NOT NULL default '0',
  `bug_id` varchar(16) NOT NULL default '0',
  PRIMARY KEY  (`execution_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `execution_bugs`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `keywords`
-- 

DROP TABLE IF EXISTS `keywords`;
CREATE TABLE `keywords` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keyword` varchar(100) NOT NULL default '',
  `testproject_id` int(10) unsigned NOT NULL default '0',
  `notes` text,
  PRIMARY KEY  (`id`),
  KEY `testproject_id` (`testproject_id`),
  KEY `keyword` (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `keywords`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `milestones`
-- 

DROP TABLE IF EXISTS `milestones`;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `milestones`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `nodes_hierarchy`
-- 

DROP TABLE IF EXISTS `nodes_hierarchy`;
CREATE TABLE `nodes_hierarchy` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `parent_id` int(10) unsigned default NULL,
  `node_type_id` int(10) unsigned NOT NULL default '1',
  `node_order` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `pid_m_nodeorder` (`parent_id`,`node_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- 
-- Dumping data for table `nodes_hierarchy`
-- 

INSERT INTO `nodes_hierarchy` (`id`, `name`, `parent_id`, `node_type_id`, `node_order`) VALUES 
(1, 'Test Project', NULL, 1, 1),
(2, 'A test plan for testing', 1, 5, 0),
(3, 'Top Level Suite', 1, 2, 1),
(4, 'First test case version 3', 3, 3, 100),
(5, '', 4, 4, 0),
(6, 'Another test case', 3, 3, 100),
(7, '', 6, 4, 0),
(8, '', 4, 4, 0),
(9, '', 4, 4, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `node_types`
-- 

DROP TABLE IF EXISTS `node_types`;
CREATE TABLE `node_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default 'testproject',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `node_types`
-- 

INSERT INTO `node_types` (`id`, `description`) VALUES 
(1, 'testproject'),
(2, 'testsuite'),
(3, 'testcase'),
(4, 'testcase_version'),
(5, 'testplan'),
(6, 'requirement_spec'),
(7, 'requirement');

-- --------------------------------------------------------

-- 
-- Table structure for table `object_keywords`
-- 

DROP TABLE IF EXISTS `object_keywords`;
CREATE TABLE `object_keywords` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_id` int(10) unsigned NOT NULL default '0',
  `fk_table` varchar(30) default '',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `object_keywords`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `priorities`
-- 

DROP TABLE IF EXISTS `priorities`;
CREATE TABLE `priorities` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `priority` char(1) NOT NULL default 'B',
  `risk` char(1) NOT NULL default '2',
  `importance` char(1) NOT NULL default 'M',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `prio_risk_imp` (`testplan_id`,`priority`,`risk`,`importance`),
  KEY `testplan_id` (`testplan_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- 
-- Dumping data for table `priorities`
-- 

INSERT INTO `priorities` (`id`, `testplan_id`, `priority`, `risk`, `importance`) VALUES 
(1, 2, 'B', '1', 'H'),
(2, 2, 'B', '1', 'M'),
(3, 2, 'B', '1', 'L'),
(4, 2, 'B', '2', 'H'),
(5, 2, 'B', '2', 'M'),
(6, 2, 'B', '2', 'L'),
(7, 2, 'B', '3', 'H'),
(8, 2, 'B', '3', 'M'),
(9, 2, 'B', '3', 'L');

-- --------------------------------------------------------

-- 
-- Table structure for table `requirements`
-- 

DROP TABLE IF EXISTS `requirements`;
CREATE TABLE `requirements` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `srs_id` int(10) unsigned NOT NULL,
  `req_doc_id` varchar(32) default NULL,
  `title` varchar(100) NOT NULL,
  `scope` text,
  `status` char(1) NOT NULL default 'V',
  `type` char(1) default NULL,
  `node_order` int(10) unsigned NOT NULL default '0',
  `author_id` int(10) unsigned default NULL,
  `creation_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `modifier_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `req_doc_id` (`srs_id`,`req_doc_id`),
  KEY `srs_id` (`srs_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `requirements`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `req_coverage`
-- 

DROP TABLE IF EXISTS `req_coverage`;
CREATE TABLE `req_coverage` (
  `req_id` int(10) NOT NULL,
  `testcase_id` int(10) NOT NULL,
  KEY `req_testcase` (`req_id`,`testcase_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='relation test case ** requirements';

-- 
-- Dumping data for table `req_coverage`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `req_specs`
-- 

DROP TABLE IF EXISTS `req_specs`;
CREATE TABLE `req_specs` (
  `id` int(10) unsigned NOT NULL auto_increment,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Dev. Documents (e.g. System Requirements Specification)' AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `req_specs`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `rights`
-- 

DROP TABLE IF EXISTS `rights`;
CREATE TABLE `rights` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `rights_descr` (`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- 
-- Dumping data for table `rights`
-- 

INSERT INTO `rights` (`id`, `description`) VALUES 
(1, 'testplan_execute'),
(2, 'testplan_create_build'),
(3, 'testplan_metrics'),
(4, 'testplan_planning'),
(5, 'testplan_user_role_assignment'),
(6, 'mgt_view_tc'),
(7, 'mgt_modify_tc'),
(8, 'mgt_view_key'),
(9, 'mgt_modify_key'),
(10, 'mgt_view_req'),
(11, 'mgt_modify_req'),
(12, 'mgt_modify_product'),
(13, 'mgt_users'),
(14, 'role_management'),
(15, 'user_role_assignment'),
(16, 'mgt_testplan_create'),
(17, 'cfield_view'),
(18, 'cfield_management');

-- --------------------------------------------------------

-- 
-- Table structure for table `risk_assignments`
-- 

DROP TABLE IF EXISTS `risk_assignments`;
CREATE TABLE `risk_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `node_id` int(10) unsigned NOT NULL default '0',
  `risk` char(1) NOT NULL default '2',
  `importance` char(1) NOT NULL default 'M',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tp_node_id` (`testplan_id`,`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `risk_assignments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `roles`
-- 

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `notes` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `roles_descr` (`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- 
-- Dumping data for table `roles`
-- 

INSERT INTO `roles` (`id`, `description`, `notes`) VALUES 
(8, 'admin', NULL),
(9, 'leader', NULL),
(6, 'senior tester', NULL),
(7, 'tester', NULL),
(5, 'guest', NULL),
(4, 'test designer', NULL),
(3, '<no rights>', NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `role_rights`
-- 

DROP TABLE IF EXISTS `role_rights`;
CREATE TABLE `role_rights` (
  `role_id` int(10) NOT NULL default '0',
  `right_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`role_id`,`right_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `role_rights`
-- 

INSERT INTO `role_rights` (`role_id`, `right_id`) VALUES 
(4, 3),
(4, 6),
(4, 7),
(4, 8),
(4, 11),
(5, 3),
(5, 6),
(5, 8),
(6, 1),
(6, 2),
(6, 3),
(6, 6),
(6, 7),
(6, 8),
(6, 11),
(7, 1),
(7, 3),
(8, 1),
(8, 2),
(8, 3),
(8, 4),
(8, 5),
(8, 6),
(8, 7),
(8, 8),
(8, 9),
(8, 10),
(8, 11),
(8, 12),
(8, 13),
(8, 14),
(8, 15),
(8, 16),
(8, 17),
(8, 18),
(9, 1),
(9, 2),
(9, 3),
(9, 4),
(9, 5),
(9, 6),
(9, 7),
(9, 8),
(9, 9),
(9, 11),
(9, 15),
(9, 16);

-- --------------------------------------------------------

-- 
-- Table structure for table `tcversions`
-- 

DROP TABLE IF EXISTS `tcversions`;
CREATE TABLE `tcversions` (
  `id` int(10) unsigned NOT NULL,
  `version` smallint(5) unsigned NOT NULL default '1',
  `summary` text,
  `steps` text,
  `expected_results` text,
  `importance` char(1) NOT NULL default 'M',
  `author_id` int(10) unsigned default NULL,
  `creation_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `updater_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `tcversions`
-- 

INSERT INTO `tcversions` (`id`, `version`, `summary`, `steps`, `expected_results`, `importance`, `author_id`, `creation_ts`, `updater_id`, `modification_ts`, `active`, `is_open`) VALUES 
(5, 1, '<p>This test case should do some stuff</p>', '<p>1. create a tc</p>\r\n<p>2. execute it</p>', '<p>The test should have executed</p>', 'M', 51, '2007-11-01 14:09:36', NULL, '0000-00-00 00:00:00', 1, 1),
(7, 1, '<p>more things to do</p>', '<p>Wash windows</p>', '<p>The windows should be sparkly clean</p>', 'M', 51, '2007-11-01 14:10:05', NULL, '0000-00-00 00:00:00', 1, 1),
(8, 2, '<p>This test case should do some stuff. This is a new version.</p>', '<p>1. create a tc</p>\r\n<p>2. execute it</p>\r\n<p>3. do more stuff</p>', '<p>The test should have executed</p>', 'M', 1, '2007-11-07 14:41:46', 1, '2007-11-07 14:42:14', 1, 1),
(9, 3, '<p>This test case should do some stuff. This is yet another new version.</p>', '<p>1. create a tc</p>\r\n<p>2. execute it</p>\r\n<p>3. do more stuff</p>', '<p>The test should have executed</p>', 'M', 1, '2007-11-07 15:04:03', 1, '2007-11-07 15:04:21', 1, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `testcase_keywords`
-- 

DROP TABLE IF EXISTS `testcase_keywords`;
CREATE TABLE `testcase_keywords` (
  `testcase_id` int(10) unsigned NOT NULL default '0',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`testcase_id`,`keyword_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `testcase_keywords`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `testplans`
-- 

DROP TABLE IF EXISTS `testplans`;
CREATE TABLE `testplans` (
  `id` int(10) unsigned NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL default '0',
  `notes` text,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `testproject_id_active` (`testproject_id`,`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `testplans`
-- 

INSERT INTO `testplans` (`id`, `testproject_id`, `notes`, `active`, `is_open`) VALUES 
(2, 1, '<p>A description of a test plan for testing</p>', 1, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `testplan_tcversions`
-- 

DROP TABLE IF EXISTS `testplan_tcversions`;
CREATE TABLE `testplan_tcversions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `tcversion_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tp_tcversion` (`testplan_id`,`tcversion_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `testplan_tcversions`
-- 

INSERT INTO `testplan_tcversions` (`id`, `testplan_id`, `tcversion_id`) VALUES 
(4, 2, 7),
(3, 2, 8);

-- --------------------------------------------------------

-- 
-- Table structure for table `testprojects`
-- 

DROP TABLE IF EXISTS `testprojects`;
CREATE TABLE `testprojects` (
  `id` int(10) unsigned NOT NULL,
  `notes` text,
  `color` varchar(12) NOT NULL default '#9BD',
  `active` tinyint(1) NOT NULL default '1',
  `option_reqs` tinyint(1) NOT NULL default '0',
  `option_priority` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `id_active` (`id`,`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `testprojects`
-- 

INSERT INTO `testprojects` (`id`, `notes`, `color`, `active`, `option_reqs`, `option_priority`) VALUES 
(1, '<p>A project for testing</p>', '', 1, 1, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `testsuites`
-- 

DROP TABLE IF EXISTS `testsuites`;
CREATE TABLE `testsuites` (
  `id` int(10) unsigned NOT NULL,
  `details` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `testsuites`
-- 

INSERT INTO `testsuites` (`id`, `details`) VALUES 
(3, '<p>A suite</p>');

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

DROP TABLE IF EXISTS `users`;
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
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='User information' AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `users`
-- 

INSERT INTO `users` (`id`, `login`, `password`, `role_id`, `email`, `first`, `last`, `locale`, `default_testproject_id`, `active`) VALUES 
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 8, '', 'Testlink', 'Administrator', 'en_GB', NULL, 1),
(2, 'testuser', 'ae2b1fca515949e5d54fb22b8ed95575', 7, 'test@test.com', 'test', 'user', 'en_GB', NULL, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `user_assignments`
-- 

DROP TABLE IF EXISTS `user_assignments`;
CREATE TABLE `user_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) unsigned NOT NULL default '1',
  `feature_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned default '0',
  `deadline_ts` datetime default NULL,
  `assigner_id` int(10) unsigned default '0',
  `creation_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `status` int(10) unsigned default '1',
  PRIMARY KEY  (`id`),
  KEY `feature_id` (`feature_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `user_assignments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `user_testplan_roles`
-- 

DROP TABLE IF EXISTS `user_testplan_roles`;
CREATE TABLE `user_testplan_roles` (
  `user_id` int(10) NOT NULL default '0',
  `testplan_id` int(10) NOT NULL default '0',
  `role_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`testplan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `user_testplan_roles`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `user_testproject_roles`
-- 

DROP TABLE IF EXISTS `user_testproject_roles`;
CREATE TABLE `user_testproject_roles` (
  `user_id` int(10) NOT NULL default '0',
  `testproject_id` int(10) NOT NULL default '0',
  `role_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`testproject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `user_testproject_roles`
-- 

