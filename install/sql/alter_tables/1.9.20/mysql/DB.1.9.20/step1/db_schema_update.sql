# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
# ---------------------------------------------------------------------------------------
# @filesource db_schema_update.sql
#
# SQL script - updates DB schema for MySQL - 
# From TestLink 1.9.19 to 1.9.20
# 
#
-- since 1.9.20
INSERT INTO /*prefix*/rights (id,description) VALUES (55,'testproject_add_remove_keywords_executed_tcversions');
INSERT INTO /*prefix*/rights (id,description) VALUES (56,'delete_frozen_tcversion');

-- 
ALTER TABLE /*prefix*/builds ADD COLUMN commit_id varchar(64) NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN tag varchar(64) NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN branch varchar(64) NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN release_candidate varchar(100) NULL;

--
ALTER TABLE /*prefix*/users MODIFY password VARCHAR(255) NOT NULL default '';

-- 
ALTER TABLE /*prefix*/testplan_platforms ADD COLUMN active tinyint(1) NOT NULL default '1';
ALTER TABLE /*prefix*/platforms ADD COLUMN  enable_on_design tinyint(1) NOT NULL default '0';
ALTER TABLE /*prefix*/platforms ADD COLUMN  enable_on_execution tinyint(1) NOT NULL default '1';
ALTER TABLE /*prefix*/platforms ADD COLUMN  is_open tinyint(1) NOT NULL default '1';

--
ALTER TABLE /*prefix*/nodes_hierarchy ADD INDEX /*prefix*/nodes_hierarchy_node_type_id (node_type_id);
ALTER TABLE /*prefix*/testcase_keywords ADD INDEX /*prefix*/idx02_testcase_keywords (tcversion_id);

ALTER TABLE /*prefix*/milestones MODIFY target_date date NOT NULL;
ALTER TABLE /*prefix*/milestones MODIFY start_date date DEFAULT NULL;

-- 
CREATE TABLE /*prefix*/execution_tcsteps_wip (
   id int(10) unsigned NOT NULL auto_increment,
   tcstep_id int(10) unsigned NOT NULL default '0',
   testplan_id int(10) unsigned NOT NULL default '0',
   platform_id int(10) unsigned NOT NULL default '0',
   build_id int(10) unsigned NOT NULL default '0',
   tester_id int(10) unsigned default NULL,
   creation_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   notes text,
   status char(1) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY /*prefix*/execution_tcsteps_wip_idx1(`tcstep_id`,`testplan_id`,`platform_id`,`build_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/testcase_platforms (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  testcase_id int(10) unsigned NOT NULL DEFAULT '0',
  tcversion_id int(10) unsigned NOT NULL DEFAULT '0',
  platform_id int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY idx01_testcase_platform (testcase_id,tcversion_id,platform_id),
  KEY idx02_testcase_platform (tcversion_id)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/baseline_l1l2_context (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  testplan_id int(10) unsigned NOT NULL DEFAULT '0',
  platform_id int(10) unsigned NOT NULL DEFAULT '0',
  begin_exec_ts timestamp NOT NULL,
  end_exec_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creation_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY /*prefix*/udx1_details (testplan_id,platform_id,creation_ts)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/baseline_l1l2_details (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  context_id int(10) unsigned NOT NULL,
  top_tsuite_id int(10) unsigned NOT NULL DEFAULT '0',
  child_tsuite_id int(10) unsigned NOT NULL DEFAULT '0',
  status char(1) DEFAULT NULL,
  qty int(10) unsigned NOT NULL DEFAULT '0',
  total_tc int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY /*prefix*/udx1_details (context_id,top_tsuite_id,child_tsuite_id,status)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/testcase_aliens (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  testproject_id int(10) unsigned NOT NULL default '0',
  testcase_id int(10) unsigned NOT NULL DEFAULT '0',
  tcversion_id int(10) NOT NULL,
  alien_id varchar(64) NOT NULL default '0',
  relation_type int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (id),
  UNIQUE KEY /*prefix*/idx01_testcase_aliens (testproject_id,testcase_id,tcversion_id,alien_id),
  KEY /*prefix*/idx02_testcase_aliens (tcversion_id)
) DEFAULT CHARSET=utf8;

#
CREATE OR REPLACE VIEW /*prefix*/latest_exec_by_testplan 
AS SELECT tcversion_id, testplan_id, MAX(id) AS id 
FROM /*prefix*/executions 
GROUP BY tcversion_id,testplan_id;

#
CREATE OR REPLACE VIEW /*prefix*/latest_exec_by_context
AS SELECT tcversion_id, testplan_id,build_id,platform_id,max(id) AS id
FROM /*prefix*/executions 
GROUP BY tcversion_id,testplan_id,build_id,platform_id;


CREATE OR REPLACE VIEW /*prefix*/tcversions_without_platforms
AS SELECT
   NHTCV.parent_id AS testcase_id,
   NHTCV.id AS id
FROM /*prefix*/nodes_hierarchy NHTCV 
WHERE NHTCV.node_type_id = 4 AND
NOT(EXISTS(SELECT 1 FROM /*prefix*/testcase_platforms TCPL
           WHERE TCPL.tcversion_id = NHTCV.id));

CREATE OR REPLACE VIEW /*prefix*/latest_exec_by_testplan_plat
AS SELECT tcversion_id, testplan_id,platform_id,max(id) AS id
FROM /*prefix*/executions 
GROUP BY tcversion_id,testplan_id,platform_id;

#
CREATE OR REPLACE VIEW /*prefix*/tsuites_tree_depth_2
AS SELECT TPRJ.prefix,
NHTPRJ.name AS testproject_name,    
NHTS_L1.name AS level1_name,
NHTS_L2.name AS level2_name,
NHTPRJ.id AS testproject_id, 
NHTS_L1.id AS level1_id, 
NHTS_L2.id AS level2_id 
FROM /*prefix*/testprojects TPRJ 
JOIN /*prefix*/nodes_hierarchy NHTPRJ 
ON TPRJ.id = NHTPRJ.id
LEFT OUTER JOIN /*prefix*/nodes_hierarchy NHTS_L1 
ON NHTS_L1.parent_id = NHTPRJ.id
LEFT OUTER JOIN /*prefix*/nodes_hierarchy NHTS_L2
ON NHTS_L2.parent_id = NHTS_L1.id 
WHERE NHTPRJ.node_type_id = 1 
AND NHTS_L1.node_type_id = 2
AND NHTS_L2.node_type_id = 2;

##
CREATE OR REPLACE VIEW /*prefix*/exec_by_date_time 
AS (
SELECT NHTPL.name AS testplan_name, 
DATE_FORMAT(E.execution_ts, '%Y-%m-%d') AS yyyy_mm_dd,
DATE_FORMAT(E.execution_ts, '%Y-%m') AS yyyy_mm,
DATE_FORMAT(E.execution_ts, '%H') AS hh,
DATE_FORMAT(E.execution_ts, '%k') AS hour,
E.* FROM /*prefix*/executions E
JOIN /*prefix*/testplans TPL on TPL.id=E.testplan_id
JOIN /*prefix*/nodes_hierarchy NHTPL on NHTPL.id = TPL.id);

# END