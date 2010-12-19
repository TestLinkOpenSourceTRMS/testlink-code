/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema MySQL database for TestLink 1.9 from version 1.8 
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * $Id: db_schema_update.sql,v 1.1 2010/12/19 17:26:00 franciscom Exp $
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * internal revision:
 *
 */

/* update some config data */

/* Step 3 - simple structure updates */

/* cfield_testprojects */
ALTER TABLE /*prefix*/cfield_testprojects ADD COLUMN required tinyint NOT NULL DEFAULT '0';
ALTER TABLE /*prefix*/cfield_testprojects DROP COLUMN required_on_design;
ALTER TABLE /*prefix*/cfield_testprojects DROP COLUMN required_on_execution;
/* ----- END ----- */