-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres

-- Just to avoid issues
--
-- VIEWS
--
CREATE OR REPLACE VIEW /*prefix*/tcases_active AS 
(
  SELECT DISTINCT nhtcv.parent_id AS tcase_id, tcv.tc_external_id
  FROM /*prefix*/nodes_hierarchy nhtcv
  JOIN /*prefix*/tcversions tcv ON tcv.id = nhtcv.id
  WHERE tcv.active = 1
);

-- 
--
CREATE OR REPLACE VIEW /*prefix*/tcversions_last_active AS 
(
  SELECT tcv.id, tcv.tc_external_id, tcv.version, tcv.layout, tcv.status, 
       tcv.summary, tcv.preconditions, tcv.importance, tcv.author_id, tcv.creation_ts, 
       tcv.updater_id, tcv.modification_ts, tcv.active, tcv.is_open, tcv.execution_type, 
       ac.tcase_id
  FROM /*prefix*/tcversions tcv
  JOIN( 
    SELECT nhtcv.parent_id AS tcase_id, max(tcv.id) AS tcversion_id
    FROM /*prefix*/nodes_hierarchy nhtcv
    JOIN /*prefix*/tcversions tcv ON tcv.id = nhtcv.id
    WHERE tcv.active = 1
    GROUP BY nhtcv.parent_id, tcv.tc_external_id
    ) ac 
  ON tcv.id = ac.tcversion_id
);


--
CREATE OR REPLACE VIEW /*prefix*/latest_tcase_version_number AS 
( 
  SELECT NH_TC.id AS testcase_id,max(TCV.version) AS version 
  FROM /*prefix*/nodes_hierarchy NH_TC 
  JOIN /*prefix*/nodes_hierarchy NH_TCV 
  ON NH_TCV.parent_id = NH_TC.id
  JOIN /*prefix*/tcversions TCV 
  ON NH_TCV.id = TCV.id 
  GROUP BY testcase_id
);

--
-- @uses latest_tcase_version_number
--
CREATE OR REPLACE VIEW /*prefix*/latest_tcase_version_id AS 
(
  SELECT LTCVN.testcase_id AS testcase_id,
         LTCVN.version AS version,
         TCV.id AS tcversion_id
  FROM /*prefix*/latest_tcase_version_number LTCVN 
  JOIN /*prefix*/nodes_hierarchy NHTCV 
  ON NHTCV.parent_id = LTCVN.testcase_id
  JOIN /*prefix*/tcversions TCV 
  ON  TCV.id = NHTCV.id 
  AND TCV.version = LTCVN.version
);


--
-- @used_by latest_req_version_id
--
CREATE OR REPLACE VIEW /*prefix*/latest_req_version AS
( 
  SELECT RQ.id AS req_id,max(RQV.version) AS version 
  FROM /*prefix*/nodes_hierarchy NHRQV 
  JOIN /*prefix*/requirements RQ 
  ON RQ.id = NHRQV.parent_id 
  JOIN /*prefix*/req_versions RQV 
  ON RQV.id = NHRQV.id
  GROUP BY RQ.id
);


--
-- @uses latest_req_version
-- 
CREATE OR REPLACE VIEW /*prefix*/latest_req_version_id AS 
( 
  SELECT LRQVN.req_id AS req_id, LRQVN.version AS version,
         REQV.id AS req_version_id
  FROM /*prefix*/latest_req_version LRQVN JOIN 
       /*prefix*/nodes_hierarchy NHRQV
  ON NHRQV.parent_id = LRQVN.req_id 
  JOIN /*prefix*/req_versions REQV 
  ON REQV.id = NHRQV.id AND REQV.version = LRQVN.version
);


--
--
CREATE OR REPLACE VIEW /*prefix*/latest_rspec_revision AS 
(
  SELECT RSR.parent_id AS req_spec_id, RS.testproject_id AS testproject_id,
  MAX(RSR.revision) AS revision 
  FROM /*prefix*/req_specs_revisions RSR 
  JOIN /*prefix*/req_specs RS 
  ON RS.id = RSR.parent_id
  GROUP BY RSR.parent_id,RS.testproject_id
);


-- 
ALTER TABLE /*prefix*/req_coverage ADD COLUMN id BIGSERIAL  NOT NULL;
ALTER TABLE /*prefix*/req_coverage ADD PRIMARY KEY (id);

ALTER TABLE /*prefix*/req_coverage ADD COLUMN req_version_id INTEGER NOT NULL DEFAULT '0';
ALTER TABLE /*prefix*/req_coverage ADD COLUMN tcversion_id INTEGER NOT NULL DEFAULT '0';
ALTER TABLE /*prefix*/req_coverage ADD COLUMN link_status INT2 NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/req_coverage ADD COLUMN is_active INT2 NOT NULL DEFAULT '1';

DROP INDEX /*prefix*/req_coverage_req_testcase;
CREATE UNIQUE INDEX /*prefix*/req_coverage_full_link ON /*prefix*/req_coverage ("req_id","req_version_id","testcase_id","tcversion_id");

---

--- _pkey is POSTGRES Standard for unnamed pk
--- why to use constraint?
--- because I've messed a lot the postgres schema definition :(
ALTER TABLE /*prefix*/testcase_keywords DROP constraint /*prefix*/testcase_keywords_pkey;

ALTER TABLE /*prefix*/testcase_keywords ADD COLUMN id BIGSERIAL NOT NULL;
ALTER TABLE /*prefix*/testcase_keywords ADD PRIMARY KEY (id);
ALTER TABLE /*prefix*/testcase_keywords ADD COLUMN tcversion_id INTEGER NOT NULL DEFAULT '0';

CREATE UNIQUE INDEX /*prefix*/idx01_testcase_keywords ON /*prefix*/testcase_keywords ("testcase_id","tcversion_id","keyword_id");

--- 
ALTER TABLE /*prefix*/testcase_relations ADD COLUMN link_status INT2 NOT NULL DEFAULT '1';




--
CREATE OR REPLACE VIEW /*prefix*/latest_tcase_version_number 
AS SELECT NH_TC.id AS testcase_id,max(TCV.version) AS version 
FROM /*prefix*/nodes_hierarchy NH_TC 
JOIN /*prefix*/nodes_hierarchy NH_TCV 
ON NH_TCV.parent_id = NH_TC.id
JOIN /*prefix*/tcversions TCV 
ON NH_TCV.id = TCV.id 
GROUP BY testcase_id;

CREATE OR REPLACE VIEW /*prefix*/latest_req_version 
AS SELECT RQ.id AS req_id,max(RQV.version) AS version 
FROM /*prefix*/nodes_hierarchy NHRQV 
JOIN /*prefix*/requirements RQ 
ON RQ.id = NHRQV.parent_id 
JOIN /*prefix*/req_versions RQV 
ON RQV.id = NHRQV.id
GROUP BY RQ.id;

CREATE OR REPLACE VIEW /*prefix*/latest_rspec_revision 
AS SELECT RSR.parent_id AS req_spec_id, RS.testproject_id AS testproject_id,
MAX(RSR.revision) AS revision 
FROM /*prefix*/req_specs_revisions RSR 
JOIN /*prefix*/req_specs RS 
ON RS.id = RSR.parent_id
GROUP BY RSR.parent_id,RS.testproject_id;


-- UPDATE DATA
UPDATE /*prefix*/req_coverage
SET req_version_id = LRQVID.req_version_id
FROM /*prefix*/latest_req_version_id LRQVID
WHERE req_coverage.req_id = LRQVID.req_id;


UPDATE /*prefix*/req_coverage
SET tcversion_id = LTCVID.tcversion_id
FROM /*prefix*/latest_tcase_version_id LTCVID
WHERE req_coverage.testcase_id = LTCVID.testcase_id;

UPDATE /*prefix*/testcase_keywords
SET tcversion_id = LTCVID.tcversion_id
FROM /*prefix*/latest_tcase_version_id LTCVID
WHERE testcase_keywords.testcase_id = LTCVID.testcase_id;

-- A little bit complex migration
-- Test Case Relations
SELECT * INTO /*prefix*/testcase_relations_backup
FROM /*prefix*/testcase_relations;

ALTER TABLE /*prefix*/testcase_relations ADD COLUMN tcase_source_id INTEGER NOT NULL DEFAULT '0';
ALTER TABLE /*prefix*/testcase_relations ADD COLUMN tcase_destination_id INTEGER  NOT NULL DEFAULT '0';

UPDATE /*prefix*/testcase_relations SET tcase_source_id = source_id;
UPDATE /*prefix*/testcase_relations SET tcase_destination_id = destination_id;

-- Update SOURCE_ID 
UPDATE /*prefix*/testcase_relations
SET source_id = LTCVID.tcversion_id
FROM /*prefix*/latest_tcase_version_id LTCVID
WHERE testcase_relations.tcase_source_id = LTCVID.testcase_id;

-- Update DESTINATION_ID 
UPDATE /*prefix*/testcase_relations
SET destination_id = LTCVID.tcversion_id
FROM /*prefix*/latest_tcase_version_id LTCVID
WHERE testcase_relations.tcase_destination_id = LTCVID.testcase_id;

-- Attachments
SELECT * INTO /*prefix*/attachments_backup
FROM /*prefix*/attachments;

ALTER TABLE /*prefix*/attachments ADD COLUMN original_fk_id INTEGER NOT NULL default '0';
ALTER TABLE /*prefix*/attachments ADD COLUMN original_fk_table VARCHAR(250) default '';

UPDATE /*prefix*/attachments SET original_fk_id = fk_id;
UPDATE /*prefix*/attachments SET original_fk_table = fk_table;

-- Work on REQ Attachments
UPDATE /*prefix*/attachments
SET fk_id = LRQVID.req_version_id,fk_table ='req_versions'
FROM /*prefix*/latest_req_version_id LRQVID
WHERE original_fk_id = LRQVID.req_id
AND original_fk_table = 'requirements';

-- Work on TEST CASE Attachments
UPDATE /*prefix*/attachments ATT
SET fk_id = LTCVID.tcversion_id,fk_table ='tcversions'
FROM /*prefix*/latest_tcase_version_id LTCVID
WHERE original_fk_id = LTCVID.testcase_id
AND original_fk_table = 'nodes_hierarchy';

-- END