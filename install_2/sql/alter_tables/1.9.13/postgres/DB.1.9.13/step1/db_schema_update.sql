-- TestLink Open Source Project - http://testlink.sourceforge.net/
-- This script is distributed under the GNU General Public License 2 or later.
-- $Id: testlink_create_tables.sql,v 1.63.2.2 2010/12/11 17:25:21 franciscom Exp $
--
-- SQL script - Postgres   
-- 
-- IMPORTANT NOTE:
-- each NEW TABLE added here NEED TO BE DEFINED in object.class.php getDBTables()
--
--
--
ALTER TABLE /*prefix*/execution_tcsteps DROP CONSTRAINT /*prefix*/execution_tcsteps_pkey;
ALTER TABLE /*prefix*/execution_tcsteps ADD COLUMN id SERIAL;
UPDATE /*prefix*/execution_tcsteps SET id = DEFAULT;
CREATE UNIQUE INDEX /*prefix*/execution_tcsteps_uidx1 ON  /*prefix*/execution_tcsteps ("execution_id","tcstep_id");