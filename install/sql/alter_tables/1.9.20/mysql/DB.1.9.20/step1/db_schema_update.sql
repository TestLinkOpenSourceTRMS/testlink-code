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

-- 
ALTER TABLE /*prefix*/builds ADD COLUMN commit_id varchar(64) NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN tag varchar(64) NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN branch varchar(64) NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN release_candidate varchar(100) NULL;


CREATE TABLE /*prefix*/testcase_platforms (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  testcase_id int(10) unsigned NOT NULL DEFAULT '0',
  tcversion_id int(10) unsigned NOT NULL DEFAULT '0',
  platform_id int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY idx01_testcase_platform (testcase_id,tcversion_id,platform_id),
  KEY idx02_testcase_platform (tcversion_id)
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

ALTER TABLE /*prefix*/nodes_hierarchy ADD INDEX /*prefix*/nodes_hierarchy_node_type_id (node_type_id);
ALTER TABLE /*prefix*/testcase_keywords ADD INDEX /*prefix*/idx02_testcase_keywords (tcversion_id);


CREATE OR REPLACE VIEW /*prefix*/tcversions_without_platforms
AS SELECT
   NHTCV.parent_id AS testcase_id,
   NHTCV.id AS id
FROM /*prefix*/nodes_hierarchy NHTCV 
WHERE NHTCV.node_type_id = 4 AND
NOT(EXISTS(SELECT 1 FROM /*prefix*/testcase_platforms TCPL
           WHERE TCPL.tcversion_id = NHTCV.id));


# END