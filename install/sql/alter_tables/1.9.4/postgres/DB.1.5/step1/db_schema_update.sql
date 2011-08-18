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
 * 20110815 - franciscom - improvements on cookie_string generation (after Julian indications)
 * 20110808 - franciscom - manual migration from 1.9.1 (DB 1.4) to 1.9.4 (DB 1.5)
 */

-- ==============================================================================
-- ATTENTION PLEASE - replace /*prefix*/ with your table prefix if you have any. 
-- ==============================================================================

/* update some config data */
INSERT INTO /*prefix*/node_types ("id","description") VALUES (11,'requirement_spec_revision');

-- TICKET 4661
CREATE TABLE /*prefix*/req_specs_revisions (
  "parent_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/req_specs (id),
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "revision" INTEGER NOT NULL DEFAULT '1',
  "doc_id" VARCHAR(64) NULL,   /* it's OK to allow a simple update query on code */
  "name" VARCHAR(100) NULL,
  "scope" TEXT NULL DEFAULT NULL,
  "total_req" INTEGER NOT NULL DEFAULT '0',
  "status" INTEGER NOT NULL DEFAULT '1',
  "type" CHAR(1) NULL DEFAULT 'N',
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  "log_message" TEXT NULL DEFAULT NULL,
  PRIMARY KEY  ("id")
);
CREATE UNIQUE INDEX /*prefix*/req_specs_revisions_uidx1 ON /*prefix*/req_revisions ("parent_id","revision");

/* Create Req Spec Revision Nodes */
INSERT INTO /*prefix*/nodes_hierarchy 
(parent_id,name,node_type_id)
SELECT RSP.id,NHRSP.name,11
FROM /*prefix*/req_specs RSP JOIN /*prefix*/nodes_hierarchy NHRSP ON NHRSP.id = RSP.id;

/* Populate Req Spec Revisions Table */
INSERT INTO /*prefix*/req_specs_revisions 
(parent_id,doc_id,scope,total_req,type,author_id,creation_ts,id,name)
SELECT RSP.id,RSP.doc_id,RSP.scope,RSP.total_req,RSP.type,RSP.author_id,RSP.creation_ts,
NHRSPREV.id,NHRSPREV.name
FROM /*prefix*/req_specs RSP JOIN /*prefix*/nodes_hierarchy NHRSPREV
ON NHRSPREV.parent_id = RSP.id AND NHRSPREV.node_type_id=11; 

UPDATE /*prefix*/req_specs_revisions SET log_message='Requirement Specification Revision migrated from Testlink <= 1.9.3'; 

/* Drop Columns from Req Specs Table */
ALTER TABLE /*prefix*/req_specs DROP COLUMN scope;
ALTER TABLE /*prefix*/req_specs DROP COLUMN total_req;
ALTER TABLE /*prefix*/req_specs DROP COLUMN type;
ALTER TABLE /*prefix*/req_specs DROP COLUMN author_id;
ALTER TABLE /*prefix*/req_specs DROP COLUMN creation_ts;
ALTER TABLE /*prefix*/req_specs DROP COLUMN modifier_id;
ALTER TABLE /*prefix*/req_specs DROP COLUMN modification_ts;

COMMENT ON TABLE /*prefix*/req_specs IS 'Updated to TL 1.9.4 - DB 1.5';


/* users */
/* 
Issues with MD5(RANDOM()) - solved thanks to stackoverflow 
http://stackoverflow.com/questions/3970795/how-do-you-create-a-random-string-in-postgresql
*/
ALTER TABLE /*prefix*/users ADD COLUMN cookie_string varchar(64) NOT NULL DEFAULT '';
UPDATE /*prefix*/users SET cookie_string=(MD5(RANDOM()::text) || MD5(login));
CREATE UNIQUE INDEX /*prefix*/users_cookie_string ON /*prefix*/users ("cookie_string") ;
COMMENT ON TABLE /*prefix*/users  IS 'Updated to TL 1.9.4 - DB 1.5';
/* ----- END ----- */