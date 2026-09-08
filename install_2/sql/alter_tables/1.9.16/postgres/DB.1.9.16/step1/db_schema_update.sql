-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres   
-- 
--
ALTER TABLE /*prefix*/execution_bugs ADD COLUMN "tcstep_id" BIGINT NOT NULL DEFAULT 0;
ALTER TABLE /*prefix*/execution_bugs DROP CONSTRAINT /*prefix*/execution_bugs_pkey;
ALTER TABLE /*prefix*/execution_bugs ADD PRIMARY KEY ("execution_id","bug_id","tcstep_id");
