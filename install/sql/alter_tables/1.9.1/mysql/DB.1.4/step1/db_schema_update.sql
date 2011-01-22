/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema MySQL database for TestLink 1.9 from version 1.8 
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * $Id: db_schema_update.sql,v 1.1.2.3 2011/01/22 13:47:31 franciscom Exp $
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * internal revision:
 *
 *  20101215 - franciscom - manula migration from 1.9.0 to 1.9.1
 */

/* update some config data */
INSERT INTO /*prefix*/node_types (id,description) VALUES (10,'requirement_revision');

ALTER TABLE /*prefix*/req_versions ADD COLUMN `revision` smallint(5) unsigned NOT NULL default '1' AFTER `version`;
ALTER TABLE /*prefix*/req_versions ADD COLUMN `log_message` text;
ALTER TABLE /*prefix*/req_versions DROP PRIMARY KEY;
ALTER TABLE /*prefix*/req_versions ADD PRIMARY KEY (`id`);
ALTER TABLE /*prefix*/req_versions COMMENT = 'Updated to TL 1.9.1 - DB 1.4';


CREATE TABLE /*prefix*/req_revisions (
  `parent_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `revision` smallint(5) unsigned NOT NULL default '1',
  `req_doc_id` varchar(64) NULL,   /* it's OK to allow a simple update query on code */
  `name` varchar(100) NULL,
  `scope` text,
  `status` char(1) NOT NULL default 'V',
  `type` char(1) default NULL,
  `active` tinyint(1) NOT NULL default '1',
  `is_open` tinyint(1) NOT NULL default '1',
  `expected_coverage` int(10) NOT NULL default '1',
  `log_message` text,
  `author_id` int(10) unsigned default NULL,
  `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifier_id` int(10) unsigned default NULL,
  `modification_ts` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/req_revisions_uidx1 (`parent_id`,`revision`)
) DEFAULT CHARSET=utf8;

/* ----- END ----- */