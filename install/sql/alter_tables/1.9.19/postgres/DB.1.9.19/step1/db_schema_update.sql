-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
--
-- SQL script - Postgres   
-- 
--
--
-- Table structure for table "execution_tcsteps_wip"
--
CREATE TABLE /*prefix*/execution_tcsteps_backup (
   "id" BIGSERIAL NOT NULL ,
   "tcstep_id" INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/tcsteps (id),
   "testplan_id" int(10) INTEGER NOT NULL DEFAULT '0' REFERENCES  /*prefix*/testplans (id),
   "platform_id" int(10) INTEGER NOT NULL DEFAULT '0',
   "build_id" INTEGER NOT NULL DEFAULT '0',
   "tester_id" BIGINT NULL DEFAULT NULL,
   "backup_date" TIMESTAMP NOT NULL DEFAULT now(),
   "notes" TEXT NULL DEFAULT NULL,
   "status" CHAR(1) NULL DEFAULT NULL,
  PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX /*prefix*/execution_tcsteps_backup_uidx1 ON  /*prefix*/execution_tcsteps_wip ("tcstep_id","testplan_id","platform_id","build_id");
