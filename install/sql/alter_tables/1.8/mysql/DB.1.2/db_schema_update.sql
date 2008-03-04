/* 
$Revision: 1.9 $
$Date: 2008/03/04 08:57:52 $
$Author: havlat $
$RCSfile: db_schema_update.sql,v $
*/

/* Step 1 - Drops if needed */
DROP TABLE IF EXISTS `priorities`;
DROP TABLE IF EXISTS `risk_assignments`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `text_templates`;
DROP TABLE IF EXISTS `test_urgency`;
DROP TABLE IF EXISTS `user_group`;
DROP TABLE IF EXISTS `user_group_assign`;


/* Step 2 - new tables */
CREATE TABLE  `events` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `transaction_id` int(10) unsigned NOT NULL default '0',
  `log_level` smallint(5) unsigned NOT NULL default '0',
  `source` varchar(45) NULL,
  `description` text NOT NULL,
  `fired_at` int(10) unsigned NOT NULL default '0',
  `activity` varchar(45) NULL,
  `object_id` int(10) unsigned NULL,
  `object_type` varchar(45) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE  `transactions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `entry_point` varchar(45) NOT NULL default '',
  `start_time` int(10) unsigned NOT NULL default '0',
  `end_time` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `session_id` varchar(45) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `text_templates` (
  `id` int(10) unsigned NOT NULL,
  `tpl_type` smallint(5) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `template_data` text,
  `author_id` int(10) unsigned default NULL,
  `create_ts` datetime NOT NULL default '1900-00-00 01:00:00',
  `is_public` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `idx_text_templates` (`tpl_type`,`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Global Project Templates';

CREATE TABLE `test_urgency` (
  `node_id` int(10) unsigned NOT NULL,
  `testplan_id` int(10) unsigned NOT NULL,
  `urgency` smallint(5) unsigned NOT NULL default '2',
  UNIQUE KEY `idx_test_urgency` (`node_id`,`testplan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Urgence of testing test suite in a Test Plan';


CREATE TABLE `user_group` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `owner_id` int(10) unsigned NOT NULL,
  `testproject_id` int(10) unsigned NOT NULL,
  UNIQUE KEY (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `user_group_assign` (
  `usergroup_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/* Step 3 - table changes */
/* tcversions */
ALTER TABLE tcversions ADD COLUMN execution_type tinyint(1) default '1' COMMENT '1 -> manual, 2 -> automated';
ALTER TABLE tcversions ADD COLUMN `tc_external_id` int(10) unsigned NOT NULL; 
ALTER TABLE tcversions ADD COLUMN `importance` smallint(5) unsigned NOT NULL default '2';
ALTER TABLE tcversions COMMENT = 'Updated to TL 1.8.0 Development - DB 1.2';

/* testprojects */
ALTER TABLE testprojects ADD COLUMN `prefix` varchar(30) NULL;
ALTER TABLE testprojects ADD COLUMN `tc_counter` int(10) unsigned NULL default '0';
ALTER TABLE testprojects ADD COLUMN `option_automation` tinyint(1) NOT NULL default '0';
ALTER TABLE testprojects COMMENT = 'Updated to TL 1.8.0 Development - DB 1.2';


/* user */
ALTER TABLE users ADD COLUMN `script_key` varchar(32) NULL;
ALTER TABLE users COMMENT = 'Updated to TL 1.8.0 Development - DB 1.2';

/* executions */
ALTER TABLE executions ADD COLUMN execution_type tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated' AFTER tcversion_id;
ALTER TABLE executions COMMENT = 'Updated to TL 1.8.0 Development - DB 1.2';

/* db_version */
ALTER TABLE db_version ADD COLUMN notes  text;
ALTER TABLE executions COMMENT = 'Updated to TL 1.8.0 Development - DB 1.2';



/* data update */
INSERT INTO `rights` (id,description) VALUES (19,'system_configuraton');
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,19);

INSERT INTO `rights` (id,description) VALUES (20,'mgt_view_events');
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,20);

INSERT INTO `rights` (id,description) VALUES (21,'mgt_view_usergroups');
INSERT INTO `role_rights` (role_id,right_id) VALUES (8,21);
