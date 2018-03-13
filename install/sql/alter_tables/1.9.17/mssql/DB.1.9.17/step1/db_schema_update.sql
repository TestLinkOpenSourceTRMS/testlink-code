--  -----------------------------------------------------------------------------------
-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- @filesource testlink_create_tables.sql
--
-- SQL script - update db tables for TL
-- Database Type: Microsoft SQL Server
--
-- ATTENTION: do not use a different naming convention, that one already in use.
--            TEXTIMAGE Option can be used only tables that have fields of type:
--            varchar(MAXSIZEALLOWED), nvarchar(MAXSIZEALLOWED), varbinary(MAXSIZEALLOWED), xml 
-- 
--        Find issue with custom_fields table if two fields were char(4000)
--        changed to varchar(4000) everything goes OK
--            http://www.mssqltips.com/sqlservertip/2242/row-sizes-exceeding-8060-bytes-in-sql-2005/
-- 
-- ATTENTION: 
-- 
-- @internal revisions
--                          
--  -----------------------------------------------------------------------------------
--
--- 
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
  tcversion_id int NOT NULL CONSTRAINT /*prefix*/DF_testcase_script_links_tcversion_id DEFAULT ((0)),
  project_key varchar(64)  NOT NULL,
  repository_name varchar(64)  NOT NULL,
  code_path varchar(255)  NOT NULL,
  branch_name varchar(64)  NULL,
  commit_id varchar(40)  NULL,
 CONSTRAINT /*prefix*/PK_testcase_script_links PRIMARY KEY CLUSTERED 
(
  tcversion_id ASC,
  project_key ASC,
  repository_name ASC,
  code_path ASC
) ON [PRIMARY]
) ON [PRIMARY];

CREATE TABLE /*prefix*/codetrackers
(
  id int IDENTITY(1,1) NOT NULL,
  name VARCHAR(100) NOT NULL,
  type int NOT NULL CONSTRAINT /*prefix*/DF_codetrackers_type DEFAULT ((0)),
  cfg nvarchar(max)  NULL,
  CONSTRAINT /*prefix*/PK_codetrackers PRIMARY KEY  CLUSTERED 
  (
    id
  )  ON [PRIMARY],
    CONSTRAINT /*prefix*/UIX_codetrackers UNIQUE NONCLUSTERED 
   ( 
  name ASC
   ) ON [PRIMARY]  
) ON [PRIMARY];


CREATE TABLE /*prefix*/testproject_codetracker
(
  testproject_id int NOT NULL,
  codetracker_id int NOT NULL,
    CONSTRAINT /*prefix*/UIX_testproject_codetracker UNIQUE NONCLUSTERED 
   ( 
  testproject_id ASC
   ) ON [PRIMARY]    
)ON [PRIMARY];

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

ALTER TABLE /*prefix*/testprojects ADD COLUMN code_tracker_enabled tinyint NOT NULL CONSTRAINT /*prefix*/DF_testprojects_code_tracker_enabled DEFAULT ((0));
ALTER TABLE /*prefix*/users ADD COLUMN creation_ts timestamp NOT NULL DEFAULT now();
ALTER TABLE /*prefix*/users ADD COLUMN expiration_date date DEFAULT NULL;
