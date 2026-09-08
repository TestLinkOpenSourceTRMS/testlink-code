--  -----------------------------------------------------------------
--  TestLink Open Source Project - http://testlink.sourceforge.net/
--  @filesource testlink_create_default_data.sql
--  SQL script - create default data (rights & admin account)
--
--  Database Type: Postgres 
--
--  -----------------------------------------------------------------

--  Database version -
INSERT INTO /*prefix*/db_version ("version","upgrade_ts","notes") VALUES ('DB 1.9.18',now(),'TestLink 1.9.18 Gaura');
