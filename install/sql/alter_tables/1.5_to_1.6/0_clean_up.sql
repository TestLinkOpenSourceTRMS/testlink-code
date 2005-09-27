/* Migration from 1.5 to 1.6 POST RC1 - 20050925 - fm*/

DELETE FROM bugs
where bugs.tcid = 0;

DELETE FROM bugs
where NOT EXISTS (select * from  testcase where bugs.tcid = testcase.id);

DELETE FROM bugs
where NOT EXISTS (select * from  build where bugs.build = build.build);

DELETE FROM results
where results.tcid=0;

DELETE FROM results
where NOT EXISTS (select * from testcase where results.tcid = testcase.id);

DELETE FROM results
where NOT EXISTS (select * from build where results.build = build.build);

