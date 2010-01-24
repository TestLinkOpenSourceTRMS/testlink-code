/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema MySQL database for TestLink 1.9 from version 1.8 
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * $Id: db_schema_update.sql,v 1.6 2010/01/24 15:19:27 franciscom Exp $
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * internal revision:
 *
 *  20090919 - franciscom
 *  value size for custom fields
 *
 *  20090717 - franciscom
 *  cfield_testprojects new field location
 *  testprojects new fiels is_public
 *  testplans new fiels is_public
 */

/* update some config data */
INSERT INTO /*prefix*/node_types (id,description) VALUES (8,'requirement_version');
INSERT INTO /*prefix*/node_types (id,description) VALUES (9,'testcase_step');

/* New Tables */
CREATE TABLE /*prefix*/req_versions (
  `id` int(10) unsigned NOT NULL,
  `version` smallint(5) unsigned NOT NULL default '1',
  `scope` text,
  `status` char(1) NOT NULL default 'V',
  `type` char(1) default NULL,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  `expected_coverage` int(10) NOT NULL default '1',
  `author_id` int(10) unsigned default NULL,
  `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`,`version`)
) DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/tcsteps (  
  id int(10) unsigned NOT NULL,
  step_number INT NOT NULL DEFAULT '1',
  actions TEXT,
  expected_results TEXT,
  active tinyint(1) NOT NULL default '1',
  execution_type tinyint(1) NOT NULL default '1' COMMENT '1 -> manual, 2 -> automated',
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/platforms (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  testproject_id INTEGER UNSIGNED NOT NULL,
  notes text NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY /*prefix*/idx_platforms (testproject_id,name)
) DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/testplan_platforms (
  id int(10) unsigned NOT NULL auto_increment,
  testplan_id int(10) unsigned NOT NULL,
  platform_id int(10) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY /*prefix*/idx_testplan_platforms(testplan_id,platform_id)
) DEFAULT CHARSET=utf8 COMMENT='Connects a testplan with platforms';


CREATE TABLE /*prefix*/infrastructure (
  id int(10) unsigned NOT NULL auto_increment,
	`testproject_id` INT( 10 ) UNSIGNED NOT NULL ,
	`owner_id` INT(10) UNSIGNED NOT NULL ,
	`name` VARCHAR(255) NOT NULL ,
	`ipaddress` VARCHAR(255) NOT NULL ,
	`content` TEXT NULL ,
	`creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modification_ts` TIMESTAMP NOT NULL,
	PRIMARY KEY (`id`),
	KEY /*prefix*/infrastructure_idx1 (`testproject_id`)
) DEFAULT CHARSET=utf8; 


/* Step 3 - simple structure updates */

/* builds */
ALTER TABLE /*prefix*/builds ADD COLUMN`author_id` int(10) unsigned default NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE /*prefix*/builds ADD COLUMN `release_date` date NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN `closed_on_date` date NULL;


/* cfield* */
ALTER TABLE /*prefix*/cfield_design_values MODIFY COLUMN value varchar(4000) NOT NULL default '';
ALTER TABLE /*prefix*/cfield_execution_values MODIFY COLUMN value varchar(4000) NOT NULL default '';
ALTER TABLE /*prefix*/cfield_testplan_design_values MODIFY COLUMN value varchar(4000) NOT NULL default '';
  
ALTER TABLE /*prefix*/custom_fields MODIFY COLUMN possible_values varchar(4000) NOT NULL default '';
ALTER TABLE /*prefix*/custom_fields MODIFY COLUMN default_value varchar(4000) NOT NULL default '';

/* cfield_testprojects */
ALTER TABLE /*prefix*/cfield_testprojects  ADD COLUMN location tinyint NOT NULL DEFAULT '1';


/* tcversions */
ALTER TABLE /*prefix*/tcversions ADD COLUMN layout smallint(5) unsigned NOT NULL default '1';
ALTER TABLE /*prefix*/tcversions ADD COLUMN `status` smallint(5) unsigned NOT NULL default '1';
ALTER TABLE /*prefix*/tcversions ADD COLUMN preconditions TEXT NULL;
ALTER TABLE /*prefix*/tcversions COMMENT = 'Updated to TL 1.9.0 - DB 1.3';


/* testprojects */
ALTER TABLE /*prefix*/testprojects ADD COLUMN is_public tinyint NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/testprojects ADD COLUMN `options` text;
ALTER TABLE /*prefix*/testprojects COMMENT = 'Updated to TL 1.9.0 - DB 1.3';

/* testplans */
ALTER TABLE /*prefix*/testplans ADD COLUMN is_public tinyint NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/testplans COMMENT = 'Updated to TL 1.9.0 - DB 1.3';


/* testplan_tcversions */
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN author_id int(10) unsigned default NULL;
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN creation_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN platform_id int(10) unsigned NOT NULL default '0';
ALTER TABLE /*prefix*/testplan_tcversions COMMENT = 'Updated to TL 1.9.0 - DB 1.3';


/* NEED TO ALTER INDEX */
/* 1 - drop old index */
DROP INDEX /*prefix*/tp_tcversion ON /*prefix*/testplan_tcversions;
CREATE UNIQUE INDEX /*prefix*/testplan_tcversions_tplan_tcversion ON /*prefix*/testplan_tcversions (testplan_id,tcversion_id,platform_id);


/* executions */
ALTER TABLE /*prefix*/executions  ADD COLUMN platform_id int(10) unsigned NOT NULL default '0';
ALTER TABLE /*prefix*/executions COMMENT = 'Updated to TL 1.9.0 - DB 1.3';


/* milestones */
ALTER TABLE /*prefix*/milestones ADD COLUMN start_date date NOT NULL default '0000-00-00';
ALTER TABLE /*prefix*/milestones COMMENT = 'Updated to TL 1.9.0 - DB 1.3';

/* req_spec */
ALTER TABLE /*prefix*/req_specs ADD COLUMN doc_id VARCHAR(64) NOT NULL DEFAULT 'RS_DOC_ID';
ALTER TABLE /*prefix*/req_specs COMMENT = 'Updated to TL 1.9.0 - DB 1.3';

/* requirements */
ALTER TABLE /*prefix*/requirements MODIFY COLUMN req_doc_id VARCHAR(64);
ALTER TABLE /*prefix*/requirements COMMENT = 'Updated to TL 1.9.0 - DB 1.3';
/* ----- END ----- */