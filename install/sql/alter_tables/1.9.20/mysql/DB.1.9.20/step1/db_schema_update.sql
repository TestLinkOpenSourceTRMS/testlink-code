# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
# ---------------------------------------------------------------------------------------
# @filesource db_schema_update.sql
#
# SQL script - updates DB schema for MySQL - 
# From TestLink 1.9.19 to 1.9.20
# 
#
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

ALTER TABLE /*prefix*/nodes_hierarchy ADD INDEX /*prefix*/nodes_hierarchy_node_type_id (`node_type_id`);
ALTER TABLE /*prefix*/testcase_keywords ADD INDEX /*prefix*/idx02_testcase_keywords (`tcversion_id`);

# END