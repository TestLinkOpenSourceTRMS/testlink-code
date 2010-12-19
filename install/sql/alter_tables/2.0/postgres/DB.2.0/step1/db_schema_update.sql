-- $Revision: 1.2 $
-- $Date: 2010/12/19 17:51:39 $
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
--  20101219 - franciscom - 
--  work started
--
--
-- update some config data

-- Step 1 - Drops if needed

-- Step 2 - new tables
-- Step 3 - simple structure updates

-- user_assigments
-- builds
-- cfield_testprojects
ALTER TABLE /*prefix*/cfield_testprojects DROP COLUMN required_on_design;
ALTER TABLE /*prefix*/cfield_testprojects DROP COLUMN required_on_execution;
COMMENT ON TABLE /*prefix*/cfield_testprojects IS 'Updated to TL 2.0.0 - DB 2.0';

-- custom_fields
ALTER TABLE /*prefix*/custom_fields ADD COLUMN required INT2 NOT NULL DEFAULT '0';
COMMENT ON TABLE /*prefix*/custom_fields IS 'Updated to TL 2.0.0 - DB 2.0';

