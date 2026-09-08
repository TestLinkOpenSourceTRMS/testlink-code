/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema Postgres database for TestLink 1.9.4 from version 1.9.1
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * @filesource	z_final_step.sql
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * @internal revisions:
 *
 * 20110808 - franciscom - manual migration from 1.9.1 (DB 1.4) to 1.9.4 (DB 1.5)
 */

-- ==============================================================================
-- ATTENTION PLEASE - replace /*prefix*/ with your table prefix if you have any. 
-- ==============================================================================

/* database version update */
INSERT INTO /*prefix*/db_version ("version","upgrade_ts","notes") VALUES ('DB 1.6',now(),'TestLink 1.9.6');
