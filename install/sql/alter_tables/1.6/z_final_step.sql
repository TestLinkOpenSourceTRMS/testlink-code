/* Migration from 1.5 to 1.6 POST RC1 - 20050925 - fm*/

/* results table */
UPDATE project TP, component COMP, category CAT, testcase TC, 
       results RES, build 
SET RES.build_id = build.id
WHERE TP.id = COMP.projid 
AND COMP.id =CAT.compid
AND CAT.id = TC.catid
AND TC.id = RES.tcid
AND build.BUILD = RES.build;

/* bugs table */
UPDATE project TP, component COMP, category CAT, testcase TC, 
       bugs, build 
SET bugs.build_id = build.id
WHERE TP.id = COMP.projid 
AND COMP.id =CAT.compid
AND CAT.id = TC.catid
AND TC.id = bugs.tcid
AND build.BUILD = bugs.build;


ALTER TABLE bugs DROP PRIMARY KEY;
ALTER TABLE bugs DROP INDEX build;
ALTER TABLE bugs ADD PRIMARY KEY  (`tcid`,`build_id`,`bug`);
ALTER TABLE bugs ADD INDEX  KEY `build_id` (`build_id`);

ALTER TABLE results DROP PRIMARY KEY;
ALTER TABLE results ADD PRIMARY KEY  (`tcid`,`build_id`);

ALTER TABLE bugs DROP COLUMN build;
ALTER TABLE results DROP COLUMN build;
ALTER TABLE build DROP COLUMN build;

INSERT INTO db_version VALUES('1.6 POST RC1', CURRENT_TIMESTAMP());