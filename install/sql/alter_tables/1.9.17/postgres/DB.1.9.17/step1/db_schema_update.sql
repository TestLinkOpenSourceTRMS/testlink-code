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

CREATE TABLE /*prefix*/testcase_script_links(  
  "tcversion_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcversions (id) ON DELETE CASCADE,
  "project_key" VARCHAR(64) NOT NULL,
  "repository_name" VARCHAR(64) NOT NULL,
  "code_path" VARCHAR(255) NOT NULL,
  "branch_name" VARCHAR(64) NULL,
  "commit_id" VARCHAR(40) NULL,
  PRIMARY KEY ("tcversion_id","project_key","repository_name","code_path")
); 

CREATE TABLE /*prefix*/codetrackers
(
  "id" BIGSERIAL NOT NULL ,
  "name" VARCHAR(100) NOT NULL,
  "type" INTEGER NOT NULL DEFAULT '0',
  "cfg" TEXT,
  PRIMARY KEY  ("id")
);
CREATE UNIQUE INDEX /*prefix*/codetrackers_uidx1 ON /*prefix*/codetrackers ("name");


CREATE TABLE /*prefix*/testproject_codetracker
(
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id) ON DELETE CASCADE,
  "codetracker_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/codetrackers (id) ON DELETE CASCADE,
  PRIMARY KEY ("testproject_id")
);

-- since 1.9.17
INSERT INTO /*prefix*/rights (id,description) VALUES (49,'exec_ro_access');
INSERT INTO /*prefix*/rights (id,description) VALUES (50,'monitor_requirement');
INSERT INTO /*prefix*/rights (id,description) VALUES (51,'codetracker_management');
INSERT INTO /*prefix*/rights (id,description) VALUES (52,'codetracker_view');
INSERT INTO /*prefix*/rights (id,description) VALUES (53,'cfield_assignment');
INSERT INTO /*prefix*/rights (id,description) VALUES (54,'exec_assign_testcases');

INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,28);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,29);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,30);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,50);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,51);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,52);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,53);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,54);

ALTER TABLE /*prefix*/testprojects ADD COLUMN code_tracker_enabled INT2 NOT NULL DEFAULT '0';
ALTER TABLE /*prefix*/users ADD COLUMN creation_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE /*prefix*/users ADD COLUMN expiration_date date DEFAULT NULL;
