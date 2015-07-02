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
ALTER TABLE /*prefix*/testprojects ADD COLUMN "reqmgr_integration_enabled" INT2 NOT NULL DEFAULT '0';

/* New tables */
CREATE TABLE /*prefix*/reqmgrsystems
(
  "id" BIGSERIAL NOT NULL ,
  "name" VARCHAR(100) NOT NULL,
  "type" INTEGER NOT NULL DEFAULT '0',
  "cfg" TEXT,
  PRIMARY KEY  ("id")
);
CREATE UNIQUE INDEX /*prefix*/reqmgrsystems_uidx1 ON /*prefix*/reqmgrsystems ("name");

CREATE TABLE /*prefix*/testproject_reqmgrsystem
(
  "testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id),
  "reqmgrsystem_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/reqmgrsystems (id)
);
CREATE UNIQUE INDEX /*prefix*/testproject_reqmgrsystem_uidx1 ON /*prefix*/testproject_reqmgrsystem ("testproject_id");


/* new rights */
INSERT INTO /*prefix*/rights (id,description) VALUES (33,'reqmgrsystem_management');
INSERT INTO /*prefix*/rights (id,description) VALUES (34,'reqmgrsystem_view');


/* update rights on admin role */
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,33);
INSERT INTO /*prefix*/role_rights (role_id,right_id) VALUES (8,34);
/* ----- END ----- */