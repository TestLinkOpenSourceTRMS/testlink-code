-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.0.24-community-max-nt


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema testlinky
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ tl18sample;
USE tl18sample;

--
-- Table structure for table `tl18sample`.`assignment_status`
--

DROP TABLE IF EXISTS `assignment_status`;
CREATE TABLE `assignment_status` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default 'unknown',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`assignment_status`
--

/*!40000 ALTER TABLE `assignment_status` DISABLE KEYS */;
INSERT INTO `assignment_status` (`id`,`description`) VALUES 
 (1,'open'),
 (2,'closed'),
 (3,'completed'),
 (4,'todo_urgent'),
 (5,'todo');
/*!40000 ALTER TABLE `assignment_status` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`assignment_types`
--

DROP TABLE IF EXISTS `assignment_types`;
CREATE TABLE `assignment_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_table` varchar(30) default '',
  `description` varchar(100) NOT NULL default 'unknown',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`assignment_types`
--

/*!40000 ALTER TABLE `assignment_types` DISABLE KEYS */;
INSERT INTO `assignment_types` (`id`,`fk_table`,`description`) VALUES 
 (1,'testplan_tcversions','testcase_execution'),
 (2,'tcversions','testcase_review');
/*!40000 ALTER TABLE `assignment_types` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`attachments`
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`attachments`
--

/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`builds`
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Available builds';

--
-- Dumping data for table `tl18sample`.`builds`
--

/*!40000 ALTER TABLE `builds` DISABLE KEYS */;
INSERT INTO `builds` (`id`,`testplan_id`,`name`,`notes`,`active`,`is_open`) VALUES 
 (1,178,'Release 1','',1,1);
/*!40000 ALTER TABLE `builds` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`cfield_design_values`
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
-- Dumping data for table `tl18sample`.`cfield_design_values`
--

/*!40000 ALTER TABLE `cfield_design_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_design_values` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`cfield_execution_values`
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
-- Dumping data for table `tl18sample`.`cfield_execution_values`
--

/*!40000 ALTER TABLE `cfield_execution_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_execution_values` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`cfield_node_types`
--

DROP TABLE IF EXISTS `cfield_node_types`;
CREATE TABLE `cfield_node_types` (
  `field_id` int(10) NOT NULL default '0',
  `node_type_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`field_id`,`node_type_id`),
  KEY `idx_custom_fields_assign` (`node_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`cfield_node_types`
--

/*!40000 ALTER TABLE `cfield_node_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_node_types` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`cfield_testplan_design_values`
--

DROP TABLE IF EXISTS `cfield_testplan_design_values`;
CREATE TABLE `cfield_testplan_design_values` (
  `field_id` int(10) NOT NULL default '0',
  `link_id` int(10) NOT NULL default '0' COMMENT 'point to testplan_tcversion id',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`link_id`),
  KEY `idx_cfield_tplan_design_val` (`link_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`cfield_testplan_design_values`
--

/*!40000 ALTER TABLE `cfield_testplan_design_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_testplan_design_values` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`cfield_testprojects`
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
-- Dumping data for table `tl18sample`.`cfield_testprojects`
--

/*!40000 ALTER TABLE `cfield_testprojects` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_testprojects` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`custom_fields`
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
  `show_on_testplan_design` tinyint(3) unsigned NOT NULL default '0',
  `enable_on_testplan_design` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_custom_fields_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`custom_fields`
--

/*!40000 ALTER TABLE `custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_fields` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`db_version`
--

DROP TABLE IF EXISTS `db_version`;
CREATE TABLE `db_version` (
  `version` varchar(50) NOT NULL default 'unknown',
  `upgrade_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `notes` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`db_version`
--

/*!40000 ALTER TABLE `db_version` DISABLE KEYS */;
INSERT INTO `db_version` (`version`,`upgrade_ts`,`notes`) VALUES 
 ('DB 1.2','2009-02-21 15:54:14','first version with API feature');
/*!40000 ALTER TABLE `db_version` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`events`
--

DROP TABLE IF EXISTS `events`;
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
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`events`
--

/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` (`id`,`transaction_id`,`log_level`,`source`,`description`,`fired_at`,`activity`,`object_id`,`object_type`) VALUES 
 (1,1,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:21:\"audit_login_succeeded\";s:6:\"params\";a:2:{i:0;s:5:\"admin\";i:1;s:9:\"127.0.0.1\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228062,'LOGIN',1,'users'),
 (2,2,2,'GUI','No project found: Assume a new installation and redirect to create it',1235228064,NULL,NULL,NULL),
 (3,3,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_testproject_created\";s:6:\"params\";a:1:{i:0;s:21:\"Enterprise Space Ship\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228081,'CREATE',1,'testprojects'),
 (4,4,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:7:\"keyword\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228089,'LOCALIZATION',NULL,NULL),
 (5,5,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:7:\"keyword\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228118,'LOCALIZATION',NULL,NULL),
 (6,6,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:7:\"keyword\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228118,'LOCALIZATION',NULL,NULL);
INSERT INTO `events` (`id`,`transaction_id`,`log_level`,`source`,`description`,`fired_at`,`activity`,`object_id`,`object_type`) VALUES 
 (7,7,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:18:\"audit_user_created\";s:6:\"params\";a:1:{i:0;s:12:\"Captain Kirk\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228193,'CREATE',2,'users'),
 (8,8,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:18:\"audit_user_created\";s:6:\"params\";a:1:{i:0;s:8:\"Dr Spock\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228243,'CREATE',3,'users'),
 (9,9,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:22:\"audit_testplan_created\";s:6:\"params\";a:2:{i:0;s:21:\"Enterprise Space Ship\";i:1;s:15:\"Deep Space Nine\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228285,'CREATED',178,'testplans'),
 (10,10,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:19:\"audit_build_created\";s:6:\"params\";a:3:{i:0;s:21:\"Enterprise Space Ship\";i:1;s:15:\"Deep Space Nine\";i:2;s:9:\"Release 1\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228303,'CREATE',1,'builds'),
 (11,11,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:19:\"req_status_approved\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228317,'LOCALIZATION',NULL,NULL);
INSERT INTO `events` (`id`,`transaction_id`,`log_level`,`source`,`description`,`fired_at`,`activity`,`object_id`,`object_type`) VALUES 
 (12,12,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:19:\"req_status_approved\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228341,'LOCALIZATION',NULL,NULL),
 (13,12,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:22:\"audit_req_spec_created\";s:6:\"params\";a:2:{i:0;s:21:\"Enterprise Space Ship\";i:1;s:22:\"Req. Spec Warp Engine \";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228341,'CREATE',179,'req_specs'),
 (14,13,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:19:\"req_status_approved\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228353,'LOCALIZATION',NULL,NULL),
 (15,14,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:19:\"req_status_approved\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228401,'LOCALIZATION',NULL,NULL),
 (16,14,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_requirement_created\";s:6:\"params\";a:1:{i:0;s:7:\"WE-0001\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228401,'CREATE',180,'requirements');
INSERT INTO `events` (`id`,`transaction_id`,`log_level`,`source`,`description`,`fired_at`,`activity`,`object_id`,`object_type`) VALUES 
 (17,15,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:19:\"req_status_approved\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228411,'LOCALIZATION',NULL,NULL),
 (18,16,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:19:\"req_status_approved\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228458,'LOCALIZATION',NULL,NULL),
 (19,16,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_requirement_created\";s:6:\"params\";a:1:{i:0;s:7:\"WE-9999\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228458,'CREATE',181,'requirements'),
 (20,17,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:19:\"req_status_approved\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228470,'LOCALIZATION',NULL,NULL),
 (21,18,2,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_missing_localization\";s:6:\"params\";a:2:{i:0;s:19:\"req_status_approved\";i:1;s:5:\"en_GB\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:1;}',1235228500,'LOCALIZATION',NULL,NULL);
INSERT INTO `events` (`id`,`transaction_id`,`log_level`,`source`,`description`,`fired_at`,`activity`,`object_id`,`object_type`) VALUES 
 (22,18,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_requirement_created\";s:6:\"params\";a:1:{i:0;s:7:\"WE-X666\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1235228500,'CREATE',182,'requirements');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`execution_bugs`
--

DROP TABLE IF EXISTS `execution_bugs`;
CREATE TABLE `execution_bugs` (
  `execution_id` int(10) unsigned NOT NULL default '0',
  `bug_id` varchar(16) NOT NULL default '0',
  PRIMARY KEY  (`execution_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`execution_bugs`
--

/*!40000 ALTER TABLE `execution_bugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `execution_bugs` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`executions`
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
  `tcversion_number` smallint(5) unsigned NOT NULL default '1',
  `execution_type` tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated',
  `notes` text,
  PRIMARY KEY  (`id`),
  KEY `testplan_id_tcversion_id` (`testplan_id`,`tcversion_id`),
  KEY `exec_tcversion_id` (`tcversion_id`),
  KEY `exec_build_id` (`build_id`),
  KEY `execution_type` (`execution_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`executions`
--

/*!40000 ALTER TABLE `executions` DISABLE KEYS */;
/*!40000 ALTER TABLE `executions` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`keywords`
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`keywords`
--

/*!40000 ALTER TABLE `keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `keywords` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`milestones`
--

DROP TABLE IF EXISTS `milestones`;
CREATE TABLE `milestones` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `target_date` date NOT NULL default '0000-00-00',
  `a` tinyint(3) unsigned NOT NULL default '0',
  `b` tinyint(3) unsigned NOT NULL default '0',
  `c` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default 'undefined',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name_testplan_id` (`name`,`testplan_id`),
  KEY `testplan_id` (`testplan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`milestones`
--

/*!40000 ALTER TABLE `milestones` DISABLE KEYS */;
/*!40000 ALTER TABLE `milestones` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`node_types`
--

DROP TABLE IF EXISTS `node_types`;
CREATE TABLE `node_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default 'testproject',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`node_types`
--

/*!40000 ALTER TABLE `node_types` DISABLE KEYS */;
INSERT INTO `node_types` (`id`,`description`) VALUES 
 (1,'testproject'),
 (2,'testsuite'),
 (3,'testcase'),
 (4,'testcase_version'),
 (5,'testplan'),
 (6,'requirement_spec'),
 (7,'requirement');
/*!40000 ALTER TABLE `node_types` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`nodes_hierarchy`
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
) ENGINE=MyISAM AUTO_INCREMENT=183 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`nodes_hierarchy`
--

/*!40000 ALTER TABLE `nodes_hierarchy` DISABLE KEYS */;
INSERT INTO `nodes_hierarchy` (`id`,`name`,`parent_id`,`node_type_id`,`node_order`) VALUES 
 (1,'Enterprise Space Ship',NULL,1,1),
 (2,'Communications',1,2,0),
 (3,'Handheld devices',2,2,0),
 (4,'medium range devices',3,2,0),
 (5,'100% moisture conditions',4,3,0),
 (6,'',5,4,0),
 (7,'subatomic powered',4,2,0),
 (8,'nickel cadmiun powered',4,2,0),
 (9,'short range devices',3,2,0),
 (10,'100% moisture conditions',9,3,0),
 (11,'',10,4,0),
 (12,'acid rain half power',9,3,0),
 (13,'',12,4,0),
 (14,'Gamma Ray Storm',3,3,0),
 (15,'',14,4,0),
 (16,'10 G shock',3,3,0),
 (17,'',16,4,0),
 (18,'Subspace channels',2,2,0),
 (19,'short range devices',18,2,0),
 (20,'100% moisture conditions',19,3,0),
 (21,'',20,4,0),
 (22,'acid rain half power',19,3,0),
 (23,'',22,4,0),
 (24,'medium range devices',18,2,0),
 (25,'100% moisture conditions',24,3,0),
 (26,'',25,4,0),
 (27,'subatomic powered',24,2,0),
 (28,'nickel cadmiun powered',24,2,0),
 (29,'Black hole test',18,3,0),
 (30,'',29,4,0),
 (31,'Holodeck',1,2,0),
 (32,'Apollo 10  Simulation',31,2,0);
INSERT INTO `nodes_hierarchy` (`id`,`name`,`parent_id`,`node_type_id`,`node_order`) VALUES 
 (33,'Deploy',32,2,0),
 (34,'From Disk',33,3,0),
 (35,'',34,4,0),
 (36,'From Network',33,3,0),
 (37,'',36,4,0),
 (38,'From USB device',33,3,0),
 (39,'',38,4,0),
 (40,'From flash device',33,3,0),
 (41,'',40,4,0),
 (42,'Rewind',32,2,0),
 (43,'Full speed unload',42,3,0),
 (44,'',43,4,0),
 (45,'Half speed unload',42,3,0),
 (46,'',45,4,0),
 (47,'Reload',32,2,0),
 (48,'From USB device',47,3,0),
 (49,'',48,4,0),
 (50,'From flash device',47,3,0),
 (51,'',50,4,0),
 (52,'From Network',47,3,0),
 (53,'',52,4,0),
 (54,'From Disk',47,3,0),
 (55,'',54,4,0),
 (56,'Unload',32,2,0),
 (57,'Full speed unload',56,3,0),
 (58,'',57,4,0),
 (59,'Half speed unload',56,3,0),
 (60,'',59,4,0),
 (61,'Antartic Simulation',31,2,0),
 (62,'Deploy',61,2,0),
 (63,'From Disk',62,3,0),
 (64,'',63,4,0),
 (65,'From Network',62,3,0),
 (66,'',65,4,0),
 (67,'From USB device',62,3,0),
 (68,'',67,4,0),
 (69,'From flash device',62,3,0),
 (70,'',69,4,0),
 (71,'Rewind',61,2,0);
INSERT INTO `nodes_hierarchy` (`id`,`name`,`parent_id`,`node_type_id`,`node_order`) VALUES 
 (72,'Full speed unload',71,3,0),
 (73,'',72,4,0),
 (74,'Half speed unload',71,3,0),
 (75,'',74,4,0),
 (76,'Reload',61,2,0),
 (77,'From USB device',76,3,0),
 (78,'',77,4,0),
 (79,'From flash device',76,3,0),
 (80,'',79,4,0),
 (81,'From Network',76,3,0),
 (82,'',81,4,0),
 (83,'From Disk',76,3,0),
 (84,'',83,4,0),
 (85,'Unload',61,2,0),
 (86,'Full speed unload',85,3,0),
 (87,'',86,4,0),
 (88,'Half speed unload',85,3,0),
 (89,'',88,4,0),
 (90,'Wild West Simulation',31,2,0),
 (91,'Deploy',90,2,0),
 (92,'From Disk',91,3,0),
 (93,'',92,4,0),
 (94,'From Network',91,3,0),
 (95,'',94,4,0),
 (96,'From USB device',91,3,0),
 (97,'',96,4,0),
 (98,'From flash device',91,3,0),
 (99,'',98,4,0),
 (100,'Rewind',90,2,0),
 (101,'Full speed unload',100,3,0),
 (102,'',101,4,0),
 (103,'Half speed unload',100,3,0),
 (104,'',103,4,0),
 (105,'Reload',90,2,0),
 (106,'From USB device',105,3,0),
 (107,'',106,4,0),
 (108,'From flash device',105,3,0),
 (109,'',108,4,0);
INSERT INTO `nodes_hierarchy` (`id`,`name`,`parent_id`,`node_type_id`,`node_order`) VALUES 
 (110,'From Network',105,3,0),
 (111,'',110,4,0),
 (112,'From Disk',105,3,0),
 (113,'',112,4,0),
 (114,'Unload',90,2,0),
 (115,'Full speed unload',114,3,0),
 (116,'',115,4,0),
 (117,'Half speed unload',114,3,0),
 (118,'',117,4,0),
 (119,'UnderSea Life Simulation',31,2,0),
 (120,'Deploy',119,2,0),
 (121,'From Disk',120,3,0),
 (122,'',121,4,0),
 (123,'From Network',120,3,0),
 (124,'',123,4,0),
 (125,'From USB device',120,3,0),
 (126,'',125,4,0),
 (127,'From flash device',120,3,0),
 (128,'',127,4,0),
 (129,'Rewind',119,2,0),
 (130,'Full speed unload',129,3,0),
 (131,'',130,4,0),
 (132,'Half speed unload',129,3,0),
 (133,'',132,4,0),
 (134,'Reload',119,2,0),
 (135,'From USB device',134,3,0),
 (136,'',135,4,0),
 (137,'From flash device',134,3,0),
 (138,'',137,4,0),
 (139,'From Network',134,3,0),
 (140,'',139,4,0),
 (141,'From Disk',134,3,0),
 (142,'',141,4,0),
 (143,'Unload',119,2,0),
 (144,'Full speed unload',143,3,0),
 (145,'',144,4,0);
INSERT INTO `nodes_hierarchy` (`id`,`name`,`parent_id`,`node_type_id`,`node_order`) VALUES 
 (146,'Half speed unload',143,3,0),
 (147,'',146,4,0),
 (148,'Light settings',31,3,0),
 (149,'',148,4,0),
 (150,'Sound Settings',31,3,0),
 (151,'',150,4,0),
 (152,'3D Settings',31,3,0),
 (153,'',152,4,0),
 (154,'Stop',31,3,0),
 (155,'',154,4,0),
 (156,'Start',31,3,0),
 (157,'',156,4,0),
 (158,'Propulsion Systems',1,2,0),
 (159,'Main engine',158,2,0),
 (160,'Emergency stop',159,3,0),
 (161,'',160,4,0),
 (162,'Transportation',1,2,0),
 (163,'Individual',162,2,0),
 (164,'High speed',163,3,0),
 (165,'',164,4,0),
 (166,'Half speed stop',163,3,0),
 (167,'',166,4,0),
 (168,'Jump start',163,3,0),
 (169,'',168,4,0),
 (170,'Terrestrial',162,2,0),
 (171,'Infrared guidance on moon eclipse',170,3,0),
 (172,'',171,4,0),
 (173,'HyperSpace',162,2,0),
 (174,'Start gate connection',173,3,0),
 (175,'',174,4,0),
 (176,'Stop gate connection',173,3,0),
 (177,'',176,4,0),
 (178,'Deep Space Nine',1,5,0),
 (179,'Req. Spec Warp Engine',1,6,0),
 (180,'Quick Start Low temperature',179,7,0);
INSERT INTO `nodes_hierarchy` (`id`,`name`,`parent_id`,`node_type_id`,`node_order`) VALUES 
 (181,'Gamma Ray Emissions',179,7,0),
 (182,'Coriolis Effet',179,7,0);
/*!40000 ALTER TABLE `nodes_hierarchy` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`object_keywords`
--

DROP TABLE IF EXISTS `object_keywords`;
CREATE TABLE `object_keywords` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_id` int(10) unsigned NOT NULL default '0',
  `fk_table` varchar(30) default '',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`object_keywords`
--

/*!40000 ALTER TABLE `object_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `object_keywords` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`req_coverage`
--

DROP TABLE IF EXISTS `req_coverage`;
CREATE TABLE `req_coverage` (
  `req_id` int(10) NOT NULL,
  `testcase_id` int(10) NOT NULL,
  KEY `req_testcase` (`req_id`,`testcase_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='relation test case ** requirements';

--
-- Dumping data for table `tl18sample`.`req_coverage`
--

/*!40000 ALTER TABLE `req_coverage` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_coverage` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`req_specs`
--

DROP TABLE IF EXISTS `req_specs`;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Dev. Documents (e.g. System Requirements Specification)';

--
-- Dumping data for table `tl18sample`.`req_specs`
--

/*!40000 ALTER TABLE `req_specs` DISABLE KEYS */;
INSERT INTO `req_specs` (`id`,`testproject_id`,`title`,`scope`,`total_req`,`type`,`author_id`,`creation_ts`,`modifier_id`,`modification_ts`) VALUES 
 (179,1,'Req. Spec Warp Engine','',0,'n',1,'2009-02-21 15:59:01',NULL,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `req_specs` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`requirements`
--

DROP TABLE IF EXISTS `requirements`;
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
  UNIQUE KEY `req_doc_id` (`srs_id`,`req_doc_id`),
  KEY `srs_id` (`srs_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`requirements`
--

/*!40000 ALTER TABLE `requirements` DISABLE KEYS */;
INSERT INTO `requirements` (`id`,`srs_id`,`req_doc_id`,`title`,`scope`,`status`,`type`,`author_id`,`creation_ts`,`modifier_id`,`modification_ts`) VALUES 
 (180,179,'WE-0001','Quick Start Low temperature','','V','V',1,'2009-02-21 16:00:01',NULL,'0000-00-00 00:00:00'),
 (181,179,'WE-9999','Gamma Ray Emissions','','V','V',1,'2009-02-21 16:00:58',NULL,'0000-00-00 00:00:00'),
 (182,179,'WE-X666','Coriolis Effet','','V','V',1,'2009-02-21 16:01:40',NULL,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `requirements` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`rights`
--

DROP TABLE IF EXISTS `rights`;
CREATE TABLE `rights` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `rights_descr` (`description`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`rights`
--

/*!40000 ALTER TABLE `rights` DISABLE KEYS */;
INSERT INTO `rights` (`id`,`description`) VALUES 
 (1,'testplan_execute'),
 (2,'testplan_create_build'),
 (3,'testplan_metrics'),
 (4,'testplan_planning'),
 (5,'testplan_user_role_assignment'),
 (6,'mgt_view_tc'),
 (7,'mgt_modify_tc'),
 (8,'mgt_view_key'),
 (9,'mgt_modify_key'),
 (10,'mgt_view_req'),
 (11,'mgt_modify_req'),
 (12,'mgt_modify_product'),
 (13,'mgt_users'),
 (14,'role_management'),
 (15,'user_role_assignment'),
 (16,'mgt_testplan_create'),
 (17,'cfield_view'),
 (18,'cfield_management'),
 (19,'system_configuration'),
 (20,'mgt_view_events'),
 (21,'mgt_view_usergroups'),
 (22,'events_mgt');
/*!40000 ALTER TABLE `rights` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`risk_assignments`
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`risk_assignments`
--

/*!40000 ALTER TABLE `risk_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_assignments` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`role_rights`
--

DROP TABLE IF EXISTS `role_rights`;
CREATE TABLE `role_rights` (
  `role_id` int(10) NOT NULL default '0',
  `right_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`role_id`,`right_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`role_rights`
--

/*!40000 ALTER TABLE `role_rights` DISABLE KEYS */;
INSERT INTO `role_rights` (`role_id`,`right_id`) VALUES 
 (4,3),
 (4,6),
 (4,7),
 (4,8),
 (4,9),
 (4,10),
 (4,11),
 (5,3),
 (5,6),
 (5,8),
 (6,1),
 (6,2),
 (6,3),
 (6,6),
 (6,7),
 (6,8),
 (6,9),
 (6,11),
 (7,1),
 (7,3),
 (7,6),
 (7,8),
 (8,1),
 (8,2),
 (8,3),
 (8,4),
 (8,5),
 (8,6),
 (8,7),
 (8,8),
 (8,9),
 (8,10),
 (8,11),
 (8,12),
 (8,13),
 (8,14),
 (8,15),
 (8,16),
 (8,17),
 (8,18),
 (8,19),
 (8,20),
 (8,21),
 (8,22),
 (9,1),
 (9,2),
 (9,3),
 (9,4),
 (9,5),
 (9,6),
 (9,7),
 (9,8),
 (9,9),
 (9,10),
 (9,11),
 (9,15),
 (9,16);
/*!40000 ALTER TABLE `role_rights` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `notes` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `roles_descr` (`description`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`roles`
--

/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`,`description`,`notes`) VALUES 
 (1,'<reserved system role 1>',NULL),
 (2,'<reserved system role 2>',NULL),
 (3,'<no rights>',NULL),
 (4,'test designer',NULL),
 (5,'guest',NULL),
 (6,'senior tester',NULL),
 (7,'tester',NULL),
 (8,'admin',NULL),
 (9,'leader',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`tcversions`
--

DROP TABLE IF EXISTS `tcversions`;
CREATE TABLE `tcversions` (
  `id` int(10) unsigned NOT NULL,
  `tc_external_id` int(10) unsigned default NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`tcversions`
--

/*!40000 ALTER TABLE `tcversions` DISABLE KEYS */;
INSERT INTO `tcversions` (`id`,`tc_external_id`,`version`,`summary`,`steps`,`expected_results`,`importance`,`author_id`,`creation_ts`,`updater_id`,`modification_ts`,`active`,`is_open`,`execution_type`) VALUES 
 (6,1,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (11,2,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (13,3,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (15,4,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (17,5,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (21,6,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (23,7,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (26,8,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (30,9,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (35,10,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (37,11,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1);
INSERT INTO `tcversions` (`id`,`tc_external_id`,`version`,`summary`,`steps`,`expected_results`,`importance`,`author_id`,`creation_ts`,`updater_id`,`modification_ts`,`active`,`is_open`,`execution_type`) VALUES 
 (39,12,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (41,13,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (44,14,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (46,15,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (49,16,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (51,17,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (53,18,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (55,19,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (58,20,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (60,21,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (64,22,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1);
INSERT INTO `tcversions` (`id`,`tc_external_id`,`version`,`summary`,`steps`,`expected_results`,`importance`,`author_id`,`creation_ts`,`updater_id`,`modification_ts`,`active`,`is_open`,`execution_type`) VALUES 
 (66,23,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (68,24,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (70,25,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (73,26,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (75,27,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (78,28,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (80,29,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (82,30,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (84,31,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (87,32,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (89,33,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1);
INSERT INTO `tcversions` (`id`,`tc_external_id`,`version`,`summary`,`steps`,`expected_results`,`importance`,`author_id`,`creation_ts`,`updater_id`,`modification_ts`,`active`,`is_open`,`execution_type`) VALUES 
 (93,34,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (95,35,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (97,36,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (99,37,1,'','','',2,1,'2009-02-21 15:55:16',NULL,'0000-00-00 00:00:00',1,1,1),
 (102,38,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (104,39,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (107,40,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (109,41,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (111,42,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (113,43,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (116,44,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1);
INSERT INTO `tcversions` (`id`,`tc_external_id`,`version`,`summary`,`steps`,`expected_results`,`importance`,`author_id`,`creation_ts`,`updater_id`,`modification_ts`,`active`,`is_open`,`execution_type`) VALUES 
 (118,45,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (122,46,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (124,47,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (126,48,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (128,49,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (131,50,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (133,51,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (136,52,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (138,53,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (140,54,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (142,55,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1);
INSERT INTO `tcversions` (`id`,`tc_external_id`,`version`,`summary`,`steps`,`expected_results`,`importance`,`author_id`,`creation_ts`,`updater_id`,`modification_ts`,`active`,`is_open`,`execution_type`) VALUES 
 (145,56,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (147,57,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (149,58,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (151,59,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (153,60,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (155,61,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (157,62,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (161,63,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (165,64,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (167,65,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (169,66,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1);
INSERT INTO `tcversions` (`id`,`tc_external_id`,`version`,`summary`,`steps`,`expected_results`,`importance`,`author_id`,`creation_ts`,`updater_id`,`modification_ts`,`active`,`is_open`,`execution_type`) VALUES 
 (172,67,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (175,68,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1),
 (177,69,1,'','','',2,1,'2009-02-21 15:55:17',NULL,'0000-00-00 00:00:00',1,1,1);
/*!40000 ALTER TABLE `tcversions` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`testcase_keywords`
--

DROP TABLE IF EXISTS `testcase_keywords`;
CREATE TABLE `testcase_keywords` (
  `testcase_id` int(10) unsigned NOT NULL default '0',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`testcase_id`,`keyword_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`testcase_keywords`
--

/*!40000 ALTER TABLE `testcase_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `testcase_keywords` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`testplan_tcversions`
--

DROP TABLE IF EXISTS `testplan_tcversions`;
CREATE TABLE `testplan_tcversions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `testplan_id` int(10) unsigned NOT NULL default '0',
  `tcversion_id` int(10) unsigned NOT NULL default '0',
  `node_order` int(10) unsigned NOT NULL default '1',
  `urgency` smallint(5) NOT NULL default '2',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tp_tcversion` (`testplan_id`,`tcversion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`testplan_tcversions`
--

/*!40000 ALTER TABLE `testplan_tcversions` DISABLE KEYS */;
/*!40000 ALTER TABLE `testplan_tcversions` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`testplans`
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
-- Dumping data for table `tl18sample`.`testplans`
--

/*!40000 ALTER TABLE `testplans` DISABLE KEYS */;
INSERT INTO `testplans` (`id`,`testproject_id`,`notes`,`active`,`is_open`) VALUES 
 (178,1,'',1,1);
/*!40000 ALTER TABLE `testplans` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`testprojects`
--

DROP TABLE IF EXISTS `testprojects`;
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
  UNIQUE KEY `prefix` (`prefix`),
  KEY `id_active` (`id`,`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`testprojects`
--

/*!40000 ALTER TABLE `testprojects` DISABLE KEYS */;
INSERT INTO `testprojects` (`id`,`notes`,`color`,`active`,`option_reqs`,`option_priority`,`option_automation`,`prefix`,`tc_counter`) VALUES 
 (1,'','',1,1,1,1,'ESP',69);
/*!40000 ALTER TABLE `testprojects` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`testsuites`
--

DROP TABLE IF EXISTS `testsuites`;
CREATE TABLE `testsuites` (
  `id` int(10) unsigned NOT NULL,
  `details` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`testsuites`
--

/*!40000 ALTER TABLE `testsuites` DISABLE KEYS */;
INSERT INTO `testsuites` (`id`,`details`) VALUES 
 (2,'<p>Communication Systems of all types</p>'),
 (3,''),
 (4,''),
 (7,''),
 (8,''),
 (9,''),
 (18,'<p>Only basic subspace features</p>'),
 (19,''),
 (24,''),
 (27,''),
 (28,''),
 (31,''),
 (32,''),
 (33,''),
 (42,''),
 (47,''),
 (56,''),
 (61,''),
 (62,''),
 (71,''),
 (76,''),
 (85,''),
 (90,''),
 (91,''),
 (100,''),
 (105,''),
 (114,''),
 (119,''),
 (120,''),
 (129,''),
 (134,''),
 (143,''),
 (158,''),
 (159,''),
 (162,''),
 (163,''),
 (170,''),
 (173,'');
/*!40000 ALTER TABLE `testsuites` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`text_templates`
--

DROP TABLE IF EXISTS `text_templates`;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Global Project Templates';

--
-- Dumping data for table `tl18sample`.`text_templates`
--

/*!40000 ALTER TABLE `text_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `text_templates` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `entry_point` varchar(45) NOT NULL default '',
  `start_time` int(10) unsigned NOT NULL default '0',
  `end_time` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `session_id` varchar(45) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`transactions`
--

/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` (`id`,`entry_point`,`start_time`,`end_time`,`user_id`,`session_id`) VALUES 
 (1,'/w3/tl/tl18/head_20090204/login.php',1235228062,1235228062,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (2,'/tl18/head_20090204/lib/general/mainPage.php',1235228064,1235228064,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (3,'/head_20090204/lib/project/projectEdit.php',1235228081,1235228081,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (4,'/lib/testcases/listTestCases.php',1235228089,1235228089,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (5,'/lib/testcases/listTestCases.php',1235228118,1235228118,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (6,'/lib/testcases/listTestCases.php',1235228118,1235228118,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (7,'/lib/usermanagement/usersEdit.php',1235228193,1235228193,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (8,'/lib/usermanagement/usersEdit.php',1235228243,1235228243,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (9,'/tl/tl18/head_20090204/lib/plan/planEdit.php',1235228285,1235228285,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (10,'/tl/tl18/head_20090204/lib/plan/buildEdit.php',1235228303,1235228303,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (11,'/lib/requirements/reqSpecEdit.php',1235228317,1235228317,1,'4atbmt4clgs0mfoo5karo8htf4');
INSERT INTO `transactions` (`id`,`entry_point`,`start_time`,`end_time`,`user_id`,`session_id`) VALUES 
 (12,'/lib/requirements/reqSpecEdit.php',1235228341,1235228341,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (13,'/head_20090204/lib/requirements/reqEdit.php',1235228353,1235228353,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (14,'/head_20090204/lib/requirements/reqEdit.php',1235228401,1235228401,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (15,'/head_20090204/lib/requirements/reqEdit.php',1235228411,1235228412,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (16,'/head_20090204/lib/requirements/reqEdit.php',1235228458,1235228458,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (17,'/head_20090204/lib/requirements/reqEdit.php',1235228470,1235228470,1,'4atbmt4clgs0mfoo5karo8htf4'),
 (18,'/head_20090204/lib/requirements/reqEdit.php',1235228500,1235228500,1,'4atbmt4clgs0mfoo5karo8htf4');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`user_assignments`
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`user_assignments`
--

/*!40000 ALTER TABLE `user_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_assignments` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`user_group`
--

DROP TABLE IF EXISTS `user_group`;
CREATE TABLE `user_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`user_group`
--

/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`user_group_assign`
--

DROP TABLE IF EXISTS `user_group_assign`;
CREATE TABLE `user_group_assign` (
  `usergroup_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `idx_user_group_assign` (`usergroup_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`user_group_assign`
--

/*!40000 ALTER TABLE `user_group_assign` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_group_assign` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`user_testplan_roles`
--

DROP TABLE IF EXISTS `user_testplan_roles`;
CREATE TABLE `user_testplan_roles` (
  `user_id` int(10) NOT NULL default '0',
  `testplan_id` int(10) NOT NULL default '0',
  `role_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`testplan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`user_testplan_roles`
--

/*!40000 ALTER TABLE `user_testplan_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_testplan_roles` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`user_testproject_roles`
--

DROP TABLE IF EXISTS `user_testproject_roles`;
CREATE TABLE `user_testproject_roles` (
  `user_id` int(10) NOT NULL default '0',
  `testproject_id` int(10) NOT NULL default '0',
  `role_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`testproject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tl18sample`.`user_testproject_roles`
--

/*!40000 ALTER TABLE `user_testproject_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_testproject_roles` ENABLE KEYS */;


--
-- Table structure for table `tl18sample`.`users`
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
  `script_key` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='User information';

--
-- Dumping data for table `tl18sample`.`users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`,`login`,`password`,`role_id`,`email`,`first`,`last`,`locale`,`default_testproject_id`,`active`,`script_key`) VALUES 
 (1,'admin','21232f297a57a5a743894a0e4a801fc3',8,'','Testlink','Administrator','en_GB',NULL,1,NULL),
 (2,'Captain Kirk','4eae35f1b35977a00ebd8086c259d4c9',9,'james.kirk@enterprise.federation.org','James','Kirk','en_GB',NULL,1,NULL),
 (3,'Dr Spock','4eae35f1b35977a00ebd8086c259d4c9',4,'dr.spock@vulcan.universe.org','Spock','The Vulcan','en_GB',NULL,1,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
