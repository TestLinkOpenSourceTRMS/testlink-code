-- MariaDB dump 10.19  Distrib 10.5.21-MariaDB, for debian-linux-gnu (aarch64)
--
-- Host: testlink-mysql    Database: testlink
-- ------------------------------------------------------
-- Server version	8.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `assignment_status`
--

DROP TABLE IF EXISTS `assignment_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `fk_table` varchar(30) DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `fk_id` int unsigned NOT NULL DEFAULT '0',
  `fk_table` varchar(250) DEFAULT '',
  `title` varchar(250) DEFAULT '',
  `description` varchar(250) DEFAULT '',
  `file_name` varchar(250) NOT NULL DEFAULT '',
  `file_path` varchar(250) DEFAULT '',
  `file_size` int NOT NULL DEFAULT '0',
  `file_type` varchar(250) NOT NULL DEFAULT '',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `content` longblob,
  `compression_type` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `attachments_idx1` (`fk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `baseline_l1l2_context`
--

DROP TABLE IF EXISTS `baseline_l1l2_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `baseline_l1l2_context` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int unsigned NOT NULL DEFAULT '0',
  `platform_id` int unsigned NOT NULL DEFAULT '0',
  `begin_exec_ts` timestamp NOT NULL,
  `end_exec_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `udx1_context` (`testplan_id`,`platform_id`,`creation_ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `baseline_l1l2_context`
--

LOCK TABLES `baseline_l1l2_context` WRITE;
/*!40000 ALTER TABLE `baseline_l1l2_context` DISABLE KEYS */;
/*!40000 ALTER TABLE `baseline_l1l2_context` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `baseline_l1l2_details`
--

DROP TABLE IF EXISTS `baseline_l1l2_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `baseline_l1l2_details` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `context_id` int unsigned NOT NULL,
  `top_tsuite_id` int unsigned NOT NULL DEFAULT '0',
  `child_tsuite_id` int unsigned NOT NULL DEFAULT '0',
  `status` char(1) DEFAULT NULL,
  `qty` int unsigned NOT NULL DEFAULT '0',
  `total_tc` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `udx1_details` (`context_id`,`top_tsuite_id`,`child_tsuite_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `baseline_l1l2_details`
--

LOCK TABLES `baseline_l1l2_details` WRITE;
/*!40000 ALTER TABLE `baseline_l1l2_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `baseline_l1l2_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `builds`
--

DROP TABLE IF EXISTS `builds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `builds` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT 'undefined',
  `notes` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `release_date` date DEFAULT NULL,
  `closed_on_date` date DEFAULT NULL,
  `commit_id` varchar(64) DEFAULT NULL,
  `tag` varchar(64) DEFAULT NULL,
  `branch` varchar(64) DEFAULT NULL,
  `release_candidate` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`testplan_id`,`name`),
  KEY `testplan_id` (`testplan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COMMENT='Available builds';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `builds`
--

LOCK TABLES `builds` WRITE;
/*!40000 ALTER TABLE `builds` DISABLE KEYS */;
INSERT INTO `builds` VALUES (1,4,'PZ X.1','',1,1,NULL,'2022-05-01 09:26:40',NULL,NULL,NULL,NULL,NULL,NULL),(2,4,'PZ X.2','',1,1,NULL,'2022-05-01 09:26:54',NULL,NULL,NULL,NULL,NULL,NULL),(3,4,'PZ X.3','',1,1,NULL,'2022-05-01 09:27:08',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `builds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cfield_build_design_values`
--

DROP TABLE IF EXISTS `cfield_build_design_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfield_build_design_values` (
  `field_id` int NOT NULL DEFAULT '0',
  `node_id` int NOT NULL DEFAULT '0',
  `value` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`field_id`,`node_id`),
  KEY `idx_cfield_build_design_values` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cfield_build_design_values`
--

LOCK TABLES `cfield_build_design_values` WRITE;
/*!40000 ALTER TABLE `cfield_build_design_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_build_design_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cfield_design_values`
--

DROP TABLE IF EXISTS `cfield_design_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfield_design_values` (
  `field_id` int NOT NULL DEFAULT '0',
  `node_id` int NOT NULL DEFAULT '0',
  `value` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`field_id`,`node_id`),
  KEY `idx_cfield_design_values` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `field_id` int NOT NULL DEFAULT '0',
  `execution_id` int NOT NULL DEFAULT '0',
  `testplan_id` int NOT NULL DEFAULT '0',
  `tcversion_id` int NOT NULL DEFAULT '0',
  `value` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`field_id`,`execution_id`,`testplan_id`,`tcversion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `field_id` int NOT NULL DEFAULT '0',
  `node_type_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`,`node_type_id`),
  KEY `idx_custom_fields_assign` (`node_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `field_id` int NOT NULL DEFAULT '0',
  `link_id` int NOT NULL DEFAULT '0' COMMENT 'point to testplan_tcversion id',
  `value` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`field_id`,`link_id`),
  KEY `idx_cfield_tplan_design_val` (`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `field_id` int unsigned NOT NULL DEFAULT '0',
  `testproject_id` int unsigned NOT NULL DEFAULT '0',
  `display_order` smallint unsigned NOT NULL DEFAULT '1',
  `location` smallint unsigned NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `required_on_design` tinyint(1) NOT NULL DEFAULT '0',
  `required_on_execution` tinyint(1) NOT NULL DEFAULT '0',
  `monitorable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`,`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cfield_testprojects`
--

LOCK TABLES `cfield_testprojects` WRITE;
/*!40000 ALTER TABLE `cfield_testprojects` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfield_testprojects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codetrackers`
--

DROP TABLE IF EXISTS `codetrackers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `codetrackers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` int DEFAULT '0',
  `cfg` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codetrackers_uidx1` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codetrackers`
--

LOCK TABLES `codetrackers` WRITE;
/*!40000 ALTER TABLE `codetrackers` DISABLE KEYS */;
/*!40000 ALTER TABLE `codetrackers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_fields`
--

DROP TABLE IF EXISTS `custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `label` varchar(64) NOT NULL DEFAULT '' COMMENT 'label to display on user interface',
  `type` smallint NOT NULL DEFAULT '0',
  `possible_values` varchar(4000) NOT NULL DEFAULT '',
  `default_value` varchar(4000) NOT NULL DEFAULT '',
  `valid_regexp` varchar(255) NOT NULL DEFAULT '',
  `length_min` int NOT NULL DEFAULT '0',
  `length_max` int NOT NULL DEFAULT '0',
  `show_on_design` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '1=> show it during specification design',
  `enable_on_design` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '1=> user can write/manage it during specification design',
  `show_on_execution` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '1=> show it during test case execution',
  `enable_on_execution` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '1=> user can write/manage it during test case execution',
  `show_on_testplan_design` tinyint unsigned NOT NULL DEFAULT '0',
  `enable_on_testplan_design` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_custom_fields_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `upgrade_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `db_version`
--

LOCK TABLES `db_version` WRITE;
/*!40000 ALTER TABLE `db_version` DISABLE KEYS */;
INSERT INTO `db_version` VALUES ('DB 1.9.20','2022-02-04 14:59:03','TestLink 1.9.20 Raijin');
/*!40000 ALTER TABLE `db_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` int unsigned NOT NULL DEFAULT '0',
  `log_level` smallint unsigned NOT NULL DEFAULT '0',
  `source` varchar(45) DEFAULT NULL,
  `description` text NOT NULL,
  `fired_at` int unsigned NOT NULL DEFAULT '0',
  `activity` varchar(45) DEFAULT NULL,
  `object_id` int unsigned DEFAULT NULL,
  `object_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `fired_at` (`fired_at`)
) ENGINE=InnoDB AUTO_INCREMENT=302 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (265,50,16,'GUI','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:18:\"audit_login_failed\";s:6:\"params\";a:2:{i:0;s:5:\"admin\";i:1;s:12:\"192.168.65.1\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1707059206,'LOGIN_FAILED',1,'users'),(266,51,16,'GUI - Test Project ID : 2','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:21:\"audit_login_succeeded\";s:6:\"params\";a:2:{i:0;s:5:\"admin\";i:1;s:12:\"192.168.65.1\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1707059211,'LOGIN',1,'users'),(267,52,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: DataTablesSelector - in /var/www/testlink/gui/templates_c/6e35d38c467944a048705ebd8774a98926e2ced6_0.file.DataTables.inc.tpl.php - Line 35',1707059214,'PHP',0,NULL),(268,52,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to get property \'value\' of non-object - in /var/www/testlink/gui/templates_c/6e35d38c467944a048705ebd8774a98926e2ced6_0.file.DataTables.inc.tpl.php - Line 35',1707059214,'PHP',0,NULL),(269,53,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: DataTablesSelector - in /var/www/testlink/gui/templates_c/6e35d38c467944a048705ebd8774a98926e2ced6_0.file.DataTables.inc.tpl.php - Line 35',1707059224,'PHP',0,NULL),(270,53,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to get property \'value\' of non-object - in /var/www/testlink/gui/templates_c/6e35d38c467944a048705ebd8774a98926e2ced6_0.file.DataTables.inc.tpl.php - Line 35',1707059224,'PHP',0,NULL),(271,53,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: DataTablesSelector - in /var/www/testlink/gui/templates_c/8c2b5b97f935ad5a2c97c9ca634a8aad40cbfb8f_0.file.DataTablesColumnFiltering.inc.tpl.php - Line 31',1707059224,'PHP',0,NULL),(272,53,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to get property \'value\' of non-object - in /var/www/testlink/gui/templates_c/8c2b5b97f935ad5a2c97c9ca634a8aad40cbfb8f_0.file.DataTablesColumnFiltering.inc.tpl.php - Line 31',1707059224,'PHP',0,NULL),(273,53,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: DataTablesSelector - in /var/www/testlink/gui/templates_c/8c2b5b97f935ad5a2c97c9ca634a8aad40cbfb8f_0.file.DataTablesColumnFiltering.inc.tpl.php - Line 48',1707059224,'PHP',0,NULL),(274,53,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to get property \'value\' of non-object - in /var/www/testlink/gui/templates_c/8c2b5b97f935ad5a2c97c9ca634a8aad40cbfb8f_0.file.DataTablesColumnFiltering.inc.tpl.php - Line 48',1707059224,'PHP',0,NULL),(275,53,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: DataTablesSelector - in /var/www/testlink/gui/templates_c/8c2b5b97f935ad5a2c97c9ca634a8aad40cbfb8f_0.file.DataTablesColumnFiltering.inc.tpl.php - Line 49',1707059224,'PHP',0,NULL),(276,53,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to get property \'value\' of non-object - in /var/www/testlink/gui/templates_c/8c2b5b97f935ad5a2c97c9ca634a8aad40cbfb8f_0.file.DataTablesColumnFiltering.inc.tpl.php - Line 49',1707059224,'PHP',0,NULL),(277,53,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: DataTablesSelector - in /var/www/testlink/gui/templates_c/8c2b5b97f935ad5a2c97c9ca634a8aad40cbfb8f_0.file.DataTablesColumnFiltering.inc.tpl.php - Line 51',1707059224,'PHP',0,NULL),(278,53,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to get property \'value\' of non-object - in /var/www/testlink/gui/templates_c/8c2b5b97f935ad5a2c97c9ca634a8aad40cbfb8f_0.file.DataTablesColumnFiltering.inc.tpl.php - Line 51',1707059224,'PHP',0,NULL),(279,54,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 298',1707059232,'PHP',0,NULL),(280,55,2,'GUI - Test Project ID : 2','E_WARNING\ngetimagesize(http://localhost:8080/gui/themes/default/images/tl-logo-transparent-25.png): failed to open stream: Cannot assign requested address - in /var/www/testlink/lib/functions/print.inc.php - Line 694',1707059578,'PHP',0,NULL),(281,56,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 489',1707059648,'PHP',0,NULL),(282,57,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 489',1707059672,'PHP',0,NULL),(283,58,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 489',1707059672,'PHP',0,NULL),(284,59,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 489',1707059678,'PHP',0,NULL),(285,59,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 489',1707059678,'PHP',0,NULL),(286,59,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/gui/templates_c/76a998150d2db34c4f158f1d58a406eac7079c26_0.file.inc_exec_show_tc_exec.tpl.php - Line 242',1707059678,'PHP',0,NULL),(287,60,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 489',1707059678,'PHP',0,NULL),(288,60,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 489',1707059678,'PHP',0,NULL),(289,60,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/gui/templates_c/76a998150d2db34c4f158f1d58a406eac7079c26_0.file.inc_exec_show_tc_exec.tpl.php - Line 242',1707059678,'PHP',0,NULL),(290,61,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 489',1707059679,'PHP',0,NULL),(291,61,2,'GUI - Test Project ID : 2','E_NOTICE\nUndefined index: build - in /var/www/testlink/lib/functions/cfield_mgr.class.php - Line 489',1707059679,'PHP',0,NULL),(292,61,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/gui/templates_c/76a998150d2db34c4f158f1d58a406eac7079c26_0.file.inc_exec_show_tc_exec.tpl.php - Line 242',1707059679,'PHP',0,NULL),(293,62,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/lib/functions/specview.php - Line 706',1707059689,'PHP',0,NULL),(294,62,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/lib/functions/specview.php - Line 711',1707059689,'PHP',0,NULL),(295,62,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/lib/functions/specview.php - Line 748',1707059689,'PHP',0,NULL),(296,62,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/lib/functions/specview.php - Line 1172',1707059689,'PHP',0,NULL),(297,63,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/lib/functions/specview.php - Line 706',1707059692,'PHP',0,NULL),(298,63,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/lib/functions/specview.php - Line 711',1707059692,'PHP',0,NULL),(299,63,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/lib/functions/specview.php - Line 748',1707059692,'PHP',0,NULL),(300,63,2,'GUI - Test Project ID : 2','E_NOTICE\nTrying to access array offset on value of type null - in /var/www/testlink/lib/functions/specview.php - Line 1172',1707059692,'PHP',0,NULL),(301,64,16,'GUI - Test Project ID : 2','O:18:\"tlMetaStringHelper\":4:{s:5:\"label\";s:17:\"audit_user_logout\";s:6:\"params\";a:1:{i:0;s:5:\"admin\";}s:13:\"bDontLocalize\";b:0;s:14:\"bDontFireEvent\";b:0;}',1707059711,'LOGOUT',1,'users');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `exec_by_date_time`
--

DROP TABLE IF EXISTS `exec_by_date_time`;
/*!50001 DROP VIEW IF EXISTS `exec_by_date_time`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `exec_by_date_time` AS SELECT
 1 AS `testplan_name`,
  1 AS `yyyy_mm_dd`,
  1 AS `yyyy_mm`,
  1 AS `hh`,
  1 AS `hour`,
  1 AS `id`,
  1 AS `build_id`,
  1 AS `tester_id`,
  1 AS `execution_ts`,
  1 AS `status`,
  1 AS `testplan_id`,
  1 AS `tcversion_id`,
  1 AS `tcversion_number`,
  1 AS `platform_id`,
  1 AS `execution_type`,
  1 AS `execution_duration`,
  1 AS `notes` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `execution_bugs`
--

DROP TABLE IF EXISTS `execution_bugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `execution_bugs` (
  `execution_id` int unsigned NOT NULL DEFAULT '0',
  `bug_id` varchar(64) NOT NULL DEFAULT '0',
  `tcstep_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`execution_id`,`bug_id`,`tcstep_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `execution_bugs`
--

LOCK TABLES `execution_bugs` WRITE;
/*!40000 ALTER TABLE `execution_bugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `execution_bugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `execution_tcsteps`
--

DROP TABLE IF EXISTS `execution_tcsteps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `execution_tcsteps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `execution_id` int unsigned NOT NULL DEFAULT '0',
  `tcstep_id` int unsigned NOT NULL DEFAULT '0',
  `notes` text,
  `status` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `execution_tcsteps_idx1` (`execution_id`,`tcstep_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `execution_tcsteps`
--

LOCK TABLES `execution_tcsteps` WRITE;
/*!40000 ALTER TABLE `execution_tcsteps` DISABLE KEYS */;
/*!40000 ALTER TABLE `execution_tcsteps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `execution_tcsteps_wip`
--

DROP TABLE IF EXISTS `execution_tcsteps_wip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `execution_tcsteps_wip` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tcstep_id` int unsigned NOT NULL DEFAULT '0',
  `testplan_id` int unsigned NOT NULL DEFAULT '0',
  `platform_id` int unsigned NOT NULL DEFAULT '0',
  `build_id` int unsigned NOT NULL DEFAULT '0',
  `tester_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text,
  `status` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `execution_tcsteps_wip_idx1` (`tcstep_id`,`testplan_id`,`platform_id`,`build_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `execution_tcsteps_wip`
--

LOCK TABLES `execution_tcsteps_wip` WRITE;
/*!40000 ALTER TABLE `execution_tcsteps_wip` DISABLE KEYS */;
/*!40000 ALTER TABLE `execution_tcsteps_wip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `executions`
--

DROP TABLE IF EXISTS `executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `executions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `build_id` int NOT NULL DEFAULT '0',
  `tester_id` int unsigned DEFAULT NULL,
  `execution_ts` datetime DEFAULT NULL,
  `status` char(1) DEFAULT NULL,
  `testplan_id` int unsigned NOT NULL DEFAULT '0',
  `tcversion_id` int unsigned NOT NULL DEFAULT '0',
  `tcversion_number` smallint unsigned NOT NULL DEFAULT '1',
  `platform_id` int unsigned NOT NULL DEFAULT '0',
  `execution_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 -> manual, 2 -> automated',
  `execution_duration` decimal(6,2) DEFAULT NULL COMMENT 'NULL will be considered as NO DATA Provided by user',
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `executions_idx1` (`testplan_id`,`tcversion_id`,`platform_id`,`build_id`),
  KEY `executions_idx2` (`execution_type`),
  KEY `executions_idx3` (`tcversion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `testproject_id` int unsigned NOT NULL,
  `owner_id` int unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `ipaddress` varchar(255) NOT NULL,
  `content` text,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inventory_idx1` (`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `issuetrackers`
--

DROP TABLE IF EXISTS `issuetrackers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `issuetrackers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` int DEFAULT '0',
  `cfg` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `issuetrackers_uidx1` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `issuetrackers`
--

LOCK TABLES `issuetrackers` WRITE;
/*!40000 ALTER TABLE `issuetrackers` DISABLE KEYS */;
/*!40000 ALTER TABLE `issuetrackers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keywords`
--

DROP TABLE IF EXISTS `keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keywords` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `testproject_id` int unsigned NOT NULL DEFAULT '0',
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword_testproject_id` (`keyword`,`testproject_id`),
  KEY `testproject_id` (`testproject_id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
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
-- Temporary table structure for view `latest_exec_by_context`
--

DROP TABLE IF EXISTS `latest_exec_by_context`;
/*!50001 DROP VIEW IF EXISTS `latest_exec_by_context`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latest_exec_by_context` AS SELECT
 1 AS `tcversion_id`,
  1 AS `testplan_id`,
  1 AS `build_id`,
  1 AS `platform_id`,
  1 AS `id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `latest_exec_by_testplan`
--

DROP TABLE IF EXISTS `latest_exec_by_testplan`;
/*!50001 DROP VIEW IF EXISTS `latest_exec_by_testplan`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latest_exec_by_testplan` AS SELECT
 1 AS `tcversion_id`,
  1 AS `testplan_id`,
  1 AS `id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `latest_exec_by_testplan_plat`
--

DROP TABLE IF EXISTS `latest_exec_by_testplan_plat`;
/*!50001 DROP VIEW IF EXISTS `latest_exec_by_testplan_plat`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latest_exec_by_testplan_plat` AS SELECT
 1 AS `tcversion_id`,
  1 AS `testplan_id`,
  1 AS `platform_id`,
  1 AS `id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `latest_req_version`
--

DROP TABLE IF EXISTS `latest_req_version`;
/*!50001 DROP VIEW IF EXISTS `latest_req_version`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latest_req_version` AS SELECT
 1 AS `req_id`,
  1 AS `version` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `latest_req_version_id`
--

DROP TABLE IF EXISTS `latest_req_version_id`;
/*!50001 DROP VIEW IF EXISTS `latest_req_version_id`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latest_req_version_id` AS SELECT
 1 AS `req_id`,
  1 AS `version`,
  1 AS `req_version_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `latest_rspec_revision`
--

DROP TABLE IF EXISTS `latest_rspec_revision`;
/*!50001 DROP VIEW IF EXISTS `latest_rspec_revision`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latest_rspec_revision` AS SELECT
 1 AS `req_spec_id`,
  1 AS `testproject_id`,
  1 AS `revision` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `latest_tcase_version_id`
--

DROP TABLE IF EXISTS `latest_tcase_version_id`;
/*!50001 DROP VIEW IF EXISTS `latest_tcase_version_id`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latest_tcase_version_id` AS SELECT
 1 AS `testcase_id`,
  1 AS `version`,
  1 AS `tcversion_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `latest_tcase_version_number`
--

DROP TABLE IF EXISTS `latest_tcase_version_number`;
/*!50001 DROP VIEW IF EXISTS `latest_tcase_version_number`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `latest_tcase_version_number` AS SELECT
 1 AS `testcase_id`,
  1 AS `version` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `milestones`
--

DROP TABLE IF EXISTS `milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milestones` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int unsigned NOT NULL DEFAULT '0',
  `target_date` date NOT NULL,
  `start_date` date DEFAULT NULL,
  `a` tinyint unsigned NOT NULL DEFAULT '0',
  `b` tinyint unsigned NOT NULL DEFAULT '0',
  `c` tinyint unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT 'undefined',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_testplan_id` (`name`,`testplan_id`),
  KEY `testplan_id` (`testplan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT 'testproject',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `node_type_id` int unsigned NOT NULL DEFAULT '1',
  `node_order` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid_m_nodeorder` (`parent_id`,`node_order`),
  KEY `nodes_hierarchy_node_type_id` (`node_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `fk_id` int unsigned NOT NULL DEFAULT '0',
  `fk_table` varchar(30) DEFAULT '',
  `keyword_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `udx01_object_keywords` (`fk_id`,`fk_table`,`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `testproject_id` int unsigned NOT NULL,
  `notes` text NOT NULL,
  `enable_on_design` tinyint unsigned NOT NULL DEFAULT '0',
  `enable_on_execution` tinyint unsigned NOT NULL DEFAULT '1',
  `is_open` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_platforms` (`testproject_id`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platforms`
--

LOCK TABLES `platforms` WRITE;
/*!40000 ALTER TABLE `platforms` DISABLE KEYS */;
INSERT INTO `platforms` VALUES (1,'Ferrari',2,'',0,1,1),(2,'Mc Laren',2,'',0,1,1),(3,'Red Bull',2,'',0,1,1),(4,'Mercedes',2,'',0,1,1),(7,'Renault',2,'',0,1,1);
/*!40000 ALTER TABLE `platforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `basename` varchar(100) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins`
--

LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins_configuration`
--

DROP TABLE IF EXISTS `plugins_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins_configuration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `testproject_id` int NOT NULL,
  `config_key` varchar(255) NOT NULL,
  `config_type` int NOT NULL,
  `config_value` varchar(255) NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins_configuration`
--

LOCK TABLES `plugins_configuration` WRITE;
/*!40000 ALTER TABLE `plugins_configuration` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugins_configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_coverage`
--

DROP TABLE IF EXISTS `req_coverage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_coverage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `req_id` int NOT NULL,
  `req_version_id` int NOT NULL,
  `testcase_id` int NOT NULL,
  `tcversion_id` int NOT NULL,
  `link_status` int NOT NULL DEFAULT '1',
  `is_active` int NOT NULL DEFAULT '1',
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `review_requester_id` int unsigned DEFAULT NULL,
  `review_request_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `req_coverage_full_link` (`req_id`,`req_version_id`,`testcase_id`,`tcversion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='relation test case version ** requirement version';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_coverage`
--

LOCK TABLES `req_coverage` WRITE;
/*!40000 ALTER TABLE `req_coverage` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_coverage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_monitor`
--

DROP TABLE IF EXISTS `req_monitor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_monitor` (
  `req_id` int NOT NULL,
  `user_id` int NOT NULL,
  `testproject_id` int NOT NULL,
  PRIMARY KEY (`req_id`,`user_id`,`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_monitor`
--

LOCK TABLES `req_monitor` WRITE;
/*!40000 ALTER TABLE `req_monitor` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_monitor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_relations`
--

DROP TABLE IF EXISTS `req_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_relations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int unsigned NOT NULL,
  `destination_id` int unsigned NOT NULL,
  `relation_type` smallint unsigned NOT NULL DEFAULT '1',
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `parent_id` int unsigned NOT NULL,
  `id` int unsigned NOT NULL,
  `revision` smallint unsigned NOT NULL DEFAULT '1',
  `req_doc_id` varchar(64) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `scope` text,
  `status` char(1) NOT NULL DEFAULT 'V',
  `type` char(1) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `expected_coverage` int NOT NULL DEFAULT '1',
  `log_message` text,
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int unsigned DEFAULT NULL,
  `modification_ts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `req_revisions_uidx1` (`parent_id`,`revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL,
  `testproject_id` int unsigned NOT NULL,
  `doc_id` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `req_spec_uk1` (`doc_id`,`testproject_id`),
  KEY `testproject_id` (`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Dev. Documents (e.g. System Requirements Specification)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_specs`
--

LOCK TABLES `req_specs` WRITE;
/*!40000 ALTER TABLE `req_specs` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_specs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_specs_revisions`
--

DROP TABLE IF EXISTS `req_specs_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_specs_revisions` (
  `parent_id` int unsigned NOT NULL,
  `id` int unsigned NOT NULL,
  `revision` smallint unsigned NOT NULL DEFAULT '1',
  `doc_id` varchar(64) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `scope` text,
  `total_req` int NOT NULL DEFAULT '0',
  `status` int unsigned DEFAULT '1',
  `type` char(1) DEFAULT NULL,
  `log_message` text,
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int unsigned DEFAULT NULL,
  `modification_ts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `req_specs_revisions_uidx1` (`parent_id`,`revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_specs_revisions`
--

LOCK TABLES `req_specs_revisions` WRITE;
/*!40000 ALTER TABLE `req_specs_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_specs_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `req_versions`
--

DROP TABLE IF EXISTS `req_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `req_versions` (
  `id` int unsigned NOT NULL,
  `version` smallint unsigned NOT NULL DEFAULT '1',
  `revision` smallint unsigned NOT NULL DEFAULT '1',
  `scope` text,
  `status` char(1) NOT NULL DEFAULT 'V',
  `type` char(1) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `expected_coverage` int NOT NULL DEFAULT '1',
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int unsigned DEFAULT NULL,
  `modification_ts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `req_versions`
--

LOCK TABLES `req_versions` WRITE;
/*!40000 ALTER TABLE `req_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `req_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reqmgrsystems`
--

DROP TABLE IF EXISTS `reqmgrsystems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reqmgrsystems` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` int DEFAULT '0',
  `cfg` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reqmgrsystems_uidx1` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reqmgrsystems`
--

LOCK TABLES `reqmgrsystems` WRITE;
/*!40000 ALTER TABLE `reqmgrsystems` DISABLE KEYS */;
/*!40000 ALTER TABLE `reqmgrsystems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requirements`
--

DROP TABLE IF EXISTS `requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requirements` (
  `id` int unsigned NOT NULL,
  `srs_id` int unsigned NOT NULL,
  `req_doc_id` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requirements_req_doc_id` (`srs_id`,`req_doc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rights_descr` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int unsigned NOT NULL DEFAULT '0',
  `node_id` int unsigned NOT NULL DEFAULT '0',
  `risk` char(1) NOT NULL DEFAULT '2',
  `importance` char(1) NOT NULL DEFAULT 'M',
  PRIMARY KEY (`id`),
  UNIQUE KEY `risk_assignments_tplan_node_id` (`testplan_id`,`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `role_id` int NOT NULL DEFAULT '0',
  `right_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`role_id`,`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_rights_roles_descr` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL,
  `step_number` int NOT NULL DEFAULT '1',
  `actions` text,
  `expected_results` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `execution_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 -> manual, 2 -> automated',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL,
  `tc_external_id` int unsigned DEFAULT NULL,
  `version` smallint unsigned NOT NULL DEFAULT '1',
  `layout` smallint unsigned NOT NULL DEFAULT '1',
  `status` smallint unsigned NOT NULL DEFAULT '1',
  `summary` text,
  `preconditions` text,
  `importance` smallint unsigned NOT NULL DEFAULT '2',
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updater_id` int unsigned DEFAULT NULL,
  `modification_ts` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `execution_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 -> manual, 2 -> automated',
  `estimated_exec_duration` decimal(6,2) DEFAULT NULL COMMENT 'NULL will be considered as NO DATA Provided by user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tcversions`
--

LOCK TABLES `tcversions` WRITE;
/*!40000 ALTER TABLE `tcversions` DISABLE KEYS */;
INSERT INTO `tcversions` VALUES (32,1,1,1,1,'','',2,1,'2022-05-01 10:08:43',1,'2022-05-01 12:11:47',1,1,1,NULL),(34,2,1,1,1,'','',2,1,'2022-05-01 10:08:56',1,'2022-05-01 12:12:01',1,1,1,NULL),(36,3,1,1,1,'','',2,1,'2022-05-01 10:20:26',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(38,4,1,1,1,'','',2,1,'2022-05-01 10:20:26',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(40,5,1,1,1,'','',2,1,'2022-05-01 10:20:26',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(42,6,1,1,1,'','',2,1,'2022-05-01 10:20:26',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(44,7,1,1,1,'','',2,1,'2022-05-01 10:21:22',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(46,8,1,1,1,'','',2,1,'2022-05-01 10:21:22',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(48,9,1,1,1,'','',2,1,'2022-05-01 10:21:22',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(50,10,1,1,1,'','',2,1,'2022-05-01 10:21:22',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(52,11,1,1,1,'','',2,1,'2022-05-01 10:22:27',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(54,12,1,1,1,'','',2,1,'2022-05-01 10:22:27',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(56,13,1,1,1,'','',2,1,'2022-05-01 10:22:27',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(58,14,1,1,1,'','',2,1,'2022-05-01 10:22:27',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(60,15,1,1,1,'','',2,1,'2022-05-01 10:23:12',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(62,16,1,1,1,'','',2,1,'2022-05-01 10:23:12',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(64,17,1,1,1,'','',2,1,'2022-05-01 10:23:12',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(66,18,1,1,1,'','',2,1,'2022-05-01 10:23:12',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(68,19,1,1,1,'','',2,1,'2022-05-01 10:24:07',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(70,20,1,1,1,'','',2,1,'2022-05-01 10:24:07',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(72,21,1,1,1,'','',2,1,'2022-05-01 10:24:07',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(74,22,1,1,1,'','',2,1,'2022-05-01 10:24:07',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(76,23,1,1,1,'','',2,1,'2022-05-01 10:24:50',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(78,24,1,1,1,'','',2,1,'2022-05-01 10:24:51',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(80,25,1,1,1,'','',2,1,'2022-05-01 10:24:51',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(82,26,1,1,1,'','',2,1,'2022-05-01 10:24:51',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(84,27,1,1,1,'','',2,1,'2022-05-01 10:25:56',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(86,28,1,1,1,'','',2,1,'2022-05-01 10:25:56',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(88,29,1,1,1,'','',2,1,'2022-05-01 10:25:56',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(90,30,1,1,1,'','',2,1,'2022-05-01 10:25:56',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(92,31,1,1,1,'','',2,1,'2022-05-01 10:25:56',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(94,32,1,1,1,'','',2,1,'2022-05-01 10:26:50',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(96,33,1,1,1,'','',2,1,'2022-05-01 10:26:50',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(98,34,1,1,1,'','',2,1,'2022-05-01 10:26:51',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(100,35,1,1,1,'','',2,1,'2022-05-01 10:26:51',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(102,36,1,1,1,'','',2,1,'2022-05-01 10:26:51',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(104,37,1,1,1,'','',2,1,'2022-05-01 10:27:44',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(106,38,1,1,1,'','',2,1,'2022-05-01 10:27:44',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(108,39,1,1,1,'','',2,1,'2022-05-01 10:27:44',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(110,40,1,1,1,'','',2,1,'2022-05-01 10:27:44',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(112,41,1,1,1,'','',2,1,'2022-05-01 10:27:44',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(114,42,1,1,1,'','',2,1,'2022-05-01 10:28:26',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(116,43,1,1,1,'','',2,1,'2022-05-01 10:28:26',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(118,44,1,1,1,'','',2,1,'2022-05-01 10:28:27',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(120,45,1,1,1,'','',2,1,'2022-05-01 10:28:27',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(122,46,1,1,1,'','',2,1,'2022-05-01 10:28:27',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(124,47,1,1,1,'','',2,1,'2022-05-01 10:29:04',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(126,48,1,1,1,'','',2,1,'2022-05-01 10:29:04',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(128,49,1,1,1,'','',2,1,'2022-05-01 10:29:04',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(130,50,1,1,1,'','',2,1,'2022-05-01 10:29:04',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(132,51,1,1,1,'','',2,1,'2022-05-01 10:29:04',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(134,52,1,1,1,'','',2,1,'2022-05-01 10:29:45',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(136,53,1,1,1,'','',2,1,'2022-05-01 10:29:45',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(138,54,1,1,1,'','',2,1,'2022-05-01 10:29:45',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(140,55,1,1,1,'','',2,1,'2022-05-01 10:29:45',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(142,56,1,1,1,'','',2,1,'2022-05-01 10:29:45',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(144,57,1,1,1,'','',2,1,'2022-05-01 10:30:20',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(146,58,1,1,1,'','',2,1,'2022-05-01 10:30:20',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(148,59,1,1,1,'','',2,1,'2022-05-01 10:30:20',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(150,60,1,1,1,'','',2,1,'2022-05-01 10:30:20',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(152,61,1,1,1,'','',2,1,'2022-05-01 10:30:20',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(154,62,1,1,1,'','',2,1,'2022-05-01 10:30:46',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(156,63,1,1,1,'','',2,1,'2022-05-01 10:30:46',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(158,64,1,1,1,'','',2,1,'2022-05-01 10:30:46',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(160,65,1,1,1,'','',2,1,'2022-05-01 10:30:46',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(162,66,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(164,67,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(166,69,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(168,71,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(170,73,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(172,75,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(174,77,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(176,79,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(178,81,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(180,83,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(182,85,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(184,87,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(186,89,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(188,91,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(190,93,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(192,95,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(194,97,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(196,99,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(198,101,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(200,103,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(202,105,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(204,107,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(206,109,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(208,111,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(210,113,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(212,115,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(214,117,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(216,119,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(218,121,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(220,123,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(222,125,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(224,127,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(226,129,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(228,131,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(230,133,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(232,135,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(234,137,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(236,139,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(238,141,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(240,143,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(242,145,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(244,147,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(246,149,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(248,151,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(250,153,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(252,155,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(254,157,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(256,159,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL),(258,161,1,1,1,'','',2,1,'2022-05-01 10:30:47',NULL,'2022-05-01 12:13:31',1,1,1,NULL);
/*!40000 ALTER TABLE `tcversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `tcversions_without_keywords`
--

DROP TABLE IF EXISTS `tcversions_without_keywords`;
/*!50001 DROP VIEW IF EXISTS `tcversions_without_keywords`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tcversions_without_keywords` AS SELECT
 1 AS `testcase_id`,
  1 AS `id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tcversions_without_platforms`
--

DROP TABLE IF EXISTS `tcversions_without_platforms`;
/*!50001 DROP VIEW IF EXISTS `tcversions_without_platforms`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tcversions_without_platforms` AS SELECT
 1 AS `testcase_id`,
  1 AS `id` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `testcase_keywords`
--

DROP TABLE IF EXISTS `testcase_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testcase_keywords` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `testcase_id` int unsigned NOT NULL DEFAULT '0',
  `tcversion_id` int unsigned NOT NULL DEFAULT '0',
  `keyword_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx01_testcase_keywords` (`testcase_id`,`tcversion_id`,`keyword_id`),
  KEY `idx02_testcase_keywords` (`tcversion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testcase_keywords`
--

LOCK TABLES `testcase_keywords` WRITE;
/*!40000 ALTER TABLE `testcase_keywords` DISABLE KEYS */;
INSERT INTO `testcase_keywords` VALUES (1,31,92,1),(2,31,92,6),(3,33,96,2),(4,33,96,6),(5,35,100,1),(6,35,100,6),(7,37,104,2),(8,37,104,6),(9,39,108,1),(10,39,108,6),(11,41,112,2),(12,41,112,6),(13,43,116,1),(14,43,116,6),(15,45,120,2),(16,45,120,6),(17,47,124,1),(18,47,124,6),(19,49,128,2),(20,49,128,6),(21,51,132,1),(22,51,132,6),(23,53,136,2),(24,53,136,6),(25,55,140,1),(26,55,140,6),(27,57,144,2),(28,57,144,6),(29,59,148,1),(30,59,148,6),(31,61,152,2),(32,61,152,6),(33,63,156,1),(34,63,156,6),(35,65,160,2),(36,65,160,6),(37,67,164,1),(38,67,164,6),(39,69,166,2),(40,69,166,6),(41,71,168,1),(42,71,168,6),(43,73,170,2),(44,73,170,6),(45,75,172,1),(46,75,172,6),(47,77,174,2),(48,77,174,6),(49,79,176,1),(50,79,176,6),(51,81,178,2),(52,81,178,6),(53,83,180,1),(54,83,180,6),(55,85,182,2),(56,85,182,6),(57,87,184,1),(58,87,184,6),(59,89,186,2),(60,89,186,6),(61,91,188,2),(62,91,188,6),(63,93,190,1),(64,93,190,6),(65,95,192,2),(66,95,192,6),(67,97,194,1),(68,97,194,6),(69,99,196,2),(70,99,196,6),(71,101,198,2),(72,101,198,6),(73,103,200,1),(74,103,200,6),(75,105,202,2),(76,105,202,6),(77,107,204,1),(78,107,204,6),(79,109,206,2),(80,109,206,6),(81,111,208,2),(82,111,208,6),(83,113,210,1),(84,113,210,6),(85,115,212,2),(86,115,212,6),(87,117,214,1),(88,117,214,6),(89,119,216,2),(90,119,216,6),(91,121,218,2),(92,121,218,6),(93,123,220,1),(94,123,220,6),(95,125,222,2),(96,125,222,6),(97,127,224,1),(98,127,224,6),(99,129,226,2),(100,129,226,6),(101,131,228,2),(102,131,228,6),(103,133,230,1),(104,133,230,6),(105,135,232,2),(106,135,232,6),(107,137,234,1),(108,137,234,6),(109,139,236,2),(110,139,236,6),(111,141,238,2),(112,141,238,6),(113,143,240,1),(114,143,240,6),(115,145,242,2),(116,145,242,6),(117,147,244,1),(118,147,244,6),(119,149,246,2),(120,149,246,6),(121,151,248,2),(122,151,248,6),(123,153,250,1),(124,153,250,6),(125,155,252,2),(126,155,252,6),(127,157,254,1),(128,157,254,6),(129,159,256,2),(130,159,256,6),(131,161,258,2),(132,161,258,6);
/*!40000 ALTER TABLE `testcase_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testcase_platforms`
--

DROP TABLE IF EXISTS `testcase_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testcase_platforms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `testcase_id` int unsigned NOT NULL DEFAULT '0',
  `tcversion_id` int unsigned NOT NULL DEFAULT '0',
  `platform_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx01_testcase_platform` (`testcase_id`,`tcversion_id`,`platform_id`),
  KEY `idx02_testcase_platform` (`tcversion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testcase_platforms`
--

LOCK TABLES `testcase_platforms` WRITE;
/*!40000 ALTER TABLE `testcase_platforms` DISABLE KEYS */;
/*!40000 ALTER TABLE `testcase_platforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testcase_relations`
--

DROP TABLE IF EXISTS `testcase_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testcase_relations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int unsigned NOT NULL,
  `destination_id` int unsigned NOT NULL,
  `link_status` tinyint(1) NOT NULL DEFAULT '1',
  `relation_type` smallint unsigned NOT NULL DEFAULT '1',
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testcase_relations`
--

LOCK TABLES `testcase_relations` WRITE;
/*!40000 ALTER TABLE `testcase_relations` DISABLE KEYS */;
/*!40000 ALTER TABLE `testcase_relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testcase_script_links`
--

DROP TABLE IF EXISTS `testcase_script_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testcase_script_links` (
  `tcversion_id` int unsigned NOT NULL DEFAULT '0',
  `project_key` varchar(64) NOT NULL,
  `repository_name` varchar(64) NOT NULL,
  `code_path` varchar(255) NOT NULL,
  `branch_name` varchar(64) DEFAULT NULL,
  `commit_id` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`tcversion_id`,`project_key`,`repository_name`,`code_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testcase_script_links`
--

LOCK TABLES `testcase_script_links` WRITE;
/*!40000 ALTER TABLE `testcase_script_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `testcase_script_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testplan_platforms`
--

DROP TABLE IF EXISTS `testplan_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testplan_platforms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int unsigned NOT NULL,
  `platform_id` int unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_testplan_platforms` (`testplan_id`,`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COMMENT='Connects a testplan with platforms';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testplan_platforms`
--

LOCK TABLES `testplan_platforms` WRITE;
/*!40000 ALTER TABLE `testplan_platforms` DISABLE KEYS */;
INSERT INTO `testplan_platforms` VALUES (1,4,1,1),(2,4,2,1),(3,4,4,1),(4,4,3,1);
/*!40000 ALTER TABLE `testplan_platforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testplan_tcversions`
--

DROP TABLE IF EXISTS `testplan_tcversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testplan_tcversions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `testplan_id` int unsigned NOT NULL DEFAULT '0',
  `tcversion_id` int unsigned NOT NULL DEFAULT '0',
  `node_order` int unsigned NOT NULL DEFAULT '1',
  `urgency` smallint NOT NULL DEFAULT '2',
  `platform_id` int unsigned NOT NULL DEFAULT '0',
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `testplan_tcversions_tplan_tcversion` (`testplan_id`,`tcversion_id`,`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testplan_tcversions`
--

LOCK TABLES `testplan_tcversions` WRITE;
/*!40000 ALTER TABLE `testplan_tcversions` DISABLE KEYS */;
INSERT INTO `testplan_tcversions` VALUES (1,4,32,1000,2,1,1,'2022-05-01 10:33:49'),(2,4,32,1000,2,2,1,'2022-05-01 10:33:49'),(3,4,32,1000,2,4,1,'2022-05-01 10:33:49'),(4,4,32,1000,2,3,1,'2022-05-01 10:33:49'),(5,4,34,1010,2,1,1,'2022-05-01 10:33:49'),(6,4,34,1010,2,2,1,'2022-05-01 10:33:49'),(7,4,34,1010,2,4,1,'2022-05-01 10:33:49'),(8,4,34,1010,2,3,1,'2022-05-01 10:33:49'),(9,4,44,1000,2,1,1,'2022-05-01 10:34:05'),(10,4,44,1000,2,2,1,'2022-05-01 10:34:05'),(11,4,44,1000,2,4,1,'2022-05-01 10:34:05'),(12,4,48,1000,2,1,1,'2022-05-01 10:34:05'),(13,4,48,1000,2,2,1,'2022-05-01 10:34:05'),(14,4,48,1000,2,4,1,'2022-05-01 10:34:05'),(15,4,46,1010,2,1,1,'2022-05-01 10:34:05'),(16,4,46,1010,2,2,1,'2022-05-01 10:34:05'),(17,4,46,1010,2,4,1,'2022-05-01 10:34:05'),(18,4,50,1010,2,1,1,'2022-05-01 10:34:05'),(19,4,50,1010,2,2,1,'2022-05-01 10:34:05'),(20,4,50,1010,2,4,1,'2022-05-01 10:34:05'),(21,4,94,1000,2,4,1,'2022-05-01 10:34:41'),(22,4,94,1000,2,3,1,'2022-05-01 10:34:41'),(23,4,98,1000,2,4,1,'2022-05-01 10:34:41'),(24,4,98,1000,2,3,1,'2022-05-01 10:34:41'),(25,4,96,1010,2,4,1,'2022-05-01 10:34:41'),(26,4,96,1010,2,3,1,'2022-05-01 10:34:41'),(27,4,100,1010,2,4,1,'2022-05-01 10:34:41'),(28,4,100,1010,2,3,1,'2022-05-01 10:34:41'),(29,4,102,1010,2,4,1,'2022-05-01 10:34:41'),(30,4,102,1010,2,3,1,'2022-05-01 10:34:41'),(31,4,104,1000,2,4,1,'2022-05-01 10:34:41'),(32,4,104,1000,2,3,1,'2022-05-01 10:34:41'),(33,4,108,1000,2,4,1,'2022-05-01 10:34:41'),(34,4,108,1000,2,3,1,'2022-05-01 10:34:41'),(35,4,106,1010,2,4,1,'2022-05-01 10:34:41'),(36,4,106,1010,2,3,1,'2022-05-01 10:34:41'),(37,4,110,1010,2,4,1,'2022-05-01 10:34:41'),(38,4,110,1010,2,3,1,'2022-05-01 10:34:41'),(39,4,112,1010,2,4,1,'2022-05-01 10:34:41'),(40,4,112,1010,2,3,1,'2022-05-01 10:34:41'),(41,4,144,1000,2,1,1,'2022-05-01 10:34:56'),(42,4,144,1000,2,2,1,'2022-05-01 10:34:56'),(43,4,144,1000,2,4,1,'2022-05-01 10:34:56'),(44,4,144,1000,2,3,1,'2022-05-01 10:34:56'),(45,4,148,1000,2,1,1,'2022-05-01 10:34:56'),(46,4,148,1000,2,2,1,'2022-05-01 10:34:56'),(47,4,148,1000,2,4,1,'2022-05-01 10:34:56'),(48,4,148,1000,2,3,1,'2022-05-01 10:34:56'),(49,4,146,1010,2,1,1,'2022-05-01 10:34:56'),(50,4,146,1010,2,2,1,'2022-05-01 10:34:56'),(51,4,146,1010,2,4,1,'2022-05-01 10:34:56'),(52,4,146,1010,2,3,1,'2022-05-01 10:34:56'),(53,4,150,1010,2,1,1,'2022-05-01 10:34:56'),(54,4,150,1010,2,2,1,'2022-05-01 10:34:56'),(55,4,150,1010,2,4,1,'2022-05-01 10:34:56'),(56,4,150,1010,2,3,1,'2022-05-01 10:34:56'),(57,4,152,1010,2,1,1,'2022-05-01 10:34:56'),(58,4,152,1010,2,2,1,'2022-05-01 10:34:56'),(59,4,152,1010,2,4,1,'2022-05-01 10:34:56'),(60,4,152,1010,2,3,1,'2022-05-01 10:34:56');
/*!40000 ALTER TABLE `testplan_tcversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testplans`
--

DROP TABLE IF EXISTS `testplans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testplans` (
  `id` int unsigned NOT NULL,
  `testproject_id` int unsigned NOT NULL DEFAULT '0',
  `notes` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `api_key` varchar(64) NOT NULL DEFAULT '829a2ded3ed0829a2dedd8ab81dfa2c77e8235bc3ed0d8ab81dfa2c77e8235bc',
  PRIMARY KEY (`id`),
  UNIQUE KEY `testplans_api_key` (`api_key`),
  KEY `testplans_testproject_id_active` (`testproject_id`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testplans`
--

LOCK TABLES `testplans` WRITE;
/*!40000 ALTER TABLE `testplans` DISABLE KEYS */;
INSERT INTO `testplans` VALUES (4,2,'<p><strong>P Zero&trade; Red</strong>, a supersoft for street circuits. Of the  four slick tyres, this is the only one to remain unchanged from the 2011  season. It showed itself to be particularly versatile, offering high  peaks of performance over slow and twisty circuits that are  characterised by slippery asphalt and low lateral loadings. This is the  ideal compound for street circuits or semipermanent facilities.</p>',1,1,1,'829a2ded3ed0829a2dedd8ab81dfa2c77e8235bc3ed0d8ab81dfa2c77e8235ba'),(5,2,'<p><strong>P Zero&trade; Yellow</strong>, softer with less blistering. The new  soft tyre is well suited to circuits with low tyre wear. It is designed  to offer a high level of grip coupled with a significant amount of  degradation, resulting in a comparatively short lifespan that will give  the teams a greater number of options with pit stop strategy and even  closer racing. Compared to the equivalent tyre in 2011, the new soft  offers greater thermal resistance to reduce the risk of blistering.  Tested for the first time during free practice at last year&rsquo;s Abu Dhabi  Grand Prix, the new soft tyre is set to be one of the most frequent  nominations in 2012, together with the new medium tyre. This combination  offers a great deal of flexibility and also a rapid warm-up time.</p>',1,1,1,'829a2ded3ed0829a2dedd8ab81dfa2c77e8235bc3ed0d8ab81dfa2c77e8235bb'),(6,2,'<p><strong>P Zero&trade; White</strong>, the medium tyre that is well suited to  all conditions. This extremely versatile tyre adapts itself well to all  sorts of track conditions, particularly when asphalt and circuit  characteristics are variable. The brand new P Zero&trade; White is intended as  the &lsquo;option&rsquo; tyre on tracks with high temperatures or abrasive surfaces  and as the &lsquo;prime&rsquo; tyre on tracks that are less severe with fewer  demands on the tyres. The new medium compound was tried out last year  during free practice at the German Grand Prix and made another  appearance during the young driver test in Abu Dhabi.</p>',1,1,1,'829a2ded3ed0829a2dedd8ab81dfa2c77e8235bc3ed0d8ab81dfa2c77e8235bc'),(7,2,'<p><strong>P Zero&trade; Silver</strong>, hard but not inflexible. The new hard  tyre guarantees maximum durability and the least degradation, together  with optimal resistance to the most extreme conditions, but is not as  hard as the equivalent tyre last year. The P Zero&trade; Silver is ideal for  long runs, taking more time to warm up, as well as being suited to  circuits with abrasive asphalt, big lateral forces and high  temperatures. The new P Zero&trade; Silver was tested at the Barcelona circuit  by Pirelli&rsquo;s test driver Lucas di Grassi, and is the only one of the  new compounds that the regular drivers have not yet experienced.</p>',1,1,1,'829a2ded3ed0829a2dedd8ab81dfa2c77e8235bc3ed0d8ab81dfa2c77e8235bd'),(8,3,'<p><strong>Cinturato&trade; Green</strong>, the intermediate for light rain.  After the excellent performances seen from this tyre throughout the 2011  season during particularly demanding races such as the Canadian Grand  Prix, Pirelli&rsquo;s engineers decided not to make any changes to the  intermediate tyres. The shallower grooves compared to the full wet tyres  mean that the intermediates do not drain away as much water, making  this the ideal choice for wet or drying asphalt, without compromising on  performance.</p>',1,1,1,'829a2ded3ed0829a2dedd8ab81dfa2c77e8235bc3ed0d8ab81dfa2c77e8235be'),(9,3,'<p><strong>Cinturato&trade; Blue</strong>, the full wets. Of the two wet tyres,  only the full wet has been significantly altered compared to the 2011  version. The changes relate to the rear tyres, which use a different  profile in order to optimise the dispersal of water in case of  aquaplaning and guarantee a greater degree of driving precision.  Characterised by deep grooves, similar to those seen on a road car tyre,  the wet tyres are designed to expel more than 60 litres of water per  second at a speed of 300 kph: six times more than a road car tyre, which  disperses about 10 litres per second at a much lower speed.</p>',1,1,1,'829a2ded3ed0829a2dedd8ab81dfa2c77e8235bc3ed0d8ab81dfa2c77e8235bf');
/*!40000 ALTER TABLE `testplans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testproject_codetracker`
--

DROP TABLE IF EXISTS `testproject_codetracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testproject_codetracker` (
  `testproject_id` int unsigned NOT NULL,
  `codetracker_id` int unsigned NOT NULL,
  PRIMARY KEY (`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testproject_codetracker`
--

LOCK TABLES `testproject_codetracker` WRITE;
/*!40000 ALTER TABLE `testproject_codetracker` DISABLE KEYS */;
/*!40000 ALTER TABLE `testproject_codetracker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testproject_issuetracker`
--

DROP TABLE IF EXISTS `testproject_issuetracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testproject_issuetracker` (
  `testproject_id` int unsigned NOT NULL,
  `issuetracker_id` int unsigned NOT NULL,
  PRIMARY KEY (`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testproject_issuetracker`
--

LOCK TABLES `testproject_issuetracker` WRITE;
/*!40000 ALTER TABLE `testproject_issuetracker` DISABLE KEYS */;
/*!40000 ALTER TABLE `testproject_issuetracker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testproject_reqmgrsystem`
--

DROP TABLE IF EXISTS `testproject_reqmgrsystem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testproject_reqmgrsystem` (
  `testproject_id` int unsigned NOT NULL,
  `reqmgrsystem_id` int unsigned NOT NULL,
  PRIMARY KEY (`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testproject_reqmgrsystem`
--

LOCK TABLES `testproject_reqmgrsystem` WRITE;
/*!40000 ALTER TABLE `testproject_reqmgrsystem` DISABLE KEYS */;
/*!40000 ALTER TABLE `testproject_reqmgrsystem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testprojects`
--

DROP TABLE IF EXISTS `testprojects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testprojects` (
  `id` int unsigned NOT NULL,
  `notes` text,
  `color` varchar(12) NOT NULL DEFAULT '#9BD',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `option_reqs` tinyint(1) NOT NULL DEFAULT '0',
  `option_priority` tinyint(1) NOT NULL DEFAULT '0',
  `option_automation` tinyint(1) NOT NULL DEFAULT '0',
  `options` text,
  `prefix` varchar(16) NOT NULL,
  `tc_counter` int unsigned NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `issue_tracker_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `code_tracker_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `reqmgr_integration_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `api_key` varchar(64) NOT NULL DEFAULT '0d8ab81dfa2c77e8235bc829a2ded3edfa2c78235bc829a27eded3ed0d8ab81d',
  PRIMARY KEY (`id`),
  UNIQUE KEY `testprojects_prefix` (`prefix`),
  UNIQUE KEY `testprojects_api_key` (`api_key`),
  KEY `testprojects_id_active` (`id`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testprojects`
--

LOCK TABLES `testprojects` WRITE;
/*!40000 ALTER TABLE `testprojects` DISABLE KEYS */;
INSERT INTO `testprojects` VALUES (2,'<p>In accordance with the regulations laid down by the FIA (F&eacute;d&eacute;ration  Internationale de l\'Automobile) Pirelli will supply two different types of tyre designed for two different types of use.<br />\r\nThe first type of tyre has been designed for dry surfaces, while the second is for wet surfaces.</p>','',1,0,0,0,'O:8:\"stdClass\":4:{s:19:\"requirementsEnabled\";i:1;s:19:\"testPriorityEnabled\";i:1;s:17:\"automationEnabled\";i:1;s:16:\"inventoryEnabled\";i:1;}','PDT',66,1,0,0,0,'0d8ab81dfa2c77e8235bc829a2ded3edfa2c78235bc829a27eded3ed0d8ab81d'),(3,'<p>in accordance with the regulations laid down by the FIA (F&eacute;d&eacute;ration  Internationale de l\'Automobile) Pirelli will supply two different types of tyre designed for two different types of use.<br />\r\nThe first type of tyre has been designed for dry surfaces, while the second is for wet surfaces.</p>','',1,0,0,0,'O:8:\"stdClass\":4:{s:19:\"requirementsEnabled\";i:1;s:19:\"testPriorityEnabled\";i:1;s:17:\"automationEnabled\";i:1;s:16:\"inventoryEnabled\";i:1;}','PWT',0,1,0,0,0,'0d8ab81dfa2c77e8235bc829a2ded3edfa2c78235bc829a27eded3ed0d8ab81e');
/*!40000 ALTER TABLE `testprojects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testsuites`
--

DROP TABLE IF EXISTS `testsuites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testsuites` (
  `id` int unsigned NOT NULL,
  `details` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
-- Table structure for table `text_templates`
--

DROP TABLE IF EXISTS `text_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `text_templates` (
  `id` int unsigned NOT NULL,
  `type` smallint unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `template_data` text,
  `author_id` int unsigned DEFAULT NULL,
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_text_templates` (`type`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Global Project Templates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `text_templates`
--

LOCK TABLES `text_templates` WRITE;
/*!40000 ALTER TABLE `text_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `text_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entry_point` varchar(45) NOT NULL DEFAULT '',
  `start_time` int unsigned NOT NULL DEFAULT '0',
  `end_time` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `session_id` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,'/tl193-untouched/lib/general/navBar.php',1335862631,1335862631,210,'95p2svhjqusr1vjbhgj9bird23'),(2,'/tl193-untouched/lib/general/mainPage.php',1335862631,1335862631,210,'95p2svhjqusr1vjbhgj9bird23'),(3,'/tl193-untouched/lib/general/mainPage.php',1335862696,1335862696,210,'95p2svhjqusr1vjbhgj9bird23'),(4,'/tl193-untouched/lib/general/navBar.php',1335862696,1335862696,210,'95p2svhjqusr1vjbhgj9bird23'),(5,'/development/tl-old/tl193-untouched/login.php',1335862713,1335862713,1,'190eh67vq7bsrde26gde2g46c3'),(6,'/tl193-untouched/lib/general/mainPage.php',1335862714,1335862714,1,'190eh67vq7bsrde26gde2g46c3'),(7,'/tl193-untouched/lib/project/projectEdit.php',1335862746,1335862746,1,'190eh67vq7bsrde26gde2g46c3'),(8,'/tl193-untouched/lib/project/projectEdit.php',1335864014,1335864014,1,'190eh67vq7bsrde26gde2g46c3'),(9,'/tl193-untouched/lib/project/projectEdit.php',1335864053,1335864053,1,'190eh67vq7bsrde26gde2g46c3'),(10,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864120,1335864120,1,'190eh67vq7bsrde26gde2g46c3'),(11,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864160,1335864160,1,'190eh67vq7bsrde26gde2g46c3'),(12,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864193,1335864193,1,'190eh67vq7bsrde26gde2g46c3'),(13,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864246,1335864246,1,'190eh67vq7bsrde26gde2g46c3'),(14,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864293,1335864294,1,'190eh67vq7bsrde26gde2g46c3'),(15,'/tl-old/tl193-untouched/lib/plan/planEdit.php',1335864326,1335864326,1,'190eh67vq7bsrde26gde2g46c3'),(16,'/tl193-untouched/lib/project/projectEdit.php',1335864347,1335864347,1,'190eh67vq7bsrde26gde2g46c3'),(17,'/tl193-untouched/lib/plan/buildEdit.php',1335864400,1335864400,1,'190eh67vq7bsrde26gde2g46c3'),(18,'/tl193-untouched/lib/plan/buildEdit.php',1335864414,1335864414,1,'190eh67vq7bsrde26gde2g46c3'),(19,'/tl193-untouched/lib/plan/buildEdit.php',1335864428,1335864429,1,'190eh67vq7bsrde26gde2g46c3'),(20,'/tl193-untouched/lib/plan/buildEdit.php',1335864459,1335864459,1,'190eh67vq7bsrde26gde2g46c3'),(21,'/tl193-untouched/lib/plan/buildEdit.php',1335864466,1335864466,1,'190eh67vq7bsrde26gde2g46c3'),(22,'/tl193-untouched/lib/plan/buildEdit.php',1335864473,1335864473,1,'190eh67vq7bsrde26gde2g46c3'),(23,'/lib/keywords/keywordsEdit.php',1335866991,1335866991,1,'190eh67vq7bsrde26gde2g46c3'),(24,'/lib/keywords/keywordsEdit.php',1335867005,1335867005,1,'190eh67vq7bsrde26gde2g46c3'),(25,'/lib/keywords/keywordsEdit.php',1335867022,1335867022,1,'190eh67vq7bsrde26gde2g46c3'),(26,'/lib/keywords/keywordsEdit.php',1335867034,1335867034,1,'190eh67vq7bsrde26gde2g46c3'),(27,'/lib/keywords/keywordsEdit.php',1335867048,1335867048,1,'190eh67vq7bsrde26gde2g46c3'),(28,'/lib/keywords/keywordsEdit.php',1335867057,1335867057,1,'190eh67vq7bsrde26gde2g46c3'),(29,'/tl193-untouched/lib/testcases/tcEdit.php',1335867108,1335867108,1,'190eh67vq7bsrde26gde2g46c3'),(30,'/tl193-untouched/lib/testcases/tcEdit.php',1335867121,1335867121,1,'190eh67vq7bsrde26gde2g46c3'),(31,'/tl193-untouched/lib/testcases/tcImport.php',1335867626,1335867626,1,'190eh67vq7bsrde26gde2g46c3'),(32,'/tl193-untouched/lib/testcases/tcImport.php',1335867682,1335867682,1,'190eh67vq7bsrde26gde2g46c3'),(33,'/tl193-untouched/lib/testcases/tcImport.php',1335867747,1335867747,1,'190eh67vq7bsrde26gde2g46c3'),(34,'/tl193-untouched/lib/testcases/tcImport.php',1335867792,1335867792,1,'190eh67vq7bsrde26gde2g46c3'),(35,'/tl193-untouched/lib/testcases/tcImport.php',1335867847,1335867847,1,'190eh67vq7bsrde26gde2g46c3'),(36,'/tl193-untouched/lib/testcases/tcImport.php',1335867890,1335867891,1,'190eh67vq7bsrde26gde2g46c3'),(37,'/tl193-untouched/lib/testcases/tcImport.php',1335867956,1335867956,1,'190eh67vq7bsrde26gde2g46c3'),(38,'/tl193-untouched/lib/testcases/tcImport.php',1335868010,1335868011,1,'190eh67vq7bsrde26gde2g46c3'),(39,'/tl193-untouched/lib/testcases/tcImport.php',1335868064,1335868064,1,'190eh67vq7bsrde26gde2g46c3'),(40,'/tl193-untouched/lib/testcases/tcImport.php',1335868106,1335868107,1,'190eh67vq7bsrde26gde2g46c3'),(41,'/tl193-untouched/lib/testcases/tcImport.php',1335868144,1335868144,1,'190eh67vq7bsrde26gde2g46c3'),(42,'/tl193-untouched/lib/testcases/tcImport.php',1335868185,1335868185,1,'190eh67vq7bsrde26gde2g46c3'),(43,'/tl193-untouched/lib/testcases/tcImport.php',1335868220,1335868220,1,'190eh67vq7bsrde26gde2g46c3'),(44,'/tl193-untouched/lib/testcases/tcImport.php',1335868246,1335868247,1,'190eh67vq7bsrde26gde2g46c3'),(45,'/lib/plan/planAddTCNavigator.php',1335868398,1335868399,1,'190eh67vq7bsrde26gde2g46c3'),(46,'/tl193-untouched/lib/plan/planAddTC.php',1335868429,1335868429,1,'190eh67vq7bsrde26gde2g46c3'),(47,'/tl193-untouched/lib/plan/planAddTC.php',1335868446,1335868446,1,'190eh67vq7bsrde26gde2g46c3'),(48,'/tl193-untouched/lib/plan/planAddTC.php',1335868481,1335868481,1,'190eh67vq7bsrde26gde2g46c3'),(49,'/tl193-untouched/lib/plan/planAddTC.php',1335868496,1335868496,1,'190eh67vq7bsrde26gde2g46c3'),(50,'/login.php',1707059206,1707059206,0,NULL),(51,'/login.php',1707059211,1707059211,1,'4u1jb7quupeh336m1aj69j5qu5'),(52,'/lib/project/projectView.php',1707059214,1707059214,1,'4u1jb7quupeh336m1aj69j5qu5'),(53,'/lib/usermanagement/usersAssign.php',1707059224,1707059224,1,'4u1jb7quupeh336m1aj69j5qu5'),(54,'/lib/cfields/cfieldsTprojectAssign.php',1707059232,1707059232,1,'4u1jb7quupeh336m1aj69j5qu5'),(55,'/lib/results/printDocument.php',1707059578,1707059578,1,'4u1jb7quupeh336m1aj69j5qu5'),(56,'/lib/plan/buildView.php',1707059648,1707059648,1,'4u1jb7quupeh336m1aj69j5qu5'),(57,'/lib/execute/execDashboard.php',1707059672,1707059672,1,'4u1jb7quupeh336m1aj69j5qu5'),(58,'/lib/execute/execDashboard.php',1707059672,1707059672,1,'4u1jb7quupeh336m1aj69j5qu5'),(59,'/lib/execute/execSetResults.php',1707059678,1707059678,1,'4u1jb7quupeh336m1aj69j5qu5'),(60,'/lib/execute/execSetResults.php',1707059678,1707059678,1,'4u1jb7quupeh336m1aj69j5qu5'),(61,'/lib/execute/execSetResults.php',1707059679,1707059679,1,'4u1jb7quupeh336m1aj69j5qu5'),(62,'/lib/plan/planAddTC.php',1707059689,1707059689,1,'4u1jb7quupeh336m1aj69j5qu5'),(63,'/lib/plan/planAddTC.php',1707059692,1707059692,1,'4u1jb7quupeh336m1aj69j5qu5'),(64,'/logout.php',1707059711,1707059711,1,'4u1jb7quupeh336m1aj69j5qu5');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `tsuites_tree_depth_2`
--

DROP TABLE IF EXISTS `tsuites_tree_depth_2`;
/*!50001 DROP VIEW IF EXISTS `tsuites_tree_depth_2`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tsuites_tree_depth_2` AS SELECT
 1 AS `prefix`,
  1 AS `testproject_name`,
  1 AS `level1_name`,
  1 AS `level2_name`,
  1 AS `testproject_id`,
  1 AS `level1_id`,
  1 AS `level2_id` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_assignments`
--

DROP TABLE IF EXISTS `user_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_assignments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` int unsigned NOT NULL DEFAULT '1',
  `feature_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned DEFAULT '0',
  `build_id` int unsigned DEFAULT '0',
  `deadline_ts` datetime DEFAULT NULL,
  `assigner_id` int unsigned DEFAULT '0',
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_assignments_feature_id` (`feature_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_assignments`
--

LOCK TABLES `user_assignments` WRITE;
/*!40000 ALTER TABLE `user_assignments` DISABLE KEYS */;
INSERT INTO `user_assignments` VALUES (1,1,1,4,1,NULL,1,'2022-05-01 10:36:33',1),(2,1,2,3,1,NULL,1,'2022-05-01 10:36:33',1),(3,1,4,2,1,NULL,1,'2022-05-01 10:36:33',1),(4,1,3,5,1,NULL,1,'2022-05-01 10:36:33',1),(5,1,5,4,1,NULL,1,'2022-05-01 10:36:33',1),(6,1,6,3,1,NULL,1,'2022-05-01 10:36:33',1),(7,1,8,2,1,NULL,1,'2022-05-01 10:36:33',1),(8,1,7,5,1,NULL,1,'2022-05-01 10:36:33',1),(9,1,9,4,1,NULL,1,'2022-05-01 10:36:58',1),(10,1,10,3,1,NULL,1,'2022-05-01 10:36:58',1),(11,1,11,5,1,NULL,1,'2022-05-01 10:36:58',1),(12,1,12,4,1,NULL,1,'2022-05-01 10:36:58',1),(13,1,13,3,1,NULL,1,'2022-05-01 10:36:58',1),(14,1,14,5,1,NULL,1,'2022-05-01 10:36:58',1),(15,1,22,2,1,NULL,1,'2022-05-01 10:37:46',1),(16,1,24,2,1,NULL,1,'2022-05-01 10:37:46',1),(17,1,23,5,1,NULL,1,'2022-05-01 10:37:46',1),(18,1,26,2,1,NULL,1,'2022-05-01 10:37:46',1),(19,1,25,5,1,NULL,1,'2022-05-01 10:37:46',1),(20,1,28,2,1,NULL,1,'2022-05-01 10:37:46',1),(21,1,27,5,1,NULL,1,'2022-05-01 10:37:46',1),(22,1,30,2,1,NULL,1,'2022-05-01 10:37:46',1);
/*!40000 ALTER TABLE `user_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_group` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `usergroup_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  UNIQUE KEY `idx_user_group_assign` (`usergroup_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `user_id` int NOT NULL DEFAULT '0',
  `testplan_id` int NOT NULL DEFAULT '0',
  `role_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`testplan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `user_id` int NOT NULL DEFAULT '0',
  `testproject_id` int NOT NULL DEFAULT '0',
  `role_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`testproject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
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
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `role_id` int unsigned NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '',
  `first` varchar(50) NOT NULL DEFAULT '',
  `last` varchar(50) NOT NULL DEFAULT '',
  `locale` varchar(10) NOT NULL DEFAULT 'en_GB',
  `default_testproject_id` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `script_key` varchar(32) DEFAULT NULL,
  `cookie_string` varchar(64) NOT NULL DEFAULT '',
  `auth_method` varchar(10) DEFAULT '',
  `creation_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiration_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_login` (`login`),
  UNIQUE KEY `users_cookie_string` (`cookie_string`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COMMENT='User information';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$R2V1vQ8341Pamp7xz5XwPuMGNWlfkukqeanCHHFZE19rHbZDDGeD6',8,'','Testlink','Administrator','en_GB',NULL,1,NULL,'36ddb442ea04877d8ff949e6120843c405cf032131f34b2d54f379eac0cc1b88',NULL,'2022-05-01 10:36:33',NULL),(2,'Mark.Webber','9651cbc7c0b5fb1a81f2858a07813c82',8,'Mark.Webber@formulaone.com','Mark','Webber','en_GB',NULL,1,'DEVKEY-Webber','b',NULL,'2022-05-01 10:36:33',NULL),(3,'Lewis.Hamilton','9651cbc7c0b5fb1a81f2858a07813c82',9,'Lewis.Hamilton@formulaone.com','Lewis','Hamilton','it_IT',NULL,1,'DEVKEY-Hamilton','c',NULL,'2022-05-01 10:36:33',NULL),(4,'Fernando.Alonso','9651cbc7c0b5fb1a81f2858a07813c82',6,'Fernando.Alonso@formulaone.com','Fernando','Alonso','en_GB',NULL,1,'DEVKEY-Alonso','d',NULL,'2022-05-01 10:36:33',NULL),(5,'Michael.Schumacher','9651cbc7c0b5fb1a81f2858a07813c82',8,'Michael.Schumacher@formulaone.com','Michael','Schumacher','en_GB',NULL,1,'DEVKEY-Schumacher','e',NULL,'2022-05-01 10:36:33',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'testlink'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDFStripHTMLTags` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;

DELIMITER ;;
CREATE DEFINER=`testlink`@`%` FUNCTION `UDFStripHTMLTags`(Dirty TEXT) RETURNS text CHARSET utf8mb3
    DETERMINISTIC
BEGIN
DECLARE iStart, iEnd, iLength int;
   WHILE Locate( '<', Dirty ) > 0 And Locate( '>', Dirty, Locate( '<', Dirty )) > 0 DO
      BEGIN
        SET iStart = Locate( '<', Dirty ), iEnd = Locate( '>', Dirty, Locate('<', Dirty ));
        SET iLength = ( iEnd - iStart) + 1;
        IF iLength > 0 THEN
          BEGIN
            SET Dirty = Insert( Dirty, iStart, iLength, '');
          END;
        END IF;
      END;
    END WHILE;
RETURN Dirty;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `exec_by_date_time`
--

/*!50001 DROP VIEW IF EXISTS `exec_by_date_time`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `exec_by_date_time` AS select `NHTPL`.`name` AS `testplan_name`,date_format(`E`.`execution_ts`,'%Y-%m-%d') AS `yyyy_mm_dd`,date_format(`E`.`execution_ts`,'%Y-%m') AS `yyyy_mm`,date_format(`E`.`execution_ts`,'%H') AS `hh`,date_format(`E`.`execution_ts`,'%k') AS `hour`,`E`.`id` AS `id`,`E`.`build_id` AS `build_id`,`E`.`tester_id` AS `tester_id`,`E`.`execution_ts` AS `execution_ts`,`E`.`status` AS `status`,`E`.`testplan_id` AS `testplan_id`,`E`.`tcversion_id` AS `tcversion_id`,`E`.`tcversion_number` AS `tcversion_number`,`E`.`platform_id` AS `platform_id`,`E`.`execution_type` AS `execution_type`,`E`.`execution_duration` AS `execution_duration`,`E`.`notes` AS `notes` from ((`executions` `E` join `testplans` `TPL` on((`TPL`.`id` = `E`.`testplan_id`))) join `nodes_hierarchy` `NHTPL` on((`NHTPL`.`id` = `TPL`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latest_exec_by_context`
--

/*!50001 DROP VIEW IF EXISTS `latest_exec_by_context`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `latest_exec_by_context` AS select `executions`.`tcversion_id` AS `tcversion_id`,`executions`.`testplan_id` AS `testplan_id`,`executions`.`build_id` AS `build_id`,`executions`.`platform_id` AS `platform_id`,max(`executions`.`id`) AS `id` from `executions` group by `executions`.`tcversion_id`,`executions`.`testplan_id`,`executions`.`build_id`,`executions`.`platform_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latest_exec_by_testplan`
--

/*!50001 DROP VIEW IF EXISTS `latest_exec_by_testplan`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `latest_exec_by_testplan` AS select `executions`.`tcversion_id` AS `tcversion_id`,`executions`.`testplan_id` AS `testplan_id`,max(`executions`.`id`) AS `id` from `executions` group by `executions`.`tcversion_id`,`executions`.`testplan_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latest_exec_by_testplan_plat`
--

/*!50001 DROP VIEW IF EXISTS `latest_exec_by_testplan_plat`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `latest_exec_by_testplan_plat` AS select `executions`.`tcversion_id` AS `tcversion_id`,`executions`.`testplan_id` AS `testplan_id`,`executions`.`platform_id` AS `platform_id`,max(`executions`.`id`) AS `id` from `executions` group by `executions`.`tcversion_id`,`executions`.`testplan_id`,`executions`.`platform_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latest_req_version`
--

/*!50001 DROP VIEW IF EXISTS `latest_req_version`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `latest_req_version` AS select `RQ`.`id` AS `req_id`,max(`RQV`.`version`) AS `version` from ((`nodes_hierarchy` `NHRQV` join `requirements` `RQ` on((`RQ`.`id` = `NHRQV`.`parent_id`))) join `req_versions` `RQV` on((`RQV`.`id` = `NHRQV`.`id`))) group by `RQ`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latest_req_version_id`
--

/*!50001 DROP VIEW IF EXISTS `latest_req_version_id`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `latest_req_version_id` AS select `LRQVN`.`req_id` AS `req_id`,`LRQVN`.`version` AS `version`,`REQV`.`id` AS `req_version_id` from ((`latest_req_version` `LRQVN` join `nodes_hierarchy` `NHRQV` on((`NHRQV`.`parent_id` = `LRQVN`.`req_id`))) join `req_versions` `REQV` on(((`REQV`.`id` = `NHRQV`.`id`) and (`REQV`.`version` = `LRQVN`.`version`)))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latest_rspec_revision`
--

/*!50001 DROP VIEW IF EXISTS `latest_rspec_revision`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `latest_rspec_revision` AS select `RSR`.`parent_id` AS `req_spec_id`,`RS`.`testproject_id` AS `testproject_id`,max(`RSR`.`revision`) AS `revision` from (`req_specs_revisions` `RSR` join `req_specs` `RS` on((`RS`.`id` = `RSR`.`parent_id`))) group by `RSR`.`parent_id`,`RS`.`testproject_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latest_tcase_version_id`
--

/*!50001 DROP VIEW IF EXISTS `latest_tcase_version_id`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `latest_tcase_version_id` AS select `LTCVN`.`testcase_id` AS `testcase_id`,`LTCVN`.`version` AS `version`,`TCV`.`id` AS `tcversion_id` from ((`latest_tcase_version_number` `LTCVN` join `nodes_hierarchy` `NHTCV` on((`NHTCV`.`parent_id` = `LTCVN`.`testcase_id`))) join `tcversions` `TCV` on(((`TCV`.`id` = `NHTCV`.`id`) and (`TCV`.`version` = `LTCVN`.`version`)))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `latest_tcase_version_number`
--

/*!50001 DROP VIEW IF EXISTS `latest_tcase_version_number`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `latest_tcase_version_number` AS select `NH_TC`.`id` AS `testcase_id`,max(`TCV`.`version`) AS `version` from ((`nodes_hierarchy` `NH_TC` join `nodes_hierarchy` `NH_TCV` on((`NH_TCV`.`parent_id` = `NH_TC`.`id`))) join `tcversions` `TCV` on((`NH_TCV`.`id` = `TCV`.`id`))) group by `testcase_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tcversions_without_keywords`
--

/*!50001 DROP VIEW IF EXISTS `tcversions_without_keywords`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `tcversions_without_keywords` AS select `NHTCV`.`parent_id` AS `testcase_id`,`NHTCV`.`id` AS `id` from `nodes_hierarchy` `NHTCV` where ((`NHTCV`.`node_type_id` = 4) and exists(select 1 from `testcase_keywords` `TCK` where (`TCK`.`tcversion_id` = `NHTCV`.`id`)) is false) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tcversions_without_platforms`
--

/*!50001 DROP VIEW IF EXISTS `tcversions_without_platforms`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `tcversions_without_platforms` AS select `NHTCV`.`parent_id` AS `testcase_id`,`NHTCV`.`id` AS `id` from `nodes_hierarchy` `NHTCV` where ((`NHTCV`.`node_type_id` = 4) and exists(select 1 from `testcase_platforms` `TCPL` where (`TCPL`.`tcversion_id` = `NHTCV`.`id`)) is false) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tsuites_tree_depth_2`
--

/*!50001 DROP VIEW IF EXISTS `tsuites_tree_depth_2`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `tsuites_tree_depth_2` AS select `TPRJ`.`prefix` AS `prefix`,`NHTPRJ`.`name` AS `testproject_name`,`NHTS_L1`.`name` AS `level1_name`,`NHTS_L2`.`name` AS `level2_name`,`NHTPRJ`.`id` AS `testproject_id`,`NHTS_L1`.`id` AS `level1_id`,`NHTS_L2`.`id` AS `level2_id` from (((`testprojects` `TPRJ` join `nodes_hierarchy` `NHTPRJ` on((`TPRJ`.`id` = `NHTPRJ`.`id`))) left join `nodes_hierarchy` `NHTS_L1` on((`NHTS_L1`.`parent_id` = `NHTPRJ`.`id`))) left join `nodes_hierarchy` `NHTS_L2` on((`NHTS_L2`.`parent_id` = `NHTS_L1`.`id`))) where ((`NHTPRJ`.`node_type_id` = 1) and (`NHTS_L1`.`node_type_id` = 2) and (`NHTS_L2`.`node_type_id` = 2)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-02-04 15:57:11
