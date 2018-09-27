# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
# ---------------------------------------------------------------------------------------
# @filesource db_schema_update.sql
#
# SQL script - updates DB schema for MySQL - From TestLink 1.9.17 to 1.9.18
# 
#
ALTER TABLE /*prefix*/req_coverage ADD COLUMN `id` int(10) unsigned primary KEY AUTO_INCREMENT FIRST;
ALTER TABLE /*prefix*/req_coverage ADD COLUMN `req_version_id` int(10) NOT NULL AFTER req_id;
ALTER TABLE /*prefix*/req_coverage ADD COLUMN `tcversion_id` int(10) NOT NULL AFTER testcase_id;
ALTER TABLE /*prefix*/req_coverage ADD COLUMN `link_status` int(11) NOT NULL DEFAULT '1' AFTER tcversion_id;
ALTER TABLE /*prefix*/req_coverage ADD COLUMN `is_active` int(11) NOT NULL DEFAULT '1' AFTER link_status;

ALTER TABLE /*prefix*/req_coverage DROP KEY /*prefix*/req_testcase;
ALTER TABLE /*prefix*/req_coverage ADD UNIQUE KEY /*prefix*/req_coverage_full_link (`req_id`,`req_version_id`,`testcase_id`,`tcversion_id`);


ALTER TABLE /*prefix*/testcase_keywords ADD COLUMN `tcversion_id` int(10) NOT NULL AFTER testcase_id;
ALTER TABLE /*prefix*/testcase_keywords DROP PRIMARY KEY;
ALTER TABLE /*prefix*/testcase_keywords ADD COLUMN `id` int(10) unsigned primary KEY AUTO_INCREMENT FIRST;
ALTER TABLE /*prefix*/testcase_keywords ADD UNIQUE KEY /*prefix*/idx01_testcase_keywords (`testcase_id`,`tcversion_id`,`keyword_id`);

ALTER TABLE /*prefix*/testcase_relations ADD COLUMN `link_status` tinyint(1) NOT NULL DEFAULT '1' AFTER relation_type;



CREATE OR REPLACE VIEW /*prefix*/tcversions_without_keywords
AS SELECT
   `NHTCV`.`parent_id` AS `testcase_id`,
   `NHTCV`.`id` AS `id`
FROM /*prefix*/nodes_hierarchy NHTCV where ((`NHTCV`.`node_type_id` = 4) and (not(exists(select 1 from /*prefix*/testcase_keywords TCK where (`TCK`.`tcversion_id` = `NHTCV`.`id`)))));


CREATE OR REPLACE VIEW /*prefix*/latest_tcase_version_id
AS SELECT
   `ltcvn`.`testcase_id` AS `testcase_id`,
   `ltcvn`.`version` AS `version`,
   `TCV`.`id` AS `tcversion_id`
FROM ((/*prefix*/latest_tcase_version_number LTCVN 
       join /*prefix*/nodes_hierarchy NHTCV 
       on ((`NHTCV`.`parent_id` = `ltcvn`.`testcase_id`))) 
       join /*prefix*/tcversions `TCV` 
       on (((`TCV`.`id` = `NHTCV`.`id`) 
       and (`TCV`.`version` = `ltcvn`.`version`))));

CREATE OR REPLACE VIEW /*prefix*/latest_req_version_id
AS SELECT
   `lrqvn`.`req_id` AS `req_id`,
   `lrqvn`.`version` AS `version`,
   `REQV`.`id` AS `req_version_id`
FROM ((`latest_req_version` `LRQVN` 
join `nodes_hierarchy` `NHRQV` on ((`NHRQV`.`parent_id` = `lrqvn`.`req_id`))) 
join `req_versions` `REQV` on (((`REQV`.`id` = `NHRQV`.`id`) and (`REQV`.`version` = `lrqvn`.`version`))));


# UPDATE DATA
UPDATE /*prefix*/req_coverage RCOV,/*prefix*/latest_req_version_id LRQVID
SET RCOV.req_version_id = LRQVID.req_version_id
WHERE RCOV.req_id = LRQVID.req_id;

UPDATE /*prefix*/req_coverage RCOV,/*prefix*/latest_tcase_version_id LTCVID
SET RCOV.tcversion_id = LTCVID.tcversion_id
WHERE RCOV.testcase_id = LTCVID.testcase_id;

UPDATE /*prefix*/testcase_keywords TCKW,/*prefix*/latest_tcase_version_id LTCVID
SET TCKW.tcversion_id = LTCVID.tcversion_id
WHERE TCKW.testcase_id = LTCVID.testcase_id;

# A little bit complex migration
# Test Case Relations
CREATE TABLE /*prefix*/testcase_relations_backup SELECT * FROM /*prefix*/testcase_relations;
ALTER TABLE /*prefix*/testcase_relations ADD COLUMN `tcase_source_id` int(10) unsigned NOT NULL;
ALTER TABLE /*prefix*/testcase_relations ADD COLUMN `tcase_destination_id` int(10) unsigned NOT NULL;

UPDATE /*prefix*/testcase_relations SET tcase_source_id = source_id;
UPDATE /*prefix*/testcase_relations SET tcase_destination_id = destination_id;

# Update SOURCE_ID 
UPDATE /*prefix*/testcase_relations TCREL
INNER JOIN /*prefix*/latest_tcase_version_id LTCVID
ON TCREL.tcase_source_id = LTCVID.testcase_id
SET TCREL.source_id = LTCVID.tcversion_id 

# Update DESTINATION_ID 
UPDATE /*prefix*/testcase_relations TCREL
INNER JOIN /*prefix*/latest_tcase_version_id LTCVID
ON TCREL.tcase_destination_id = LTCVID.testcase_id
SET TCREL.destination_id = LTCVID.tcversion_id

# Attachments
CREATE TABLE /*prefix*/attachments_backup SELECT * FROM /*prefix*/attachments;
ALTER TABLE /*prefix*/attachments ADD COLUMN `original_fk_id` int(10) unsigned NOT NULL default '0';
ALTER TABLE /*prefix*/attachments ADD COLUMN `original_fk_table` varchar(250) default '';

UPDATE /*prefix*/attachments SET original_fk_id = fk_id;
UPDATE /*prefix*/attachments SET original_fk_table = fk_table;

# Work on REQ Attachments
UPDATE /*prefix*/attachments ATT,/*prefix*/latest_req_version_id LRQVID
SET ATT.fk_id = LRQVID.req_version_id,ATT.fk_table ='req_versions'
WHERE ATT.original_fk_id = LRQVID.req_id
AND ATT.original_fk_table = 'requirements';

# Work on TEST CASE Attachments
UPDATE /*prefix*/attachments ATT,/*prefix*/latest_tcase_version_id LTCVID
SET ATT.fk_id = LTCVID.tcversion_id,ATT.fk_table ='tcversions'
WHERE ATT.original_fk_id = LTCVID.testcase_id
AND ATT.original_fk_table = 'nodes_hierarchy';

# END