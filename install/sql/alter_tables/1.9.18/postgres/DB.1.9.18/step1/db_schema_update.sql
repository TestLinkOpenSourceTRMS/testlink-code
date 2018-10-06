-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres   
-- 
ALTER TABLE /*prefix*/req_coverage ADD COLUMN id BIGSERIAL  NOT NULL;
ALTER TABLE /*prefix*/req_coverage ADD PRIMARY KEY (id);

ALTER TABLE /*prefix*/req_coverage ADD COLUMN req_version_id INTEGER NOT NULL;
ALTER TABLE /*prefix*/req_coverage ADD COLUMN tcversion_id INTEGER NOT NULL;
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
ALTER TABLE /*prefix*/testcase_keywords ADD COLUMN tcversion_id INTEGER NOT NULL;

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
