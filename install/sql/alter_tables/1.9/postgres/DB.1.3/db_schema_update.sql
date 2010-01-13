-- $Revision: 1.7 $
-- $Date: 2010/01/13 20:18:15 $
-- $Author: franciscom $
-- $RCSfile: db_schema_update.sql,v $
-- DB: Postgres
--
-- Changing a Column's Default Value
-- ALTER TABLE products ALTER COLUMN price SET DEFAULT 7.77;
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
--
-- internal revision:
--  20100113 - franciscom
--  work started
--
-- Step 1 - Drops if needed

-- Step 2 - new tables
CREATE TABLE /*prefix*/req_versions(  
  "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
  "version" INTEGER NOT NULL DEFAULT '1',
  "scope" TEXT NULL DEFAULT NULL,
  "status" CHAR(1) NOT NULL DEFAULT 'V',
  "type" CHAR(1) NULL DEFAULT NULL,
  "expected_coverage" INTEGER NOT NULL DEFAULT 1,
  "author_id" BIGINT NULL DEFAULT NULL,
  "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
  "modifier_id" BIGINT NULL DEFAULT NULL,
  "modification_ts" TIMESTAMP NULL,
  PRIMARY KEY ("id","version")
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



-- Step 3 - simple structure updates
-- builds
ALTER TABLE /*prefix*/builds ADD COLUMN author_id BIGINT NULL DEFAULT NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN creation_ts TIMESTAMP NOT NULL DEFAULT now();
ALTER TABLE /*prefix*/builds ADD COLUMN release_date DATE NOT NULL;
ALTER TABLE /*prefix*/builds ADD COLUMN closed_on_date DATE NULL;

--- TO BE CHECKED
--- CREATE INDEX /*prefix*/builds_testplan_id ON /*prefix*/builds ("testplan_id");
COMMENT ON TABLE /*prefix*/builds IS 'Updated to TL 1.9.0 Development - DB 1.3';

-- testprojects
ALTER TABLE /*prefix*/testprojects ADD COLUMN is_public INT2 NOT NULL DEFAULT '1';

-- testplans
ALTER TABLE /*prefix*/testplans ADD COLUMN is_public INT2 NOT NULL DEFAULT '1';

-- testplan_tcversions
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN author_id BIGINT NULL DEFAULT NULL;
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN creation_ts TIMESTAMP NOT NULL DEFAULT now();
ALTER TABLE /*prefix*/testplan_tcversions ADD COLUMN platform_id BIGINT NOT NULL DEFAULT '0';
COMMENT ON TABLE /*prefix*/testplan_tcversions IS 'Updated to TL 1.9.0 Development - DB 1.3';

-- cfield_testprojects
ALTER TABLE /*prefix*/cfield_testprojects  ADD COLUMN location INT2 NOT NULL DEFAULT '1';

-- req_spec
ALTER TABLE /*prefix*/req_specs ADD COLUMN doc_id VARCHAR(64) NOT NULL;

-- requirements
ALTER TABLE /*prefix*/requirements ADD COLUMN expected_coverage INTEGER NOT NULL DEFAULT 1;


