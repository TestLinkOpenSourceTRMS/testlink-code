/* mysql */
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

CREATE TABLE /*prefix*/testcase_script_links (
  `tcversion_id` int(10) unsigned NOT NULL default '0',
  `project_key` varchar(64) NOT NULL,
  `repository_name` varchar(64) NOT NULL,
  `code_path` varchar(255) NOT NULL,
  `branch_name` varchar(64) default NULL,
  `commit_id` varchar(40) default NULL,
  PRIMARY KEY  (`tcversion_id`,`project_key`,`repository_name`,`code_path`)
) DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/codetrackers
(
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `type` int(10) default 0,
  `cfg` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/codetrackers_uidx1 (`name`)
) DEFAULT CHARSET=utf8;


CREATE TABLE /*prefix*/testproject_codetracker
(
  `testproject_id` int(10) unsigned NOT NULL,
  `codetracker_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`testproject_id`)
) DEFAULT CHARSET=utf8;

-- since 1.9.17
INSERT INTO /*prefix*/rights (id,description) VALUES (49,'exec_ro_access');
INSERT INTO /*prefix*/rights (id,description) VALUES (50,'monitor_requirement');
INSERT INTO /*prefix*/rights (id,description) VALUES (51,'codetracker_management');
INSERT INTO /*prefix*/rights (id,description) VALUES (52,'codetracker_view');
INSERT INTO /*prefix*/rights (id,description) VALUES (53,'cfield_assignment');
INSERT INTO /*prefix*/rights (id,description) VALUES (54,'exec_assign_testcases');

INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,28);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,29);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,50);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,51);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,52);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,53);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,54);


ALTER TABLE /*prefix*/testprojects ADD COLUMN code_tracker_enabled tinyint(1) NOT NULL default '0';
ALTER TABLE /*prefix*/users ADD COLUMN creation_ts timestamp NOT NULL DEFAULT now();
ALTER TABLE /*prefix*/users ADD COLUMN expiration_date date DEFAULT NULL;
