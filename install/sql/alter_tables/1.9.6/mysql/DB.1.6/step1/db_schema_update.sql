/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema MySQL database for TestLink 1.9 from version 1.8 
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * @filesource	db_schema_update.sql
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * @internal revisions
 * @since 1.9.6
 */

# ==============================================================================
# ATTENTION PLEASE - replace /*prefix*/ with your table prefix if you have any. 
# ==============================================================================


/* testprojects */
ALTER TABLE /*prefix*/testprojects ADD COLUMN reqmgr_integration_enabled tinyint(1) NOT NULL default '0' AFTER issue_tracker_enabled;

CREATE TABLE /*prefix*/reqmgrsystems
(
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `type` int(10) default 0,
  `cfg` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY /*prefix*/reqmgrsystems_uidx1 (`name`)
) DEFAULT CHARSET=utf8;

CREATE TABLE /*prefix*/testproject_reqmgrsystem
(
  `testproject_id` int(10) unsigned NOT NULL,
  `reqmgrsystem_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`testproject_id`)
) DEFAULT CHARSET=utf8;



/* new rights */
INSERT INTO /*prefix*/rights  (id,description) VALUES (33,'reqmgrsystem_management');
INSERT INTO /*prefix*/rights  (id,description) VALUES (34,'reqmgrsystem_view');


/* update rights on admin role */
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,33);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,34);
/* ----- END ----- */