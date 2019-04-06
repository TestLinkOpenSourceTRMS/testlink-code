-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres

-- 
--
CREATE OR REPLACE VIEW /*prefix*/latest_exec_by_testplan AS 
( 
  SELECT tcversion_id, testplan_id, MAX(id) AS id 
  FROM /*prefix*/executions 
  GROUP BY tcversion_id,testplan_id
);  
--
-- END