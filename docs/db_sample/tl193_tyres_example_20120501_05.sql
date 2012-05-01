-- MySQL dump 10.13  Distrib 5.5.16, for Linux (i686)
--
-- Host: localhost    Database: tl193_sample01
-- ------------------------------------------------------
-- Server version	5.5.16

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `tl193_sample01`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `tl193_sample01` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `tl193_sample01`;

--
-- Table structure for table `assignment_status`
--

DROP TABLE IF EXISTS `assignment_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_status`
--

LOCK TABLES `assignment_status` WRITE;
/*!40000 ALTER TABLE `assignment_status` DISABLE KEYS */;
INSERT INTO `assignment_status` VALUES (1,'open'),(2,'closed'),(3,'completed'),(4,'todo_urgent'),(5,'todo');
/*!40000 ALTER TABLE `assignment_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignment_types`
--

DROP TABLE IF EXISTS `assignment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_table` varchar(30) DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_types`
--

LOCK TABLES `assignment_types` WRITE;
/*!40000 ALTER TABLE `assignment_types` DISABLE KEYS */;
INSERT INTO `assignment_types` VALUES (1,'testplan_tcversions','testcase_execution'),(2,'tcversions','testcase_review');
/*!40000 ALTER TABLE `assignment_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fk_table` varchar(250) DEFAULT '',
  `title` varchar(250) DEFAULT '',
  `description` varchar(250) DEFAULT '',
  `file_name` varchar(250) NOT NULL DEFAULT '',
  `file_path` varchar(250) DEFAULT '',
  `file_size` int(11) NOT NULL DEFAULT '0',
  `file_type` varchar(250) NOT NULL DEFAULT '',
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `content` longblob,
  `compression_type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `builds`
--

DROP TABLE IF EXISTS `builds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `builds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT 'undefined',
  `notes` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `author_id` int(10) unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `release_date` date DEFAULT NULL,
  `closed_on_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`testplan_id`,`name`),
  KEY `testplan_id` (`testplan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Available builds';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `builds`
--

LOCK TABLES `builds` WRITE;
/*!40000 ALTER TABLE `builds` DISABLE KEYS */;
INSERT INTO `builds` VALUES (1,4,'PZ X.1','',1,1,NULL,'2012-05-01 09:26:40',NULL,NULL),(2,4,'PZ X.2','',1,1,NULL,'2012-05-01 09:26:54',NULL,NULL),(3,4,'PZ X.3','',1,1,NULL,'2012-05-01 09:27:08',NULL,NULL);
/*!40000 ALTER TABLE `builds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cfield_design_values`
--

DROP TABLE IF EXISTS `cfield_design_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfield_design_values` (
  `field_id` int(10) NOT NULL DEFAULT '0',
  `node_id` int(10) NOT NULL DEFAULT '0',
  `value` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`field_id`,`node_id`),
  KEY `idx_cfield_design_values` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cfield_design_values`
--

LOCK TABLES `cfield_design_values` WRITE;
/*!40000 ALTER TABLE `cfield_design_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_design_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cfield_execution_values`
--

DROP TABLE IF EXISTS `cfield_execution_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfield_execution_values` (
  `field_id` int(10) NOT NULL DEFAULT '0',
  `execution_id` int(10) NOT NULL DEFAULT '0',
  `testplan_id` int(10) NOT NULL DEFAULT '0',
  `tcversion_id` int(10) NOT NULL DEFAULT '0',
  `value` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`field_id`,`execution_id`,`testplan_id`,`tcversion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cfield_execution_values`
--

LOCK TABLES `cfield_execution_values` WRITE;
/*!40000 ALTER TABLE `cfield_execution_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_execution_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cfield_node_types`
--

DROP TABLE IF EXISTS `cfield_node_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfield_node_types` (
  `field_id` int(10) NOT NULL DEFAULT '0',
  `node_type_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`,`node_type_id`),
  KEY `idx_custom_fields_assign` (`node_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cfield_node_types`
--

LOCK TABLES `cfield_node_types` WRITE;
/*!40000 ALTER TABLE `cfield_node_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_node_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cfield_testplan_design_values`
--

DROP TABLE IF EXISTS `cfield_testplan_design_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfield_testplan_design_values` (
  `field_id` int(10) NOT NULL DEFAULT '0',
  `link_id` int(10) NOT NULL DEFAULT '0' COMMENT 'point to testplan_tcversion id',
  `value` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`field_id`,`link_id`),
  KEY `idx_cfield_tplan_design_val` (`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cfield_testplan_design_values`
--

LOCK TABLES `cfield_testplan_design_values` WRITE;
/*!40000 ALTER TABLE `cfield_testplan_design_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_testplan_design_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cfield_testprojects`
--

DROP TABLE IF EXISTS `cfield_testprojects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfield_testprojects` (
  `field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `testproject_id` int(10) unsigned NOT NULL DEFAULT '0',
  `display_order` smallint(5) unsigned NOT NULL DEFAULT '1',
  `location` smallint(5) unsigned NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `required_on_design` tinyint(1) NOT NULL DEFAULT '0',
  `required_on_execution` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`,`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cfield_testprojects`
--

LOCK TABLES `cfield_testprojects` WRITE;
/*!40000 ALTER TABLE `cfield_testprojects` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_testprojects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_fields`
--

DROP TABLE IF EXISTS `custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_fields` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `label` varchar(64) NOT NULL DEFAULT '' COMMENT 'label to display on user interface',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `possible_values` varchar(4000) NOT NULL DEFAULT '',
  `default_value` varchar(4000) NOT NULL DEFAULT '',
  `valid_regexp` varchar(255) NOT NULL DEFAULT '',
  `length_min` int(10) NOT NULL DEFAULT '0',
  `length_max` int(10) NOT NULL DEFAULT '0',
  `show_on_design` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1=> show it during specification design',
  `enable_on_design` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1=> user can write/manage it during specification design',
  `show_on_execution` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1=> show it during test case execution',
  `enable_on_execution` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1=> user can write/manage it during test case execution',
  `show_on_testplan_design` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `enable_on_testplan_design` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_custom_fields_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_fields`
--

LOCK TABLES `custom_fields` WRITE;
/*!40000 ALTER TABLE `custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `db_version`
--

DROP TABLE IF EXISTS `db_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_version` (
  `version` varchar(50) NOT NULL DEFAULT 'unknown',
  `upgrade_ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `db_version`
--

LOCK TABLES `db_version` WRITE;
/*!40000 ALTER TABLE `db_version` DISABLE KEYS */;
INSERT INTO `db_version` VALUES ('DB 1.4','2012-05-01 10:56:28','TestLink 1.9.1');
/*!40000 ALTER TABLE `db_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` int(10) unsigned NOT NULL DEFAULT '0',
  `log_level` smallint(5) unsigned NOT NULL DEFAULT '0',
  `source` varchar(45) DEFAULT NULL,
  `description` text NOT NULL,
  `fired_at` int(10) unsigned NOT NULL DEFAULT '0',
  `activity` varchar(45) DEFAULT NULL,
  `object_id` int(10) unsigned DEFAULT NULL,
  `object_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `fired_at` (`fired_at`)
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,1,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(2,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(3,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(4,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(5,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(6,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(7,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(8,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(9,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(10,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(11,2,2,'GUI','E_NOTICE\nUndefined index: testprojectOptions - in /hdextra/development/tl-old/tl193-untouched/lib/general/mainPage.php - Line 63',1335862631,'PHP',NULL,NULL),(12,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/general/mainPage.php - Line 63',1335862631,'PHP',NULL,NULL),(13,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(14,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(15,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(16,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(17,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(18,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(19,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(20,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(21,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(22,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(23,2,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862631,'PHP',NULL,NULL),(24,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(25,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(26,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(27,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(28,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(29,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(30,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(31,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(32,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(33,3,2,'GUI','E_NOTICE\nUndefined index: testprojectOptions - in /hdextra/development/tl-old/tl193-untouched/lib/general/mainPage.php - Line 63',1335862696,'PHP',NULL,NULL),(34,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/general/mainPage.php - Line 63',1335862696,'PHP',NULL,NULL),(35,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(36,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(37,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(38,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(39,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(40,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(41,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(42,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(43,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(44,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(45,3,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(46,4,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlUser.class.php - Line 726',1335862696,'PHP',NULL,NULL),(47,5,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:21:\"audit_login_succeeded\";s:6:\"params\";a:2:{i:0;s:5:\"admin\";i:1;s:9:\"127.0.0.1\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335862713,'LOGIN',1,'users'),(48,6,2,'GUI','No project found: Assume a new installation and redirect to create it',1335862714,NULL,NULL,NULL),(49,7,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_testproject_created\";s:6:\"params\";a:1:{i:0;s:22:\"TestLink 193 - Reports\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335862746,'CREATE',1,'testprojects'),(50,8,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_testproject_created\";s:6:\"params\";a:1:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864014,'CREATE',2,'testprojects'),(51,9,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_testproject_created\";s:6:\"params\";a:1:{i:0;s:29:\"Formula One Pirelli Wet Tyres\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864053,'CREATE',3,'testprojects'),(52,10,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:22:\"audit_testplan_created\";s:6:\"params\";a:2:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:22:\"P Zero Red (Supersoft)\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864120,'CREATED',4,'testplans'),(53,11,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:22:\"audit_testplan_created\";s:6:\"params\";a:2:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:20:\"P Zero Yellow (Soft)\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864160,'CREATED',5,'testplans'),(54,12,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:22:\"audit_testplan_created\";s:6:\"params\";a:2:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:21:\"P Zero White (Medium)\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864193,'CREATED',6,'testplans'),(55,13,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:22:\"audit_testplan_created\";s:6:\"params\";a:2:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:20:\"P Zero Silver (Hard)\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864246,'CREATED',7,'testplans'),(56,14,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:22:\"audit_testplan_created\";s:6:\"params\";a:2:{i:0;s:29:\"Formula One Pirelli Wet Tyres\";i:1;s:30:\"Cinturato Green (Intermediate)\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864293,'CREATED',8,'testplans'),(57,15,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:22:\"audit_testplan_created\";s:6:\"params\";a:2:{i:0;s:29:\"Formula One Pirelli Wet Tyres\";i:1;s:20:\"Cinturato Blue (Wet)\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864326,'CREATED',9,'testplans'),(58,16,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:40:\"audit_all_user_roles_removed_testproject\";s:6:\"params\";a:1:{i:0;s:22:\"TestLink 193 - Reports\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864347,'ASSIGN',1,'testprojects'),(59,16,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_testproject_deleted\";s:6:\"params\";a:1:{i:0;s:22:\"TestLink 193 - Reports\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864347,'DELETE',1,'testprojects'),(60,17,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:19:\"audit_build_created\";s:6:\"params\";a:3:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:22:\"P Zero Red (Supersoft)\";i:2;s:11:\"ZeroRed X.1\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864400,'CREATE',1,'builds'),(61,18,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:19:\"audit_build_created\";s:6:\"params\";a:3:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:22:\"P Zero Red (Supersoft)\";i:2;s:11:\"ZeroRed X.2\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864414,'CREATE',2,'builds'),(62,19,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:19:\"audit_build_created\";s:6:\"params\";a:3:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:22:\"P Zero Red (Supersoft)\";i:2;s:11:\"ZeroRed X.3\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864428,'CREATE',3,'builds'),(63,20,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:17:\"audit_build_saved\";s:6:\"params\";a:3:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:22:\"P Zero Red (Supersoft)\";i:2;s:6:\"PZ X.1\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864459,'SAVE',1,'builds'),(64,21,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:17:\"audit_build_saved\";s:6:\"params\";a:3:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:22:\"P Zero Red (Supersoft)\";i:2;s:6:\"PZ X.2\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864466,'SAVE',2,'builds'),(65,22,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:17:\"audit_build_saved\";s:6:\"params\";a:3:{i:0;s:29:\"Formula One Pirelli Dry Tyres\";i:1;s:22:\"P Zero Red (Supersoft)\";i:2;s:6:\"PZ X.3\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335864473,'SAVE',3,'builds'),(66,23,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:21:\"audit_keyword_created\";s:6:\"params\";a:1:{i:0;s:10:\"Mechanical\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335866991,'CREATE',1,'keywords'),(67,24,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:21:\"audit_keyword_created\";s:6:\"params\";a:1:{i:0;s:10:\"Resistance\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867005,'CREATE',2,'keywords'),(68,25,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:21:\"audit_keyword_created\";s:6:\"params\";a:1:{i:0;s:15:\"Tear Resistance\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867022,'CREATE',3,'keywords'),(69,26,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:21:\"audit_keyword_created\";s:6:\"params\";a:1:{i:0;s:12:\"Mountability\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867034,'CREATE',4,'keywords'),(70,27,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:21:\"audit_keyword_created\";s:6:\"params\";a:1:{i:0;s:10:\"Subjective\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867048,'CREATE',5,'keywords'),(71,28,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:21:\"audit_keyword_created\";s:6:\"params\";a:1:{i:0;s:9:\"Objective\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867057,'CREATE',6,'keywords'),(72,29,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:11:\"GRIP-SL-001\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867108,'ASSIGN',31,'nodes_hierarchy'),(73,29,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"GRIP-SL-001\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867108,'ASSIGN',31,'nodes_hierarchy'),(74,30,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"GRIP-SL-002\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867121,'ASSIGN',33,'nodes_hierarchy'),(75,30,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:11:\"GRIP-SL-002\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867121,'ASSIGN',33,'nodes_hierarchy'),(76,31,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:11:\"GRIP-LC-001\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867626,'ASSIGN',35,'nodes_hierarchy'),(77,31,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"GRIP-LC-001\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867626,'ASSIGN',35,'nodes_hierarchy'),(78,31,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:11:\"GRIP-LC-002\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867626,'ASSIGN',37,'nodes_hierarchy'),(79,31,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"GRIP-LC-002\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867626,'ASSIGN',37,'nodes_hierarchy'),(80,31,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:11:\"GRIP-LC-003\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867626,'ASSIGN',39,'nodes_hierarchy'),(81,31,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"GRIP-LC-003\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867626,'ASSIGN',39,'nodes_hierarchy'),(82,31,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:11:\"GRIP-LC-004\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867626,'ASSIGN',41,'nodes_hierarchy'),(83,31,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"GRIP-LC-004\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867626,'ASSIGN',41,'nodes_hierarchy'),(84,32,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:12:\"GRIP-HYD-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867682,'ASSIGN',43,'nodes_hierarchy'),(85,32,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:12:\"GRIP-HYD-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867682,'ASSIGN',43,'nodes_hierarchy'),(86,32,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:12:\"GRIP-HYD-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867682,'ASSIGN',45,'nodes_hierarchy'),(87,32,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:12:\"GRIP-HYD-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867682,'ASSIGN',45,'nodes_hierarchy'),(88,32,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:12:\"GRIP-HYD-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867682,'ASSIGN',47,'nodes_hierarchy'),(89,32,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:12:\"GRIP-HYD-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867682,'ASSIGN',47,'nodes_hierarchy'),(90,32,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:12:\"GRIP-HYD-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867682,'ASSIGN',49,'nodes_hierarchy'),(91,32,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:12:\"GRIP-HYD-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867682,'ASSIGN',49,'nodes_hierarchy'),(92,33,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"BRK-AA-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867747,'ASSIGN',51,'nodes_hierarchy'),(93,33,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"BRK-AA-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867747,'ASSIGN',51,'nodes_hierarchy'),(94,33,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"BRK-AA-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867747,'ASSIGN',53,'nodes_hierarchy'),(95,33,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"BRK-AA-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867747,'ASSIGN',53,'nodes_hierarchy'),(96,33,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"BRK-AA-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867747,'ASSIGN',55,'nodes_hierarchy'),(97,33,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"BRK-AA-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867747,'ASSIGN',55,'nodes_hierarchy'),(98,33,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"BRK-AA-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867747,'ASSIGN',57,'nodes_hierarchy'),(99,33,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"BRK-AA-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867747,'ASSIGN',57,'nodes_hierarchy'),(100,34,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"BRK-BB-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867792,'ASSIGN',59,'nodes_hierarchy'),(101,34,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"BRK-BB-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867792,'ASSIGN',59,'nodes_hierarchy'),(102,34,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"BRK-BB-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867792,'ASSIGN',61,'nodes_hierarchy'),(103,34,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"BRK-BB-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867792,'ASSIGN',61,'nodes_hierarchy'),(104,34,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"BRK-BB-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867792,'ASSIGN',63,'nodes_hierarchy'),(105,34,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"BRK-BB-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867792,'ASSIGN',63,'nodes_hierarchy'),(106,34,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"BRK-BB-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867792,'ASSIGN',65,'nodes_hierarchy'),(107,34,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"BRK-BB-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867792,'ASSIGN',65,'nodes_hierarchy'),(108,35,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"DRV-AX-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867847,'ASSIGN',67,'nodes_hierarchy'),(109,35,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-AX-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867847,'ASSIGN',67,'nodes_hierarchy'),(110,35,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"DRV-AX-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867847,'ASSIGN',69,'nodes_hierarchy'),(111,35,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-AX-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867847,'ASSIGN',69,'nodes_hierarchy'),(112,35,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"DRV-AX-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867847,'ASSIGN',71,'nodes_hierarchy'),(113,35,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-AX-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867847,'ASSIGN',71,'nodes_hierarchy'),(114,35,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"DRV-AX-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867847,'ASSIGN',73,'nodes_hierarchy'),(115,35,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-AX-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867847,'ASSIGN',73,'nodes_hierarchy'),(116,36,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"DRV-JT-801\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867890,'ASSIGN',75,'nodes_hierarchy'),(117,36,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-JT-801\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867890,'ASSIGN',75,'nodes_hierarchy'),(118,36,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"DRV-JT-802\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867891,'ASSIGN',77,'nodes_hierarchy'),(119,36,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-JT-802\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867891,'ASSIGN',77,'nodes_hierarchy'),(120,36,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"DRV-JT-803\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867891,'ASSIGN',79,'nodes_hierarchy'),(121,36,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-JT-803\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867891,'ASSIGN',79,'nodes_hierarchy'),(122,36,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"DRV-JT-804\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867891,'ASSIGN',81,'nodes_hierarchy'),(123,36,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-JT-804\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867891,'ASSIGN',81,'nodes_hierarchy'),(124,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"DRV-TC-701\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',83,'nodes_hierarchy'),(125,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-TC-701\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',83,'nodes_hierarchy'),(126,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"DRV-TC-702\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',85,'nodes_hierarchy'),(127,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-TC-702\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',85,'nodes_hierarchy'),(128,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"DRV-TC-703\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',87,'nodes_hierarchy'),(129,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-TC-703\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',87,'nodes_hierarchy'),(130,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"DRV-TC-704\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',89,'nodes_hierarchy'),(131,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-TC-704\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',89,'nodes_hierarchy'),(132,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"DRV-TC-705\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',91,'nodes_hierarchy'),(133,37,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"DRV-TC-705\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335867956,'ASSIGN',91,'nodes_hierarchy'),(134,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"INO-ZX-701\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868010,'ASSIGN',93,'nodes_hierarchy'),(135,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZX-701\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868010,'ASSIGN',93,'nodes_hierarchy'),(136,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"INO-ZX-702\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868010,'ASSIGN',95,'nodes_hierarchy'),(137,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZX-702\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868010,'ASSIGN',95,'nodes_hierarchy'),(138,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"INO-ZX-703\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868011,'ASSIGN',97,'nodes_hierarchy'),(139,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZX-703\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868011,'ASSIGN',97,'nodes_hierarchy'),(140,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"INO-ZX-704\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868011,'ASSIGN',99,'nodes_hierarchy'),(141,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZX-704\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868011,'ASSIGN',99,'nodes_hierarchy'),(142,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"INO-ZX-705\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868011,'ASSIGN',101,'nodes_hierarchy'),(143,38,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZX-705\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868011,'ASSIGN',101,'nodes_hierarchy'),(144,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"INO-ZW-601\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',103,'nodes_hierarchy'),(145,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZW-601\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',103,'nodes_hierarchy'),(146,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"INO-ZW-602\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',105,'nodes_hierarchy'),(147,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZW-602\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',105,'nodes_hierarchy'),(148,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"INO-ZW-603\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',107,'nodes_hierarchy'),(149,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZW-603\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',107,'nodes_hierarchy'),(150,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"INO-ZW-604\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',109,'nodes_hierarchy'),(151,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZW-604\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',109,'nodes_hierarchy'),(152,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"INO-ZW-605\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',111,'nodes_hierarchy'),(153,39,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"INO-ZW-605\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868064,'ASSIGN',111,'nodes_hierarchy'),(154,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:11:\"EXNO-ZW-601\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868106,'ASSIGN',113,'nodes_hierarchy'),(155,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-ZW-601\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868106,'ASSIGN',113,'nodes_hierarchy'),(156,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:11:\"EXNO-ZW-602\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868106,'ASSIGN',115,'nodes_hierarchy'),(157,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-ZW-602\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868106,'ASSIGN',115,'nodes_hierarchy'),(158,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:11:\"EXNO-ZW-603\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868107,'ASSIGN',117,'nodes_hierarchy'),(159,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-ZW-603\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868107,'ASSIGN',117,'nodes_hierarchy'),(160,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:11:\"EXNO-ZW-604\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868107,'ASSIGN',119,'nodes_hierarchy'),(161,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-ZW-604\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868107,'ASSIGN',119,'nodes_hierarchy'),(162,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:11:\"EXNO-ZW-605\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868107,'ASSIGN',121,'nodes_hierarchy'),(163,40,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-ZW-605\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868107,'ASSIGN',121,'nodes_hierarchy'),(164,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:11:\"EXNO-WW-001\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',123,'nodes_hierarchy'),(165,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-WW-001\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',123,'nodes_hierarchy'),(166,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:11:\"EXNO-WW-002\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',125,'nodes_hierarchy'),(167,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-WW-002\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',125,'nodes_hierarchy'),(168,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:11:\"EXNO-WW-003\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',127,'nodes_hierarchy'),(169,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-WW-003\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',127,'nodes_hierarchy'),(170,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:11:\"EXNO-WW-004\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',129,'nodes_hierarchy'),(171,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-WW-004\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',129,'nodes_hierarchy'),(172,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:11:\"EXNO-WW-005\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',131,'nodes_hierarchy'),(173,41,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:11:\"EXNO-WW-005\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868144,'ASSIGN',131,'nodes_hierarchy'),(174,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"TYW-77-001\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',133,'nodes_hierarchy'),(175,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-001\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',133,'nodes_hierarchy'),(176,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"TYW-77-002\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',135,'nodes_hierarchy'),(177,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-002\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',135,'nodes_hierarchy'),(178,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"TYW-77-003\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',137,'nodes_hierarchy'),(179,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-003\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',137,'nodes_hierarchy'),(180,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"TYW-77-004\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',139,'nodes_hierarchy'),(181,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-004\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',139,'nodes_hierarchy'),(182,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"TYW-77-005\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',141,'nodes_hierarchy'),(183,42,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-005\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868185,'ASSIGN',141,'nodes_hierarchy'),(184,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"TYW-77-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',143,'nodes_hierarchy'),(185,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-101\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',143,'nodes_hierarchy'),(186,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"TYW-77-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',145,'nodes_hierarchy'),(187,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-102\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',145,'nodes_hierarchy'),(188,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"TYW-77-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',147,'nodes_hierarchy'),(189,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-103\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',147,'nodes_hierarchy'),(190,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"TYW-77-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',149,'nodes_hierarchy'),(191,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-104\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',149,'nodes_hierarchy'),(192,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"TYW-77-105\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',151,'nodes_hierarchy'),(193,43,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-105\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868220,'ASSIGN',151,'nodes_hierarchy'),(194,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"TYW-77-201\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868246,'ASSIGN',153,'nodes_hierarchy'),(195,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-201\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868246,'ASSIGN',153,'nodes_hierarchy'),(196,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"TYW-77-202\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868246,'ASSIGN',155,'nodes_hierarchy'),(197,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-202\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868246,'ASSIGN',155,'nodes_hierarchy'),(198,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Mechanical\";i:1;s:10:\"TYW-77-203\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868246,'ASSIGN',157,'nodes_hierarchy'),(199,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-203\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868246,'ASSIGN',157,'nodes_hierarchy'),(200,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"TYW-77-204\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868246,'ASSIGN',159,'nodes_hierarchy'),(201,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-204\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868246,'ASSIGN',159,'nodes_hierarchy'),(202,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:10:\"Resistance\";i:1;s:10:\"TYW-77-205\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868246,'ASSIGN',161,'nodes_hierarchy'),(203,44,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:25:\"audit_keyword_assigned_tc\";s:6:\"params\";a:2:{i:0;s:9:\"Objective\";i:1;s:10:\"TYW-77-205\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868247,'ASSIGN',161,'nodes_hierarchy'),(204,45,2,'GUI','E_NOTICE\nTrying to get property of non-object - in /hdextra/development/tl-old/tl193-untouched/lib/functions/tlTestCaseFilterControl.class.php - Line 880',1335868398,'PHP',NULL,NULL),(205,46,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-1 : GRIP-SL-001\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868429,'ASSIGN',4,'testplans'),(206,46,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-1 : GRIP-SL-001\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868429,'ASSIGN',4,'testplans'),(207,46,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-1 : GRIP-SL-001\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868429,'ASSIGN',4,'testplans'),(208,46,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-1 : GRIP-SL-001\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868429,'ASSIGN',4,'testplans'),(209,46,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-2 : GRIP-SL-002\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868429,'ASSIGN',4,'testplans'),(210,46,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-2 : GRIP-SL-002\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868429,'ASSIGN',4,'testplans'),(211,46,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-2 : GRIP-SL-002\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868429,'ASSIGN',4,'testplans'),(212,46,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-2 : GRIP-SL-002\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868429,'ASSIGN',4,'testplans'),(213,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:20:\"PDT-7 : GRIP-HYD-101\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(214,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:20:\"PDT-7 : GRIP-HYD-101\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(215,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:20:\"PDT-7 : GRIP-HYD-101\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(216,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:20:\"PDT-9 : GRIP-HYD-103\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(217,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:20:\"PDT-9 : GRIP-HYD-103\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(218,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:20:\"PDT-9 : GRIP-HYD-103\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(219,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:20:\"PDT-8 : GRIP-HYD-102\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(220,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:20:\"PDT-8 : GRIP-HYD-102\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(221,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:20:\"PDT-8 : GRIP-HYD-102\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(222,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:21:\"PDT-10 : GRIP-HYD-104\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(223,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:21:\"PDT-10 : GRIP-HYD-104\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(224,47,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:21:\"PDT-10 : GRIP-HYD-104\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868446,'ASSIGN',4,'testplans'),(225,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-32 : INO-ZX-701\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(226,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-32 : INO-ZX-701\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(227,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-34 : INO-ZX-703\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(228,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-34 : INO-ZX-703\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(229,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-33 : INO-ZX-702\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(230,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-33 : INO-ZX-702\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(231,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-35 : INO-ZX-704\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(232,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-35 : INO-ZX-704\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(233,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-36 : INO-ZX-705\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(234,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-36 : INO-ZX-705\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(235,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-37 : INO-ZW-601\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(236,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-37 : INO-ZW-601\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(237,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-39 : INO-ZW-603\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(238,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-39 : INO-ZW-603\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(239,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-38 : INO-ZW-602\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(240,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-38 : INO-ZW-602\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(241,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-40 : INO-ZW-604\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(242,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-40 : INO-ZW-604\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(243,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-41 : INO-ZW-605\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(244,48,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-41 : INO-ZW-605\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868481,'ASSIGN',4,'testplans'),(245,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-57 : TYW-77-101\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(246,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-57 : TYW-77-101\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(247,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-57 : TYW-77-101\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(248,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-57 : TYW-77-101\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(249,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-59 : TYW-77-103\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(250,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-59 : TYW-77-103\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(251,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-59 : TYW-77-103\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(252,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-59 : TYW-77-103\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(253,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-58 : TYW-77-102\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(254,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-58 : TYW-77-102\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(255,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-58 : TYW-77-102\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(256,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-58 : TYW-77-102\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(257,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-60 : TYW-77-104\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(258,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-60 : TYW-77-104\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(259,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-60 : TYW-77-104\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(260,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-60 : TYW-77-104\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(261,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-61 : TYW-77-105\";i:1;s:1:\"1\";i:2;s:41:\"P Zero Red (Supersoft) - Platform:Ferrari\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(262,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-61 : TYW-77-105\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mc Laren\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(263,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-61 : TYW-77-105\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Mercedes\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans'),(264,49,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:26:\"audit_tc_added_to_testplan\";s:6:\"params\";a:3:{i:0;s:19:\"PDT-61 : TYW-77-105\";i:1;s:1:\"1\";i:2;s:42:\"P Zero Red (Supersoft) - Platform:Red Bull\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1335868496,'ASSIGN',4,'testplans');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `execution_bugs`
--

DROP TABLE IF EXISTS `execution_bugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `execution_bugs` (
  `execution_id` int(10) unsigned NOT NULL DEFAULT '0',
  `bug_id` varchar(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`execution_id`,`bug_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `execution_bugs`
--

LOCK TABLES `execution_bugs` WRITE;
/*!40000 ALTER TABLE `execution_bugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `execution_bugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `executions`
--

DROP TABLE IF EXISTS `executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `executions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `build_id` int(10) NOT NULL DEFAULT '0',
  `tester_id` int(10) unsigned DEFAULT NULL,
  `execution_ts` datetime DEFAULT NULL,
  `status` char(1) DEFAULT NULL,
  `testplan_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tcversion_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tcversion_number` smallint(5) unsigned NOT NULL DEFAULT '1',
  `platform_id` int(10) unsigned NOT NULL DEFAULT '0',
  `execution_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 -> manual, 2 -> automated',
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `executions_idx1` (`testplan_id`,`tcversion_id`,`platform_id`,`build_id`),
  KEY `executions_idx2` (`execution_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `executions`
--

LOCK TABLES `executions` WRITE;
/*!40000 ALTER TABLE `executions` DISABLE KEYS */;
/*!40000 ALTER TABLE `executions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testproject_id` int(10) unsigned NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `ipaddress` varchar(255) NOT NULL,
  `content` text,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_ts` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `inventory_idx1` (`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keywords`
--

DROP TABLE IF EXISTS `keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keywords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `testproject_id` int(10) unsigned NOT NULL DEFAULT '0',
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `testproject_id` (`testproject_id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keywords`
--

LOCK TABLES `keywords` WRITE;
/*!40000 ALTER TABLE `keywords` DISABLE KEYS */;
INSERT INTO `keywords` VALUES (1,'Mechanical',2,''),(2,'Resistance',2,''),(3,'Tear Resistance',2,''),(4,'Mountability',2,''),(5,'Subjective',2,''),(6,'Objective',2,'');
/*!40000 ALTER TABLE `keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milestones`
--

DROP TABLE IF EXISTS `milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milestones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int(10) unsigned NOT NULL DEFAULT '0',
  `target_date` date DEFAULT NULL,
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `a` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `b` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `c` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT 'undefined',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_testplan_id` (`name`,`testplan_id`),
  KEY `testplan_id` (`testplan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milestones`
--

LOCK TABLES `milestones` WRITE;
/*!40000 ALTER TABLE `milestones` DISABLE KEYS */;
/*!40000 ALTER TABLE `milestones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_types`
--

DROP TABLE IF EXISTS `node_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT 'testproject',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_types`
--

LOCK TABLES `node_types` WRITE;
/*!40000 ALTER TABLE `node_types` DISABLE KEYS */;
INSERT INTO `node_types` VALUES (1,'testproject'),(2,'testsuite'),(3,'testcase'),(4,'testcase_version'),(5,'testplan'),(6,'requirement_spec'),(7,'requirement'),(8,'requirement_version'),(9,'testcase_step'),(10,'requirement_revision');
/*!40000 ALTER TABLE `node_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nodes_hierarchy`
--

DROP TABLE IF EXISTS `nodes_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nodes_hierarchy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `node_type_id` int(10) unsigned NOT NULL DEFAULT '1',
  `node_order` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid_m_nodeorder` (`parent_id`,`node_order`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nodes_hierarchy`
--

LOCK TABLES `nodes_hierarchy` WRITE;
/*!40000 ALTER TABLE `nodes_hierarchy` DISABLE KEYS */;
INSERT INTO `nodes_hierarchy` VALUES (2,'Formula One Pirelli Dry Tyres',NULL,1,1),(3,'Formula One Pirelli Wet Tyres',NULL,1,1),(4,'P Zero Red (Supersoft)',2,5,0),(5,'P Zero Yellow (Soft)',2,5,0),(6,'P Zero White (Medium)',2,5,0),(7,'P Zero Silver (Hard)',2,5,0),(8,'Cinturato Green (Intermediate)',3,5,0),(9,'Cinturato Blue (Wet)',3,5,0),(10,'Grip',2,2,1),(11,'Braking',2,2,2),(12,'Driving comfort',2,2,3),(13,'Internal noise level',2,2,4),(14,'Exterior noise levels',2,2,5),(15,'Tyre wear',2,2,6),(16,'BRK - Single-Wheel Braking, Driving, and Lateral Traction',11,2,1),(17,'BRK - Stopping Distance',11,2,2),(18,'GRIP - Slalom',10,2,1),(19,'GRIP - Lane Change',10,2,2),(20,'GRIP - Hydroplaning',10,2,3),(21,'DRVC - Cornering Response',12,2,1),(22,'DRVC - J-Turn',12,2,2),(23,'DRVC - Tethered Circle',12,2,3),(24,'TYW - Stresses & motions in the tyre footprint',15,2,1),(25,'TYW - Tyre forces & moments',15,2,2),(26,'INO - Lateral, longitudinal & vertical spring rates',13,2,1),(27,'EXNO - Lateral, longitudinal & vertical spring rates',14,2,1),(28,'TYW - Thermal Profile/Reliability',15,2,3),(29,'INO - High Speed Operation',13,2,2),(30,'EXNO - High Speed Operation',14,2,2),(31,'GRIP-SL-001',18,3,100),(32,'',31,4,0),(33,'GRIP-SL-002',18,3,101),(34,'',33,4,0),(35,'GRIP-LC-001',19,3,100),(36,'',35,4,0),(37,'GRIP-LC-002',19,3,101),(38,'',37,4,0),(39,'GRIP-LC-003',19,3,100),(40,'',39,4,0),(41,'GRIP-LC-004',19,3,101),(42,'',41,4,0),(43,'GRIP-HYD-101',20,3,100),(44,'',43,4,0),(45,'GRIP-HYD-102',20,3,101),(46,'',45,4,0),(47,'GRIP-HYD-103',20,3,100),(48,'',47,4,0),(49,'GRIP-HYD-104',20,3,101),(50,'',49,4,0),(51,'BRK-AA-101',16,3,100),(52,'',51,4,0),(53,'BRK-AA-102',16,3,101),(54,'',53,4,0),(55,'BRK-AA-103',16,3,100),(56,'',55,4,0),(57,'BRK-AA-104',16,3,101),(58,'',57,4,0),(59,'BRK-BB-101',17,3,100),(60,'',59,4,0),(61,'BRK-BB-102',17,3,101),(62,'',61,4,0),(63,'BRK-BB-103',17,3,100),(64,'',63,4,0),(65,'BRK-BB-104',17,3,101),(66,'',65,4,0),(67,'DRV-AX-101',21,3,100),(68,'',67,4,0),(69,'DRV-AX-102',21,3,101),(70,'',69,4,0),(71,'DRV-AX-103',21,3,100),(72,'',71,4,0),(73,'DRV-AX-104',21,3,101),(74,'',73,4,0),(75,'DRV-JT-801',22,3,100),(76,'',75,4,0),(77,'DRV-JT-802',22,3,101),(78,'',77,4,0),(79,'DRV-JT-803',22,3,100),(80,'',79,4,0),(81,'DRV-JT-804',22,3,101),(82,'',81,4,0),(83,'DRV-TC-701',23,3,100),(84,'',83,4,0),(85,'DRV-TC-702',23,3,101),(86,'',85,4,0),(87,'DRV-TC-703',23,3,100),(88,'',87,4,0),(89,'DRV-TC-704',23,3,101),(90,'',89,4,0),(91,'DRV-TC-705',23,3,101),(92,'',91,4,0),(93,'INO-ZX-701',26,3,100),(94,'',93,4,0),(95,'INO-ZX-702',26,3,101),(96,'',95,4,0),(97,'INO-ZX-703',26,3,100),(98,'',97,4,0),(99,'INO-ZX-704',26,3,101),(100,'',99,4,0),(101,'INO-ZX-705',26,3,101),(102,'',101,4,0),(103,'INO-ZW-601',29,3,100),(104,'',103,4,0),(105,'INO-ZW-602',29,3,101),(106,'',105,4,0),(107,'INO-ZW-603',29,3,100),(108,'',107,4,0),(109,'INO-ZW-604',29,3,101),(110,'',109,4,0),(111,'INO-ZW-605',29,3,101),(112,'',111,4,0),(113,'EXNO-ZW-601',27,3,100),(114,'',113,4,0),(115,'EXNO-ZW-602',27,3,101),(116,'',115,4,0),(117,'EXNO-ZW-603',27,3,100),(118,'',117,4,0),(119,'EXNO-ZW-604',27,3,101),(120,'',119,4,0),(121,'EXNO-ZW-605',27,3,101),(122,'',121,4,0),(123,'EXNO-WW-001',30,3,100),(124,'',123,4,0),(125,'EXNO-WW-002',30,3,101),(126,'',125,4,0),(127,'EXNO-WW-003',30,3,100),(128,'',127,4,0),(129,'EXNO-WW-004',30,3,101),(130,'',129,4,0),(131,'EXNO-WW-005',30,3,101),(132,'',131,4,0),(133,'TYW-77-001',24,3,100),(134,'',133,4,0),(135,'TYW-77-002',24,3,101),(136,'',135,4,0),(137,'TYW-77-003',24,3,100),(138,'',137,4,0),(139,'TYW-77-004',24,3,101),(140,'',139,4,0),(141,'TYW-77-005',24,3,101),(142,'',141,4,0),(143,'TYW-77-101',25,3,100),(144,'',143,4,0),(145,'TYW-77-102',25,3,101),(146,'',145,4,0),(147,'TYW-77-103',25,3,100),(148,'',147,4,0),(149,'TYW-77-104',25,3,101),(150,'',149,4,0),(151,'TYW-77-105',25,3,101),(152,'',151,4,0),(153,'TYW-77-201',28,3,100),(154,'',153,4,0),(155,'TYW-77-202',28,3,101),(156,'',155,4,0),(157,'TYW-77-203',28,3,100),(158,'',157,4,0),(159,'TYW-77-204',28,3,101),(160,'',159,4,0),(161,'TYW-77-205',28,3,101),(162,'',161,4,0);
/*!40000 ALTER TABLE `nodes_hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `object_keywords`
--

DROP TABLE IF EXISTS `object_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `object_keywords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fk_table` varchar(30) DEFAULT '',
  `keyword_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `object_keywords`
--

LOCK TABLES `object_keywords` WRITE;
/*!40000 ALTER TABLE `object_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `object_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platforms`
--

DROP TABLE IF EXISTS `platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platforms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_platforms` (`testproject_id`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platforms`
--

LOCK TABLES `platforms` WRITE;
/*!40000 ALTER TABLE `platforms` DISABLE KEYS */;
INSERT INTO `platforms` VALUES (1,'Ferrari',2,''),(2,'Mc Laren',2,''),(3,'Red Bull',2,''),(4,'Mercedes',2,''),(7,'Renault',2,'');
/*!40000 ALTER TABLE `platforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_coverage`
--

DROP TABLE IF EXISTS `req_coverage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_coverage` (
  `req_id` int(10) NOT NULL,
  `testcase_id` int(10) NOT NULL,
  KEY `req_testcase` (`req_id`,`testcase_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='relation test case ** requirements';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_coverage`
--

LOCK TABLES `req_coverage` WRITE;
/*!40000 ALTER TABLE `req_coverage` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_coverage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_relations`
--

DROP TABLE IF EXISTS `req_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_relations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int(10) unsigned NOT NULL,
  `destination_id` int(10) unsigned NOT NULL,
  `relation_type` smallint(5) unsigned NOT NULL DEFAULT '1',
  `author_id` int(10) unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_relations`
--

LOCK TABLES `req_relations` WRITE;
/*!40000 ALTER TABLE `req_relations` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_revisions`
--

DROP TABLE IF EXISTS `req_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_revisions` (
  `parent_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `revision` smallint(5) unsigned NOT NULL DEFAULT '1',
  `req_doc_id` varchar(64) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `scope` text,
  `status` char(1) NOT NULL DEFAULT 'V',
  `type` char(1) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `expected_coverage` int(10) NOT NULL DEFAULT '1',
  `log_message` text,
  `author_id` int(10) unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int(10) unsigned DEFAULT NULL,
  `modification_ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `req_revisions_uidx1` (`parent_id`,`revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_revisions`
--

LOCK TABLES `req_revisions` WRITE;
/*!40000 ALTER TABLE `req_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_specs`
--

DROP TABLE IF EXISTS `req_specs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_specs` (
  `id` int(10) unsigned NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL,
  `doc_id` varchar(64) NOT NULL,
  `scope` text,
  `total_req` int(10) NOT NULL DEFAULT '0',
  `type` char(1) DEFAULT 'n',
  `author_id` int(10) unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int(10) unsigned DEFAULT NULL,
  `modification_ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `req_spec_uk1` (`doc_id`,`testproject_id`),
  KEY `testproject_id` (`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dev. Documents (e.g. System Requirements Specification)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_specs`
--

LOCK TABLES `req_specs` WRITE;
/*!40000 ALTER TABLE `req_specs` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_specs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_versions`
--

DROP TABLE IF EXISTS `req_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_versions` (
  `id` int(10) unsigned NOT NULL,
  `version` smallint(5) unsigned NOT NULL DEFAULT '1',
  `revision` smallint(5) unsigned NOT NULL DEFAULT '1',
  `scope` text,
  `status` char(1) NOT NULL DEFAULT 'V',
  `type` char(1) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `expected_coverage` int(10) NOT NULL DEFAULT '1',
  `author_id` int(10) unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int(10) unsigned DEFAULT NULL,
  `modification_ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `log_message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_versions`
--

LOCK TABLES `req_versions` WRITE;
/*!40000 ALTER TABLE `req_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requirements`
--

DROP TABLE IF EXISTS `requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requirements` (
  `id` int(10) unsigned NOT NULL,
  `srs_id` int(10) unsigned NOT NULL,
  `req_doc_id` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requirements_req_doc_id` (`srs_id`,`req_doc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requirements`
--

LOCK TABLES `requirements` WRITE;
/*!40000 ALTER TABLE `requirements` DISABLE KEYS */;
/*!40000 ALTER TABLE `requirements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rights`
--

DROP TABLE IF EXISTS `rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rights` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rights_descr` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rights`
--

LOCK TABLES `rights` WRITE;
/*!40000 ALTER TABLE `rights` DISABLE KEYS */;
INSERT INTO `rights` VALUES (18,'cfield_management'),(17,'cfield_view'),(22,'events_mgt'),(9,'mgt_modify_key'),(12,'mgt_modify_product'),(11,'mgt_modify_req'),(7,'mgt_modify_tc'),(16,'mgt_testplan_create'),(13,'mgt_users'),(20,'mgt_view_events'),(8,'mgt_view_key'),(10,'mgt_view_req'),(6,'mgt_view_tc'),(21,'mgt_view_usergroups'),(24,'platform_management'),(25,'platform_view'),(26,'project_inventory_management'),(27,'project_inventory_view'),(14,'role_management'),(19,'system_configuration'),(2,'testplan_create_build'),(1,'testplan_execute'),(3,'testplan_metrics'),(4,'testplan_planning'),(5,'testplan_user_role_assignment'),(23,'testproject_user_role_assignment'),(15,'user_role_assignment');
/*!40000 ALTER TABLE `rights` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_assignments`
--

DROP TABLE IF EXISTS `risk_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `risk_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int(10) unsigned NOT NULL DEFAULT '0',
  `node_id` int(10) unsigned NOT NULL DEFAULT '0',
  `risk` char(1) NOT NULL DEFAULT '2',
  `importance` char(1) NOT NULL DEFAULT 'M',
  PRIMARY KEY (`id`),
  UNIQUE KEY `risk_assignments_tplan_node_id` (`testplan_id`,`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_assignments`
--

LOCK TABLES `risk_assignments` WRITE;
/*!40000 ALTER TABLE `risk_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_rights`
--

DROP TABLE IF EXISTS `role_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_rights` (
  `role_id` int(10) NOT NULL DEFAULT '0',
  `right_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`role_id`,`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_rights`
--

LOCK TABLES `role_rights` WRITE;
/*!40000 ALTER TABLE `role_rights` DISABLE KEYS */;
INSERT INTO `role_rights` VALUES (4,3),(4,6),(4,7),(4,8),(4,9),(4,10),(4,11),(5,3),(5,6),(5,8),(6,1),(6,2),(6,3),(6,6),(6,7),(6,8),(6,9),(6,11),(6,25),(6,27),(7,1),(7,3),(7,6),(7,8),(8,1),(8,2),(8,3),(8,4),(8,5),(8,6),(8,7),(8,8),(8,9),(8,10),(8,11),(8,12),(8,13),(8,14),(8,15),(8,16),(8,17),(8,18),(8,19),(8,20),(8,21),(8,22),(8,23),(8,24),(8,25),(8,26),(8,27),(9,1),(9,2),(9,3),(9,4),(9,5),(9,6),(9,7),(9,8),(9,9),(9,10),(9,11),(9,15),(9,16),(9,24),(9,25),(9,26),(9,27);
/*!40000 ALTER TABLE `role_rights` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_rights_roles_descr` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'<reserved system role 1>',NULL),(2,'<reserved system role 2>',NULL),(3,'<no rights>',NULL),(4,'test designer',NULL),(5,'guest',NULL),(6,'senior tester',NULL),(7,'tester',NULL),(8,'admin',NULL),(9,'leader',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tcsteps`
--

DROP TABLE IF EXISTS `tcsteps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tcsteps` (
  `id` int(10) unsigned NOT NULL,
  `step_number` int(11) NOT NULL DEFAULT '1',
  `actions` text,
  `expected_results` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `execution_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 -> manual, 2 -> automated',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tcsteps`
--

LOCK TABLES `tcsteps` WRITE;
/*!40000 ALTER TABLE `tcsteps` DISABLE KEYS */;
/*!40000 ALTER TABLE `tcsteps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tcversions`
--

DROP TABLE IF EXISTS `tcversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tcversions` (
  `id` int(10) unsigned NOT NULL,
  `tc_external_id` int(10) unsigned DEFAULT NULL,
  `version` smallint(5) unsigned NOT NULL DEFAULT '1',
  `layout` smallint(5) unsigned NOT NULL DEFAULT '1',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1',
  `summary` text,
  `preconditions` text,
  `importance` smallint(5) unsigned NOT NULL DEFAULT '2',
  `author_id` int(10) unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updater_id` int(10) unsigned DEFAULT NULL,
  `modification_ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `execution_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 -> manual, 2 -> automated',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tcversions`
--

LOCK TABLES `tcversions` WRITE;
/*!40000 ALTER TABLE `tcversions` DISABLE KEYS */;
INSERT INTO `tcversions` VALUES (32,1,1,1,1,'','',2,1,'2012-05-01 10:08:43',1,'2012-05-01 12:11:47',1,1,1),(34,2,1,1,1,'','',2,1,'2012-05-01 10:08:56',1,'2012-05-01 12:12:01',1,1,1),(36,3,1,1,1,'','',2,1,'2012-05-01 10:20:26',NULL,'0000-00-00 00:00:00',1,1,1),(38,4,1,1,1,'','',2,1,'2012-05-01 10:20:26',NULL,'0000-00-00 00:00:00',1,1,1),(40,5,1,1,1,'','',2,1,'2012-05-01 10:20:26',NULL,'0000-00-00 00:00:00',1,1,1),(42,6,1,1,1,'','',2,1,'2012-05-01 10:20:26',NULL,'0000-00-00 00:00:00',1,1,1),(44,7,1,1,1,'','',2,1,'2012-05-01 10:21:22',NULL,'0000-00-00 00:00:00',1,1,1),(46,8,1,1,1,'','',2,1,'2012-05-01 10:21:22',NULL,'0000-00-00 00:00:00',1,1,1),(48,9,1,1,1,'','',2,1,'2012-05-01 10:21:22',NULL,'0000-00-00 00:00:00',1,1,1),(50,10,1,1,1,'','',2,1,'2012-05-01 10:21:22',NULL,'0000-00-00 00:00:00',1,1,1),(52,11,1,1,1,'','',2,1,'2012-05-01 10:22:27',NULL,'0000-00-00 00:00:00',1,1,1),(54,12,1,1,1,'','',2,1,'2012-05-01 10:22:27',NULL,'0000-00-00 00:00:00',1,1,1),(56,13,1,1,1,'','',2,1,'2012-05-01 10:22:27',NULL,'0000-00-00 00:00:00',1,1,1),(58,14,1,1,1,'','',2,1,'2012-05-01 10:22:27',NULL,'0000-00-00 00:00:00',1,1,1),(60,15,1,1,1,'','',2,1,'2012-05-01 10:23:12',NULL,'0000-00-00 00:00:00',1,1,1),(62,16,1,1,1,'','',2,1,'2012-05-01 10:23:12',NULL,'0000-00-00 00:00:00',1,1,1),(64,17,1,1,1,'','',2,1,'2012-05-01 10:23:12',NULL,'0000-00-00 00:00:00',1,1,1),(66,18,1,1,1,'','',2,1,'2012-05-01 10:23:12',NULL,'0000-00-00 00:00:00',1,1,1),(68,19,1,1,1,'','',2,1,'2012-05-01 10:24:07',NULL,'0000-00-00 00:00:00',1,1,1),(70,20,1,1,1,'','',2,1,'2012-05-01 10:24:07',NULL,'0000-00-00 00:00:00',1,1,1),(72,21,1,1,1,'','',2,1,'2012-05-01 10:24:07',NULL,'0000-00-00 00:00:00',1,1,1),(74,22,1,1,1,'','',2,1,'2012-05-01 10:24:07',NULL,'0000-00-00 00:00:00',1,1,1),(76,23,1,1,1,'','',2,1,'2012-05-01 10:24:50',NULL,'0000-00-00 00:00:00',1,1,1),(78,24,1,1,1,'','',2,1,'2012-05-01 10:24:51',NULL,'0000-00-00 00:00:00',1,1,1),(80,25,1,1,1,'','',2,1,'2012-05-01 10:24:51',NULL,'0000-00-00 00:00:00',1,1,1),(82,26,1,1,1,'','',2,1,'2012-05-01 10:24:51',NULL,'0000-00-00 00:00:00',1,1,1),(84,27,1,1,1,'','',2,1,'2012-05-01 10:25:56',NULL,'0000-00-00 00:00:00',1,1,1),(86,28,1,1,1,'','',2,1,'2012-05-01 10:25:56',NULL,'0000-00-00 00:00:00',1,1,1),(88,29,1,1,1,'','',2,1,'2012-05-01 10:25:56',NULL,'0000-00-00 00:00:00',1,1,1),(90,30,1,1,1,'','',2,1,'2012-05-01 10:25:56',NULL,'0000-00-00 00:00:00',1,1,1),(92,31,1,1,1,'','',2,1,'2012-05-01 10:25:56',NULL,'0000-00-00 00:00:00',1,1,1),(94,32,1,1,1,'','',2,1,'2012-05-01 10:26:50',NULL,'0000-00-00 00:00:00',1,1,1),(96,33,1,1,1,'','',2,1,'2012-05-01 10:26:50',NULL,'0000-00-00 00:00:00',1,1,1),(98,34,1,1,1,'','',2,1,'2012-05-01 10:26:51',NULL,'0000-00-00 00:00:00',1,1,1),(100,35,1,1,1,'','',2,1,'2012-05-01 10:26:51',NULL,'0000-00-00 00:00:00',1,1,1),(102,36,1,1,1,'','',2,1,'2012-05-01 10:26:51',NULL,'0000-00-00 00:00:00',1,1,1),(104,37,1,1,1,'','',2,1,'2012-05-01 10:27:44',NULL,'0000-00-00 00:00:00',1,1,1),(106,38,1,1,1,'','',2,1,'2012-05-01 10:27:44',NULL,'0000-00-00 00:00:00',1,1,1),(108,39,1,1,1,'','',2,1,'2012-05-01 10:27:44',NULL,'0000-00-00 00:00:00',1,1,1),(110,40,1,1,1,'','',2,1,'2012-05-01 10:27:44',NULL,'0000-00-00 00:00:00',1,1,1),(112,41,1,1,1,'','',2,1,'2012-05-01 10:27:44',NULL,'0000-00-00 00:00:00',1,1,1),(114,42,1,1,1,'','',2,1,'2012-05-01 10:28:26',NULL,'0000-00-00 00:00:00',1,1,1),(116,43,1,1,1,'','',2,1,'2012-05-01 10:28:26',NULL,'0000-00-00 00:00:00',1,1,1),(118,44,1,1,1,'','',2,1,'2012-05-01 10:28:27',NULL,'0000-00-00 00:00:00',1,1,1),(120,45,1,1,1,'','',2,1,'2012-05-01 10:28:27',NULL,'0000-00-00 00:00:00',1,1,1),(122,46,1,1,1,'','',2,1,'2012-05-01 10:28:27',NULL,'0000-00-00 00:00:00',1,1,1),(124,47,1,1,1,'','',2,1,'2012-05-01 10:29:04',NULL,'0000-00-00 00:00:00',1,1,1),(126,48,1,1,1,'','',2,1,'2012-05-01 10:29:04',NULL,'0000-00-00 00:00:00',1,1,1),(128,49,1,1,1,'','',2,1,'2012-05-01 10:29:04',NULL,'0000-00-00 00:00:00',1,1,1),(130,50,1,1,1,'','',2,1,'2012-05-01 10:29:04',NULL,'0000-00-00 00:00:00',1,1,1),(132,51,1,1,1,'','',2,1,'2012-05-01 10:29:04',NULL,'0000-00-00 00:00:00',1,1,1),(134,52,1,1,1,'','',2,1,'2012-05-01 10:29:45',NULL,'0000-00-00 00:00:00',1,1,1),(136,53,1,1,1,'','',2,1,'2012-05-01 10:29:45',NULL,'0000-00-00 00:00:00',1,1,1),(138,54,1,1,1,'','',2,1,'2012-05-01 10:29:45',NULL,'0000-00-00 00:00:00',1,1,1),(140,55,1,1,1,'','',2,1,'2012-05-01 10:29:45',NULL,'0000-00-00 00:00:00',1,1,1),(142,56,1,1,1,'','',2,1,'2012-05-01 10:29:45',NULL,'0000-00-00 00:00:00',1,1,1),(144,57,1,1,1,'','',2,1,'2012-05-01 10:30:20',NULL,'0000-00-00 00:00:00',1,1,1),(146,58,1,1,1,'','',2,1,'2012-05-01 10:30:20',NULL,'0000-00-00 00:00:00',1,1,1),(148,59,1,1,1,'','',2,1,'2012-05-01 10:30:20',NULL,'0000-00-00 00:00:00',1,1,1),(150,60,1,1,1,'','',2,1,'2012-05-01 10:30:20',NULL,'0000-00-00 00:00:00',1,1,1),(152,61,1,1,1,'','',2,1,'2012-05-01 10:30:20',NULL,'0000-00-00 00:00:00',1,1,1),(154,62,1,1,1,'','',2,1,'2012-05-01 10:30:46',NULL,'0000-00-00 00:00:00',1,1,1),(156,63,1,1,1,'','',2,1,'2012-05-01 10:30:46',NULL,'0000-00-00 00:00:00',1,1,1),(158,64,1,1,1,'','',2,1,'2012-05-01 10:30:46',NULL,'0000-00-00 00:00:00',1,1,1),(160,65,1,1,1,'','',2,1,'2012-05-01 10:30:46',NULL,'0000-00-00 00:00:00',1,1,1),(162,66,1,1,1,'','',2,1,'2012-05-01 10:30:47',NULL,'0000-00-00 00:00:00',1,1,1);
/*!40000 ALTER TABLE `tcversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testcase_keywords`
--

DROP TABLE IF EXISTS `testcase_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testcase_keywords` (
  `testcase_id` int(10) unsigned NOT NULL DEFAULT '0',
  `keyword_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`testcase_id`,`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testcase_keywords`
--

LOCK TABLES `testcase_keywords` WRITE;
/*!40000 ALTER TABLE `testcase_keywords` DISABLE KEYS */;
INSERT INTO `testcase_keywords` VALUES (31,1),(31,6),(33,2),(33,6),(35,1),(35,6),(37,2),(37,6),(39,1),(39,6),(41,2),(41,6),(43,1),(43,6),(45,2),(45,6),(47,1),(47,6),(49,2),(49,6),(51,1),(51,6),(53,2),(53,6),(55,1),(55,6),(57,2),(57,6),(59,1),(59,6),(61,2),(61,6),(63,1),(63,6),(65,2),(65,6),(67,1),(67,6),(69,2),(69,6),(71,1),(71,6),(73,2),(73,6),(75,1),(75,6),(77,2),(77,6),(79,1),(79,6),(81,2),(81,6),(83,1),(83,6),(85,2),(85,6),(87,1),(87,6),(89,2),(89,6),(91,2),(91,6),(93,1),(93,6),(95,2),(95,6),(97,1),(97,6),(99,2),(99,6),(101,2),(101,6),(103,1),(103,6),(105,2),(105,6),(107,1),(107,6),(109,2),(109,6),(111,2),(111,6),(113,1),(113,6),(115,2),(115,6),(117,1),(117,6),(119,2),(119,6),(121,2),(121,6),(123,1),(123,6),(125,2),(125,6),(127,1),(127,6),(129,2),(129,6),(131,2),(131,6),(133,1),(133,6),(135,2),(135,6),(137,1),(137,6),(139,2),(139,6),(141,2),(141,6),(143,1),(143,6),(145,2),(145,6),(147,1),(147,6),(149,2),(149,6),(151,2),(151,6),(153,1),(153,6),(155,2),(155,6),(157,1),(157,6),(159,2),(159,6),(161,2),(161,6);
/*!40000 ALTER TABLE `testcase_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testplan_platforms`
--

DROP TABLE IF EXISTS `testplan_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testplan_platforms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int(10) unsigned NOT NULL,
  `platform_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_testplan_platforms` (`testplan_id`,`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='Connects a testplan with platforms';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testplan_platforms`
--

LOCK TABLES `testplan_platforms` WRITE;
/*!40000 ALTER TABLE `testplan_platforms` DISABLE KEYS */;
INSERT INTO `testplan_platforms` VALUES (1,4,1),(2,4,2),(4,4,3),(3,4,4);
/*!40000 ALTER TABLE `testplan_platforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testplan_tcversions`
--

DROP TABLE IF EXISTS `testplan_tcversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testplan_tcversions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tcversion_id` int(10) unsigned NOT NULL DEFAULT '0',
  `node_order` int(10) unsigned NOT NULL DEFAULT '1',
  `urgency` smallint(5) NOT NULL DEFAULT '2',
  `platform_id` int(10) unsigned NOT NULL DEFAULT '0',
  `author_id` int(10) unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `testplan_tcversions_tplan_tcversion` (`testplan_id`,`tcversion_id`,`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testplan_tcversions`
--

LOCK TABLES `testplan_tcversions` WRITE;
/*!40000 ALTER TABLE `testplan_tcversions` DISABLE KEYS */;
INSERT INTO `testplan_tcversions` VALUES (1,4,32,1000,2,1,1,'2012-05-01 10:33:49'),(2,4,32,1000,2,2,1,'2012-05-01 10:33:49'),(3,4,32,1000,2,4,1,'2012-05-01 10:33:49'),(4,4,32,1000,2,3,1,'2012-05-01 10:33:49'),(5,4,34,1010,2,1,1,'2012-05-01 10:33:49'),(6,4,34,1010,2,2,1,'2012-05-01 10:33:49'),(7,4,34,1010,2,4,1,'2012-05-01 10:33:49'),(8,4,34,1010,2,3,1,'2012-05-01 10:33:49'),(9,4,44,1000,2,1,1,'2012-05-01 10:34:05'),(10,4,44,1000,2,2,1,'2012-05-01 10:34:05'),(11,4,44,1000,2,4,1,'2012-05-01 10:34:05'),(12,4,48,1000,2,1,1,'2012-05-01 10:34:05'),(13,4,48,1000,2,2,1,'2012-05-01 10:34:05'),(14,4,48,1000,2,4,1,'2012-05-01 10:34:05'),(15,4,46,1010,2,1,1,'2012-05-01 10:34:05'),(16,4,46,1010,2,2,1,'2012-05-01 10:34:05'),(17,4,46,1010,2,4,1,'2012-05-01 10:34:05'),(18,4,50,1010,2,1,1,'2012-05-01 10:34:05'),(19,4,50,1010,2,2,1,'2012-05-01 10:34:05'),(20,4,50,1010,2,4,1,'2012-05-01 10:34:05'),(21,4,94,1000,2,4,1,'2012-05-01 10:34:41'),(22,4,94,1000,2,3,1,'2012-05-01 10:34:41'),(23,4,98,1000,2,4,1,'2012-05-01 10:34:41'),(24,4,98,1000,2,3,1,'2012-05-01 10:34:41'),(25,4,96,1010,2,4,1,'2012-05-01 10:34:41'),(26,4,96,1010,2,3,1,'2012-05-01 10:34:41'),(27,4,100,1010,2,4,1,'2012-05-01 10:34:41'),(28,4,100,1010,2,3,1,'2012-05-01 10:34:41'),(29,4,102,1010,2,4,1,'2012-05-01 10:34:41'),(30,4,102,1010,2,3,1,'2012-05-01 10:34:41'),(31,4,104,1000,2,4,1,'2012-05-01 10:34:41'),(32,4,104,1000,2,3,1,'2012-05-01 10:34:41'),(33,4,108,1000,2,4,1,'2012-05-01 10:34:41'),(34,4,108,1000,2,3,1,'2012-05-01 10:34:41'),(35,4,106,1010,2,4,1,'2012-05-01 10:34:41'),(36,4,106,1010,2,3,1,'2012-05-01 10:34:41'),(37,4,110,1010,2,4,1,'2012-05-01 10:34:41'),(38,4,110,1010,2,3,1,'2012-05-01 10:34:41'),(39,4,112,1010,2,4,1,'2012-05-01 10:34:41'),(40,4,112,1010,2,3,1,'2012-05-01 10:34:41'),(41,4,144,1000,2,1,1,'2012-05-01 10:34:56'),(42,4,144,1000,2,2,1,'2012-05-01 10:34:56'),(43,4,144,1000,2,4,1,'2012-05-01 10:34:56'),(44,4,144,1000,2,3,1,'2012-05-01 10:34:56'),(45,4,148,1000,2,1,1,'2012-05-01 10:34:56'),(46,4,148,1000,2,2,1,'2012-05-01 10:34:56'),(47,4,148,1000,2,4,1,'2012-05-01 10:34:56'),(48,4,148,1000,2,3,1,'2012-05-01 10:34:56'),(49,4,146,1010,2,1,1,'2012-05-01 10:34:56'),(50,4,146,1010,2,2,1,'2012-05-01 10:34:56'),(51,4,146,1010,2,4,1,'2012-05-01 10:34:56'),(52,4,146,1010,2,3,1,'2012-05-01 10:34:56'),(53,4,150,1010,2,1,1,'2012-05-01 10:34:56'),(54,4,150,1010,2,2,1,'2012-05-01 10:34:56'),(55,4,150,1010,2,4,1,'2012-05-01 10:34:56'),(56,4,150,1010,2,3,1,'2012-05-01 10:34:56'),(57,4,152,1010,2,1,1,'2012-05-01 10:34:56'),(58,4,152,1010,2,2,1,'2012-05-01 10:34:56'),(59,4,152,1010,2,4,1,'2012-05-01 10:34:56'),(60,4,152,1010,2,3,1,'2012-05-01 10:34:56');
/*!40000 ALTER TABLE `testplan_tcversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testplans`
--

DROP TABLE IF EXISTS `testplans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testplans` (
  `id` int(10) unsigned NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL DEFAULT '0',
  `notes` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `testplans_testproject_id_active` (`testproject_id`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testplans`
--

LOCK TABLES `testplans` WRITE;
/*!40000 ALTER TABLE `testplans` DISABLE KEYS */;
INSERT INTO `testplans` VALUES (4,2,'<p><strong>P Zero&trade; Red</strong>, a supersoft for street circuits. Of the  four slick tyres, this is the only one to remain unchanged from the 2011  season. It showed itself to be particularly versatile, offering high  peaks of performance over slow and twisty circuits that are  characterised by slippery asphalt and low lateral loadings. This is the  ideal compound for street circuits or semipermanent facilities.</p>',1,1,1),(5,2,'<p><strong>P Zero&trade; Yellow</strong>, softer with less blistering. The new  soft tyre is well suited to circuits with low tyre wear. It is designed  to offer a high level of grip coupled with a significant amount of  degradation, resulting in a comparatively short lifespan that will give  the teams a greater number of options with pit stop strategy and even  closer racing. Compared to the equivalent tyre in 2011, the new soft  offers greater thermal resistance to reduce the risk of blistering.  Tested for the first time during free practice at last year&rsquo;s Abu Dhabi  Grand Prix, the new soft tyre is set to be one of the most frequent  nominations in 2012, together with the new medium tyre. This combination  offers a great deal of flexibility and also a rapid warm-up time.</p>',1,1,1),(6,2,'<p><strong>P Zero&trade; White</strong>, the medium tyre that is well suited to  all conditions. This extremely versatile tyre adapts itself well to all  sorts of track conditions, particularly when asphalt and circuit  characteristics are variable. The brand new P Zero&trade; White is intended as  the &lsquo;option&rsquo; tyre on tracks with high temperatures or abrasive surfaces  and as the &lsquo;prime&rsquo; tyre on tracks that are less severe with fewer  demands on the tyres. The new medium compound was tried out last year  during free practice at the German Grand Prix and made another  appearance during the young driver test in Abu Dhabi.</p>',1,1,1),(7,2,'<p><strong>P Zero&trade; Silver</strong>, hard but not inflexible. The new hard  tyre guarantees maximum durability and the least degradation, together  with optimal resistance to the most extreme conditions, but is not as  hard as the equivalent tyre last year. The P Zero&trade; Silver is ideal for  long runs, taking more time to warm up, as well as being suited to  circuits with abrasive asphalt, big lateral forces and high  temperatures. The new P Zero&trade; Silver was tested at the Barcelona circuit  by Pirelli&rsquo;s test driver Lucas di Grassi, and is the only one of the  new compounds that the regular drivers have not yet experienced.</p>',1,1,1),(8,3,'<p><strong>Cinturato&trade; Green</strong>, the intermediate for light rain.  After the excellent performances seen from this tyre throughout the 2011  season during particularly demanding races such as the Canadian Grand  Prix, Pirelli&rsquo;s engineers decided not to make any changes to the  intermediate tyres. The shallower grooves compared to the full wet tyres  mean that the intermediates do not drain away as much water, making  this the ideal choice for wet or drying asphalt, without compromising on  performance.</p>',1,1,1),(9,3,'<p><strong>Cinturato&trade; Blue</strong>, the full wets. Of the two wet tyres,  only the full wet has been significantly altered compared to the 2011  version. The changes relate to the rear tyres, which use a different  profile in order to optimise the dispersal of water in case of  aquaplaning and guarantee a greater degree of driving precision.  Characterised by deep grooves, similar to those seen on a road car tyre,  the wet tyres are designed to expel more than 60 litres of water per  second at a speed of 300 kph: six times more than a road car tyre, which  disperses about 10 litres per second at a much lower speed.</p>',1,1,1);
/*!40000 ALTER TABLE `testplans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testprojects`
--

DROP TABLE IF EXISTS `testprojects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testprojects` (
  `id` int(10) unsigned NOT NULL,
  `notes` text,
  `color` varchar(12) NOT NULL DEFAULT '#9BD',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `option_reqs` tinyint(1) NOT NULL DEFAULT '0',
  `option_priority` tinyint(1) NOT NULL DEFAULT '0',
  `option_automation` tinyint(1) NOT NULL DEFAULT '0',
  `options` text,
  `prefix` varchar(16) NOT NULL,
  `tc_counter` int(10) unsigned NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `testprojects_prefix` (`prefix`),
  KEY `testprojects_id_active` (`id`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testprojects`
--

LOCK TABLES `testprojects` WRITE;
/*!40000 ALTER TABLE `testprojects` DISABLE KEYS */;
INSERT INTO `testprojects` VALUES (2,'<p>In accordance with the regulations laid down by the FIA (F&eacute;d&eacute;ration  Internationale de l\'Automobile) Pirelli will supply two different types  of tyre designed for two different types of use.<br />\r\nThe first type of tyre has been designed for dry surfaces, while the second is for wet surfaces.</p>','',1,0,0,0,'O:8:\"stdClass\":4:{s:19:\"requirementsEnabled\";i:1;s:19:\"testPriorityEnabled\";i:1;s:17:\"automationEnabled\";i:1;s:16:\"inventoryEnabled\";i:1;}','PDT',66,1),(3,'<p>in accordance with the regulations laid down by the FIA (F&eacute;d&eacute;ration  Internationale de l\'Automobile) Pirelli will supply two different types  of tyre designed for two different types of use.<br />\r\nThe first type of tyre has been designed for dry surfaces, while the second is for wet surfaces.</p>','',1,0,0,0,'O:8:\"stdClass\":4:{s:19:\"requirementsEnabled\";i:1;s:19:\"testPriorityEnabled\";i:1;s:17:\"automationEnabled\";i:1;s:16:\"inventoryEnabled\";i:1;}','PWT',0,1);
/*!40000 ALTER TABLE `testprojects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testsuites`
--

DROP TABLE IF EXISTS `testsuites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testsuites` (
  `id` int(10) unsigned NOT NULL,
  `details` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testsuites`
--

LOCK TABLES `testsuites` WRITE;
/*!40000 ALTER TABLE `testsuites` DISABLE KEYS */;
INSERT INTO `testsuites` VALUES (10,''),(11,'<p><br />\r\n<br />\r\n<br />\r\n</p>'),(12,''),(13,''),(14,''),(15,'<p><br />\r\n</p>'),(16,''),(17,''),(18,''),(19,''),(20,''),(21,''),(22,''),(23,''),(24,''),(25,''),(26,''),(27,''),(28,''),(29,''),(30,'');
/*!40000 ALTER TABLE `testsuites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entry_point` varchar(45) NOT NULL DEFAULT '',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `session_id` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,'/tl193-untouched/lib/general/navBar.php',1335862631,1335862631,210,'95p2svhjqusr1vjbhgj9bird23'),(2,'/tl193-untouched/lib/general/mainPage.php',1335862631,1335862631,210,'95p2svhjqusr1vjbhgj9bird23'),(3,'/tl193-untouched/lib/general/mainPage.php',1335862696,1335862696,210,'95p2svhjqusr1vjbhgj9bird23'),(4,'/tl193-untouched/lib/general/navBar.php',1335862696,1335862696,210,'95p2svhjqusr1vjbhgj9bird23'),(5,'/development/tl-old/tl193-untouched/login.php',1335862713,1335862713,1,'190eh67vq7bsrde26gde2g46c3'),(6,'/tl193-untouched/lib/general/mainPage.php',1335862714,1335862714,1,'190eh67vq7bsrde26gde2g46c3'),(7,'/tl193-untouched/lib/project/projectEdit.php',1335862746,1335862746,1,'190eh67vq7bsrde26gde2g46c3'),(8,'/tl193-untouched/lib/project/projectEdit.php',1335864014,1335864014,1,'190eh67vq7bsrde26gde2g46c3'),(9,'/tl193-untouched/lib/project/projectEdit.php',1335864053,1335864053,1,'190eh67vq7bsrde26gde2g46c3'),(10,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864120,1335864120,1,'190eh67vq7bsrde26gde2g46c3'),(11,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864160,1335864160,1,'190eh67vq7bsrde26gde2g46c3'),(12,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864193,1335864193,1,'190eh67vq7bsrde26gde2g46c3'),(13,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864246,1335864246,1,'190eh67vq7bsrde26gde2g46c3'),(14,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864293,1335864294,1,'190eh67vq7bsrde26gde2g46c3'),(15,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864326,1335864326,1,'190eh67vq7bsrde26gde2g46c3'),(16,'/tl193-untouched/lib/project/projectEdit.php',1335864347,1335864347,1,'190eh67vq7bsrde26gde2g46c3'),(17,'/tl193-untouched/lib/plan/buildEdit.php',1335864400,1335864400,1,'190eh67vq7bsrde26gde2g46c3'),(18,'/tl193-untouched/lib/plan/buildEdit.php',1335864414,1335864414,1,'190eh67vq7bsrde26gde2g46c3'),(19,'/tl193-untouched/lib/plan/buildEdit.php',1335864428,1335864429,1,'190eh67vq7bsrde26gde2g46c3'),(20,'/tl193-untouched/lib/plan/buildEdit.php',1335864459,1335864459,1,'190eh67vq7bsrde26gde2g46c3'),(21,'/tl193-untouched/lib/plan/buildEdit.php',1335864466,1335864466,1,'190eh67vq7bsrde26gde2g46c3'),(22,'/tl193-untouched/lib/plan/buildEdit.php',1335864473,1335864473,1,'190eh67vq7bsrde26gde2g46c3'),(23,'/lib/keywords/keywordsEdit.php',1335866991,1335866991,1,'190eh67vq7bsrde26gde2g46c3'),(24,'/lib/keywords/keywordsEdit.php',1335867005,1335867005,1,'190eh67vq7bsrde26gde2g46c3'),(25,'/lib/keywords/keywordsEdit.php',1335867022,1335867022,1,'190eh67vq7bsrde26gde2g46c3'),(26,'/lib/keywords/keywordsEdit.php',1335867034,1335867034,1,'190eh67vq7bsrde26gde2g46c3'),(27,'/lib/keywords/keywordsEdit.php',1335867048,1335867048,1,'190eh67vq7bsrde26gde2g46c3'),(28,'/lib/keywords/keywordsEdit.php',1335867057,1335867057,1,'190eh67vq7bsrde26gde2g46c3'),(29,'/tl193-untouched/lib/testcases/tcEdit.php',1335867108,1335867108,1,'190eh67vq7bsrde26gde2g46c3'),(30,'/tl193-untouched/lib/testcases/tcEdit.php',1335867121,1335867121,1,'190eh67vq7bsrde26gde2g46c3'),(31,'/tl193-untouched/lib/testcases/tcImport.php',1335867626,1335867626,1,'190eh67vq7bsrde26gde2g46c3'),(32,'/tl193-untouched/lib/testcases/tcImport.php',1335867682,1335867682,1,'190eh67vq7bsrde26gde2g46c3'),(33,'/tl193-untouched/lib/testcases/tcImport.php',1335867747,1335867747,1,'190eh67vq7bsrde26gde2g46c3'),(34,'/tl193-untouched/lib/testcases/tcImport.php',1335867792,1335867792,1,'190eh67vq7bsrde26gde2g46c3'),(35,'/tl193-untouched/lib/testcases/tcImport.php',1335867847,1335867847,1,'190eh67vq7bsrde26gde2g46c3'),(36,'/tl193-untouched/lib/testcases/tcImport.php',1335867890,1335867891,1,'190eh67vq7bsrde26gde2g46c3'),(37,'/tl193-untouched/lib/testcases/tcImport.php',1335867956,1335867956,1,'190eh67vq7bsrde26gde2g46c3'),(38,'/tl193-untouched/lib/testcases/tcImport.php',1335868010,1335868011,1,'190eh67vq7bsrde26gde2g46c3'),(39,'/tl193-untouched/lib/testcases/tcImport.php',1335868064,1335868064,1,'190eh67vq7bsrde26gde2g46c3'),(40,'/tl193-untouched/lib/testcases/tcImport.php',1335868106,1335868107,1,'190eh67vq7bsrde26gde2g46c3'),(41,'/tl193-untouched/lib/testcases/tcImport.php',1335868144,1335868144,1,'190eh67vq7bsrde26gde2g46c3'),(42,'/tl193-untouched/lib/testcases/tcImport.php',1335868185,1335868185,1,'190eh67vq7bsrde26gde2g46c3'),(43,'/tl193-untouched/lib/testcases/tcImport.php',1335868220,1335868220,1,'190eh67vq7bsrde26gde2g46c3'),(44,'/tl193-untouched/lib/testcases/tcImport.php',1335868246,1335868247,1,'190eh67vq7bsrde26gde2g46c3'),(45,'/lib/plan/planAddTCNavigator.php',1335868398,1335868399,1,'190eh67vq7bsrde26gde2g46c3'),(46,'/tl193-untouched/lib/plan/planAddTC.php',1335868429,1335868429,1,'190eh67vq7bsrde26gde2g46c3'),(47,'/tl193-untouched/lib/plan/planAddTC.php',1335868446,1335868446,1,'190eh67vq7bsrde26gde2g46c3'),(48,'/tl193-untouched/lib/plan/planAddTC.php',1335868481,1335868481,1,'190eh67vq7bsrde26gde2g46c3'),(49,'/tl193-untouched/lib/plan/planAddTC.php',1335868496,1335868496,1,'190eh67vq7bsrde26gde2g46c3');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_assignments`
--

DROP TABLE IF EXISTS `user_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(10) unsigned NOT NULL DEFAULT '1',
  `feature_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned DEFAULT '0',
  `build_id` int(10) unsigned DEFAULT '0',
  `deadline_ts` datetime DEFAULT NULL,
  `assigner_id` int(10) unsigned DEFAULT '0',
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_assignments_feature_id` (`feature_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_assignments`
--

LOCK TABLES `user_assignments` WRITE;
/*!40000 ALTER TABLE `user_assignments` DISABLE KEYS */;
INSERT INTO `user_assignments` VALUES (1,1,1,4,1,NULL,1,'2012-05-01 10:36:33',1),(2,1,2,3,1,NULL,1,'2012-05-01 10:36:33',1),(3,1,4,2,1,NULL,1,'2012-05-01 10:36:33',1),(4,1,3,5,1,NULL,1,'2012-05-01 10:36:33',1),(5,1,5,4,1,NULL,1,'2012-05-01 10:36:33',1),(6,1,6,3,1,NULL,1,'2012-05-01 10:36:33',1),(7,1,8,2,1,NULL,1,'2012-05-01 10:36:33',1),(8,1,7,5,1,NULL,1,'2012-05-01 10:36:33',1),(9,1,9,4,1,NULL,1,'2012-05-01 10:36:58',1),(10,1,10,3,1,NULL,1,'2012-05-01 10:36:58',1),(11,1,11,5,1,NULL,1,'2012-05-01 10:36:58',1),(12,1,12,4,1,NULL,1,'2012-05-01 10:36:58',1),(13,1,13,3,1,NULL,1,'2012-05-01 10:36:58',1),(14,1,14,5,1,NULL,1,'2012-05-01 10:36:58',1),(15,1,22,2,1,NULL,1,'2012-05-01 10:37:46',1),(16,1,24,2,1,NULL,1,'2012-05-01 10:37:46',1),(17,1,23,5,1,NULL,1,'2012-05-01 10:37:46',1),(18,1,26,2,1,NULL,1,'2012-05-01 10:37:46',1),(19,1,25,5,1,NULL,1,'2012-05-01 10:37:46',1),(20,1,28,2,1,NULL,1,'2012-05-01 10:37:46',1),(21,1,27,5,1,NULL,1,'2012-05-01 10:37:46',1),(22,1,30,2,1,NULL,1,'2012-05-01 10:37:46',1);
/*!40000 ALTER TABLE `user_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_group` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group`
--

LOCK TABLES `user_group` WRITE;
/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group_assign`
--

DROP TABLE IF EXISTS `user_group_assign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group_assign` (
  `usergroup_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `idx_user_group_assign` (`usergroup_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group_assign`
--

LOCK TABLES `user_group_assign` WRITE;
/*!40000 ALTER TABLE `user_group_assign` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_group_assign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_testplan_roles`
--

DROP TABLE IF EXISTS `user_testplan_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_testplan_roles` (
  `user_id` int(10) NOT NULL DEFAULT '0',
  `testplan_id` int(10) NOT NULL DEFAULT '0',
  `role_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`testplan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_testplan_roles`
--

LOCK TABLES `user_testplan_roles` WRITE;
/*!40000 ALTER TABLE `user_testplan_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_testplan_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_testproject_roles`
--

DROP TABLE IF EXISTS `user_testproject_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_testproject_roles` (
  `user_id` int(10) NOT NULL DEFAULT '0',
  `testproject_id` int(10) NOT NULL DEFAULT '0',
  `role_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_testproject_roles`
--

LOCK TABLES `user_testproject_roles` WRITE;
/*!40000 ALTER TABLE `user_testproject_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_testproject_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `role_id` int(10) unsigned NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '',
  `first` varchar(30) NOT NULL DEFAULT '',
  `last` varchar(30) NOT NULL DEFAULT '',
  `locale` varchar(10) NOT NULL DEFAULT 'en_GB',
  `default_testproject_id` int(10) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `script_key` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='User information';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','21232f297a57a5a743894a0e4a801fc3',8,'','Testlink','Administrator','en_GB',NULL,1,NULL),(2,'Mark.Webber','9651cbc7c0b5fb1a81f2858a07813c82',8,'Mark.Webber@formulaone.com','Mark','Webber','en_GB',NULL,1,'DEVKEY-Webber'),(3,'Lewis.Hamilton','9651cbc7c0b5fb1a81f2858a07813c82',9,'Lewis.Hamilton@formulaone.com','Lewis','Hamilton','it_IT',NULL,1,'DEVKEY-Hamilton'),(4,'Fernando.Alonso','9651cbc7c0b5fb1a81f2858a07813c82',6,'Fernando.Alonso@formulaone.com','Fernando','Alonso','en_GB',NULL,1,'DEVKEY-Alonso'),(5,'Michael.Schumacher','9651cbc7c0b5fb1a81f2858a07813c82',8,'Michael.Schumacher@formulaone.com','Michael','Schumacher','en_GB',NULL,1,'DEVKEY-Schumacher');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-05-01 12:38:54
