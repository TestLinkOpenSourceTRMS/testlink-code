--  -----------------------------------------------------------------------------------
--  TestLink Open Source Project - http://testlink.sourceforge.net/
--  testlink_create_default_data.sql
--  SQL script - create default data (rights & admin account)
--  
--
-- Database Type: Microsoft SQL Server
--
--  -----------------------------------------------------------------------------------

--  Database version
INSERT INTO /*prefix*/db_version (version,notes,upgrade_ts) VALUES ('DB 1.9.16','Test Link 1.9.16 Moka Pot',GETDATE());
