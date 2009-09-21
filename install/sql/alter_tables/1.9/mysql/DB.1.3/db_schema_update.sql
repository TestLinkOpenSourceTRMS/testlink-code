/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema MySQL database for TestLink 1.9 from version 1.8 
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * $Id: db_schema_update.sql,v 1.6 2009/09/21 09:27:53 franciscom Exp $
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

/* cfield* */

ALTER TABLE /*prefix*/cfield_design_values MODIFY COLUMN value varchar(4000) NOT NULL default '';
ALTER TABLE /*prefix*/cfield_execution_values MODIFY COLUMN value varchar(4000) NOT NULL default '';
ALTER TABLE /*prefix*/cfield_testplan_design_values MODIFY COLUMN value varchar(4000) NOT NULL default '';
  

ALTER TABLE /*prefix*/custom_fields MODIFY COLUMN possible_values varchar(4000) NOT NULL default '';
ALTER TABLE /*prefix*/custom_fields MODIFY COLUMN default_value varchar(4000) NOT NULL default '';


/* testprojects */
ALTER TABLE /*prefix*/testprojects ADD COLUMN is_public tinyint NOT NULL DEFAULT '1';

/* testplans */
ALTER TABLE /*prefix*/testplans ADD COLUMN is_public tinyint NOT NULL DEFAULT '1';

/* builds */
ALTER TABLE /*prefix*/builds ADD COLUMN `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE /*prefix*/builds ADD COLUMN `release_date` date NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN `closed_on_date` date NULL;


/* testplan_tcversions */
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN author_id int(10) unsigned default NULL;
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN creation_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

/* cfield_testprojects */
ALTER TABLE /*prefix*/cfield_testprojects  ADD COLUMN location tinyint NOT NULL DEFAULT '1';

/* data update */
INSERT INTO /*prefix*/rights (id,description) VALUES (24,'project_review');

INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,24);

/* database version update */
INSERT INTO /*prefix*/db_version (version,notes,upgrade_ts) VALUES('DB 1.3', 'TestLink 1.9',CURRENT_TIMESTAMP());

/* ----- END ----- */