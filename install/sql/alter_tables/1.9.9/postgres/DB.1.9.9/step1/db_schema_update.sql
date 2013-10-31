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

INSERT INTO /*prefix*/node_types (id,description) VALUES (12,'build');
INSERT INTO /*prefix*/node_types (id,description) VALUES (13,'platform');
INSERT INTO /*prefix*/node_types (id,description) VALUES (14,'user');

--
-- Table structure for table cfield_build_design_values
--
CREATE TABLE /*prefix*/cfield_design_values(  
  "field_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/custom_fields (id) ON DELETE CASCADE,
  "node_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/builds (id) ON DELETE CASCADE,
  "value" VARCHAR(4000) NOT NULL DEFAULT '',
  PRIMARY KEY ("field_id","node_id")
); 
CREATE INDEX /*prefix*/IX_cfield_design_values ON /*prefix*/cfield_design_values ("node_id");
/* ----- END ----- */