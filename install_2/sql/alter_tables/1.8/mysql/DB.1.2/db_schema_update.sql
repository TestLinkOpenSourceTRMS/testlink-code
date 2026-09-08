/* 
$Revision: 1.23 $
$Date: 2009/04/15 12:51:01 $
$Author: havlat $
$RCSfile: db_schema_update.sql,v $

DB: mysql

Important Warning: 
This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
USE SEMICOLONS ONLY to signal END of SQL Statements.

Change/Modify operation on columns:

want to change NAME  -> CHANGE
want to change column properties -> MODIFY

rev: 
     20090123 - havlatm - BUG 2013 (remove right ID=19 before add, it was there a minute in 1.7)
     20081109 - franciscom - added new right events_mgt
     20081018 - franciscom - renamed new events indexes to follow standard naming convention
     20081003 - franciscom - added  CREATE TABLE cfield_testplan_design_values
     20080927 - franciscom - fix bug when migration tcversions.importance
     20080810 - franciscom - ALTER custom_fields table
     20080622 - franciscom - added executions.tcversion_number
*/

/* Step 1 - Drops if needed */
DROP TABLE IF EXISTS priorities;
DROP TABLE IF EXISTS risk_assignments;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS text_templates;
DROP TABLE IF EXISTS test_urgency;
DROP TABLE IF EXISTS user_group;
DROP TABLE IF EXISTS user_group_assign;


/* Step 2 - new tables */
CREATE TABLE `events` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `transaction_id` int(10) unsigned NOT NULL default '0',
  `log_level` smallint(5) unsigned NOT NULL default '0',
  `source` varchar(45) default NULL,
  `description` text NOT NULL,
  `fired_at` int(10) unsigned NOT NULL default '0',
  `activity` varchar(45) default NULL,
  `object_id` int(10) unsigned default NULL,
  `object_type` varchar(45) default NULL,
  PRIMARY KEY  (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `fired_at` (`fired_at`)
) DEFAULT CHARSET=utf8;

CREATE TABLE  transactions (
  id int(10) unsigned NOT NULL auto_increment,
  entry_point varchar(45) NOT NULL default '',
  start_time int(10) unsigned NOT NULL default '0',
  end_time int(10) unsigned NOT NULL default '0',
  user_id int(10) unsigned NOT NULL default '0',
  session_id varchar(45) default NULL,
  PRIMARY KEY  (id)
) DEFAULT CHARSET=utf8;

CREATE TABLE text_templates (
  id int(10) unsigned NOT NULL,
  tpl_type smallint(5) unsigned NOT NULL,
  title varchar(100) NOT NULL,
  template_data text,
  author_id int(10) unsigned default NULL,
  creation_ts datetime NOT NULL default '1900-00-00 01:00:00',
  is_public tinyint(1) NOT NULL default '0',
  UNIQUE KEY idx_text_templates (tpl_type,title)
) DEFAULT CHARSET=utf8 COMMENT='Global Project Templates';


CREATE TABLE user_group (
  id int(10) unsigned NOT NULL,
  title varchar(100) NOT NULL,
  description text,
  owner_id int(10) unsigned NOT NULL,
  testproject_id int(10) unsigned NOT NULL,
  UNIQUE KEY (title)
) DEFAULT CHARSET=utf8;

CREATE TABLE user_group_assign (
  usergroup_id int(10) unsigned NOT NULL,
  user_id int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `cfield_testplan_design_values` (
  field_id int(10) NOT NULL default '0',
  link_id int(10) NOT NULL default '0' COMMENT 'point to testplan_tcversion id',   
  value varchar(255) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`link_id`),
  KEY `idx_cfield_tplan_design_val` (`link_id`)
) DEFAULT CHARSET=utf8;



/* Step 3 - table changes */
/* tcversions */
ALTER TABLE tcversions ADD COLUMN execution_type tinyint(1) default '1' COMMENT '1 -> manual, 2 -> automated';
ALTER TABLE tcversions ADD COLUMN tc_external_id int(10) unsigned NOT NULL; 

-- 20080622 - franciscom
-- Present in 1.7.4
-- MHT: but invalid type (need to reenter)
UPDATE tcversions
SET importance='2'
WHERE importance IN('M','m');

UPDATE tcversions
SET importance='3'
WHERE importance IN('H','h');

UPDATE tcversions
SET importance='1'
WHERE importance IN('L','l');


ALTER TABLE tcversions MODIFY COLUMN importance smallint(5) unsigned NOT NULL default '2';
ALTER TABLE tcversions COMMENT = 'Updated to TL 1.8 RC3 - DB 1.2';

/* testprojects */
ALTER TABLE testprojects ADD COLUMN prefix varchar(30) NULL;
ALTER TABLE testprojects ADD COLUMN tc_counter int(10) unsigned NULL default '0';
ALTER TABLE testprojects ADD COLUMN option_automation tinyint(1) NOT NULL default '0';
ALTER TABLE testprojects COMMENT = 'Updated to TL 1.8 RC3 - DB 1.2';


/* user */
ALTER TABLE users ADD COLUMN script_key varchar(32) NULL;
ALTER TABLE users COMMENT = 'Updated to TL 1.8 RC3 - DB 1.2';

/* executions */
ALTER TABLE executions ADD COLUMN tcversion_number smallint(5) unsigned NOT NULL default '1' COMMENT 'test case version used for this execution' AFTER tcversion_id;
ALTER TABLE executions ADD COLUMN execution_type tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated' AFTER  tcversion_number;
ALTER TABLE executions COMMENT = 'Updated to TL 1.8 RC3 - DB 1.2';

/* testplan_tcversions */
ALTER TABLE testplan_tcversions ADD COLUMN node_order int(10) unsigned NOT NULL default '1' COMMENT 'order in execution tree' AFTER tcversion_id;
ALTER TABLE testplan_tcversions ADD COLUMN urgency smallint(5) unsigned NOT NULL default '2' COMMENT 'test prioritization, default is MEDIUM';
ALTER TABLE testplan_tcversions COMMENT = 'Updated to TL 1.8 RC3  - DB 1.2';


/* db_version */
ALTER TABLE db_version ADD COLUMN notes  text;
ALTER TABLE db_version COMMENT = 'Updated to TL 1.8 RC3 - DB 1.2';

/* requirements */
ALTER TABLE requirements MODIFY COLUMN id int(10) unsigned NOT NULL;

/* req_specs */
ALTER TABLE req_specs MODIFY COLUMN   id int(10) unsigned NOT NULL;

/* custom_fields */
ALTER TABLE custom_fields ADD COLUMN show_on_testplan_design tinyint(3) unsigned NOT NULL default '0' AFTER enable_on_execution;
ALTER TABLE custom_fields ADD COLUMN enable_on_testplan_design tinyint(3) unsigned NOT NULL default '0' AFTER show_on_testplan_design;
ALTER TABLE custom_fields COMMENT = 'Updated to TL 1.8 RC3  - DB 1.2';


/* data update */
DELETE FROM rights WHERE id=19;
INSERT INTO rights (id,description) VALUES (19,'system_configuration');
INSERT INTO rights (id,description) VALUES (20,'mgt_view_events');
INSERT INTO rights (id,description) VALUES (21,'mgt_view_usergroups');
INSERT INTO rights (id,description) VALUES (22,'events_mgt');

DELETE FROM `role_rights` WHERE `right_id`=19;
INSERT INTO role_rights (role_id,right_id) VALUES (8,19);
INSERT INTO role_rights (role_id,right_id) VALUES (8,20);
INSERT INTO role_rights (role_id,right_id) VALUES (8,21);
INSERT INTO role_rights (role_id,right_id) VALUES (8,22);