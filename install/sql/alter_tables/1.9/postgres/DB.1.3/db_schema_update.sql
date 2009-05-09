-- $Revision: 1.1 $
-- $Date: 2009/05/09 15:14:57 $
-- $Author: franciscom $
-- $RCSfile: db_schema_update.sql,v $
-- DB: Postgres
--
-- Changing a Column's Default Value
-- ALTER TABLE products ALTER COLUMN price SET DEFAULT 7.77;
-- To remove any default value, use:
-- ALTER TABLE products ALTER COLUMN price DROP DEFAULT;
-- This is effectively the same as setting the default to null. 
-- As a consequence, it is not an error to drop a default where one hadn't been defined, because the default is implicitly the null value.
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
-- Step 1 - Drops if needed


-- Step 2 - new tables

-- Step 3 - table changes

-- testplan_tcversions
ALTER TABLE testplan_tcversions ADD COLUMN author_id BIGINT NULL DEFAULT NULL;
ALTER TABLE testplan_tcversions ADD COLUMN creation_ts TIMESTAMP NOT NULL DEFAULT now();
COMMENT ON TABLE testplan_tcversions IS 'Updated to TL 1.9.0 Development - DB 1.3';

-- users
-- ALTER TABLE users ALTER COLUMN email TYPE VARCHAR(320);

-- builds
ALTER TABLE builds ADD COLUMN author_id BIGINT NULL DEFAULT NULL;
ALTER TABLE builds ADD COLUMN creation_ts TIMESTAMP NOT NULL DEFAULT now();
ALTER TABLE builds ADD COLUMN relase_date DATE NOT NULL;
COMMENT ON TABLE builds IS 'Updated to TL 1.9.0 Development - DB 1.3';



-- db_version
ALTER TABLE db_version ADD COLUMN notes  text;
COMMENT ON TABLE db_version IS 'Updated to TL 1.9.0 Development - DB 1.3';

