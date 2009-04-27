# TestLink Open Source Project - http://testlink.sourceforge.net/
# $Id: testlink_create_default_data.sql,v 1.26 2009/04/27 07:50:13 franciscom Exp $
# SQL script - create default data (rights & admin account)
#
# Database Type: MySQL 
#
# 20090426 - franciscom - new right testproject_user_role_assignment
# 20090126 - havlatm - role definition update
# 20081029 - franciscom - add events_mgt right
#                         fixed typo error system_configuraton -> system_configuration
# 20070724 - franciscom - BUGID 950 
#            removed right with id=19
#            renamed right with id=5 
#            updated db version- due to changes in milestone table
# ---------------------------------------------------------------------------------

# Database version
INSERT INTO db_version (version,notes,upgrade_ts) VALUES('DB 1.2', 'first version with API feature',CURRENT_TIMESTAMP());

# Node types -
INSERT INTO `node_types` (id,description) VALUES (1, 'testproject');
INSERT INTO `node_types` (id,description) VALUES (2, 'testsuite');
INSERT INTO `node_types` (id,description) VALUES (3, 'testcase');
INSERT INTO `node_types` (id,description) VALUES (4, 'testcase_version');
INSERT INTO `node_types` (id,description) VALUES (5, 'testplan');
INSERT INTO `node_types` (id,description) VALUES (6, 'requirement_spec');
INSERT INTO `node_types` (id,description) VALUES (7, 'requirement');


# Roles -
INSERT INTO `roles` (id,description) VALUES (1, '<reserved system role 1>');
INSERT INTO `roles` (id,description) VALUES (2, '<reserved system role 2>');
INSERT INTO `roles` (id,description) VALUES (3, '<no rights>');
INSERT INTO `roles` (id,description) VALUES (4, 'test designer');
INSERT INTO `roles` (id,description) VALUES (5, 'guest');
INSERT INTO `roles` (id,description) VALUES (6, 'senior tester');
INSERT INTO `roles` (id,description) VALUES (7, 'tester');
INSERT INTO `roles` (id,description) VALUES (8, 'admin');
INSERT INTO `roles` (id,description) VALUES (9, 'leader');

# Rights - 
INSERT INTO `rights` (id,description) VALUES (1 ,'testplan_execute');
INSERT INTO `rights` (id,description) VALUES (2 ,'testplan_create_build');
INSERT INTO `rights` (id,description) VALUES (3 ,'testplan_metrics');
INSERT INTO `rights` (id,description) VALUES (4 ,'testplan_planning');
INSERT INTO `rights` (id,description) VALUES (5 ,'testplan_user_role_assignment');
INSERT INTO `rights` (id,description) VALUES (6 ,'mgt_view_tc');
INSERT INTO `rights` (id,description) VALUES (7 ,'mgt_modify_tc');
INSERT INTO `rights` (id,description) VALUES (8 ,'mgt_view_key');
INSERT INTO `rights` (id,description) VALUES (9 ,'mgt_modify_key');
INSERT INTO `rights` (id,description) VALUES (10,'mgt_view_req');
INSERT INTO `rights` (id,description) VALUES (11,'mgt_modify_req');
INSERT INTO `rights` (id,description) VALUES (12,'mgt_modify_product');
INSERT INTO `rights` (id,description) VALUES (13,'mgt_users');
INSERT INTO `rights` (id,description) VALUES (14,'role_management');
INSERT INTO `rights` (id,description) VALUES (15,'user_role_assignment');
INSERT INTO `rights` (id,description) VALUES (16,'mgt_testplan_create');
INSERT INTO `rights` (id,description) VALUES (17,'cfield_view');
INSERT INTO `rights` (id,description) VALUES (18,'cfield_management');
INSERT INTO `rights` (id,description) VALUES (19,'system_configuration');
INSERT INTO `rights` (id,description) VALUES (20,'mgt_view_events');
INSERT INTO `rights` (id,description) VALUES (21,'mgt_view_usergroups');
INSERT INTO `rights` (id,description) VALUES (22,'events_mgt');
INSERT INTO `rights` (id,description) VALUES (23 ,'testproject_user_role_assignment');


# Rights for Administrator role
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,1 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,2 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,3 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,4 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,5 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,6 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,7 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,8 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,9 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,10);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,11);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,12);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,13);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,14);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,15);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,16);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,17);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,18);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,19);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,20);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,21);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,22);
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,23);
# Rights for guest role
INSERT INTO `role_rights` (role_id,right_id) VALUES (5,3 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (5,6 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (5,8 );

# Rights for test designer role
INSERT INTO `role_rights` (role_id,right_id) VALUES (4,3 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (4,6 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (4,7 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (4,8 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (4,9 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (4,10);
INSERT INTO `role_rights` (role_id,right_id) VALUES (4,11);

# Rights for tester role
INSERT INTO `role_rights` (role_id,right_id) VALUES (7,1 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (7,3 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (7,6 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (7,8 );

# Rights for senior tester role
INSERT INTO `role_rights` (role_id,right_id) VALUES (6,1 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (6,2 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (6,3 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (6,6 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (6,7 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (6,8 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (6,9 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (6,11);

# Rights for leader role
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,1 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,2 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,3 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,4 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,5 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,6 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,7 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,8 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,9 );
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,10);
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,11);
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,15);
INSERT INTO `role_rights` (role_id,right_id) VALUES (9,16);

# admin account 
# SECURITY: change password after first login
INSERT INTO `users` (login,password,role_id,email,first,last,locale,active)
             VALUES ('admin','21232f297a57a5a743894a0e4a801fc3', 8,'', 'Testlink', 'Administrator', 'en_GB',1);

# Assignment types
INSERT INTO assignment_types (id,fk_table,description) VALUES(1,'testplan_tcversions','testcase_execution');
INSERT INTO assignment_types (id,fk_table,description) VALUES(2,'tcversions','testcase_review');


# Assignment status
INSERT INTO assignment_status (id,description) VALUES(1,'open');
INSERT INTO assignment_status (id,description) VALUES(2,'closed');
INSERT INTO assignment_status (id,description) VALUES(3,'completed');
INSERT INTO assignment_status (id,description) VALUES(4,'todo_urgent');
INSERT INTO assignment_status (id,description) VALUES(5,'todo');
