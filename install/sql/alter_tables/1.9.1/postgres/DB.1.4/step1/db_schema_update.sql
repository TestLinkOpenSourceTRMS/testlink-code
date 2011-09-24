-- $Revision: 1.1.2.4 $
-- $Date: 2011/01/22 13:47:31 $
-- $Author: franciscom $
-- $RCSfile: db_schema_update.sql,v $
-- DB: Postgres
--
-- Changing a Column's Default Value
-- ALTER TABLE products ALTER COLUMN price SET DEFAULT 7.77;
--
-- To remove any default value, use:
-- ALTER TABLE products ALTER COLUMN price DROP DEFAULT;
-- This is effectively the same as setting the default to null. 
-- As a consequence, it is not an error to drop a default where one hadn't been defined, 
-- because the default is implicitly the null value.
--
-- Changing a Column's Data Type
-- ALTER TABLE products ALTER COLUMN price TYPE numeric(10,2);
--
-- Important Warning: 
-- This file will be processed by sqlParser.class.php, that uses SEMICOLON to find end of SQL Sentences.
-- It is not intelligent enough to ignore  SEMICOLONS inside comments, then PLEASE
-- USE SEMICOLONS ONLY to signal END of SQL Statements.
--
-- ALTER TABLE table  RENAME TO newtable
--
-- You can also define constraints on the column at the same time, using the usual syntax:
-- ALTER TABLE products ADD COLUMN description text CHECK (description <> '');
-- In fact all the options that can be applied to a column description in CREATE TABLE can be used here. 
-- Keep in mind however that the default value must satisfy the given constraints, or the ADD will fail. 
-- Alternatively, you can add constraints later (see below) after you've filled in the new column correctly.
-- Tip: Adding a column with a default requires updating each row of the table (to store the new column value). 
-- However, if no default is specified, PostgreSQL is able to avoid the physical update. 
-- So if you intend to fill the column with mostly nondefault values, it's best to add the column with no default, 
-- insert the correct values using UPDATE, and then add any desired default as described below.
--
-- 5.5.3. Adding a Constraint
--
-- To add a constraint, the table constraint syntax is used. For example:
-- 
-- ALTER TABLE products ADD CHECK (name <> '');
-- ALTER TABLE products ADD CONSTRAINT some_name UNIQUE (product_no);
-- ALTER TABLE products ADD FOREIGN KEY (product_group_id) REFERENCES product_groups;
-- To add a not-null constraint, which cannot be written as a table constraint, use this syntax:
-- 
-- ALTER TABLE products ALTER COLUMN product_no SET NOT NULL;
-- The constraint will be checked immediately, so the table data must satisfy the constraint before it can be added.
--
--
--
--
--
-- internal revision:
--  20110115 - franciscom - update to 1.9.1 DB 1.4
--													add new node types: requirement_revision
--													new tables: req_revisions
--													set some default value for new columns on old data
--
--
--  20101005 - franciscom - BUGID 3855: Upgrading from 1.8 to 1.9 does not work with PostgreSQL
--                          ALTER TABLE /*prefix*/builds ADD COLUMN release_date DATE NULL;
--  20100705 - asimon - added new column build_id to user_assignments
--  20100308 - franciscom - req_relations table added
--
--  20100113 - franciscom
--  work started
--
--
-- update some config data
INSERT INTO /*prefix*/node_types (id,description) VALUES (10,'requirement_revision');

-- Step 1 - Drops if needed

-- Step 2 - simple structure updates
-- We need to this before creating new table because we have a FK
ALTER TABLE /*prefix*/req_versions ADD COLUMN "revision" INTEGER NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/req_versions ADD COLUMN "log_message" TEXT NULL DEFAULT NULL;
ALTER TABLE /*prefix*/req_versions DROP CONSTRAINT /*prefix*/req_versions_pkey;
ALTER TABLE /*prefix*/req_versions ADD PRIMARY KEY ("id");
COMMENT ON TABLE /*prefix*/req_versions IS 'Updated to TL 1.9.1 - DB 1.4';


-- Step 3 - new tables
--- BUGID 4056
CREATE TABLE /*prefix*/req_revisions(  
  "parent_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/req_versions (id),
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "revision" INTEGER NOT NULL DEFAULT '1',   
  "req_doc_id" VARCHAR(64) NULL,  --- fman - it's OK to allow a simple update query on code ?
  "name" VARCHAR(100) NULL DEFAULT NULL,
  "scope" TEXT NULL DEFAULT NULL,
  "status" CHAR(1) NOT NULL DEFAULT 'V',
  "type" CHAR(1) NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',   --- fman - Need To understand use i.e. just as memory ?
  "is_open" INT2 NOT NULL DEFAULT '1',  --- fman - Need To understand use i.e. just as memory ?
  "expected_coverage" INTEGER NOT NULL DEFAULT 1,
  "log_message" TEXT NULL DEFAULT NULL,
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  PRIMARY KEY ("id")
); 
CREATE UNIQUE INDEX /*prefix*/req_revisions_uidx1 ON /*prefix*/req_revisions ("parent_id","revision");
