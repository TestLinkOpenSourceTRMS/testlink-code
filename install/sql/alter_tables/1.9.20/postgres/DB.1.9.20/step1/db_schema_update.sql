-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres

-- since 1.9.20
INSERT INTO /*prefix*/rights (id,description) VALUES (55,'testproject_add_remove_keywords_executed_tcversions');
INSERT INTO /*prefix*/rights (id,description) VALUES (56,'delete_frozen_tcversion');

ALTER TABLE /*prefix*/builds ADD COLUMN commit_id VARCHAR(64) NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN tag VARCHAR(64) NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN branch VARCHAR(64) NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN release_candidate VARCHAR(100) NULL;

-- 
ALTER TABLE /*prefix*/users ALTER COLUMN password TYPE VARCHAR(255);

--
ALTER TABLE /*prefix*/testplan_platforms ADD COLUMN active INT2 NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/platforms ADD COLUMN enable_on_design INT2 NOT NULL DEFAULT '0';
ALTER TABLE /*prefix*/platforms ADD COLUMN enable_on_execution INT2 NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/platforms ADD COLUMN is_open INT2 NOT NULL DEFAULT '1';

--
-- Table structure for table "testcase_platforms"
--
CREATE TABLE /*prefix*/testcase_platforms( 
  "id" BIGSERIAL NOT NULL , 
  "testcase_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "tcversion_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcversions (id),
  "platform_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/platforms (id) ON DELETE CASCADE,
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/idx01_testcase_platforms ON /*prefix*/testcase_platforms ("testcase_id","tcversion_id","platform_id");
CREATE INDEX /*prefix*/idx02_testcase_platforms ON /*prefix*/testcase_platforms ("tcversion_id");

CREATE TABLE /*prefix*/baseline_l1l2_context (
  "id" BIGSERIAL NOT NULL , 
  "testplan_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  "platform_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/platforms (id) ON DELETE CASCADE,
  "begin_exec_ts" timestamp NOT NULL,
  "end_exec_ts" timestamp NOT NULL,
  "creation_ts" timestamp NOT NULL DEFAULT now(),
  PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX /*prefix*/udx1_context ON /*prefix*/baseline_l1l2_context ("testplan_id","platform_id","creation_ts");

--
-- 
--
CREATE TABLE /*prefix*/testcase_aliens (
  "id" BIGSERIAL NOT NULL , 
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  "testcase_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "tcversion_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcversions (id),
  "alien_id" VARCHAR(64) NOT NULL DEFAULT '0',
  "relation_type" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX /*prefix*/idx01_testcase_aliens ON /*prefix*/testcase_aliens ("testproject_id","testcase_id","tcversion_id","alien_id");
CREATE INDEX /*prefix*/idx02_testcase_aliens ON /*prefix*/testcase_aliens ("tcversion_id");
--
-- 


CREATE TABLE /*prefix*/baseline_l1l2_details (
  "id" BIGSERIAL NOT NULL , 
  "context_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/baseline_l1l2_context (id),
  "top_tsuite_id" BIGINT NOT NULL DEFAULT '0'  REFERENCES  /*prefix*/testsuites (id),
  "child_tsuite_id" BIGINT NOT NULL DEFAULT '0'  REFERENCES  /*prefix*/testsuites (id),
  "status" char(1) DEFAULT NULL,
  "qty" INT NOT NULL DEFAULT '0',
  "total_tc" INT NOT NULL DEFAULT '0',
  PRIMARY KEY ("id")
) ;
CREATE UNIQUE INDEX /*prefix*/udx1_details 
ON /*prefix*/baseline_l1l2_details ("context_id","top_tsuite_id","child_tsuite_id","status");



-- 
--
CREATE OR REPLACE VIEW /*prefix*/latest_exec_by_testplan AS 
( 
  SELECT tcversion_id, testplan_id, MAX(id) AS id 
  FROM /*prefix*/executions 
  GROUP BY tcversion_id,testplan_id
);  
--

--
CREATE OR REPLACE VIEW /*prefix*/latest_exec_by_context AS 
(
  SELECT tcversion_id, testplan_id,build_id,platform_id,max(id) AS id
  FROM /*prefix*/executions 
  GROUP BY tcversion_id,testplan_id,build_id,platform_id
);


CREATE INDEX /*prefix*/nodes_hierarchy_node_type_id ON /*prefix*/nodes_hierarchy ("node_type_id");
CREATE INDEX /*prefix*/idx02_testcase_keywords ON /*prefix*/testcase_keywords ("tcversion_id");


--
--
CREATE OR REPLACE VIEW /*prefix*/tcversions_without_platforms AS 
( 
  SELECT NHTCV.parent_id AS testcase_id, NHTCV.id AS id
  FROM /*prefix*/nodes_hierarchy NHTCV 
  WHERE NHTCV.node_type_id = 4 
  AND NOT(EXISTS(SELECT 1 FROM /*prefix*/testcase_platforms TCPL
                 WHERE TCPL.tcversion_id = NHTCV.id ) )
);

CREATE OR REPLACE VIEW /*prefix*/tsuites_tree_depth_2 AS 
(
  SELECT TPRJ.prefix,
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
  AND NHTS_L2.node_type_id = 2
);

CREATE OR REPLACE VIEW /*prefix*/exec_by_date_time 
AS (
SELECT NHTPL.name AS testplan_name, 
TO_CHAR(E.execution_ts, 'YYYY-MM-DD') AS yyyy_mm_dd,
TO_CHAR(E.execution_ts, 'YYYY-MM') AS yyyy_mm,
TO_CHAR(E.execution_ts, 'HH24') AS hh,
TO_CHAR(E.execution_ts, 'HH24') AS hour,
E.* FROM /*prefix*/executions E
JOIN /*prefix*/testplans TPL on TPL.id=E.testplan_id
JOIN /*prefix*/nodes_hierarchy NHTPL on NHTPL.id = TPL.id);
-- END