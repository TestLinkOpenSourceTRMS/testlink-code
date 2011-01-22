-- $Revision: 1.11.2.5 $
-- $Date: 2011/01/22 13:53:25 $
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
--  20101214 - franciscom - update to 1.9.1 DB 1.4
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
INSERT INTO /*prefix*/node_types (id,description) VALUES (8,'requirement_version');
INSERT INTO /*prefix*/node_types (id,description) VALUES (9,'testcase_step');
INSERT INTO /*prefix*/node_types (id,description) VALUES (10,'requirement_revision');

-- Step 1 - Drops if needed

-- Step 2 - new tables
CREATE TABLE /*prefix*/req_versions(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "version" INTEGER NOT NULL DEFAULT '1',
  "revision" INTEGER NOT NULL DEFAULT '1',
  "scope" TEXT NULL DEFAULT NULL,
  "status" CHAR(1) NOT NULL DEFAULT 'V',
  "type" CHAR(1) NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "is_open" INT2 NOT NULL DEFAULT '1',
  "expected_coverage" INTEGER NOT NULL DEFAULT 1,
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  "log_message" TEXT NULL DEFAULT NULL,
  PRIMARY KEY ("id")
); 

CREATE TABLE /*prefix*/"tcsteps" (  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
  "step_number" INT NOT NULL DEFAULT '1',
  "actions" TEXT NULL DEFAULT NULL,
  "expected_results" TEXT NULL DEFAULT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "execution_type" INT2 NOT NULL DEFAULT '1',
  PRIMARY KEY ("id")
); 

CREATE TABLE /*prefix*/platforms (
  id BIGSERIAL NOT NULL,
  name VARCHAR(100) NOT NULL,
  testproject_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id),
  notes text NOT NULL,
  PRIMARY KEY (id),
  UNIQUE (testproject_id,name)
);

CREATE TABLE /*prefix*/testplan_platforms (
  id BIGSERIAL NOT NULL,
  testplan_id BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
  platform_id BIGINT NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE (testplan_id,platform_id)
);


CREATE TABLE /*prefix*/inventory (
	id BIGSERIAL NOT NULL,
	"testproject_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testprojects (id),
	"owner_id" BIGINT NOT NULL REFERENCES  /*prefix*/users (id),
	"name" VARCHAR(255) NOT NULL,
	ipaddress VARCHAR(255) NOT NULL,
	content TEXT NULL ,
	"creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
	"modification_ts" TIMESTAMP NULL,
	PRIMARY KEY (id)
);
CREATE INDEX /*prefix*/inventory_idx1 ON /*prefix*/inventory (testproject_id);
CREATE UNIQUE INDEX /*prefix*/inventory_uidx1 ON /*prefix*/inventory (name,testproject_id);


CREATE TABLE /*prefix*/req_relations (
	id BIGSERIAL NOT NULL,
  source_id INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/requirements (id),
  destination_id  INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/requirements (id),
  relation_type INT2 NOT NULL DEFAULT '1',
  author_id BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
	creation_ts TIMESTAMP NOT NULL DEFAULT now(),
	PRIMARY KEY (id)
);


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


-- Step 3 - simple structure updates

-- user_assigments
ALTER TABLE /*prefix*/user_assignments ADD COLUMN build_id BIGINT NULL DEFAULT NULL;
COMMENT ON TABLE /*prefix*/user_assignments IS 'Updated to TL 1.9.1 - DB 1.4';

-- builds
ALTER TABLE /*prefix*/builds ADD COLUMN author_id BIGINT NULL DEFAULT NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN creation_ts TIMESTAMP NOT NULL DEFAULT now();
ALTER TABLE /*prefix*/builds ADD COLUMN release_date DATE NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN closed_on_date DATE NULL;
--- TO BE CHECKED
--- CREATE INDEX /*prefix*/builds_testplan_id ON /*prefix*/builds ("testplan_id");
COMMENT ON TABLE /*prefix*/builds IS 'Updated to TL 1.9.1 - DB 1.4';

-- cfield_testprojects
ALTER TABLE /*prefix*/cfield_testprojects  ADD COLUMN location INT2 NOT NULL DEFAULT '1';
COMMENT ON TABLE /*prefix*/cfield_testprojects IS 'Updated to TL 1.9.1 - DB 1.4';

ALTER TABLE /*prefix*/cfield_design_values ALTER COLUMN value TYPE varchar(4000);
COMMENT ON TABLE /*prefix*/cfield_design_values IS 'Updated to TL 1.9.1 - DB 1.4';

ALTER TABLE /*prefix*/cfield_execution_values ALTER COLUMN value TYPE varchar(4000);
COMMENT ON TABLE /*prefix*/cfield_execution_values IS 'Updated to TL 1.9.1 - DB 1.4';

ALTER TABLE /*prefix*/cfield_testplan_design_values ALTER COLUMN value TYPE varchar(4000);
COMMENT ON TABLE /*prefix*/cfield_testplan_design_values IS 'Updated to TL 1.9.1 - DB 1.4';
  
ALTER TABLE /*prefix*/custom_fields ALTER COLUMN possible_values TYPE varchar(4000);
ALTER TABLE /*prefix*/custom_fields ALTER COLUMN default_value TYPE varchar(4000);
COMMENT ON TABLE /*prefix*/custom_fields IS 'Updated to TL 1.9.1 - DB 1.4';


-- testprojects
ALTER TABLE /*prefix*/testprojects ADD COLUMN is_public INT2 NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/testprojects ADD COLUMN options TEXT;
COMMENT ON TABLE /*prefix*/testprojects IS 'Updated to TL 1.9.1 - DB 1.4';

-- testplans
ALTER TABLE /*prefix*/testplans ADD COLUMN is_public INT2 NOT NULL DEFAULT '1';
COMMENT ON TABLE /*prefix*/testplans IS 'Updated to TL 1.9.1 - DB 1.4';

-- tcversions
ALTER TABLE /*prefix*/tcversions ADD COLUMN layout INTEGER NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/tcversions ADD COLUMN status INTEGER NOT NULL DEFAULT '1';
ALTER TABLE /*prefix*/tcversions ADD COLUMN preconditions TEXT NULL DEFAULT NULL;
COMMENT ON TABLE /*prefix*/tcversions IS 'Updated to TL 1.9.1 - DB 1.4';


-- testplan_tcversions
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN author_id BIGINT NULL DEFAULT NULL;
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN creation_ts TIMESTAMP NOT NULL DEFAULT now();
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN platform_id BIGINT NOT NULL DEFAULT '0';
COMMENT ON TABLE /*prefix*/testplan_tcversions IS 'Updated to TL 1.9.1 - DB 1.4';

-- executions
ALTER TABLE /*prefix*/executions ADD COLUMN platform_id BIGINT NOT NULL DEFAULT '0';
CREATE INDEX /*prefix*/executions_idx1 ON /*prefix*/executions (testplan_id, tcversion_id, platform_id, build_id);
COMMENT ON TABLE /*prefix*/executions IS 'Updated to TL 1.9.1 - DB 1.4';


-- req_spec
ALTER TABLE /*prefix*/req_specs ADD COLUMN doc_id VARCHAR(64) NOT NULL DEFAULT 'RS_DOC_ID';
COMMENT ON TABLE /*prefix*/req_specs IS 'Updated to TL 1.9.1 - DB 1.4';

-- requirements
ALTER TABLE /*prefix*/requirements ALTER COLUMN req_doc_id TYPE VARCHAR(64);
COMMENT ON TABLE /*prefix*/requirements IS 'Updated to TL 1.9.1 - DB 1.4';

-- milestones
ALTER TABLE /*prefix*/milestones ADD COLUMN start_date DATE NULL;
COMMENT ON TABLE /*prefix*/milestones IS 'Updated to TL 1.9.1 - DB 1.4';

--
ALTER TABLE /*prefix*/testplan_tcversions DROP CONSTRAINT /*prefix*/testplan_tcversions_testplan_id_key;
CREATE UNIQUE INDEX /*prefix*/testplan_tcversions_uidx1 ON /*prefix*/testplan_tcversions (testplan_id,tcversion_id,platform_id);
