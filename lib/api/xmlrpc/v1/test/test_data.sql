-- phpMyAdmin SQL Dump
-- version 2.11.3deb1ubuntu1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 01, 2008 at 10:56 AM
-- Server version: 5.0.51
-- PHP Version: 5.2.4-2ubuntu5.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `testlink_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_developer_keys`
--

CREATE TABLE IF NOT EXISTS `api_developer_keys` (
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

CREATE TABLE IF NOT EXISTS `assignment_status` (
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

CREATE TABLE IF NOT EXISTS `assignment_types` (
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

CREATE TABLE IF NOT EXISTS `attachments` (
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

CREATE TABLE IF NOT EXISTS `builds` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default 'undefined',
  `notes` text,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`testplan_id`,`name`),
  KEY `testplan_id` (`testplan_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Available builds' AUTO_INCREMENT=65 ;

--
-- Dumping data for table `builds`
--

INSERT INTO `builds` (`id`, `testplan_id`, `name`, `notes`, `active`, `is_open`) VALUES
(1, 2, 'A build for the test plan', '<p>Have to build some time</p>', 1, 1),
(2, 2, 'Another test build from 12/03/07 13:25:31', 'NULL', 1, 1),
(3, 2, 'Another notes test build from 12/03/07 13:25:31', 'Some notes from the build created at 12/03/07 13:25:31', 1, 1),
(4, 2, 'Another test build from 12/03/07 13:27:08', 'NULL', 1, 1),
(5, 2, 'Another notes test build from 12/03/07 13:27:08', 'Some notes from the build created at 12/03/07 13:27:08', 1, 1),
(6, 2, 'Another test build from Wed Sep 24 16:48:19 2008', '', 1, 1),
(7, 2, 'Another notes test build from Wed Sep 24 16:48:33 2008', 'Some notes from the build created at Wed Sep 24 16:48:33 2008', 1, 1),
(8, 2, 'Another test build from Mon Sep 29 09:05:15 2008', '', 1, 1),
(9, 2, 'Another notes test build from Mon Sep 29 09:05:15 2008', 'Some notes from the build created at Mon Sep 29 09:05:15 2008', 1, 1),
(10, 2, 'Another test build from Mon Sep 29 09:09:29 2008', '', 1, 1),
(11, 2, 'Another notes test build from Mon Sep 29 09:09:29 2008', 'Some notes from the build created at Mon Sep 29 09:09:29 2008', 1, 1),
(12, 2, 'Another test build from Mon Sep 29 09:09:58 2008', '', 1, 1),
(13, 2, 'Another notes test build from Mon Sep 29 09:09:58 2008', 'Some notes from the build created at Mon Sep 29 09:09:58 2008', 1, 1),
(14, 2, 'Another test build from Mon Sep 29 09:10:06 2008', '', 1, 1),
(15, 2, 'Another notes test build from Mon Sep 29 09:10:06 2008', 'Some notes from the build created at Mon Sep 29 09:10:06 2008', 1, 1),
(16, 2, 'Another test build from Mon Sep 29 09:50:20 2008', '', 1, 1),
(17, 2, 'Another notes test build from Mon Sep 29 09:50:20 2008', 'Some notes from the build created at Mon Sep 29 09:50:20 2008', 1, 1),
(18, 2, 'Another test build from Mon Sep 29 10:06:14 2008', '', 1, 1),
(19, 2, 'Another notes test build from Mon Sep 29 10:06:14 2008', 'Some notes from the build created at Mon Sep 29 10:06:14 2008', 1, 1),
(20, 2, 'Another test build from Mon Sep 29 10:06:19 2008', '', 1, 1),
(21, 2, 'Another notes test build from Mon Sep 29 10:06:19 2008', 'Some notes from the build created at Mon Sep 29 10:06:19 2008', 1, 1),
(22, 2, 'Another test build from Tue Sep 30 08:58:31 2008', '', 1, 1),
(23, 2, 'Another notes test build from Tue Sep 30 08:58:31 2008', 'Some notes from the build created at Tue Sep 30 08:58:31 2008', 1, 1),
(24, 2, 'Another test build from Tue Sep 30 10:20:34 2008', '', 1, 1),
(25, 2, 'Another notes test build from Tue Sep 30 10:20:34 2008', 'Some notes from the build created at Tue Sep 30 10:20:34 2008', 1, 1),
(26, 2, 'Another test build from Tue Sep 30 10:21:19 2008', '', 1, 1),
(27, 2, 'Another notes test build from Tue Sep 30 10:21:19 2008', 'Some notes from the build created at Tue Sep 30 10:21:19 2008', 1, 1),
(28, 2, 'Another test build from Tue Sep 30 10:22:08 2008', '', 1, 1),
(29, 2, 'Another notes test build from Tue Sep 30 10:22:08 2008', 'Some notes from the build created at Tue Sep 30 10:22:08 2008', 1, 1),
(30, 2, 'Another test build from Tue Sep 30 10:33:50 2008', '', 1, 1),
(31, 2, 'Another notes test build from Tue Sep 30 10:33:50 2008', 'Some notes from the build created at Tue Sep 30 10:33:50 2008', 1, 1),
(32, 2, 'Another test build from Tue Sep 30 10:35:17 2008', '', 1, 1),
(33, 2, 'Another notes test build from Tue Sep 30 10:35:17 2008', 'Some notes from the build created at Tue Sep 30 10:35:17 2008', 1, 1),
(34, 2, 'Another test build from Tue Sep 30 13:14:40 2008', '', 1, 1),
(35, 2, 'Another notes test build from Tue Sep 30 13:14:40 2008', 'Some notes from the build created at Tue Sep 30 13:14:40 2008', 1, 1),
(36, 2, 'Another test build from Tue Sep 30 13:51:35 2008', '', 1, 1),
(37, 2, 'Another notes test build from Tue Sep 30 13:51:35 2008', 'Some notes from the build created at Tue Sep 30 13:51:35 2008', 1, 1),
(38, 2, 'Another test build from Tue Sep 30 14:03:52 2008', '', 1, 1),
(39, 2, 'Another test build from Tue Sep 30 14:16:10 2008', '', 1, 1),
(40, 2, 'Another notes test build from Tue Sep 30 14:16:10 2008', 'Some notes from the build created at Tue Sep 30 14:16:10 2008', 1, 1),
(41, 2, 'Another test build from Tue Sep 30 14:20:28 2008', '', 1, 1),
(42, 2, 'Another notes test build from Tue Sep 30 14:20:28 2008', 'Some notes from the build created at Tue Sep 30 14:20:28 2008', 1, 1),
(43, 2, 'Another test build from Tue Sep 30 14:20:49 2008', '', 1, 1),
(44, 2, 'Another notes test build from Tue Sep 30 14:20:49 2008', 'Some notes from the build created at Tue Sep 30 14:20:49 2008', 1, 1),
(45, 2, 'Another test build from Tue Sep 30 14:24:46 2008', '', 1, 1),
(46, 2, 'Another notes test build from Tue Sep 30 14:24:46 2008', 'Some notes from the build created at Tue Sep 30 14:24:46 2008', 1, 1),
(47, 2, 'Another test build from Tue Sep 30 14:26:00 2008', '', 1, 1),
(48, 2, 'Another notes test build from Tue Sep 30 14:26:00 2008', 'Some notes from the build created at Tue Sep 30 14:26:00 2008', 1, 1),
(49, 2, 'Another test build from Tue Sep 30 14:26:26 2008', '', 1, 1),
(50, 2, 'Another notes test build from Tue Sep 30 14:26:26 2008', 'Some notes from the build created at Tue Sep 30 14:26:26 2008', 1, 1),
(51, 2, 'Another test build from Tue Sep 30 14:30:10 2008', '', 1, 1),
(52, 2, 'Another notes test build from Tue Sep 30 14:30:10 2008', 'Some notes from the build created at Tue Sep 30 14:30:10 2008', 1, 1),
(53, 2, 'Another test build from Tue Sep 30 14:30:56 2008', '', 1, 1),
(54, 2, 'Another notes test build from Tue Sep 30 14:30:56 2008', 'Some notes from the build created at Tue Sep 30 14:30:56 2008', 1, 1),
(55, 2, 'Another test build from Tue Sep 30 14:36:29 2008', '', 1, 1),
(56, 2, 'Another notes test build from Tue Sep 30 14:36:29 2008', 'Some notes from the build created at Tue Sep 30 14:36:29 2008', 1, 1),
(57, 2, 'Another test build from Tue Sep 30 14:36:57 2008', '', 1, 1),
(58, 2, 'Another notes test build from Tue Sep 30 14:36:57 2008', 'Some notes from the build created at Tue Sep 30 14:36:57 2008', 1, 1),
(59, 2, 'Another test build from Tue Sep 30 16:00:43 2008', '', 1, 1),
(60, 2, 'Another notes test build from Tue Sep 30 16:00:43 2008', 'Some notes from the build created at Tue Sep 30 16:00:43 2008', 1, 1),
(61, 2, 'Another test build from Wed Oct  1 10:14:15 2008', '', 1, 1),
(62, 2, 'Another notes test build from Wed Oct  1 10:14:15 2008', 'Some notes from the build created at Wed Oct  1 10:14:15 2008', 1, 1),
(63, 2, 'Another test build from Wed Oct  1 10:37:37 2008', '', 1, 1),
(64, 2, 'Another notes test build from Wed Oct  1 10:37:37 2008', 'Some notes from the build created at Wed Oct  1 10:37:37 2008', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cfield_design_values`
--

CREATE TABLE IF NOT EXISTS `cfield_design_values` (
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

CREATE TABLE IF NOT EXISTS `cfield_execution_values` (
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

CREATE TABLE IF NOT EXISTS `cfield_node_types` (
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

CREATE TABLE IF NOT EXISTS `cfield_testprojects` (
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

CREATE TABLE IF NOT EXISTS `custom_fields` (
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
  `show_on_testplan_design` tinyint(3) unsigned NOT NULL default '0',
  `enable_on_testplan_design` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_custom_fields_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Updated to TL 1.8 RC1  - DB 1.2' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `custom_fields`
--


-- --------------------------------------------------------

--
-- Table structure for table `db_version`
--

CREATE TABLE IF NOT EXISTS `db_version` (
  `version` varchar(50) NOT NULL default 'unknown',
  `upgrade_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `notes` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Updated to TL 1.8 RC1 - DB 1.2';

--
-- Dumping data for table `db_version`
--

INSERT INTO `db_version` (`version`, `upgrade_ts`, `notes`) VALUES
('DB 1.1', '2007-11-01 14:06:11', NULL),
('DB 1.2', '2008-09-24 11:11:35', 'first version with API feature');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `transaction_id` int(10) unsigned NOT NULL default '0',
  `log_level` smallint(5) unsigned NOT NULL default '0',
  `source` varchar(45) default NULL,
  `description` text NOT NULL,
  `fired_at` int(10) unsigned NOT NULL default '0',
  `activity` varchar(45) default NULL,
  `object_id` int(10) unsigned default NULL,
  `object_type` varchar(45) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `transaction_id`, `log_level`, `source`, `description`, `fired_at`, `activity`, `object_id`, `object_type`) VALUES
(1, 1, 16, 'GUI', 'O:18:"tlMetaStringHelper":4:{s:5:"label";s:21:"audit_login_succeeded";s:6:"params";a:2:{i:0;s:5:"admin";i:1;s:9:"127.0.0.1";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}', 1222276324, 'LOGIN', 1, 'users');

-- --------------------------------------------------------

--
-- Table structure for table `executions`
--

CREATE TABLE IF NOT EXISTS `executions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `build_id` int(10) NOT NULL default '0',
  `tester_id` int(10) unsigned default NULL,
  `execution_ts` datetime default NULL,
  `status` char(1) default NULL,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `tcversion_id` int(10) unsigned NOT NULL default '0',
  `tcversion_number` smallint(5) unsigned NOT NULL default '1' COMMENT 'test case version used for this execution',
  `execution_type` tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated',
  `notes` text,
  `automated` tinyint(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `testplan_id_tcversion_id` (`testplan_id`,`tcversion_id`),
  KEY `automated` (`automated`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Updated to TL 1.8 RC1 - DB 1.2' AUTO_INCREMENT=167 ;

--
-- Dumping data for table `executions`
--

INSERT INTO `executions` (`id`, `build_id`, `tester_id`, `execution_ts`, `status`, `testplan_id`, `tcversion_id`, `tcversion_number`, `execution_type`, `notes`, `automated`) VALUES
(1, 1, 1, '2007-11-07 15:00:29', 'f', 2, 8, 1, 1, '', NULL),
(2, 1, 1, '2007-11-07 15:00:36', 'p', 2, 7, 1, 1, '', NULL),
(3, 1, 1, '2007-12-03 13:25:30', 'b', 2, 8, 1, 1, NULL, 1),
(4, 1, 1, '2007-12-03 13:25:30', 'p', 2, 8, 1, 1, NULL, 1),
(5, 1, 1, '2007-12-03 13:25:30', 'f', 2, 8, 1, 1, NULL, 1),
(6, 1, 1, '2007-12-03 13:25:30', 'p', 2, 8, 1, 1, NULL, 1),
(7, 1, 1, '2007-12-03 13:25:30', 'f', 2, 8, 1, 1, NULL, 1),
(8, 3, 1, '2007-12-03 13:27:07', 'b', 2, 8, 1, 1, NULL, 1),
(9, 3, 1, '2007-12-03 13:27:07', 'p', 2, 8, 1, 1, NULL, 1),
(10, 3, 1, '2007-12-03 13:27:07', 'f', 2, 8, 1, 1, NULL, 1),
(11, 3, 1, '2007-12-03 13:27:08', 'p', 2, 8, 1, 1, NULL, 1),
(12, 1, 1, '2007-12-03 13:27:08', 'f', 2, 8, 1, 1, NULL, 1),
(13, 1, 1, '2008-09-24 16:59:22', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(14, 1, 1, '2008-09-29 09:05:14', 'f', 2, 8, 1, 2, NULL, NULL),
(15, 1, 1, '2008-09-29 09:05:15', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(16, 1, 1, '2008-09-29 09:09:29', 'f', 2, 8, 1, 2, NULL, NULL),
(17, 1, 1, '2008-09-29 09:09:29', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(18, 1, 1, '2008-09-29 09:09:58', 'f', 2, 8, 1, 2, NULL, NULL),
(19, 1, 1, '2008-09-29 09:09:58', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(20, 1, 1, '2008-09-29 09:10:06', 'f', 2, 8, 1, 2, NULL, NULL),
(21, 1, 1, '2008-09-29 09:10:06', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(22, 1, 1, '2008-09-29 09:50:20', 'f', 2, 8, 1, 2, NULL, NULL),
(23, 1, 1, '2008-09-29 09:50:20', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(24, 1, 1, '2008-09-29 10:03:37', 'b', 2, 8, 1, 2, NULL, NULL),
(25, 1, 1, '2008-09-29 10:04:21', 'p', 2, 8, 1, 2, NULL, NULL),
(26, 1, 1, '2008-09-29 10:04:51', 'f', 2, 8, 1, 2, NULL, NULL),
(27, 1, 1, '2008-09-29 10:05:34', 'p', 2, 8, 1, 2, NULL, NULL),
(28, 1, 1, '2008-09-29 10:06:14', 'b', 2, 8, 1, 2, NULL, NULL),
(29, 1, 1, '2008-09-29 10:06:14', 'p', 2, 8, 1, 2, NULL, NULL),
(30, 1, 1, '2008-09-29 10:06:14', 'p', 2, 8, 1, 2, NULL, NULL),
(31, 1, 1, '2008-09-29 10:06:14', 'f', 2, 8, 1, 2, NULL, NULL),
(32, 1, 1, '2008-09-29 10:06:14', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(33, 1, 1, '2008-09-29 10:06:15', 'f', 2, 8, 1, 2, NULL, NULL),
(34, 1, 1, '2008-09-29 10:06:18', 'b', 2, 8, 1, 2, NULL, NULL),
(35, 1, 1, '2008-09-29 10:06:18', 'p', 2, 8, 1, 2, NULL, NULL),
(36, 1, 1, '2008-09-29 10:06:18', 'p', 2, 8, 1, 2, NULL, NULL),
(37, 1, 1, '2008-09-29 10:06:19', 'f', 2, 8, 1, 2, NULL, NULL),
(38, 1, 1, '2008-09-29 10:06:19', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(39, 1, 1, '2008-09-29 10:06:19', 'f', 2, 8, 1, 2, NULL, NULL),
(40, 1, 1, '2008-09-30 08:58:30', 'b', 2, 8, 1, 2, NULL, NULL),
(41, 1, 1, '2008-09-30 08:58:30', 'p', 2, 8, 1, 2, NULL, NULL),
(42, 1, 1, '2008-09-30 08:58:31', 'p', 2, 8, 1, 2, NULL, NULL),
(43, 1, 1, '2008-09-30 08:58:31', 'f', 2, 8, 1, 2, NULL, NULL),
(44, 1, 1, '2008-09-30 08:58:31', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(45, 1, 1, '2008-09-30 08:58:31', 'f', 2, 8, 1, 2, NULL, NULL),
(46, 1, 1, '2008-09-30 10:20:33', 'b', 2, 8, 1, 2, NULL, NULL),
(47, 1, 1, '2008-09-30 10:20:33', 'p', 2, 8, 1, 2, NULL, NULL),
(48, 1, 1, '2008-09-30 10:20:33', 'p', 2, 8, 1, 2, NULL, NULL),
(49, 1, 1, '2008-09-30 10:20:33', 'f', 2, 8, 1, 2, NULL, NULL),
(50, 1, 1, '2008-09-30 10:20:34', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(51, 1, 1, '2008-09-30 10:20:34', 'f', 2, 8, 1, 2, NULL, NULL),
(52, 1, 1, '2008-09-30 10:21:18', 'b', 2, 8, 1, 2, NULL, NULL),
(53, 1, 1, '2008-09-30 10:21:18', 'p', 2, 8, 1, 2, NULL, NULL),
(54, 1, 1, '2008-09-30 10:21:19', 'p', 2, 8, 1, 2, NULL, NULL),
(55, 1, 1, '2008-09-30 10:21:19', 'f', 2, 8, 1, 2, NULL, NULL),
(56, 1, 1, '2008-09-30 10:21:19', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(57, 1, 1, '2008-09-30 10:21:19', 'f', 2, 8, 1, 2, NULL, NULL),
(58, 1, 1, '2008-09-30 10:22:07', 'b', 2, 8, 1, 2, NULL, NULL),
(59, 1, 1, '2008-09-30 10:22:07', 'p', 2, 8, 1, 2, NULL, NULL),
(60, 1, 1, '2008-09-30 10:22:07', 'p', 2, 8, 1, 2, NULL, NULL),
(61, 1, 1, '2008-09-30 10:22:07', 'f', 2, 8, 1, 2, NULL, NULL),
(62, 1, 1, '2008-09-30 10:22:08', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(63, 1, 1, '2008-09-30 10:22:08', 'f', 2, 8, 1, 2, NULL, NULL),
(64, 1, 1, '2008-09-30 10:33:49', 'b', 2, 8, 1, 2, NULL, NULL),
(65, 1, 1, '2008-09-30 10:33:49', 'p', 2, 8, 1, 2, NULL, NULL),
(66, 1, 1, '2008-09-30 10:33:49', 'p', 2, 8, 1, 2, NULL, NULL),
(67, 1, 1, '2008-09-30 10:33:50', 'f', 2, 8, 1, 2, NULL, NULL),
(68, 1, 1, '2008-09-30 10:33:50', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(69, 1, 1, '2008-09-30 10:33:50', 'f', 2, 8, 1, 2, NULL, NULL),
(70, 1, 1, '2008-09-30 10:35:17', 'b', 2, 8, 1, 2, NULL, NULL),
(71, 1, 1, '2008-09-30 10:35:17', 'p', 2, 8, 1, 2, NULL, NULL),
(72, 1, 1, '2008-09-30 10:35:17', 'p', 2, 8, 1, 2, NULL, NULL),
(73, 1, 1, '2008-09-30 10:35:17', 'f', 2, 8, 1, 2, NULL, NULL),
(74, 1, 1, '2008-09-30 10:35:17', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(75, 1, 1, '2008-09-30 10:35:18', 'f', 2, 8, 1, 2, NULL, NULL),
(76, 1, 1, '2008-09-30 13:14:39', 'b', 2, 8, 1, 2, NULL, NULL),
(77, 1, 1, '2008-09-30 13:14:39', 'p', 2, 8, 1, 2, NULL, NULL),
(78, 1, 1, '2008-09-30 13:14:40', 'p', 2, 8, 1, 2, NULL, NULL),
(79, 1, 1, '2008-09-30 13:14:40', 'f', 2, 8, 1, 2, NULL, NULL),
(80, 1, 1, '2008-09-30 13:14:40', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(81, 1, 1, '2008-09-30 13:14:41', 'f', 2, 8, 1, 2, NULL, NULL),
(82, 1, 1, '2008-09-30 13:25:04', 'b', 2, 8, 1, 2, NULL, NULL),
(83, 1, 1, '2008-09-30 13:25:04', 'p', 2, 8, 1, 2, NULL, NULL),
(84, 1, 1, '2008-09-30 13:25:04', 'p', 2, 8, 1, 2, NULL, NULL),
(85, 1, 1, '2008-09-30 13:25:04', 'f', 2, 8, 1, 2, NULL, NULL),
(86, 1, 1, '2008-09-30 13:25:05', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(87, 1, 1, '2008-09-30 13:25:05', 'f', 2, 8, 1, 2, NULL, NULL),
(88, 1, 1, '2008-09-30 14:16:09', 'b', 2, 8, 1, 2, NULL, NULL),
(89, 1, 1, '2008-09-30 14:16:09', 'p', 2, 8, 1, 2, NULL, NULL),
(90, 1, 1, '2008-09-30 14:16:09', 'p', 2, 8, 1, 2, NULL, NULL),
(91, 1, 1, '2008-09-30 14:16:10', 'f', 2, 8, 1, 2, NULL, NULL),
(92, 1, 1, '2008-09-30 14:16:10', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(93, 1, 1, '2008-09-30 14:16:10', 'f', 2, 8, 1, 2, NULL, NULL),
(94, 1, 1, '2008-09-30 14:20:27', 'b', 2, 8, 1, 2, NULL, NULL),
(95, 1, 1, '2008-09-30 14:20:27', 'p', 2, 8, 1, 2, NULL, NULL),
(96, 1, 1, '2008-09-30 14:20:28', 'p', 2, 8, 1, 2, NULL, NULL),
(97, 1, 1, '2008-09-30 14:20:28', 'f', 2, 8, 1, 2, NULL, NULL),
(98, 1, 1, '2008-09-30 14:20:28', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(99, 1, 1, '2008-09-30 14:20:29', 'f', 2, 8, 1, 2, NULL, NULL),
(100, 1, 1, '2008-09-30 14:20:30', 'b', 2, 8, 1, 2, NULL, NULL),
(101, 1, 1, '2008-09-30 14:20:48', 'b', 2, 8, 1, 2, NULL, NULL),
(102, 1, 1, '2008-09-30 14:20:48', 'p', 2, 8, 1, 2, NULL, NULL),
(103, 1, 1, '2008-09-30 14:20:48', 'p', 2, 8, 1, 2, NULL, NULL),
(104, 1, 1, '2008-09-30 14:20:49', 'f', 2, 8, 1, 2, NULL, NULL),
(105, 1, 1, '2008-09-30 14:20:49', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(106, 1, 1, '2008-09-30 14:20:49', 'f', 2, 8, 1, 2, NULL, NULL),
(107, 1, 1, '2008-09-30 14:24:46', 'b', 2, 8, 1, 2, NULL, NULL),
(108, 1, 1, '2008-09-30 14:24:46', 'p', 2, 8, 1, 2, NULL, NULL),
(109, 1, 1, '2008-09-30 14:24:46', 'p', 2, 8, 1, 2, NULL, NULL),
(110, 1, 1, '2008-09-30 14:24:46', 'f', 2, 8, 1, 2, NULL, NULL),
(111, 1, 1, '2008-09-30 14:24:46', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(112, 1, 1, '2008-09-30 14:24:47', 'f', 2, 8, 1, 2, NULL, NULL),
(113, 1, 1, '2008-09-30 14:25:59', 'b', 2, 8, 1, 2, NULL, NULL),
(114, 1, 1, '2008-09-30 14:25:59', 'p', 2, 8, 1, 2, NULL, NULL),
(115, 1, 1, '2008-09-30 14:25:59', 'p', 2, 8, 1, 2, NULL, NULL),
(116, 1, 1, '2008-09-30 14:26:00', 'f', 2, 8, 1, 2, NULL, NULL),
(117, 1, 1, '2008-09-30 14:26:00', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(118, 1, 1, '2008-09-30 14:26:00', 'f', 2, 8, 1, 2, NULL, NULL),
(119, 1, 1, '2008-09-30 14:26:25', 'b', 2, 8, 1, 2, NULL, NULL),
(120, 1, 1, '2008-09-30 14:26:25', 'p', 2, 8, 1, 2, NULL, NULL),
(121, 1, 1, '2008-09-30 14:26:25', 'p', 2, 8, 1, 2, NULL, NULL),
(122, 1, 1, '2008-09-30 14:26:25', 'f', 2, 8, 1, 2, NULL, NULL),
(123, 1, 1, '2008-09-30 14:26:26', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(124, 1, 1, '2008-09-30 14:26:26', 'f', 2, 8, 1, 2, NULL, NULL),
(125, 1, 1, '2008-09-30 14:30:10', 'b', 2, 8, 1, 2, NULL, NULL),
(126, 1, 1, '2008-09-30 14:30:10', 'p', 2, 8, 1, 2, NULL, NULL),
(127, 1, 1, '2008-09-30 14:30:10', 'p', 2, 8, 1, 2, NULL, NULL),
(128, 1, 1, '2008-09-30 14:30:10', 'f', 2, 8, 1, 2, NULL, NULL),
(129, 1, 1, '2008-09-30 14:30:10', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(130, 1, 1, '2008-09-30 14:30:11', 'f', 2, 8, 1, 2, NULL, NULL),
(131, 1, 1, '2008-09-30 14:30:55', 'b', 2, 8, 1, 2, NULL, NULL),
(132, 1, 1, '2008-09-30 14:30:55', 'p', 2, 8, 1, 2, NULL, NULL),
(133, 1, 1, '2008-09-30 14:30:56', 'p', 2, 8, 1, 2, NULL, NULL),
(134, 1, 1, '2008-09-30 14:30:56', 'f', 2, 8, 1, 2, NULL, NULL),
(135, 1, 1, '2008-09-30 14:30:56', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(136, 1, 1, '2008-09-30 14:30:57', 'f', 2, 8, 1, 2, NULL, NULL),
(137, 1, 1, '2008-09-30 14:36:28', 'b', 2, 8, 1, 2, NULL, NULL),
(138, 1, 1, '2008-09-30 14:36:28', 'p', 2, 8, 1, 2, NULL, NULL),
(139, 1, 1, '2008-09-30 14:36:29', 'p', 2, 8, 1, 2, NULL, NULL),
(140, 1, 1, '2008-09-30 14:36:29', 'f', 2, 8, 1, 2, NULL, NULL),
(141, 1, 1, '2008-09-30 14:36:29', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(142, 1, 1, '2008-09-30 14:36:29', 'f', 2, 8, 1, 2, NULL, NULL),
(143, 1, 1, '2008-09-30 14:36:56', 'b', 2, 8, 1, 2, NULL, NULL),
(144, 1, 1, '2008-09-30 14:36:56', 'p', 2, 8, 1, 2, NULL, NULL),
(145, 1, 1, '2008-09-30 14:36:56', 'p', 2, 8, 1, 2, NULL, NULL),
(146, 1, 1, '2008-09-30 14:36:56', 'f', 2, 8, 1, 2, NULL, NULL),
(147, 1, 1, '2008-09-30 14:36:57', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(148, 1, 1, '2008-09-30 14:36:57', 'f', 2, 8, 1, 2, NULL, NULL),
(149, 1, 1, '2008-09-30 16:00:42', 'b', 2, 8, 1, 2, NULL, NULL),
(150, 1, 1, '2008-09-30 16:00:43', 'p', 2, 8, 1, 2, NULL, NULL),
(151, 1, 1, '2008-09-30 16:00:43', 'p', 2, 8, 1, 2, NULL, NULL),
(152, 1, 1, '2008-09-30 16:00:43', 'f', 2, 8, 1, 2, NULL, NULL),
(153, 1, 1, '2008-09-30 16:00:43', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(154, 1, 1, '2008-09-30 16:00:44', 'f', 2, 8, 1, 2, NULL, NULL),
(155, 1, 1, '2008-10-01 10:14:14', 'b', 2, 8, 1, 2, NULL, NULL),
(156, 1, 1, '2008-10-01 10:14:14', 'p', 2, 8, 1, 2, NULL, NULL),
(157, 1, 1, '2008-10-01 10:14:14', 'p', 2, 8, 1, 2, NULL, NULL),
(158, 1, 1, '2008-10-01 10:14:15', 'f', 2, 8, 1, 2, NULL, NULL),
(159, 1, 1, '2008-10-01 10:14:15', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(160, 1, 1, '2008-10-01 10:14:15', 'f', 2, 8, 1, 2, NULL, NULL),
(161, 1, 1, '2008-10-01 10:37:36', 'b', 2, 8, 1, 2, NULL, NULL),
(162, 1, 1, '2008-10-01 10:37:36', 'p', 2, 8, 1, 2, NULL, NULL),
(163, 1, 1, '2008-10-01 10:37:36', 'p', 2, 8, 1, 2, NULL, NULL),
(164, 1, 1, '2008-10-01 10:37:37', 'f', 2, 8, 1, 2, NULL, NULL),
(165, 1, 1, '2008-10-01 10:37:37', 'p', 2, 8, 1, 2, 'this is a note about the test', NULL),
(166, 1, 1, '2008-10-01 10:37:37', 'f', 2, 8, 1, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `execution_bugs`
--

CREATE TABLE IF NOT EXISTS `execution_bugs` (
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

CREATE TABLE IF NOT EXISTS `keywords` (
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

CREATE TABLE IF NOT EXISTS `milestones` (
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

CREATE TABLE IF NOT EXISTS `nodes_hierarchy` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `parent_id` int(10) unsigned default NULL,
  `node_type_id` int(10) unsigned NOT NULL default '1',
  `node_order` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `pid_m_nodeorder` (`parent_id`,`node_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

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
(9, '', 4, 4, 0),
(10, 'child suite 1', 3, 2, 1),
(11, 'test case in child suite', 10, 3, 100),
(12, '', 11, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `node_types`
--

CREATE TABLE IF NOT EXISTS `node_types` (
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

CREATE TABLE IF NOT EXISTS `object_keywords` (
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
-- Table structure for table `requirements`
--

CREATE TABLE IF NOT EXISTS `requirements` (
  `id` int(10) unsigned NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `requirements`
--


-- --------------------------------------------------------

--
-- Table structure for table `req_coverage`
--

CREATE TABLE IF NOT EXISTS `req_coverage` (
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

CREATE TABLE IF NOT EXISTS `req_specs` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Dev. Documents (e.g. System Requirements Specification)';

--
-- Dumping data for table `req_specs`
--


-- --------------------------------------------------------

--
-- Table structure for table `rights`
--

CREATE TABLE IF NOT EXISTS `rights` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `rights_descr` (`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

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
(18, 'cfield_management'),
(19, 'system_configuraton'),
(20, 'mgt_view_events'),
(21, 'mgt_view_usergroups');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
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

CREATE TABLE IF NOT EXISTS `role_rights` (
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
(8, 19),
(8, 20),
(8, 21),
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

CREATE TABLE IF NOT EXISTS `tcversions` (
  `id` int(10) unsigned NOT NULL,
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
  `execution_type` tinyint(1) default '1' COMMENT '1 -> manual, 2 -> automated',
  `tc_external_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Updated to TL 1.8 RC1 - DB 1.2';

--
-- Dumping data for table `tcversions`
--

INSERT INTO `tcversions` (`id`, `version`, `summary`, `steps`, `expected_results`, `importance`, `author_id`, `creation_ts`, `updater_id`, `modification_ts`, `active`, `is_open`, `execution_type`, `tc_external_id`) VALUES
(5, 1, '<p>This test case should do some stuff</p>', '<p>1. create a tc</p>\r\n<p>2. execute it</p>', '<p>The test should have executed</p>', 0, 51, '2007-11-01 14:09:36', NULL, '0000-00-00 00:00:00', 1, 1, 1, 0),
(7, 1, '<p>more things to do</p>', '<p>Wash windows</p>', '<p>The windows should be sparkly clean</p>', 0, 51, '2007-11-01 14:10:05', NULL, '0000-00-00 00:00:00', 1, 1, 1, 0),
(8, 2, '<p>This test case should do some stuff. This is a new version.</p>', '<p>1. create a tc</p>\r\n<p>2. execute it</p>\r\n<p>3. do more stuff</p>', '<p>The test should have executed</p>', 0, 1, '2007-11-07 14:41:46', 1, '2007-11-07 14:42:14', 1, 1, 1, 0),
(9, 3, '<p>This test case should do some stuff. This is yet another new version.</p>', '<p>1. create a tc</p>\r\n<p>2. execute it</p>\r\n<p>3. do more stuff</p>', '<p>The test should have executed</p>', 0, 1, '2007-11-07 15:04:03', 1, '2007-11-07 15:04:21', 1, 1, 1, 0),
(12, 1, '', '', '', 0, 1, '2007-12-03 13:26:52', NULL, '0000-00-00 00:00:00', 1, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `testcase_keywords`
--

CREATE TABLE IF NOT EXISTS `testcase_keywords` (
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

CREATE TABLE IF NOT EXISTS `testplans` (
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

CREATE TABLE IF NOT EXISTS `testplan_tcversions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `tcversion_id` int(10) unsigned NOT NULL default '0',
  `node_order` int(10) unsigned NOT NULL default '1' COMMENT 'order in execution tree',
  `urgency` smallint(5) unsigned NOT NULL default '2' COMMENT 'test prioritization, default is MEDIUM',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tp_tcversion` (`testplan_id`,`tcversion_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Updated to TL 1.8 RC1  - DB 1.2' AUTO_INCREMENT=5 ;

--
-- Dumping data for table `testplan_tcversions`
--

INSERT INTO `testplan_tcversions` (`id`, `testplan_id`, `tcversion_id`, `node_order`, `urgency`) VALUES
(4, 2, 7, 1, 2),
(3, 2, 8, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `testprojects`
--

CREATE TABLE IF NOT EXISTS `testprojects` (
  `id` int(10) unsigned NOT NULL,
  `notes` text,
  `color` varchar(12) NOT NULL default '#9BD',
  `active` tinyint(1) NOT NULL default '1',
  `option_reqs` tinyint(1) NOT NULL default '0',
  `option_priority` tinyint(1) NOT NULL default '1',
  `prefix` varchar(30) default NULL,
  `tc_counter` int(10) unsigned default '0',
  `option_automation` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_active` (`id`,`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Updated to TL 1.8 RC1 - DB 1.2';

--
-- Dumping data for table `testprojects`
--

INSERT INTO `testprojects` (`id`, `notes`, `color`, `active`, `option_reqs`, `option_priority`, `prefix`, `tc_counter`, `option_automation`) VALUES
(1, '<p>A project for testing</p>', '', 1, 1, 1, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `testsuites`
--

CREATE TABLE IF NOT EXISTS `testsuites` (
  `id` int(10) unsigned NOT NULL,
  `details` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `testsuites`
--

INSERT INTO `testsuites` (`id`, `details`) VALUES
(3, '<p>A suite</p>'),
(10, '');

-- --------------------------------------------------------

--
-- Table structure for table `text_templates`
--

CREATE TABLE IF NOT EXISTS `text_templates` (
  `id` int(10) unsigned NOT NULL,
  `tpl_type` smallint(5) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `template_data` text,
  `author_id` int(10) unsigned default NULL,
  `creation_ts` datetime NOT NULL default '1900-00-00 01:00:00',
  `is_public` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `idx_text_templates` (`tpl_type`,`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Global Project Templates';

--
-- Dumping data for table `text_templates`
--


-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `entry_point` varchar(45) NOT NULL default '',
  `start_time` int(10) unsigned NOT NULL default '0',
  `end_time` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `session_id` varchar(45) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `entry_point`, `start_time`, `end_time`, `user_id`, `session_id`) VALUES
(1, '/testlink_trunk/login.php', 1222276324, 1222276324, 1, '1920f486cf6e87091a8eede8cf8d1c9a');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
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
  `script_key` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Updated to TL 1.8 RC1 - DB 1.2' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `role_id`, `email`, `first`, `last`, `locale`, `default_testproject_id`, `active`, `script_key`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 8, '', 'Testlink', 'Administrator', 'en_GB', NULL, 1, 'validTestDevKey'),
(2, 'testuser', 'ae2b1fca515949e5d54fb22b8ed95575', 7, 'test@test.com', 'test', 'user', 'en_GB', NULL, 1, '2f64aaa8d0ac693d0d7c934fe20c68b6'),
(3, 'norights', 'ae2b1fca515949e5d54fb22b8ed95575', 3, 'haslamjd@ldschurch.org', 'No', 'Rights', 'en_GB', NULL, 1, 'devKeyWithNoRights');

-- --------------------------------------------------------

--
-- Table structure for table `user_assignments`
--

CREATE TABLE IF NOT EXISTS `user_assignments` (
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
-- Table structure for table `user_group`
--

CREATE TABLE IF NOT EXISTS `user_group` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `owner_id` int(10) unsigned NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_group`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_group_assign`
--

CREATE TABLE IF NOT EXISTS `user_group_assign` (
  `usergroup_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_group_assign`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_testplan_roles`
--

CREATE TABLE IF NOT EXISTS `user_testplan_roles` (
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

CREATE TABLE IF NOT EXISTS `user_testproject_roles` (
  `user_id` int(10) NOT NULL default '0',
  `testproject_id` int(10) NOT NULL default '0',
  `role_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`testproject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_testproject_roles`
--

