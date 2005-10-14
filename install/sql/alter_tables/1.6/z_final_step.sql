/* 
$Revision: 1.2 $
$Date: 2005/10/14 06:44:46 $
$Author: franciscom $
$Name:  $

Migration from 1.6BETA  to 1.6 POST RC1 - 20050925 - fm
bug correction - missing join conditions found by am
*/

/* results table */
UPDATE project TP, component COMP, category CAT, testcase TC, 
       results RES, build 
SET RES.build_id = build.id
WHERE TP.id = COMP.projid 
AND   TP.id = build.projid
AND   COMP.id = CAT.compid
AND   CAT.id = TC.catid
AND   TC.id = RES.tcid
AND   build.BUILD = RES.build;


/* bugs table */
UPDATE project TP, component COMP, category CAT, testcase TC, 
       bugs, build 
SET bugs.build_id = build.id
WHERE TP.id = COMP.projid 
AND   TP.id = build.projid
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