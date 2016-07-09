--  -----------------------------------------------------------------------------------
--  TestLink Open Source Project - http://testlink.sourceforge.net/
--  $Id: testlink_create_default_data.sql,v 1.24.2.1 2010/12/11 17:35:06 franciscom Exp $
--  SQL script - create default data (rights & admin account)
--  
-- IMPORTANT NOTE:
-- each NEW TABLE added here NEED TO BE DEFINED in object.class.php getDBTables()
--
-- Database Type: Microsoft SQL Server
--
--  -----------------------------------------------------------------------------------

--  Database version
INSERT INTO /*prefix*/db_version (version,notes,upgrade_ts) VALUES ('DB 1.9.13','Test Link 1.9.13',GETDATE());
