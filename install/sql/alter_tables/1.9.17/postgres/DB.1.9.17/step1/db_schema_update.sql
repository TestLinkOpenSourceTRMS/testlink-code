-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres   
-- 
--
CREATE VIEW /*prefix*/latest_tcase_version_number 
AS SELECT NH_TC.id AS testcase_id,max(TCV.version) AS version 
FROM /*prefix*/nodes_hierarchy NH_TC 
JOIN /*prefix*/nodes_hierarchy NH_TCV 
ON NH_TCV.parent_id = NH_TC.id
JOIN /*prefix*/tcversions TCV 
ON NH_TCV.id = TCV.id 
GROUP BY testcase_id;

CREATE VIEW /*prefix*/latest_req_version 
AS SELECT RQ.id AS req_id,max(RQV.version) AS version 
FROM /*prefix*/nodes_hierarchy NHRQV 
JOIN /*prefix*/requirements RQ 
ON RQ.id = NHRQV.parent_id 
JOIN /*prefix*/req_versions RQV 
ON RQV.id = NHRQV.id
GROUP BY RQ.id;

CREATE VIEW /*prefix*/latest_rspec_revision 
AS SELECT RSR.parent_id AS req_spec_id, RS.testproject_id AS testproject_id,
MAX(RSR.revision) AS revision 
FROM /*prefix*/req_specs_revisions RSR 
JOIN /*prefix*/req_specs RS 
ON RS.id = RSR.parent_id
GROUP BY RSR.parent_id,RS.testproject_id;

-- since 1.9.17
INSERT INTO /*prefix*/rights (id,description) VALUES (49,'exec_ro_access');

ALTER TABLE /*prefix*/users ADD COLUMN creation_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE /*prefix*/users ADD COLUMN expiration_date date DEFAULT NULL;