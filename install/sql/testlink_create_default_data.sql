# TestLink Open Source Project - http://testlink.sourceforge.net/
# $Id: testlink_create_default_data.sql,v 1.5 2005/11/21 04:35:02 havlat Exp $
# SQL script - create default data (rights & admin account)
# --------------------------------------------------------

# admin account 
# SECURITY: change password after first login

INSERT INTO `user` VALUES ('21232f297a57a5a743894a0e4a801fc3', 'admin', 1, 8, '', 'ad', 'min', 'en_GB');


# data for table `rights`

INSERT INTO `rights` VALUES (8, 'admin', 'tp_execute,tp_create_build,tp_metrics,tp_planning,tp_assign_rights,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_modify_key,mgt_view_req,mgt_modify_req,mgt_modify_product,mgt_users');
INSERT INTO `rights` VALUES (9, 'leader', 'tp_execute,tp_create_build,tp_metrics,tp_planning,tp_assign_rights,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_modify_key,mgt_view_req,mgt_modify_req');
INSERT INTO `rights` VALUES (6, 'senior tester', 'tp_execute,tp_metrics,tp_create_build,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_view_req');
INSERT INTO `rights` VALUES (7, 'tester', 'tp_execute,tp_metrics,mgt_view_tc,mgt_view_key,mgt_view_req');
INSERT INTO `rights` VALUES (5, 'guest', 'tp_metrics,mgt_view_tc,mgt_view_key,mgt_view_req');
INSERT INTO `rights` VALUES (4, 'test designer', 'tp_metrics,mgt_view_tc,mgt_modify_tc,mgt_view_key,mgt_modify_req,mgt_view_req');


INSERT INTO db_version VALUES('1.6.0', CURRENT_TIMESTAMP());