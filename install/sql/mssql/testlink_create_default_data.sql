--  -----------------------------------------------------------------------------------
--  TestLink Open Source Project - http://testlink.sourceforge.net/
--  $Id: testlink_create_default_data.sql,v 1.16 2008/11/09 16:29:36 franciscom Exp $
--  SQL script - create default data (rights & admin account)
--  
--  Database Type: Microsoft SQL Server
--
--  20080102 - franciscom - added note on db_version
--  20070724 - franciscom - BUGID 950 
--             removed right with id=19
--             renamed right with id=5 
--             updated db version- due to changes in milestone table
--
--  -----------------------------------------------------------------------------------

--  Database version
INSERT INTO db_version (version,notes,upgrade_ts) VALUES ('DB 1.2','first version with API feature',GETDATE());

--  admin account 
--  SECURITY: change password after first login
INSERT INTO users (login,password,role_id,email,first,last,locale,active)
             VALUES ('admin','21232f297a57a5a743894a0e4a801fc3', 8,'', 'Testlink', 'Administrator', 'en_GB',1);


--  Node types -
SET IDENTITY_INSERT node_types ON
INSERT INTO node_types (id,description) VALUES (1, 'testproject');
INSERT INTO node_types (id,description) VALUES (2, 'testsuite');
INSERT INTO node_types (id,description) VALUES (3, 'testcase');
INSERT INTO node_types (id,description) VALUES (4, 'testcase_version');
INSERT INTO node_types (id,description) VALUES (5, 'testplan');
INSERT INTO node_types (id,description) VALUES (6, 'requirement_spec');
INSERT INTO node_types (id,description) VALUES (7, 'requirement');
SET IDENTITY_INSERT node_types OFF

--  Roles -
SET IDENTITY_INSERT roles ON
INSERT INTO roles (id,description) VALUES (8, 'admin');
INSERT INTO roles (id,description) VALUES (9, 'leader');
INSERT INTO roles (id,description) VALUES (6, 'senior tester');
INSERT INTO roles (id,description) VALUES (7, 'tester');
INSERT INTO roles (id,description) VALUES (5, 'guest');
INSERT INTO roles (id,description) VALUES (4, 'test designer');
INSERT INTO roles (id,description) VALUES (3, '<no rights>');
SET IDENTITY_INSERT roles OFF

--  Rights - 
SET IDENTITY_INSERT rights ON
INSERT INTO rights (id,description) VALUES (1 ,'testplan_execute'      );
INSERT INTO rights (id,description) VALUES (2 ,'testplan_create_build' );
INSERT INTO rights (id,description) VALUES (3 ,'testplan_metrics'      );
INSERT INTO rights (id,description) VALUES (4 ,'testplan_planning'     );
INSERT INTO rights (id,description) VALUES (5 ,'testplan_user_role_assignment'  );
INSERT INTO rights (id,description) VALUES (6 ,'mgt_view_tc'           );
INSERT INTO rights (id,description) VALUES (7 ,'mgt_modify_tc'         );
INSERT INTO rights (id,description) VALUES (8 ,'mgt_view_key'          );
INSERT INTO rights (id,description) VALUES (9 ,'mgt_modify_key'        );
INSERT INTO rights (id,description) VALUES (10,'mgt_view_req'          );
INSERT INTO rights (id,description) VALUES (11,'mgt_modify_req'        );
INSERT INTO rights (id,description) VALUES (12,'mgt_modify_product'    );
INSERT INTO rights (id,description) VALUES (13,'mgt_users'             );
INSERT INTO rights (id,description) VALUES (14,'role_management'       );
INSERT INTO rights (id,description) VALUES (15,'user_role_assignment'  );
INSERT INTO rights (id,description) VALUES (16,'mgt_testplan_create'	);
INSERT INTO rights (id,description) VALUES (17,'cfield_view');
INSERT INTO rights (id,description) VALUES (18,'cfield_management');
INSERT INTO rights (id,description) VALUES (19,'system_configuration');
INSERT INTO rights (id,description) VALUES (20,'mgt_view_events');
INSERT INTO rights (id,description) VALUES (21,'mgt_view_usergroups');
INSERT INTO rights (id,description) VALUES (22,'events_mgt');

SET IDENTITY_INSERT rights OFF

-- Assignment status
SET IDENTITY_INSERT assignment_status ON
INSERT INTO assignment_status (id,description) VALUES(1,'open');
INSERT INTO assignment_status (id,description) VALUES(2,'closed');
INSERT INTO assignment_status (id,description) VALUES(3,'completed');
INSERT INTO assignment_status (id,description) VALUES(4,'todo_urgent');
INSERT INTO assignment_status (id,description) VALUES(5,'todo');
SET IDENTITY_INSERT assignment_status OFF

-- Assignment types
SET IDENTITY_INSERT assignment_types ON
INSERT INTO assignment_types (id,fk_table,description) VALUES(1,'testplan_tcversions','testcase_execution');
INSERT INTO assignment_types (id,fk_table,description) VALUES(2,'tcversions','testcase_review');
SET IDENTITY_INSERT assignment_types OFF

--  Rights for Administrator (admin role)
INSERT INTO role_rights (role_id,right_id) VALUES (8,1 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,2 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,4 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,5 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,7 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,8 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,9 );
INSERT INTO role_rights (role_id,right_id) VALUES (8,10);
INSERT INTO role_rights (role_id,right_id) VALUES (8,11);
INSERT INTO role_rights (role_id,right_id) VALUES (8,12);
INSERT INTO role_rights (role_id,right_id) VALUES (8,13);
INSERT INTO role_rights (role_id,right_id) VALUES (8,14);
INSERT INTO role_rights (role_id,right_id) VALUES (8,15);
INSERT INTO role_rights (role_id,right_id) VALUES (8,16);
INSERT INTO role_rights (role_id,right_id) VALUES (8,17);
INSERT INTO role_rights (role_id,right_id) VALUES (8,18);
INSERT INTO role_rights (role_id,right_id) VALUES (8,19);
INSERT INTO role_rights (role_id,right_id) VALUES (8,20);


--  Rights for guest (guest role)
INSERT INTO role_rights (role_id,right_id) VALUES (5,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (5,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (5,8 );

--  Rights for test dsigner (test designer role)
INSERT INTO role_rights (role_id,right_id) VALUES (4,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (4,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (4,7 );
INSERT INTO role_rights (role_id,right_id) VALUES (4,8 );
INSERT INTO role_rights (role_id,right_id) VALUES (4,11);

--  Rights for tester (tester role)
INSERT INTO role_rights (role_id,right_id) VALUES (7,1 );
INSERT INTO role_rights (role_id,right_id) VALUES (7,3 );

--  Rights for senior tester (senior tester role)
INSERT INTO role_rights (role_id,right_id) VALUES (6,1 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,2 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,7 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,8 );
INSERT INTO role_rights (role_id,right_id) VALUES (6,11);

--  Rights for leader (leader role)
INSERT INTO role_rights (role_id,right_id) VALUES (9,1 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,2 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,3 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,4 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,5 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,6 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,7 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,8 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,9 );
INSERT INTO role_rights (role_id,right_id) VALUES (9,11);
INSERT INTO role_rights (role_id,right_id) VALUES (9,15);
INSERT INTO role_rights (role_id,right_id) VALUES (9,16);
-- 