/* mysql */
ALTER TABLE /*prefix*/execution_bugs ADD COLUMN `tcstep_id` INT(10) NOT NULL DEFAULT '0' AFTER `bug_id`;
ALTER TABLE /*prefix*/execution_bugs DROP PRIMARY KEY;
ALTER TABLE /*prefix*/execution_bugs ADD PRIMARY KEY (`execution_id`, `bug_id`, `tcstep_id`);