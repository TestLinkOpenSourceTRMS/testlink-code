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
CREATE INDEX /*prefix*/executions_idx3 ON  /*prefix*/executions ("tcversion_id");
CREATE INDEX /*prefix*/attachments_idx1 ON  /*prefix*/attachments ("fk_id");