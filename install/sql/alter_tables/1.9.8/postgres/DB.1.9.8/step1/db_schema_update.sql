/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * SQL script: Update schema Postgres database for TestLink 1.9.4 from version 1.8.1 
 * "/ *prefix* /" - placeholder for tables with defined prefix, used by sqlParser.class.php.
 *
 * @filesource	db_schema_update.sql
 *
 * Important Warning: 
 * This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
 * It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
 * USE SEMICOLONS ONLY to signal END of SQL Statements.
 *
 * @internal revisions:
 *
 */

-- ==============================================================================
-- ATTENTION PLEASE - replace /*prefix*/ with your table prefix if you have any. 
-- ==============================================================================

/* testprojects */
ALTER TABLE /*prefix*/cfield_testprojects ADD COLUMN "required" INT2 NOT NULL DEFAULT '0';


/* tcversions */
ALTER TABLE /*prefix*/tcversions ADD COLUMN "estimated_exec_duration" numeric(6,2) NULL;

/* executions */
ALTER TABLE /*prefix*/executions ADD COLUMN "execution_duration" numeric(6,2) NULL;


ALTER TABLE /*prefix*/req_coverage ADD COLUMN "author_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id);
ALTER TABLE /*prefix*/req_coverage ADD COLUMN "creation_ts" TIMESTAMP NOT NULL DEFAULT now();
ALTER TABLE /*prefix*/req_coverage ADD COLUMN "review_requester_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id);
ALTER TABLE /*prefix*/req_coverage ADD COLUMN "review_request_ts" TIMESTAMP NULL DEFAULT NULL;
/* ----- END ----- */