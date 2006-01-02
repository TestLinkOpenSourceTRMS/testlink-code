# TestLink Open Source Project - http://testlink.sourceforge.net/
# $Id: testlink_create_default_data.sql,v 1.6 2006/01/02 13:48:42 franciscom Exp $
# SQL script - create default data (rights & admin account)
# --------------------------------------------------------

# admin account 
# SECURITY: change password after first login

INSERT INTO `user` VALUES ('21232f297a57a5a743894a0e4a801fc3', 'admin', 1, 8, '', 'ad', 'min', 'en_GB', 1);


# data for table `rights`

INSERT INTO `rights` VALUES (8, 'admin', 'tp_execute,tp_create_build,tp_metrics,tp_planning,tp_assign_rights,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_modify_key,mgt_view_req,mgt_modify_req,mgt_modify_product,mgt_users');
INSERT INTO `rights` VALUES (9, 'leader', 'tp_execute,tp_create_build,tp_metrics,tp_planning,tp_assign_rights,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_modify_key,mgt_view_req,mgt_modify_req');
INSERT INTO `rights` VALUES (6, 'senior tester', 'tp_execute,tp_metrics,tp_create_build,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_view_req');
INSERT INTO `rights` VALUES (7, 'tester', 'tp_execute,tp_metrics,mgt_view_tc,mgt_view_key,mgt_view_req');
INSERT INTO `rights` VALUES (5, 'guest', 'tp_metrics,mgt_view_tc,mgt_view_key,mgt_view_req');
INSERT INTO `rights` VALUES (4, 'test designer', 'tp_metrics,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_modify_req,mgt_view_req');


INSERT INTO db_version VALUES('1.7.0 Alpha', CURRENT_TIMESTAMP());